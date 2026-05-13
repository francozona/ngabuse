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

    // Max file size in bytes (3 MB)
    private const MAX_FILE_SIZE = 3 * 1024 * 1024;

    // Max number of evidence files
    private const MAX_FILES = 3;

    // Upload destination (relative to WRITEPATH)
    private const UPLOAD_DIR = 'uploads/evidence';

  
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
            'domain_name'      => 'required|max_length[253]',
            'tld'              => [
                'label' => 'TLD',
                'rules' => 'required|in_list[.ng,.com.ng,.org.ng,.gov.ng,.edu.ng,.net.ng,.sch.ng,.name.ng,.mobi.ng,.mil.ng,.i.ng]',
            ],
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
        $user      = $userModel->firstOrCreate(
            trim($post['email']),
            trim($post['name'])
        );

        // ── 4. Store evidence files ──────────────────────────────
        $storedPaths = $this->storeFiles();

        // ── 5. Build and persist the abuse report ────────────────
        $reportModel = model(AbuseReportModel::class);
        $ticketId    = $reportModel->generateTicketId();
        $domainName  = trim($post['domain_name']);
        $tld         = $post['tld'];

        $reportData = [
            'ticket_id'                   => $ticketId,
            'user_id'                     => $user['id'],
            'domain_name'                 => $domainName,
            'tld'                         => $tld,
            'full_domain'                 => $domainName . $tld,
            'abusive_url'                 => trim($post['url']),
            'date_first_observed'         => $post['date_first_observed'],
            'abuse_category'              => $post['abuse_category'],
            'description'                 => trim($post['description']),
            'registrar_notified'          => ! empty($post['registrar_notified'])
                                                ? trim($post['registrar_notified'])
                                                : null,
            'registrar_notification_date' => ! empty($post['registrar_notification_date'])
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

        $destPath = WRITEPATH . self::UPLOAD_DIR;

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
}