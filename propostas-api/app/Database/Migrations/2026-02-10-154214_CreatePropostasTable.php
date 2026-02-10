<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePropostasTable extends Migration
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
            'cliente_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'produto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'valor_mensal' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'CANCELLED'],
                'default'    => 'DRAFT',
            ],
            'origem' => [
                'type'       => 'ENUM',
                'constraint' => ['WEB', 'MOBILE', 'API'],
            ],
            'versao' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Optimistic locking version',
            ],
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('cliente_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        $this->forge->addForeignKey('cliente_id', 'clientes', 'id', 'CASCADE', 'RESTRICT');

        $this->forge->createTable('propostas');
    }

    public function down()
    {
        $this->forge->dropTable('propostas');
    }
}
