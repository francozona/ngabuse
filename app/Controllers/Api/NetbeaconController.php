<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AbuseReportModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use App\Models\UserModel;

class NetbeaconController extends BaseController
{
    protected AbuseReportModel $abuseReportModel;

    protected AbuseReportController $reportService;

    public function __construct()
    {
        $this->abuseReportModel = new AbuseReportModel();
        $this->reportService = new AbuseReportController();
    }

    public function get_incident_reports(): ResponseInterface
    {
        set_time_limit(3600);

        $cronHeader = $this->request->getHeaderLine('NIRA-CRON-NETBEACON');

        // if ($cronHeader !== 'Netbeacon_cron') {
        //     return $this->response
        //         ->setStatusCode(401)
        //         ->setJSON([
        //             'status' => false,
        //             'message' => 'Unauthorized cron request.'
        //         ]);
        // }
        

        try {
            $netBeaconUrl = env('NETBEACON_INCIDENTS_URL');
            $netBeaconApiKey = env('NETBEACON_API_KEY');

            if (empty($netBeaconUrl)) {
                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status' => false,
                        'message' => 'NetBeacon API URL is not configured.'
                    ]);
            }

            if (empty($netBeaconApiKey)) {
                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status' => false,
                        'message' => 'NetBeacon API key is not configured.'
                    ]);
            }

            $client = Services::curlrequest([
                'timeout' => 600,
                'connect_timeout' => 800,
                'http_errors' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $netBeaconApiKey,
                ],
            ]);

            $nextUrl = $netBeaconUrl;

            $received = 0;
            $created = 0;
            $existing = 0;
            $failed = 0;
            $pages = 0;

            while (!empty($nextUrl)) 
            {
                $pages++;

                $response = $client->get($nextUrl);

                $statusCode = $response->getStatusCode();

                if ($statusCode < 200 || $statusCode >= 300) {
                    log_message(
                        'error',
                        'NetBeacon API returned HTTP {code}: {body}',
                        [
                            'code' => $statusCode,
                            'body' => $response->getBody()
                        ]
                    );

                    return $this->response
                        ->setStatusCode(502)
                        ->setJSON([
                            'status' => false,
                            'message' => 'Unable to retrieve incidents from NetBeacon.',
                            'netbeacon_status' => $statusCode,
                            'statistics' => [
                                'received' => $received,
                                'created' => $created,
                                'existing' => $existing,
                                'failed' => $failed,
                                'pages' => $pages
                            ]
                        ]);
                }

                $data = json_decode($response->getBody(), true);

                if (!is_array($data)) {
                    return $this->response
                        ->setStatusCode(502)
                        ->setJSON([
                            'status' => false,
                            'message' => 'Invalid response received from NetBeacon.'
                        ]);
                }

                $incidents = $data['incidents'] ?? [];

                if (!is_array($incidents)) {
                    $incidents = [];
                }

                $received += count($incidents);
    
                foreach ($incidents as $incident) 
                {
                    try 
                    {
                        $ticketId  = $this->abuseReportModel->generateTicketId();
                        $report = $incident['Report'] ?? [];
                        $custom = $report['Custom'] ?? [];
                        $reporterInfo = $incident['ReporterInfo'];
                        $netBeaconticketId = $custom['NetBeaconIncidentId'] ?? null;
                        $domain = $custom['Domain'] ?? null;
                        $tld = $custom['Tld'] ?? null;
                        $target = $report['SourceUrl'] ?? null;
                        $domainName = '';

                        if (!str_contains(strtolower($tld), '.ng')) {
                            continue;
                        }

                        if (empty($ticketId) || empty($domain)) {
                            $failed++;
                            continue;
                        }

                        $domain = strtolower(trim($domain));

                        if (!empty($domain)) {
                            $parts = explode('.', $domain);

                            if (count($parts) >= 3) {
                                $domainName = count($parts) >= 3
                                ? $parts[count($parts) - 3]
                                : $parts[0];
                            }

                            if (count($parts) >= 2) {
                                $domainName = $parts[0];
                            }
                        }

                        $existingReport = $this->abuseReportModel
                            ->where('netbeacon_id', $ticketId)
                            ->first();

                        if (!$existingReport) {
                            $query = $this->abuseReportModel
                                ->where('domain_name', $domain);

                            if (!empty($target)) {
                                $query->where('abusive_url', $target);
                            }

                            $existingReport = $query->first();
                        }

                        if ($existingReport) {
                            $existinqg++;
                            continue;
                        }

                        if (empty($tld)) {
                            $parts = explode('.', $domain);
                            
                            if (count($parts) >= 2) {
                                $tld = '.' . end($parts);
                            }
                        }

                        $evidence = $custom['Evidence'] ?? [];

                        if (!is_array($evidence)) {
                            $evidence = [];
                        }

                        $feedback = $incident['Feedback'] ?? [];

                        $status = 'pending';

                        // if (is_array($feedback) && !empty($feedback)) {
                        //     $latestFeedback = end($feedback);

                        //     if (is_array($latestFeedback)) {
                        //         $status = $latestFeedback['Status']
                        //             ?? 'pending';
                        //     }
                        // }

                        

                        //upsert the reporter details
                        $userModel = model(UserModel::class);
                        $password = substr(str_replace('-', '', bin2hex(random_bytes(16))), 0, 10);
                        $user      = $userModel->firstOrCreate(
                            trim($reporterInfo['ReporterContactEmail']), 
                            trim($reporterInfo['ReporterOrg'].' '.$reporterInfo['ReporterContactName']),
                            $password,
                        );

                        $registrar_email = $this->reportService->get_whois_abuse_email($domain);

                        
                        $reportData = [
                            'ticket_id'=> $ticketId,
                            'netbeacon_id' => $netBeaconticketId,
                            'user_id' => $user['id'],
                            'domain_name' => $domainName ?? $domain,
                            'tld' => $tld,
                            'registrar_email' => $registrar_email,
                            'full_domain' => $domain,
                            'abusive_url' => $target,
                            'date_first_observed' => $report['Date'] ?? null,
                            'abuse_category' => $report['ReportType'] ?? null,
                            'description' => $report['ReporterNotes'] ?? null,
                            'registrar_notified' => false,
                            'registrar_notification_date' => null,
                            'evidence_files' => json_encode(array_column($evidence, 'PayloadUrl')),
                            'status' => $status,
                        ];

                        $inserted = $this->abuseReportModel->insert($reportData);

                        if ($inserted === false) {
                            log_message(
                                'error',
                                'Failed to insert NetBeacon incident {ticket}: {errors}',
                                [
                                    'ticket' => $ticketId,
                                    'errors' => json_encode(
                                        $this->abuseReportModel->errors()
                                    )
                                ]
                            );

                            $failed++;
                            continue;
                        }

                        $created++;

                        $reportModel = model(AbuseReportModel::class);

                        $name = 'NiRA Tech Support';
                        $emailService = \Config\Services::email();
                        $base_url      = base_url();
                        $url_reporter  = $base_url . "domain-abuse/track/"     . $ticketId;
                        $report = $reportModel->where("ticket_id", $ticketId)->first();

                        $message = '
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"><title>Abuse Report Ticket</title></head>
    <body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">
    <tr><td align="center">
        <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;">

        <!-- Header -->
        <tr>
            <td style="background:#fff;padding:30px;text-align:center;">
            <div style="margin-bottom:15px;background:white;padding:10px;border-radius:10px;">
                <img src="https://nira.org.ng/wp-content/uploads/2022/01/nira-logo.fw_.png"
                    alt="NiRA Logo" style="max-height:70px;">
            </div>
            <h1 style="margin:0;color:#000000;font-size:24px;">Abuse Report Ticket Opened</h1>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:40px 35px;color:#333333;">
            <p style="margin-top:0;font-size:16px;">Dear ' . $name . ',</p>

            <p style="font-size:15px;line-height:1.7;">
                A new abuse report has been successfully received and a support ticket has been opened.
            </p>

            <!-- Ticket ID box -->
            <table cellpadding="0" cellspacing="0" style="margin:25px 0;width:100%;background:#f8fafc;border-radius:8px;border-left:4px solid #179e4f;">
                <tr><td style="padding:18px;">
                <p style="margin:0 0 6px 0;font-size:13px;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">Ticket ID</p>
                <p style="margin:0;font-size:26px;font-weight:bold;color:#179e4f;">' . $ticketId . '</p>
                </td></tr>
            </table>

            <p style="font-size:15px;line-height:1.7;">
                You can track the progress of your complaint at any time using the button below:
            </p>

            <div style="margin:25px 0;">
                <a href="' . $url_reporter . '"
                style="background:#179e4f;color:#ffffff;text-decoration:none;padding:14px 28px;
                        border-radius:8px;display:inline-block;font-size:14px;font-weight:bold;">
                Track Complaint
                </a>
            </div>

            <p style="font-size:15px;line-height:1.7;">
                Kindly access the portal for proper action on this new ticket created.
            </p>

            <div style="margin-top:35px;">
                <a href="https://nira.org.ng"
                style="background:#ffffff;color:#179e4f;text-decoration:none;padding:12px 24px;
                        border-radius:8px;display:inline-block;font-size:14px;font-weight:bold;
                        border:2px solid #179e4f;">
                Visit NiRA
                </a>
            </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding:25px 35px;background:#f9fafb;border-top:1px solid #e5e7eb;">
            <p style="margin:0;font-size:13px;color:#6b7280;line-height:1.6;">
                This email was sent by the Nigeria Internet Registration Association (NiRA).
            </p>
            <p style="margin:10px 0 0 0;font-size:12px;color:#9ca3af;">
                © ' . date('Y') . ' NiRA. All rights reserved.
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
                                ->setTo('tech_support@nira.org.ng')
                                ->setSubject("{$ticketId} - Ticket created from abuse report")
                                ->setMessage($message)
                                ->setMailType('html')
                                ->send();

                    } 
                    catch (\Throwable $e) {

                        log_message('error', 'Email exception: ' . $e->getMessage());
                       
                    }
                    


                    $reported_domain = $domain;             
                    $abuse_category  =  $report['ReportType'] ?? 'Abuse';  
                    $url_nira = base_url().'report/OPEN/'.$report['id'];


                    $message_nira = '
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

          <p style="margin-top:0;font-size:16px;">Dear Amin,</p>

          <p style="font-size:15px;line-height:1.7;">
            A domain abuse complaint has been submitted to NiRA regarding a domain registered under .ng
            Please review the details below and take appropriate action.
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
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . $ticketId . '</td>
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

          <p style="font-size:15px;line-height:1.7;">
            You can view the full complaint details and update the ticket status using the button below:
          </p>

          <div style="margin:25px 0;">
            <a href="'. $url_nira .'"
               style="background:#179e4f;color:#ffffff;text-decoration:none;padding:14px 28px;
                      border-radius:8px;display:inline-block;font-size:14px;font-weight:bold;">
              View Complaint &amp; Respond
            </a>
          </div>

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
                                    ->setTo($registrar_email)
                                    ->setSubject("{$ticketId} - New Abuse Ticket Created")
                                    ->setMessage($message_nira)
                                    ->setMailType('html')
                                    ->send();

                            } 
                            catch (\Throwable $e) {

                                log_message('error', 'Email exception: ' . $e->getMessage());

                            
                            }

                    } 
                    catch (\Throwable $e) {
                        $failed++;

                        log_message(
                            'error',
                            'NetBeacon incident processing failed: {message}',
                            [
                                'message' => $e->getMessage()
                            ]
                        );

                        
                    }
                }

                $nextUrl = $data['nextUrl'] ?? null;

                if (!empty($nextUrl)) {
                    if (!str_starts_with($nextUrl, 'http://') &&
                        !str_starts_with($nextUrl, 'https://')) {
                        $baseUrl = rtrim($netBeaconUrl, '/');

                        $parsed = parse_url($baseUrl);

                        if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
                            $origin = $parsed['scheme'] . '://' . $parsed['host'];

                            if (!empty($parsed['port'])) {
                                $origin .= ':' . $parsed['port'];
                            }

                            $nextUrl = $origin . '/' . ltrim($nextUrl, '/');
                        }
                    }
                }
            }

            return $this->response
                ->setStatusCode(200)
                ->setJSON([
                    'status' => true,
                    'message' => 'NetBeacon synchronization completed.',
                    'statistics' => [
                        'received' => $received,
                        'created' => $created,
                        'existing' => $existing,
                        'failed' => $failed,
                        'pages' => $pages
                    ]
                ]);

        } catch (\Throwable $e) {
            log_message(
                'error',
                'NetBeacon synchronization error: {message}',
                [
                    'message' => $e->getMessage()
                ]
            );

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status' => false,
                    'message' => 'An error occurred while synchronizing NetBeacon incidents.'
                ]);
        }
    }
}