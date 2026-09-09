<?php

namespace App\Controllers;
use App\Models\AbuseReportModel;

class AbuseController extends BaseController
{
    public function home(): string
    {
        return view('pages/welcome.php');
    }

    public function report(): string
    {
        return view('pages/report.php');
    }

   public function attack_chart(): string
    {
        $model = new \App\Models\AbuseReportModel(); // swap for your actual model

        [$from, $to] = $this->resolveDateRange();

        $builder = $model->select('abuse_category')
            ->where('abuse_category IS NOT NULL');

        if ($from && $to) {
            $builder->where('date_first_observed >=', $from)
                    ->where('date_first_observed <=', $to);
        }

        $rows = $builder->findAll();

        $counts = [];
        foreach ($rows as $row) {
            $category = trim($row['abuse_category'] ?? '') ?: 'Uncategorized';
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }
        arsort($counts);

        return view('admin/attack_chart.php', [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
            'total'  => array_sum($counts),
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    public function domain_chart(): string
        {
        $model = new \App\Models\AbuseReportModel(); // swap for your actual model

        [$from, $to] = $this->resolveDateRange();
        $limit = (int) ($this->request->getGet('limit') ?: 15);

        $builder = $model->select('full_domain');

        if ($from && $to) {
            $builder->where('date_first_observed >=', $from)
                    ->where('date_first_observed <=', $to);
        }

        $rows = $builder->findAll();

        $counts = [];
        foreach ($rows as $row) {
            $domain = trim($row['full_domain'] ?? '');
            if ($domain === '') {
                continue;
            }
            $counts[$domain] = ($counts[$domain] ?? 0) + 1;
        }
        arsort($counts);
        $counts = array_slice($counts, 0, $limit, true);

        return view('admin/domain_chart.php', [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
            'total'  => array_sum($counts),
            'from'   => $from,
            'to'     => $to,
            'limit'  => $limit,
        ]);
    }

    /**
     * Reads ?from=YYYY-MM-DD&to=YYYY-MM-DD&range=7|30|90 from query string.
     * 'range' presets override explicit from/to if present.
     * Returns [from, to] as 'Y-m-d H:i:s' strings, or [null, null] for "all time".
     */
    private function resolveDateRange(): array
    {
        $range = $this->request->getGet('range');
        $from  = $this->request->getGet('from');
        $to    = $this->request->getGet('to');

        if ($range && $range !== 'all') {
            $to   = date('Y-m-d 23:59:59');
            $from = date('Y-m-d 00:00:00', strtotime("-{$range} days"));
            return [$from, $to];
        }

        if ($from && $to) {
            return [
                date('Y-m-d 00:00:00', strtotime($from)),
                date('Y-m-d 23:59:59', strtotime($to)),
            ];
        }

        return [null, null]; // all time
    }

    public function registrar_chart(): string
    {
        $model = new \App\Models\AbuseReportModel(); // swap for your actual model

        [$from, $to] = $this->resolveDateRange();

        $builder = $model->select('registrar_email')
            ->where('registrar_email IS NOT NULL');

        if ($from && $to) {
            $builder->where('date_first_observed >=', $from)
                    ->where('date_first_observed <=', $to);
        }

        $reports = $builder->findAll();

        $counts = [];

        foreach ($reports as $report) {
            $email = trim($report['registrar_email'] ?? '');

            if ($email === '') {
                continue;
            }

            $registrar =  $email;
            $registrar = ucfirst(strtolower($registrar));

            $counts[$registrar] = ($counts[$registrar] ?? 0) + 1;
        }

        arsort($counts);

        return view('admin/registrar_chart.php', [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
            'total'  => array_sum($counts),
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    public function dashboard(): string
    {
        $model = model(AbuseReportModel::class);

        // Stat counts
        $stats = [
            'total'       => $model->countAllResults(false),
            'open'        => $model->where('status', 'pending')->countAllResults(false),
            'in_review'   => $model->where('status', 'assigned_to_registrar')->countAllResults(false),
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
        $domain = $this->request->getGet('domain');
        // Stat counts
        $stats = [
            'total'       => $model->countAllResults(false),
            'open'        => $model->where('status', 'pending')->countAllResults(false),
            'in_review'   => $model->where('status', 'assigned_to_registrar')->countAllResults(false),
            'actioned'    => $model->where('status', 'resolved')->countAllResults(false),
        ];

        $statusMap = [
             'pending'      => 'OPEN',
             'assigned_to_registrar' => 'ASSIGNED_TO_REGISTRAR',
             'resolved'     => 'ACTIONED',
             'rejected'     => 'CLOSED',
        ];

        // Reports table — latest 200, joined with reporter
        $reports = [];
        
        if($domain)
        {
             $reports = $model->db
                ->table('abuse_reports ar')
                ->select('ar.*, u.full_name AS reporter_name, u.email AS reporter_email')
                ->where('full_domain', $domain)
                ->join('users u', 'u.id = ar.user_id')
                ->where('ar.deleted_at', null)
                ->orderBy('ar.created_at', 'DESC')
                ->get()
                ->getResultArray();
        }
        else
        {
             $reports = $model->db
                    ->table('abuse_reports ar')
                    ->select('ar.*, u.full_name AS reporter_name, u.email AS reporter_email')
                    ->join('users u', 'u.id = ar.user_id')
                    ->where('ar.deleted_at', null)
                    ->orderBy('ar.created_at', 'DESC')
                    ->get()
                    ->getResultArray();

        }
       

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
                        'event' => ($isAdmin ? 'Admin ' : 'Registrar ') . ': ' . strip_tags($r['message']),
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

    public function uploadImage()
    {
        $file = $this->request->getFile('upload');

        if ($file && $file->isValid() && !$file->hasMoved()) {

            $newName = $file->getRandomName();

            $file->move(FCPATH . 'uploads/', $newName);

            return $this->response->setJSON([
                'uploaded' => true,
                'url' => base_url('uploads/' . $newName)
            ]);
        }

        return $this->response->setJSON([
            'error' => [
                'message' => 'Image upload failed'
            ]
        ]);
    }

    public function share_report($type, $id): string
    {
        $reportModel = new \App\Models\AbuseReportModel();

        $auth = session()->get('auth_registrar');

        $report = $reportModel->where('ticket_id', $id)->first();

        if (!$report) {
            return redirect()->back();
        }

        $statusMap = [
             'pending'      => 'OPEN',
             'assigned_to_registrar' => 'ASSIGNED_TO_REGISTRAR',
             'resolved'     => 'ACTIONED',
             'rejected'     => 'CLOSED',
        ];

        $passed = $type;

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
            ->where('report_id', $report['id'])
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return view('admin/share-report.php', [
            'report'            => $report,
            'auth'              => $auth,
            'type'              => $passed,
            'responses'         => $responses,
            'domainReportCount' => $domainReportCount,
            'lastDomainReport'  => $lastDomainReport,
        ]);
    }

    public function view_report($type, $id): string
    {
        $reportModel = new \App\Models\AbuseReportModel();

        $report = $reportModel->findWithReporter((int) $id);

        if (! $report) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userModel = new \App\Models\UserModel();
        
        $user = $userModel->find($report['user_id']);

        $statusMap = [
             'pending'      => 'OPEN',
             'assigned_to_registrar' => 'ASSIGNED_TO_REGISTRAR',
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
            'user'              => $user,
            'responses'         => $responses,
            'domainReportCount' => $domainReportCount,
            'lastDomainReport'  => $lastDomainReport,
        ]);
    }

    public function fetch_registrar_details($id) 
    {
        $reportModel = new \App\Models\AbuseReportModel();

        $report = $reportModel->find((int) $id);

        if (! $report) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userModel = model(\App\Models\UserModel::class);

        $user = $userModel
            ->where('id', $report['user_id'])
            ->first();

        $registrar_email = $this->get_whois_abuse_email($report['full_domain']);

        $password = substr(str_replace('-', '', bin2hex(random_bytes(16))), 0, 10);

        $reportModel->update($report['id'], [
            'registrar_email' => $registrar_email,
        ]);

        $userModel->update($user['id'], [
            'raw_password' => $password,
        ]);
        
        return redirect()->back()->with('success', 'Fetched details successfully.');
    }

    public function status($id) 
    {
        $reportModel = new \App\Models\AbuseReportModel();

        $report = $reportModel->find((int) $id);

        if (! $report) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $newStatus = $this->request->getPost('status');

        if (! in_array($newStatus, ['pending', 'assigned_to_registrar', 'resolved', 'rejected'])) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $statusText = ucfirst($newStatus);
 
        $userModel = model(\App\Models\UserModel::class);

        $user = $userModel
            ->where('id', $report['user_id'])
            ->first();

        $name = esc($user['full_name'] ?? 'User');

        $emailService = \Config\Services::email();
        $base_url      = base_url();
        $url_registrar = $base_url . "domain-abuse/registrar/" . $report['ticket_id'];
         
        $reportModel->update($report['id'], [
            'status' => $newStatus,
        ]);
        
    
        if( $newStatus == 'assigned_to_registrar') 
        {
            
            $reported_domain = $report['full_domain'];             
            $abuse_category  = $report['abuse_type'] ?? 'Abuse';  

$message_registrar = '
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>New Abuse Complaint</title></head>
<body style="margin:0;padding:0;background:#fff;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff;padding:30px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;">

      <!-- Header -->
      <tr>
        <td style="background:#fff;padding:30px;text-align:center;">
          <div style="margin-bottom:15px;background:white;padding:10px;border-radius:10px;">
            <img src="https://nira.org.ng/wp-content/uploads/2022/01/nira-logo.fw_.png"
                 alt="NiRA Logo" style="max-height:70px;">
          </div>
          <h1 style="margin:0;color:#000000;font-size:24px;">New Abuse Complaint Filed</h1>
          <p style="margin:8px 0 0 0;color:gray;font-size:14px;">Action may be required on your end</p>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="padding:40px 35px;color:#333333;">

          <p style="margin-top:0;font-size:16px;">Dear Registrar,</p>

          <p style="font-size:15px;line-height:1.7;">
            A domain abuse complaint has been submitted to NiRA regarding a domain registered under your account.
            Please review the details below and take appropriate action within <strong>5 business days</strong>.
          </p>

          <!-- Complaint details box -->
          <table cellpadding="0" cellspacing="0" style="margin:25px 0;width:100%;background:#fef2f2;
                 border-radius:8px;border-left:4px solid #179e4f;">
            <tr><td style="padding:20px;">

              <p style="margin:0 0 14px 0;font-size:13px;font-weight:bold;color:#179e4f;
                         text-transform:uppercase;letter-spacing:1px;">Complaint Details</p>

              <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;">
                <tr>
                  <td style="padding:6px 0;color:#6b7280;width:40%;">Ticket ID</td>
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . $report['ticket_id'] . '</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;color:#6b7280;">Reported Domain</td>
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . $reported_domain . '</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;color:#6b7280;">Abuse Category</td>
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . $abuse_category . '</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;color:#6b7280;">Date Filed</td>
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . date('d M Y, H:i') . ' UTC</td>
                </tr>
              </table>

            </td></tr>
          </table>

          <p style="margin:0 0 14px 0;font-size:13px;font-weight:bold;color:#179e4f;
                         text-transform:uppercase;letter-spacing:1px;">Login Details</p>
            <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;">
                <tr>
                  <td style="padding:6px 0;color:#6b7280;width:40%;">Email</td>
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . $report['registrar_email']. '</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;color:#6b7280;">Password</td>
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . $user['raw_password'] . '</td>
                </tr>
               
              </table>

          <p style="font-size:15px;line-height:1.7;">
            You can view the full complaint details and update the ticket status using the button below:
          </p>

          <div style="margin:25px 0;">
            <a href="' . $url_registrar . '"
               style="background:#179e4f;color:#ffffff;text-decoration:none;padding:14px 28px;
                      border-radius:8px;display:inline-block;font-size:14px;font-weight:bold;">
              View Complaint &amp; Respond
            </a>
          </div>

          <!-- What to do box -->
          <table cellpadding="0" cellspacing="0" style="margin:25px 0;width:100%;background:#f8fafc;
                 border-radius:8px;border:1px solid #e5e7eb;">
            <tr><td style="padding:20px;">
              <p style="margin:0 0 12px 0;font-size:13px;font-weight:bold;color:#374151;
                         text-transform:uppercase;letter-spacing:1px;">Expected Actions</p>
              <ul style="margin:0;padding-left:18px;font-size:14px;color:#374151;line-height:2;">
                <li>Investigate the reported domain for the alleged abuse</li>
                <li>Notify or suspend the domain registrant if abuse is confirmed</li>
                <li>Update the ticket status on the NiRA Abuse Portal</li>
                <li>Contact NiRA if you require further information</li>
              </ul>
            </td></tr>
          </table>

          <p style="font-size:14px;line-height:1.7;color:#6b7280;">
            Failure to respond within the stipulated timeframe may result in NiRA taking direct registry-level action
            on the reported domain in accordance with the NiRA Abuse Policy.
          </p>

        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="padding:25px 35px;background:#f9fafb;border-top:1px solid #e5e7eb;">
          <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.6;">
            This notification was sent by the Nigeria Internet Registration Association (NiRA) Abuse Management System.
          </p>
          <p style="margin:6px 0 0 0;font-size:12px;color:#9ca3af;">
            © ' . date('Y') . ' NiRA. All rights reserved. &nbsp;|&nbsp;
            <a href="https://nira.org.ng" style="color:#9ca3af;">nira.org.ng</a>
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>';
            try 
            {
    
                $emailService
                        ->setTo($report['registrar_email'])
                        ->setSubject("New Abuse Ticket Assigned {$report['ticket_id']}")
                        ->setMessage($message_registrar)
                        ->setMailType('html')
                        ->send();

            } 
            catch (\Throwable $e) {

                log_message('error', 'Email exception: ' . $e->getMessage());
            }
        }

      
       

$message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
</head>
<body style='margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;'>

<table width='100%' cellpadding='0' cellspacing='0' style='padding:30px 0;background:#f4f6f8;'>
<tr>
<td align='center'>

<table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;'>

    <!-- Header -->
    <tr>
        <td style='background:#179e4f;padding:30px;text-align:center;'>

            <!-- Logo -->
            <div style='margin-bottom:15px;background:white;padding:10px;border-radius:10px;'>
                <img src='https://nira.org.ng/wp-content/uploads/2022/01/nira-logo.fw_.png'
                    alt='NiRA Logo'
                    style='max-height:70px;'>
            </div>

            <h1 style='margin:0;color:#ffffff;font-size:24px;'>
                Abuse Report Update
            </h1>

        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style='padding:40px 35px;color:#333333;'>

            <p style='margin-top:0;font-size:16px;'>
                Dear {$name},
            </p>

            <p style='font-size:15px;line-height:1.7;'>
                The status of your abuse report ticket has been updated.
            </p>

            <table cellpadding='0' cellspacing='0'
                style='margin:25px 0;width:100%;background:#f8fafc;border-radius:8px;'>

                <tr>
                    <td style='padding:18px;'>

                        <p style='margin:0 0 10px 0;font-size:13px;color:#6b7280;'>
                            TICKET ID
                        </p>

                        <p style='margin:0;font-size:22px;font-weight:bold;color:#179e4f;'>
                            {$report['ticket_id']}
                        </p>

                    </td>
                </tr>

                <tr>
                    <td style='padding:18px;border-top:1px solid #e5e7eb;'>

                        <p style='margin:0 0 10px 0;font-size:13px;color:#6b7280;'>
                            CURRENT STATUS
                        </p>

                        <p style='margin:0;font-size:18px;font-weight:bold;color:#111827;'>
                            {$statusText}
                        </p>

                    </td>
                </tr>

            </table>

            <p style='font-size:15px;line-height:1.7;'>
                Please send a mail to admin@nira.org.ng with this your ABUSE ID if you wish to request additional updates or provide more information regarding this report.
            </p>

        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style='padding:25px 35px;background:#f9fafb;border-top:1px solid #e5e7eb;'>

            <p style='margin:0;font-size:13px;color:#6b7280;'>
                Nigeria Internet Registration Association (NiRA)
            </p>

            <p style='margin:10px 0 0 0;font-size:12px;color:#9ca3af;'>
                © ".date('Y')." NiRA. All rights reserved.
            </p>

        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
";

        try 
        {

          $emailService
                ->setTo($user['email'])
                ->setSubject("{$report['ticket_id']} - Ticket {$statusText}")
                ->setMessage($message)
                ->setMailType('html')
                ->send();

        } 
        catch (\Throwable $e) {

            log_message('error', 'Email exception: ' . $e->getMessage());

        }

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
              

            if (!empty($report['reporter_email'])) 
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

                $name = esc($report['reporter_name'] ?? 'Reporter');
                $adminMessage = nl2br($message);  
                $url = base_url() .'domain-abuse/track/'. $report['ticket_id'];
$message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
</head>
<body style='margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;'>

<table width='100%' cellpadding='0' cellspacing='0' style='padding:30px 0;background:#f4f6f8;'>
<tr>
<td align='center'>

<table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;'>

    <!-- HEADER -->
    <tr>
        <td style='background:#fff;padding:30px;text-align:center;'>

            <!-- LOGO -->
            <img src='https://nira.org.ng/wp-content/uploads/2022/01/nira-logo.fw_.png'
                 alt='NiRA Logo'
                 style='max-height:70px;margin-bottom:15px;'>

            <h1 style='margin:0;color:#000000;font-size:22px;'>
                Abuse Report Update
            </h1>

        </td>
    </tr>

    <!-- BODY -->
    <tr>
        <td style='padding:40px 35px;color:#333;'>

            <p style='margin:0 0 10px 0;font-size:16px;'>
                Dear {$name},
            </p>

            <p style='font-size:15px;line-height:1.7;margin-bottom:20px;'>
                There is an update on your submitted abuse report. 
            </p>

            <p style='font-size:14px;line-height:1.7;color:#555;'>
                You may log into your dashboard to view more updates or continue the conversation regarding this report.
            </p>

            <div style='margin-top:30px;'>
                <a href='.$url.'
                   style='background:#179e4f;color:#fff;padding:12px 20px;border-radius:6px;text-decoration:none;font-weight:bold;display:inline-block;'>
                    Visit Ticket
                </a>
            </div>

        </td>
    </tr>

    <!-- FOOTER -->
    <tr>
        <td style='padding:20px 30px;background:#f9fafb;border-top:1px solid #e5e7eb;'>

            <p style='margin:0;font-size:13px;color:#6b7280;'>
                Nigeria Internet Registration Association (NiRA)
            </p>

            <p style='margin:8px 0 0 0;font-size:12px;color:#9ca3af;'>
                © ".date('Y')." NiRA. All rights reserved.
            </p>

        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
";


                try
                {
                      $emailService
                        ->setTo($report['reporter_email'])
                        ->setSubject("{$report['ticket_id']} - Update on your abuse report")
                        ->setMessage($message)
                        ->setMailType('html')
                        ->send();
                } 
                catch (\Throwable $e) {

                    log_message('error', 'Email exception: ' . $e->getMessage());
                }
            }
        }

        return redirect()->to("/report/VIEW/{$id}")->with('success', 'Response sent successfully.');
    }

     public function add_registrar_response($id) 
    {

        $reportModel   = new \App\Models\AbuseReportModel();
        $responseModel = new \App\Models\AbuseReportResponseModel();

        
        $report = $reportModel->where('id', $id)->first();
        
        if (!$report) {
            return redirect()->back()->with('error', 'Response message cannot be empty.');
        }

        $message = $this->request->getPost('message');
        $ccEmails = $this->request->getPost('cc_emails');
        $user_id = $this->request->getPost('user_id');

        if(!$user_id) return redirect()->back()->with('error','Authentication Required');

        if (empty($message)) {
            return redirect()->back()->with('error', 'Response message cannot be empty.');
        }

        // Save the response
        $responseModel->insert([
            'report_id' => $id,
            'user_id'   => $user_id,
            'message'   => $message,  
        ]);

            if ($this->request->getPost('notify_reporter')) {
                

            $emailService = \Config\Services::email();
              

            if (!empty($report['reporter_email'])) 
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

                $name = esc($report['reporter_name'] ?? 'Reporter');
                $adminMessage = nl2br($message);  
                $url = base_url() .'domain-abuse/track/'. $report['ticket_id'];
$message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
</head>
<body style='margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;'>

<table width='100%' cellpadding='0' cellspacing='0' style='padding:30px 0;background:#f4f6f8;'>
<tr>
<td align='center'>

<table width='600' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:12px;overflow:hidden;'>

    <!-- HEADER -->
    <tr>
        <td style='background:#fff;padding:30px;text-align:center;'>

            <!-- LOGO -->
            <img src='https://nira.org.ng/wp-content/uploads/2022/01/nira-logo.fw_.png'
                 alt='NiRA Logo'
                 style='max-height:70px;margin-bottom:15px;'>

            <h1 style='margin:0;color:#000000;font-size:22px;'>
                Abuse Report Update
            </h1>

        </td>
    </tr>

    <!-- BODY -->
    <tr>
        <td style='padding:40px 35px;color:#333;'>

            <p style='margin:0 0 10px 0;font-size:16px;'>
                Dear {$name},
            </p>

            <p style='font-size:15px;line-height:1.7;margin-bottom:20px;'>
                There is an update on your submitted abuse report. 
            </p>

            <p style='font-size:14px;line-height:1.7;color:#555;'>
                You may log into your dashboard to view more updates or continue the conversation regarding this report.
            </p>

            <div style='margin-top:30px;'>
                <a href='.$url.'
                   style='background:#179e4f;color:#fff;padding:12px 20px;border-radius:6px;text-decoration:none;font-weight:bold;display:inline-block;'>
                    Visit Ticket
                </a>
            </div>

        </td>
    </tr>

    <!-- FOOTER -->
    <tr>
        <td style='padding:20px 30px;background:#f9fafb;border-top:1px solid #e5e7eb;'>

            <p style='margin:0;font-size:13px;color:#6b7280;'>
                Nigeria Internet Registration Association (NiRA)
            </p>

            <p style='margin:8px 0 0 0;font-size:12px;color:#9ca3af;'>
                © ".date('Y')." NiRA. All rights reserved.
            </p>

        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
";


                try
                {
                        $emailService
                            ->setTo($report['reporter_email'])
                            ->setSubject("{$report['ticket_id']} - Update on your abuse report")
                            ->setMessage($message)
                            ->setMailType('html')
                            ->send();
                } 
                catch (\Throwable $e) {

                    log_message('error', 'Email exception: ' . $e->getMessage());

                }
                
            }
        }

        return redirect()->to("/domain-abuse/registrar/{$report['ticket_id']}")->with('success', 'Response sent successfully.');
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

    public function get_whois_abuse_email(string $domain): ?string
    {
        $domain = strtolower(trim($domain));
        $url    = "https://whois.nic.net.ng/domain/{$domain}";

        // Use CI's built-in curl or file_get_contents with context
        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => "Accept: application/rdap+json\r\n",
                'timeout' => 10,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);

        if ($raw === false) {
            log_message('error', "RDAP: failed to fetch {$url}");
            return null;
        }

        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data['entities'])) {
            log_message('error', "RDAP: invalid JSON or missing entities for {$domain}");
            return null;
        }

        // Walk top-level entities to find the registrar
        foreach ($data['entities'] as $entity) {
            $roles = $entity['roles'] ?? [];

            if (! in_array('registrar', $roles, true)) {
                continue;
            }

            // Inside the registrar entity, find the abuse sub-entity
            foreach ($entity['entities'] ?? [] as $sub_entity) {
                $sub_roles = $sub_entity['roles'] ?? [];

                if (! in_array('abuse', $sub_roles, true)) {
                    continue;
                }

                // Extract email from vcardArray
                // vcardArray[1] is an array of vcard properties
                foreach ($sub_entity['vcardArray'][1] ?? [] as $vcard_prop) {
                    // Each prop: [ "type", {params}, "value_type", "value" ]
                    if (isset($vcard_prop[0], $vcard_prop[3])
                        && $vcard_prop[0] === 'email'
                        && filter_var($vcard_prop[3], FILTER_VALIDATE_EMAIL)
                    ) {
                        return $vcard_prop[3]; // e.g. "abuseteam@whogohost.com"
                    }
                }
            }
        }

        log_message('info', "RDAP: no abuse email found for {$domain}");
        return null;
    }
}
