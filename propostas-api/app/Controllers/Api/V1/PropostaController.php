<?php

namespace App\Controllers\Api\V1;

use App\Controllers\Api\BaseController;
use App\Models\PropostaModel;
use App\Models\AuditoriaPropostaModel;
use CodeIgniter\HTTP\ResponseInterface;

class PropostaController extends BaseController
{
    protected $modelName = PropostaModel::class;

    /**
     * Create a new proposal
     *
     * POST /api/v1/propostas
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->formatError('Dados inválidos', 400);
        }

        $model = new PropostaModel();

        if (!$model->insert($data)) {
            $errors = $this->formatValidationErrors($model);
            return $this->formatError('Erro de validação', 422, $errors);
        }

        $id = $model->getInsertID();
        $proposta = $model->find($id);

        return $this->formatSuccess($proposta, 'Proposta criada com sucesso', 201);
    }

    /**
     * Update a proposal with optimistic locking
     *
     * PUT /api/v1/propostas/{id}
     */
    public function update($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->formatError('ID da proposta não informado', 400);
        }

        $data = $this->request->getJSON(true);

        if (!$data) {
            return $this->formatError('Dados inválidos', 400);
        }

        if (!isset($data['versao'])) {
            return $this->formatError('Versão não informada', 400);
        }

        $expectedVersion = (int)$data['versao'];
        unset($data['versao']);

        $model = new PropostaModel();
        $proposta = $model->find($id);

        if (!$proposta) {
            return $this->formatError('Proposta não encontrada', 404);
        }

        // Check optimistic lock
        if ($proposta['versao'] !== $expectedVersion) {
            return $this->formatError('Conflito de versão. A proposta foi modificada por outro processo.', 409);
        }

        try {
            $success = $model->updateWithVersion($id, $data, $expectedVersion);

            if (!$success) {
                return $this->formatError('Conflito de versão. A proposta foi modificada por outro processo.', 409);
            }

            $proposta = $model->find($id);
            return $this->formatSuccess($proposta, 'Proposta atualizada com sucesso');
        } catch (\Exception $e) {
            return $this->formatError($e->getMessage(), 400);
        }
    }

    /**
     * Get a proposal by ID
     *
     * GET /api/v1/propostas/{id}
     */
    public function show($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->formatError('ID da proposta não informado', 400);
        }

        $model = new PropostaModel();
        $proposta = $model->find($id);

        if (!$proposta) {
            return $this->formatError('Proposta não encontrada', 404);
        }

        return $this->formatSuccess($proposta);
    }

    /**
     * List proposals with filters
     *
     * GET /api/v1/propostas
     */
    public function index(): ResponseInterface
    {
        $model = new PropostaModel();

        $filters = [];
        $request = $this->request;

        if ($request->getGet('status')) {
            $filters['status'] = $request->getGet('status');
        }

        if ($request->getGet('valor_min')) {
            $filters['valor_min'] = (float)$request->getGet('valor_min');
        }

        if ($request->getGet('valor_max')) {
            $filters['valor_max'] = (float)$request->getGet('valor_max');
        }

        if ($request->getGet('cliente_id')) {
            $filters['cliente_id'] = (int)$request->getGet('cliente_id');
        }

        if ($request->getGet('origem')) {
            $filters['origem'] = $request->getGet('origem');
        }

        $sort = $request->getGet('sort');
        $page = max(1, (int)$request->getGet('page') ?: 1);
        $perPage = max(1, min(100, (int)$request->getGet('per_page') ?: 10));

        $result = $model->findWithFilters($filters, $sort, $page, $perPage);

        return $this->formatSuccess($result);
    }

    /**
     * Submit proposal for review
     *
     * POST /api/v1/propostas/{id}/submit
     */
    public function submit($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->formatError('ID da proposta não informado', 400);
        }

        $data = $this->request->getJSON(true);

        if (!isset($data['versao'])) {
            return $this->formatError('Versão não informada', 400);
        }

        $expectedVersion = (int)$data['versao'];

        $model = new PropostaModel();
        $proposta = $model->find($id);

        if (!$proposta) {
            return $this->formatError('Proposta não encontrada', 404);
        }

        if ($proposta['versao'] !== $expectedVersion) {
            return $this->formatError('Conflito de versão. A proposta foi modificada por outro processo.', 409);
        }

        try {
            $success = $model->updateWithVersion($id, ['status' => 'SUBMITTED'], $expectedVersion);

            if (!$success) {
                return $this->formatError('Conflito de versão. A proposta foi modificada por outro processo.', 409);
            }

            $proposta = $model->find($id);
            return $this->formatSuccess($proposta, 'Proposta enviada para revisão');
        } catch (\Exception $e) {
            return $this->formatError($e->getMessage(), 400);
        }
    }

    /**
     * Approve proposal
     *
     * POST /api/v1/propostas/{id}/approve
     */
    public function approve($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->formatError('ID da proposta não informado', 400);
        }

        $model = new PropostaModel();
        $proposta = $model->find($id);

        if (!$proposta) {
            return $this->formatError('Proposta não encontrada', 404);
        }

        try {
            $model->update($id, ['status' => 'APPROVED']);
            $proposta = $model->find($id);
            return $this->formatSuccess($proposta, 'Proposta aprovada');
        } catch (\Exception $e) {
            return $this->formatError($e->getMessage(), 400);
        }
    }

    /**
     * Reject proposal
     *
     * POST /api/v1/propostas/{id}/reject
     */
    public function reject($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->formatError('ID da proposta não informado', 400);
        }

        $model = new PropostaModel();
        $proposta = $model->find($id);

        if (!$proposta) {
            return $this->formatError('Proposta não encontrada', 404);
        }

        try {
            $model->update($id, ['status' => 'REJECTED']);
            $proposta = $model->find($id);
            return $this->formatSuccess($proposta, 'Proposta rejeitada');
        } catch (\Exception $e) {
            return $this->formatError($e->getMessage(), 400);
        }
    }

    /**
     * Cancel proposal
     *
     * POST /api/v1/propostas/{id}/cancel
     */
    public function cancel($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->formatError('ID da proposta não informado', 400);
        }

        $model = new PropostaModel();
        $proposta = $model->find($id);

        if (!$proposta) {
            return $this->formatError('Proposta não encontrada', 404);
        }

        try {
            $model->update($id, ['status' => 'CANCELLED']);
            $proposta = $model->find($id);
            return $this->formatSuccess($proposta, 'Proposta cancelada');
        } catch (\Exception $e) {
            return $this->formatError($e->getMessage(), 400);
        }
    }

    /**
     * Get proposal audit trail
     *
     * GET /api/v1/propostas/{id}/auditoria
     */
    public function auditoria($id = null): ResponseInterface
    {
        if (!$id) {
            return $this->formatError('ID da proposta não informado', 400);
        }

        $model = new PropostaModel();
        $proposta = $model->find($id);

        if (!$proposta) {
            return $this->formatError('Proposta não encontrada', 404);
        }

        $auditoriaModel = new AuditoriaPropostaModel();
        $auditoria = $auditoriaModel->getAuditTrail($id);

        // Decode JSON payloads
        foreach ($auditoria as &$item) {
            if (isset($item['payload'])) {
                $item['payload'] = json_decode($item['payload'], true);
            }
        }

        return $this->formatSuccess($auditoria);
    }
}
