<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AbuseReportModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class NetbeaconController extends BaseController
{
    protected AbuseReportModel $abuseReportModel;

    public function __construct()
    {
        $this->abuseReportModel = new AbuseReportModel();
    }

    public function get_incident_reports(): ResponseInterface
    {
        $cronHeader = $this->request->getHeaderLine('NIRA-CRON-NETBEACON');

        if ($cronHeader !== 'Netbeacon_cron') {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status' => false,
                    'message' => 'Unauthorized cron request.'
                ]);
        }

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
                'connect_timeout' => 120,
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

            while (!empty($nextUrl)) {
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
                        $report = $incident['Report'] ?? [];
                        $custom = $report['Custom'] ?? [];

                        $ticketId = $custom['NetBeaconIncidentId'] ?? null;
                        $domain = $custom['Domain'] ?? null;
                        $tld = $custom['Tld'] ?? null;
                        $target = $custom['Target'] ?? null;

                        if (empty($ticketId) || empty($domain)) {
                            $failed++;
                            continue;
                        }

                        $domain = strtolower(trim($domain));

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
                            $existing++;
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

                        if (is_array($feedback) && !empty($feedback)) {
                            $latestFeedback = end($feedback);

                            if (is_array($latestFeedback)) {
                                $status = $latestFeedback['Status']
                                    ?? 'pending';
                            }
                        }

                        $reportData = [
                            'ticket_id' => $ticketId,
                            'user_id' => null,
                            'domain_name' => $domain,
                            'tld' => $tld,
                            'registrar_email' => null,
                            'full_domain' => $domain,
                            'abusive_url' => $target,
                            'date_first_observed' => $report['Date'] ?? null,
                            'abuse_category' => $report['ReportType'] ?? null,
                            'description' => $report['ReporterNotes'] ?? null,
                            'registrar_notified' => false,
                            'registrar_notification_date' => null,
                            'evidence_files' => json_encode(
                                $evidence,
                                JSON_UNESCAPED_SLASHES
                            ),
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

                    } catch (\Throwable $e) {
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