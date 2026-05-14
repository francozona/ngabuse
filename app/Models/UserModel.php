<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'full_name',
        'email',
        'image',
        'role',
        'password',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'full_name' => 'required|min_length[2]|max_length[150]',
        'email'     => 'required|valid_email|max_length[200]',
    ];

    protected $validationMessages = [
        'full_name' => [
            'required'   => 'Full name is required.',
            'min_length' => 'Full name must be at least 2 characters.',
        ],
        'email' => [
            'required'    => 'Email address is required.',
            'valid_email' => 'Please provide a valid email address.',
        ],
    ];

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Find an existing user by email, or create one if not found.
     * Returns the user row array.
     */
    public function firstOrCreate(string $email, string $fullName): array
    {
        $user = $this->where('email', $email)->first();

        if ($user) {
            return $user;
        }

        $id = $this->insert([
            'email'     => $email,
            'full_name' => $fullName,
            'role' => 'visitor'
        ], true);

        return $this->find($id);
    }

    /**
     * Return all abuse reports filed by a user.
     */
    public function reports(int $userId): array
    {
        return model(AbuseReportModel::class)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}