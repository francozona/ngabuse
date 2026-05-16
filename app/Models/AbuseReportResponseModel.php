<?php

namespace App\Models;

use CodeIgniter\Model;

class AbuseReportResponseModel extends Model
{
    protected $table            = 'abuse_report_responses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'report_id',
        'user_id',
        'message',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    
   public function getForReport(int $reportId): array
    {
        return $this->db
            ->table('abuse_report_responses r')
            ->select('r.*, u.full_name AS responder_name, u.role AS responder_role')
            ->join('users u', 'u.id = r.user_id', 'left')
            ->where('r.report_id', $reportId)
            ->where('r.deleted_at IS NULL')
            ->orderBy('r.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }
}