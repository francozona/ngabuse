<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAbuseReportsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            // Primary key
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            // Auto-generated human-readable ticket ID
            // e.g. NiRA-ABUSE-20260513-0042
            'ticket_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],

            'registrar_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 400,
                'null'       => true,
            ],

            // Foreign key → users.id  (reporter)
            'user_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => true,
            ],

            // ── Domain details ──────────────────────────────
            'domain_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 253,
                'null'       => false,
            ],
            'tld' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'comment'    => '.ng | .com.ng | .org.ng …',
            ],
            'full_domain' => [
                // Stored as a generated/computed convenience column:
                // domain_name + tld, e.g. "example.ng"
                'type'       => 'VARCHAR',
                'constraint' => 273,
                'null'       => false,
            ],
            'abusive_url' => [
                'type' => 'TEXT',
                'null' => false,
            ],

            // ── Abuse details ────────────────────────────────
            'date_first_observed' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'abuse_category' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'Malware',
                    'Botnets',
                    'Phishing',
                    'Pharming',
                    'Spam',
                    'Other forms of DNS Abuse',
                ],
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => false,
            ],

            // ── Registrar notification (optional) ────────────
            'registrar_notified' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'default'    => null,
            ],
            'registrar_notification_date' => [
                'type' => 'DATE',
                'null' => true,
                'default' => null,
            ],

            // ── Evidence files (JSON array of stored paths) ──
            // e.g. ["uploads/evidence/abc.png", "uploads/evidence/xyz.pdf"]
            'evidence_files' => [
                'type' => 'JSON',
                'null' => true,
                'default' => null,
            ],

            // ── Report status ────────────────────────────────
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'under_review', 'resolved', 'rejected'],
                'default'    => 'pending',
                'null'       => false,
            ],

            // ── Timestamps ───────────────────────────────────
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('ticket_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('abuse_category');
        $this->forge->addKey('status');
        $this->forge->addForeignKey(
            'user_id',
            'users',
            'id',
            'CASCADE',   // on update
            'RESTRICT'   // on delete — preserve reports even if user record changes
        );

        $this->forge->createTable('abuse_reports');
    }

    public function down(): void
    {
        $this->forge->dropTable('abuse_reports', true);
    }
}