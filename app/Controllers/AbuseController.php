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
        $status = $this->request->getGet('status');
        $model = model(AbuseReportModel::class);
        $responseModel = model(\App\Models\AbuseReportResponseModel::class);

        // Stat counts
        $stats = [
            'total'       => $model->countAllResults(false),
            'open'        => $model->where('status', 'pending')->countAllResults(false),
            'in_review'   => $model->where('status', 'under_review')->countAllResults(false),
            'actioned'    => $model->where('status', 'resolved')->countAllResults(false),
        ];

        $statusMap = [
             'pending'      => 'OPEN',
             'under_review' => 'UNDER_REVIEW',
             'resolved'     => 'ACTIONED',
             'rejected'     => 'CLOSED',
        ];

        // Reports table — latest 200, joined with reporter
        $reports = $model->db
            ->table('abuse_reports ar')
            ->select('ar.*, u.full_name AS reporter_name, u.email AS reporter_email')
            ->join('users u', 'u.id = ar.user_id')
            ->where('ar.deleted_at', null)
            ->orderBy('ar.created_at', 'DESC')
            ->get()
            ->getResultArray();

            foreach ($reports as &$report) {

                $responses = $responseModel
                    ->select('abuse_report_responses.*, users.full_name, users.role')
                    ->join('users', 'users.id = abuse_report_responses.user_id', 'left')
                    ->where('abuse_report_responses.report_id', $report['id'])
                    ->orderBy('abuse_report_responses.created_at', 'ASC')
                    ->findAll();

                $timeline = [];
                $userModel = model(\App\Models\UserModel::class);
                $reporter = $userModel->find($report['user_id']);
                $timeline[] = [
                    'event' => 'Report submitted by ' . ($reporter['full_name'] ?? 'User'),
                    'time'  => date('M d, Y H:i', strtotime($report['created_at'])),
                    'color' => '#16a34a',
                ];

                foreach ($responses as $r) {

                    $isAdmin = ($r['role'] ?? '') === 'admin';

                    $timeline[] = [
                        'event' => ($isAdmin ? 'Admin response' : 'Staff response') . ': ' . strip_tags($r['message']),
                        'time'  => date('M d, Y H:i', strtotime($r['created_at'])),
                        'color' => $isAdmin ? '#111827' : '#179e4f',
                    ];
                }

                $report['timeline'] = $timeline;
            }
            unset($report);
        
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
            'status'     => $statusMap[$status] ?? 'all',
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
             'pending'      => 'OPEN',
             'under_review' => 'UNDER_REVIEW',
             'resolved'     => 'ACTIONED',
             'rejected'     => 'CLOSED',
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
        
        $responseModel = new \App\Models\AbuseReportResponseModel();

        $responses = $responseModel
            ->where('report_id', $id)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return view('admin/view-report.php', [
            'report'            => $report,
            'responses'         => $responses,
            'domainReportCount' => $domainReportCount,
            'lastDomainReport'  => $lastDomainReport,
        ]);
    }

    public function status($id) 
    {
        $reportModel = new \App\Models\AbuseReportModel();

        $report = $reportModel->find((int) $id);

        if (! $report) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $newStatus = $this->request->getPost('status');

        if (! in_array($newStatus, ['pending', 'under_review', 'resolved', 'rejected'])) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $reportModel->update($report['id'], [
            'status' => $newStatus,
        ]);

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    public function add_response($id) 
    {
        $reportModel   = new \App\Models\AbuseReportModel();
        $responseModel = new \App\Models\AbuseReportResponseModel();

        
        // Make sure the report exists
        $report = $reportModel->where('id', $id)->first();
        
        if (!$report) {
            return redirect()->back()->with('error', 'Response message cannot be empty.');
        }

        $message = $this->request->getPost('message');
        $ccEmails = $this->request->getPost('cc_emails');
               

        if (empty($message)) {
            return redirect()->back()->with('error', 'Response message cannot be empty.');
        }

        // Save the response
        $responseModel->insert([
            'report_id' => $id,
            'user_id'   => session()->get('admin_id'),
            'message'   => $message,  
        ]);

            if ($this->request->getPost('notify_reporter')) {
                

            $emailService = \Config\Services::email();
              

            if (!empty($reportWithReporter['reporter_email'])) 
            {
               
                if (!empty($ccEmails)) {

                    // convert "a@x.com, b@x.com" → array
                    $ccArray = array_map('trim', explode(',', $ccEmails));

                    // remove invalid empty values
                    $ccArray = array_filter($ccArray);

                    if (!empty($ccArray)) {
                        $emailService->setCC($ccArray);
                    }
                }
                $emailService
                    ->setTo($reportWithReporter['reporter_email'])
                    ->setSubject("{$report['ticket_id']} - Update on your abuse report")
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
                'error' => [
                    'message' => 'Invalid file.'
                ]
            ]);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (! in_array($file->getMimeType(), $allowed)) {
            return $this->response->setJSON([
                'error' => [
                    'message' => 'Only image files are allowed.'
                ]
            ]);
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/responses/', $newName);

        $url = base_url("uploads/responses/{$newName}");

        return $this->response->setJSON([
            'url' => $url
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
