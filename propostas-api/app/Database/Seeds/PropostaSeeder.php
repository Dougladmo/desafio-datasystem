<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class PropostaSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('pt_BR');

        $status = ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'CANCELLED'];
        $origens = ['WEB', 'MOBILE', 'API'];
        $produtos = ['Plano Basic', 'Plano Premium', 'Plano Enterprise', 'Plano Starter', 'Plano Pro'];

        $data = [];

        for ($i = 0; $i < 30; $i++) {
            $createdAt = $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s');

            $data[] = [
                'cliente_id' => rand(1, 10),
                'produto' => $produtos[array_rand($produtos)],
                'valor_mensal' => $faker->randomFloat(2, 50, 5000),
                'status' => $status[array_rand($status)],
                'origem' => $origens[array_rand($origens)],
                'versao' => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null,
            ];
        }

        $this->db->table('propostas')->insertBatch($data);
    }
}
