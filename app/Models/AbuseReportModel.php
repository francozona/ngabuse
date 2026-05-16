<?php

namespace App\Models;

use CodeIgniter\Model;

class AbuseReportModel extends Model
{
    protected $table            = 'abuse_reports';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'ticket_id',
        'user_id',
        'domain_name',
        'tld',
        'full_domain',
        'abusive_url',
        'date_first_observed',
        'abuse_category',
        'description',
        'registrar_notified',
        'registrar_notification_date',
        'evidence_files',   // JSON column
        'status',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Automatically encode/decode the JSON evidence_files column
    protected array $casts = [
        'evidence_files' => 'json-array',
    ];

    // Validation rules
    protected $validationRules = [
        'ticket_id'           => 'required|max_length[40]|is_unique[abuse_reports.ticket_id]',
        'user_id'             => 'required|integer|is_not_unique[users.id]',
        'domain_name'         => 'required|max_length[253]',
        'tld'                 => 'required|max_length[20]',
        'full_domain'         => 'required|max_length[273]',
        'abusive_url'         => 'required|valid_url_strict',
        'date_first_observed' => 'required|valid_date',
        'abuse_category'      => 'required|in_list[Malware,Botnets,Phishing,Pharming,Spam,Other forms of DNS Abuse]',
        'description'         => 'required|min_length[10]',
        'status'              => 'in_list[pending,under_review,resolved,rejected]',
    ];

    protected $validationMessages = [
        'abusive_url' => [
            'valid_url_strict' => 'The abusive URL must be a valid URL starting with https://.',
        ],
        'abuse_category' => [
            'in_list' => 'Please select a valid abuse category.',
        ],
    ];

    // ─────────────────────────────────────────────────────────────
    // Ticket ID generation
    // ─────────────────────────────────────────────────────────────

    /**
     * Generate a unique ticket ID in the format:
     *   NiRA-ABUSE-YYYYMMDD-XXXX  (XXXX = zero-padded random 4-digit number)
     */
    public function generateTicketId(): string
    {
        do {
            $date     = date('Ymd');
            $suffix   = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $ticketId = "NiRA-ABUSE-{$date}-{$suffix}";
        } while ($this->where('ticket_id', $ticketId)->countAllResults() > 0);

        return $ticketId;
    }

    // ─────────────────────────────────────────────────────────────
    // Convenience scopes
    // ─────────────────────────────────────────────────────────────

    /** Return only pending reports. */
    public function pending(): static
    {
        return $this->where('status', 'pending');
    }

    /** Return reports for a given domain (full domain string). */
    public function forDomain(string $fullDomain): static
    {
        return $this->where('full_domain', $fullDomain);
    }

    /** Return reports by category. */
    public function byCategory(string $category): static
    {
        return $this->where('abuse_category', $category);
    }

    /**
     * Fetch a report together with its reporter info.
     * Returns a single merged array or null.
     */
    public function findWithReporter(int $id): ?array
    {
        return $this->db
            ->table('abuse_reports ar')
            ->select('ar.*, u.full_name AS reporter_name, u.email AS reporter_email')
            ->join('users u', 'u.id = ar.user_id')
            ->where('ar.id', $id)
            ->where('ar.deleted_at IS NULL')
            ->get()
            ->getRowArray();
    }
}