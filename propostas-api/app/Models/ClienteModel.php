<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['nome', 'email', 'documento'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'nome' => 'required|min_length[3]|max_length[255]',
        'email' => 'required|valid_email|is_unique[clientes.email,id,{id}]',
        'documento' => 'required|valid_documento'
    ];

    protected $validationMessages = [
        'nome' => [
            'required' => 'Nome é obrigatório',
            'min_length' => 'Nome deve ter no mínimo 3 caracteres'
        ],
        'email' => [
            'required' => 'Email é obrigatório',
            'valid_email' => 'Email inválido',
            'is_unique' => 'Email já cadastrado'
        ],
        'documento' => [
            'required' => 'CPF/CNPJ é obrigatório',
            'valid_documento' => 'CPF/CNPJ inválido'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setTimestamps'];
    protected $beforeUpdate = ['setUpdateTimestamp'];

    /**
     * Set timestamps for new records
     */
    protected function setTimestamps(array $data)
    {
        $now = date('Y-m-d H:i:s');
        if (!isset($data['data']['created_at'])) {
            $data['data']['created_at'] = $now;
        }
        if (!isset($data['data']['updated_at'])) {
            $data['data']['updated_at'] = $now;
        }
        return $data;
    }

    /**
     * Set update timestamp
     */
    protected function setUpdateTimestamp(array $data)
    {
        if (!isset($data['data']['updated_at'])) {
            $data['data']['updated_at'] = date('Y-m-d H:i:s');
        }
        return $data;
    }
}
