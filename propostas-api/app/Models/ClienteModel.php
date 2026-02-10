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
    protected $useTimestamps = true;
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
}
