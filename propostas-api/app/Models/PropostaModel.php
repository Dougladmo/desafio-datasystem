<?php

namespace App\Models;

use CodeIgniter\Model;

class PropostaModel extends Model
{
    protected $table = 'propostas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = ['cliente_id', 'produto', 'valor_mensal', 'status', 'origem', 'versao'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'cliente_id' => 'required|is_natural_no_zero',
        'produto' => 'required|min_length[3]|max_length[255]',
        'valor_mensal' => 'required|decimal|greater_than[0]',
        'origem' => 'required|in_list[WEB,MOBILE,API]'
    ];

    protected $validationMessages = [
        'cliente_id' => [
            'required' => 'Cliente é obrigatório',
            'is_natural_no_zero' => 'Cliente inválido'
        ],
        'produto' => [
            'required' => 'Produto é obrigatório'
        ],
        'valor_mensal' => [
            'required' => 'Valor mensal é obrigatório',
            'decimal' => 'Valor mensal deve ser numérico',
            'greater_than' => 'Valor mensal deve ser maior que zero'
        ],
        'origem' => [
            'required' => 'Origem é obrigatória',
            'in_list' => 'Origem inválida'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['setInitialStatus'];
    protected $afterInsert = ['registrarAuditoriaCreated'];
    protected $beforeUpdate = ['validateStatusTransition'];
    protected $afterUpdate = ['registrarAuditoriaUpdated'];
    protected $afterDelete = ['registrarAuditoriaDeleted'];

    // Status transitions map
    protected $validTransitions = [
        'DRAFT' => ['SUBMITTED', 'CANCELLED'],
        'SUBMITTED' => ['APPROVED', 'REJECTED', 'CANCELLED'],
        'APPROVED' => [],
        'REJECTED' => [],
        'CANCELLED' => []
    ];

    /**
     * Set initial status and version for new proposals
     */
    protected function setInitialStatus(array $data)
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'DRAFT';
        }
        if (!isset($data['data']['versao'])) {
            $data['data']['versao'] = 0;
        }
        return $data;
    }

    /**
     * Register audit log for created proposals
     */
    protected function registrarAuditoriaCreated(array $data)
    {
        if (!isset($data['id'])) {
            return $data;
        }

        $auditoriaModel = new AuditoriaPropostaModel();
        $auditoriaModel->registrar(
            $data['id'],
            'CREATED',
            [
                'proposta' => $data['data'] ?? []
            ]
        );

        return $data;
    }

    /**
     * Validate status transition before update
     */
    protected function validateStatusTransition(array $data)
    {
        if (!isset($data['id']) || !is_array($data['id'])) {
            return $data;
        }

        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
        $newStatus = $data['data']['status'] ?? null;

        if (!$newStatus) {
            return $data;
        }

        $current = $this->find($id);
        if (!$current) {
            return $data;
        }

        $currentStatus = $current['status'];

        if (!$this->canTransitionTo($currentStatus, $newStatus)) {
            throw new \RuntimeException("Transição de status inválida: {$currentStatus} → {$newStatus}");
        }

        return $data;
    }

    /**
     * Register audit log for updated proposals
     */
    protected function registrarAuditoriaUpdated(array $data)
    {
        if (!isset($data['id'])) {
            return $data;
        }

        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];

        $auditoriaModel = new AuditoriaPropostaModel();

        $evento = 'UPDATED';
        if (isset($data['data']['status'])) {
            $evento = strtoupper($data['data']['status']);
        }

        $auditoriaModel->registrar(
            $id,
            $evento,
            [
                'changes' => $data['data'] ?? []
            ]
        );

        return $data;
    }

    /**
     * Register audit log for deleted proposals
     */
    protected function registrarAuditoriaDeleted(array $data)
    {
        if (!isset($data['id'])) {
            return $data;
        }

        $id = is_array($data['id']) ? $data['id'][0] : $data['id'];

        $auditoriaModel = new AuditoriaPropostaModel();
        $auditoriaModel->registrar(
            $id,
            'DELETED_LOGICAL',
            []
        );

        return $data;
    }

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true;
        }

        if (!isset($this->validTransitions[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, $this->validTransitions[$currentStatus]);
    }

    /**
     * Update with optimistic locking
     */
    public function updateWithVersion(int $id, array $data, int $expectedVersion): bool
    {
        $db = $this->db;

        $builder = $db->table($this->table);

        // Add version check
        $builder->where('id', $id);
        $builder->where('versao', $expectedVersion);

        // Increment version
        $data['versao'] = $expectedVersion + 1;
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Execute update
        $result = $builder->update($data);

        if ($db->affectedRows() === 0) {
            return false;
        }

        return true;
    }

    /**
     * Find with filters and pagination
     */
    public function findWithFilters(array $filters = [], ?string $sort = null, int $page = 1, int $perPage = 10): array
    {
        $builder = $this->builder();

        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['valor_min'])) {
            $builder->where('valor_mensal >=', $filters['valor_min']);
        }

        if (isset($filters['valor_max'])) {
            $builder->where('valor_mensal <=', $filters['valor_max']);
        }

        if (!empty($filters['cliente_id'])) {
            $builder->where('cliente_id', $filters['cliente_id']);
        }

        if (!empty($filters['origem'])) {
            $builder->where('origem', $filters['origem']);
        }

        // Sorting
        if ($sort) {
            [$field, $direction] = explode(':', $sort . ':asc');
            $builder->orderBy($field, $direction);
        } else {
            $builder->orderBy('created_at', 'DESC');
        }

        // Pagination
        $offset = ($page - 1) * $perPage;
        $builder->limit($perPage, $offset);

        $results = $builder->get()->getResultArray();

        // Get total count
        $totalBuilder = $this->builder();
        if (!empty($filters['status'])) {
            $totalBuilder->where('status', $filters['status']);
        }
        if (isset($filters['valor_min'])) {
            $totalBuilder->where('valor_mensal >=', $filters['valor_min']);
        }
        if (isset($filters['valor_max'])) {
            $totalBuilder->where('valor_mensal <=', $filters['valor_max']);
        }
        if (!empty($filters['cliente_id'])) {
            $totalBuilder->where('cliente_id', $filters['cliente_id']);
        }
        if (!empty($filters['origem'])) {
            $totalBuilder->where('origem', $filters['origem']);
        }

        $total = $totalBuilder->countAllResults();

        return [
            'data' => $results,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }
}
