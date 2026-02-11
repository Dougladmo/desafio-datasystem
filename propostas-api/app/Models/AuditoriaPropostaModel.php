<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditoriaPropostaModel extends Model
{
    protected $table = 'auditoria_proposta';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['proposta_id', 'actor', 'evento', 'payload', 'created_at'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;

    // Validation
    protected $validationRules = [
        'proposta_id' => 'required|is_natural_no_zero',
        'actor' => 'required|max_length[255]',
        'evento' => 'required|in_list[CREATED,UPDATED,SUBMITTED,APPROVED,REJECTED,CANCELLED,DELETED_LOGICAL]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Register audit event
     */
    public function registrar(int $propostaId, string $evento, array $payload = []): bool
    {
        $data = [
            'proposta_id' => $propostaId,
            'actor' => $this->getActor(),
            'evento' => $evento,
            'payload' => json_encode($payload),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data) !== false;
    }

    /**
     * Get current actor (user or system)
     */
    protected function getActor(): string
    {
        // In a real application, this would get the authenticated user
        // For now, we'll use a default value
        return 'system';
    }

    /**
     * Get audit trail for a proposal
     */
    public function getAuditTrail(int $propostaId): array
    {
        return $this->where('proposta_id', $propostaId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
