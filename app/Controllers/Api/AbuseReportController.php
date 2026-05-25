<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AbuseReportModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AbuseReportController
 *
 * Accepts multipart/form-data POST requests from the NiRA DNS Abuse UI
 * and returns JSON responses.
 *
 * Routes (defined in app/Config/Routes.php):
 *   POST   /api/abuse-reports          → store()
 *   GET    /api/abuse-reports          → index()   (optional admin listing)
 *   GET    /api/abuse-reports/(:num)   → show()    (optional ticket lookup)
 */
class AbuseReportController extends BaseController
{
    // Allowed MIME types for evidence uploads
    private const ALLOWED_MIME_TYPES = ['image/png', 'image/jpeg', 'application/pdf'];

    // Max file size in bytes (5 MB)
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    // Max number of evidence files
    private const MAX_FILES = 3;

    // Upload destination (relative to WRITEPATH)
    private const UPLOAD_DIR = '/uploads/evidence';

  
    /**
     * Accept the form from the NiRA Abuse UI, validate everything,
     * persist the reporter (upsert), save the report, store evidence
     * files, and return a JSON response with the generated ticket ID.
     */
    public function store(): ResponseInterface
    {
        // ── 1. Validate text fields ──────────────────────────────
        $rules = [
            'name'             => 'required|min_length[2]|max_length[150]',
            'email'            => 'required|valid_email|max_length[200]',
            'url'              => 'required|valid_url_strict',
            'date_first_observed' => 'required|valid_date',
            'abuse_category'   => 'required|in_list[Malware,Botnets,Phishing,Pharming,Spam,Other forms of DNS Abuse]',
            'description'      => 'required|min_length[10]',
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Validation failed.',
                    'errors'  => $this->validator->getErrors(),
                ]);
        }


        $post = $this->request->getPost();

        
        // ── 2. Validate evidence file uploads ────────────────────
        $fileErrors = $this->validateFiles();
        if ($fileErrors !== null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_UNPROCESSABLE_ENTITY)
                ->setJSON([
                    'status'  => 'error',
                    'message' => $fileErrors,
                    'errors'  => ['files' => $fileErrors],
                ]);
        }

        // ── 3. Upsert reporter into `users` ──────────────────────
        $userModel = model(UserModel::class);
        $password = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789'), 0, 8);
        $user      = $userModel->firstOrCreate(
            trim($post['email']), 
            trim($post['name']),
            $password,
        );

        // ── 4. Store evidence files ──────────────────────────────
        $storedPaths = $this->storeFiles();

        $ngZones = [
            'com.ng',
            'org.ng',
            'gov.ng',
            'edu.ng',
            'mobi.ng',
            'net.ng',
            'sch.ng',
            'name.ng'
        ];
        // ── 5. Build and persist the abuse report ────────────────
        $reportModel = model(AbuseReportModel::class);

        $ticketId   = $reportModel->generateTicketId();

        $url = trim((string)($post['url'] ?? ''));

        if (is_array($url)) {
            $url = $url[0] ?? '';
        }

        $host = parse_url($url, PHP_URL_HOST) ?: $url;
        $host = strtolower(preg_replace('/^www\./', '', $host));

        $parts = explode('.', $host);
        $count = count($parts);

        $domainName = '';
        $tld = '';
        $fullDomain = '';
        $isSubdomain = false;


        if ($count >= 3) {

            $lastTwo = $parts[$count - 2] . '.' . $parts[$count - 1];

            if (in_array($lastTwo, $ngZones, true)) {
                $tld = $lastTwo;
                $domainName = $parts[$count - 3];

                if ($count > 3) {
                    $isSubdomain = true;
                }

                $fullDomain = $domainName . '.' . $tld;

            } 
            else {
                
                $tld = $parts[$count - 1];
                $domainName = $parts[$count - 2];

                if ($count > 2) {
                    $isSubdomain = true;
                }

                $fullDomain = $domainName . '.' . $tld;
            }

        } 
        elseif ($count === 2) {
            $domainName = $parts[0];
            $tld = $parts[1];
            $fullDomain = $host;
        }

        $registrar_email = $this->get_whois_abuse_email($fullDomain);
        
        $reportData = [
            'ticket_id'                   => $ticketId,
            'user_id'                     => $user['id'],
            'domain_name'                 => $domainName,
            'tld'                         => $tld,
            'full_domain'                 => $fullDomain,
            'registrar_email'             => $registrar_email,
            'abusive_url'                 => trim($post['url']),
            'date_first_observed'         => $post['date_first_observed'],
            'abuse_category'              => $post['abuse_category'],
            'description'                 => trim($post['description']),
            'registrar_notified'          => !empty($post['registrar_notified'])
                                                ? trim($post['registrar_notified'])
                                                : null,
            'registrar_notification_date' => !empty($post['registrar_notification_date'])
                                                ? $post['registrar_notification_date']
                                                : null,
            'evidence_files'              => json_encode($storedPaths),
            'status'                      => 'pending',
        ];

        

        if (! $reportModel->insert($reportData)) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Could not save the abuse report. Please try again.',
                    'errors'  => $reportModel->errors(),
                ]);
        }
        


        $name = $post['name'];
        $emailService = \Config\Services::email();
        $base_url      = base_url();
        $url_reporter  = $base_url . "domain-abuse/track/"     . $ticketId;
        $url_registrar = $base_url . "domain-abuse/registrar/" . $ticketId;

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
                Your abuse report has been successfully received and a support ticket has been opened.
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
                Track My Complaint
                </a>
            </div>

            <p style="font-size:15px;line-height:1.7;">
                Our team will review your submission and reach out if additional information is required.
                Please keep your ticket ID safe for future reference.
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
            ->setTo($post['email'])
            ->setSubject("{$ticketId} - Ticket open for your abuse report")
            ->setMessage($message)
            ->setMailType('html')
            ->send();

        } 
        catch (\Throwable $e) {

            log_message('error', 'Email exception: ' . $e->getMessage());

        }
        


        $reported_domain = $fullDomain;             
        $abuse_category  = $post['abuse_type'] ?? 'Abuse';  

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

          <p style="margin:0 0 14px 0;font-size:13px;font-weight:bold;color:#179e4f;
                         text-transform:uppercase;letter-spacing:1px;">Login Details</p>
            <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;color:#374151;">
                <tr>
                  <td style="padding:6px 0;color:#6b7280;width:40%;">Email</td>
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . $registrar_email. '</td>
                </tr>
                <tr>
                  <td style="padding:6px 0;color:#6b7280;">Password</td>
                  <td style="padding:6px 0;font-weight:bold;color:#111827;">' . $password . '</td>
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
            ->setTo($registrar_email)
            ->setSubject("{$ticketId} - New Abuse Ticket Open")
            ->setMessage($message_registrar)
            ->setMailType('html')
            ->send();

        } 
        catch (\Throwable $e) {

            log_message('error', 'Email exception: ' . $e->getMessage());

        }

        // ── 6. Success response ──────────────────────────────────
        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_CREATED)
            ->setJSON([
                'status'    => 'success',
                'message'   => 'Abuse report submitted successfully.',
                'ticket_id' => $ticketId,
                'data'      => [
                    'reporter'      => [
                        'name'  => $user['full_name'],
                        'email' => $user['email'],
                    ],
                    'domain'        => $domainName . $tld,
                    'abuse_category'=> $post['abuse_category'],
                    'files_uploaded'=> count($storedPaths),
                ],
            ]);
    }

  
    /**
     * List all abuse reports (paginated).
     * Intended for internal/admin use; add auth middleware as needed.
     */
    public function index(): ResponseInterface
    {
        $reportModel = model(AbuseReportModel::class);

        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $reports = $reportModel
            ->select('abuse_reports.*, users.full_name AS reporter_name, users.email AS reporter_email')
            ->join('users', 'users.id = abuse_reports.user_id')
            ->orderBy('abuse_reports.created_at', 'DESC')
            ->paginate($perPage);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $reports,
            'pager'  => $reportModel->pager->getDetails(),
        ]);
    }


    /**
     * Return a single abuse report with reporter info.
     * Can be looked up by numeric ID or by ticket_id string.
     */
    public function show(string $identifier): ResponseInterface
    {
        $reportModel = model(AbuseReportModel::class);

        // Allow lookup by ticket_id (e.g. NiRA-ABUSE-20260513-0042)
        if (! ctype_digit($identifier)) {
            $report = $reportModel
                ->where('ticket_id', $identifier)
                ->first();
        } else {
            $report = $reportModel->findWithReporter((int) $identifier);
        }

        if ($report === null) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Report not found.',
                ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $report,
        ]);
    }

    /**
     * Validate the uploaded evidence files.
     * Returns an error string, or null when all files are valid.
     */
    private function validateFiles(): ?string
    {
        $files = $this->request->getFileMultiple('files') ?? [];

        // Filter out any phantom "empty" entries CI sometimes adds
        $files = array_filter($files, fn($f) => $f->isValid() && ! $f->hasMoved());

        if (count($files) === 0) {
            return 'At least one evidence file is required.';
        }

        if (count($files) > self::MAX_FILES) {
            return 'You may upload a maximum of ' . self::MAX_FILES . ' files.';
        }

        foreach ($files as $file) {
            if ($file->getSizeByUnit('b') > self::MAX_FILE_SIZE) {
                return "File \"{$file->getName()}\" exceeds the 3 MB limit.";
            }

            if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
                return "File \"{$file->getName()}\" is not an allowed type (PNG, JPG, PDF).";
            }
        }

        return null;
    }

    /**
     * Move uploaded files to the evidence directory.
     * Returns an array of stored relative paths.
     */
    private function storeFiles(): array
    {
        $files       = $this->request->getFileMultiple('files') ?? [];
        $files       = array_filter($files, fn($f) => $f->isValid() && ! $f->hasMoved());
        $storedPaths = [];

        $destPath = FCPATH . self::UPLOAD_DIR;

        if (! is_dir($destPath)) {
            mkdir($destPath, 0755, true);
        }

        foreach ($files as $file) {
            $newName = $file->getRandomName();
            $file->move($destPath, $newName);
            $storedPaths[] = self::UPLOAD_DIR . '/' . $newName;
        }

        return $storedPaths;
    }

    /**
     * Query the NiRA RDAP endpoint and return the Registrar Abuse Contact Email.
     * 
     * RDAP path: https://whois.nic.net.ng/domain/{domain}
     * Abuse email lives at: entities[registrar] -> entities[abuse] -> vcardArray email
     *
     * @param  string $domain  e.g. "eventbox.ng"
     * @return string|null     Abuse email, or null on failure
     */
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