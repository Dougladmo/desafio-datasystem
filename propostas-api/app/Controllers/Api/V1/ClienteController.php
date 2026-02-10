<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\ClienteModel;
use CodeIgniter\HTTP\ResponseInterface;

class ClienteController extends BaseController
{
    protected $modelName = ClienteModel::class;

    /**
     * Create a new client
     *
     * POST /api/v1/clientes
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->formatError('Dados inválidos', 400);
        }

        $model = new ClienteModel();

        if (!$model->insert($data)) {
            $errors = $this->formatValidationErrors($model);
            return $this->formatError('Erro de validação', 422, $errors);
        }

        $id = $model->getInsertID();
        $cliente = $model->find($id);

        return $this->formatSuccess($cliente, 'Cliente criado com sucesso', 201);
    }

    /**
     * Get a client by ID
     *
     * GET /api/v1/clientes/{id}
     */
    public function show($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->formatError('ID do cliente não informado', 400);
        }

        $model = new ClienteModel();
        $cliente = $model->find($id);

        if (!$cliente) {
            return $this->formatError('Cliente não encontrado', 404);
        }

        return $this->formatSuccess($cliente);
    }
}
