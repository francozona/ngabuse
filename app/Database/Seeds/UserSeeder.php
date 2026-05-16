<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'full_name' => 'Tech Support',
                'email'     => 'tech_support@nira.org.ng',
                'role'      => 'admin',
                'password'  => password_hash('techsupport2026', PASSWORD_DEFAULT),
                'image'     => './logo.png',
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}