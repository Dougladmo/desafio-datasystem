<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaPropostaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'proposta_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'actor' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'User or system that performed the action',
            ],
            'evento' => [
                'type'       => 'ENUM',
                'constraint' => ['CREATED', 'UPDATED', 'SUBMITTED', 'APPROVED', 'REJECTED', 'CANCELLED', 'DELETED_LOGICAL'],
            ],
            'payload' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Event data and changes',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('proposta_id');
        $this->forge->addKey('created_at');

        $this->forge->addForeignKey('proposta_id', 'propostas', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('auditoria_proposta');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_proposta');
    }
}
