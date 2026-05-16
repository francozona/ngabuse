<?php

namespace App\Controllers;
use App\Models\AbuseReportModel;

class AbuseController extends BaseController
{
    public function home(): string
    {
        return view('pages/home.php');
    }

    public function dashboard(): string
    {
        $model = model(AbuseReportModel::class);

        // Stat counts
        $stats = [
            'total'       => $model->countAllResults(false),
            'open'        => $model->where('status', 'pending')->countAllResults(false),
            'in_review'   => $model->where('status', 'under_review')->countAllResults(false),
            'actioned'    => $model->where('status', 'resolved')->countAllResults(false),
        ];

        // Reports table — latest 200, joined with reporter
        $reports = $model->db
            ->table('abuse_reports ar')
            ->select('ar.*, u.full_name AS reporter_name, u.email AS reporter_email')
            ->join('users u', 'u.id = ar.user_id')
            ->where('ar.deleted_at', null)
            ->orderBy('ar.created_at', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        // Weekly submissions — last 8 weeks
        $weekly = $model->db->query("
            SELECT
                CONCAT('W', WEEK(created_at) - WEEK(DATE_SUB(NOW(), INTERVAL 8 WEEK)) + 1) AS label,
                COUNT(*) AS count
            FROM abuse_reports
            WHERE deleted_at IS NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
            GROUP BY WEEK(created_at)
            ORDER BY WEEK(created_at)
        ")->getResultArray();

        // Category breakdown
        $categories = $model->db
            ->table('abuse_reports')
            ->select('abuse_category AS name, COUNT(*) AS count')
            ->where('deleted_at', null)
            ->groupBy('abuse_category')
            ->get()
            ->getResultArray();

        return view('admin/dashboard.php', [
            'stats'      => $stats,
            'reports'    => $reports,
            'weekly'     => $weekly,
            'categories' => $categories,
        ]);
    }

    public function all_reports(): string
    {
        $model = model(AbuseReportModel::class);

        // Stat counts
        $stats = [
            'total'       => $model->countAllResults(false),
            'open'        => $model->where('status', 'pending')->countAllResults(false),
            'in_review'   => $model->where('status', 'under_review')->countAllResults(false),
            'actioned'    => $model->where('status', 'resolved')->countAllResults(false),
        ];

        // Reports table — latest 200, joined with reporter
        $reports = $model->db
            ->table('abuse_reports ar')
            ->select('ar.*, u.full_name AS reporter_name, u.email AS reporter_email')
            ->join('users u', 'u.id = ar.user_id')
            ->where('ar.deleted_at', null)
            ->orderBy('ar.created_at', 'DESC')
            ->limit(200)
            ->get()
            ->getResultArray();

        // Weekly submissions — last 8 weeks
        $weekly = $model->db->query("
            SELECT
                CONCAT('W', WEEK(created_at) - WEEK(DATE_SUB(NOW(), INTERVAL 8 WEEK)) + 1) AS label,
                COUNT(*) AS count
            FROM abuse_reports
            WHERE deleted_at IS NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
            GROUP BY WEEK(created_at)
            ORDER BY WEEK(created_at)
        ")->getResultArray();

        // Category breakdown
        $categories = $model->db
            ->table('abuse_reports')
            ->select('abuse_category AS name, COUNT(*) AS count')
            ->where('deleted_at', null)
            ->groupBy('abuse_category')
            ->get()
            ->getResultArray();

        return view('admin/reports.php', [
            'stats'      => $stats,
            'reports'    => $reports,
            'weekly'     => $weekly,
            'categories' => $categories,
        ]);
    }

    public function view_report($type, $id): string
    {
        $reportModel = new \App\Models\AbuseReportModel();

        $report = $reportModel->findWithReporter((int) $id);

        if (! $report) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $statusMap = [
            'UNDER_REVIEW' => 'UNDER_REVIEW',
            'ACTIONED'     => 'ACTIONED',
            'CLOSED'       => 'CLOSED',
            'ESCALATE'     => 'ESCALATE', 
        ];

        $type = strtoupper($type);

        if (array_key_exists($type, $statusMap)) {
            $reportModel->update($report['id'], [
                'status' => $statusMap[$type],
            ]);

             $report = $reportModel->findWithReporter((int) $id);
        }

        $domainReportCount = $reportModel
            ->where('full_domain', $report['full_domain'])
            ->countAllResults();

        $lastDomainReportRow = $reportModel
            ->select('created_at')
            ->where('full_domain', $report['full_domain'])
            ->where('id !=', $report['id'])
            ->orderBy('created_at', 'DESC')
            ->first();

        $lastDomainReport = $lastDomainReportRow
            ? date('M d, Y', strtotime($lastDomainReportRow['created_at']))
            : 'No other reports';

        return view('admin/view-report.php', [
            'report'            => $report,
            'responses'         => [],        
            'domainReportCount' => $domainReportCount,
            'lastDomainReport'  => $lastDomainReport,
        ]);
    }

    public function add_response(int $id) 
    {
        $reportModel   = new \App\Models\AbuseReportModel();
        $responseModel = new \App\Models\AbuseReportResponseModel();

        // Make sure the report exists
        $report = $reportModel->find($id);
        if (! $report) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $message = $this->request->getPost('message');

        if (empty(trim(strip_tags($message)))) {
            return redirect()->back()->with('error', 'Response message cannot be empty.');
        }

        // Save the response
        $responseModel->insert([
            'report_id' => $id,
            'user_id'   => auth()->id(),
            'message'   => $message,  
        ]);

        if ($this->request->getPost('notify_reporter')) {
            $reportWithReporter = $reportModel->findWithReporter($id);

            if (! empty($reportWithReporter['reporter_email'])) {
                \Config\Services::email()
                    ->setTo($reportWithReporter['reporter_email'])
                    ->setSubject("Update on your abuse report: {$report['ticket_id']}")
                    ->setMessage($message)
                    ->setMailType('html')
                    ->send();
            }
        }

        return redirect()->to("/report/VIEW/{$id}")->with('success', 'Response sent successfully.');
    }

    public function upload_response_image()
    {
        $file = $this->request->getFile('upload');

        if (! $file || ! $file->isValid()) {
            return $this->response->setJSON([
                'error' => ['message' => 'Invalid file upload.'],
            ]);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowed)) {
            return $this->response->setJSON([
                'error' => ['message' => 'Only image files are allowed.'],
            ]);
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/responses/', $newName);

        return $this->response->setJSON([
            'url' => base_url("uploads/responses/{$newName}"),
        ]);
    }
    
    public function login(): string
    {
        return view('admin/login.php');
    }

    public function logout(): string
    {
        return view('admin/home.php');
    }
}
