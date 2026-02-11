<?php

namespace Tests\Unit\Api;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class PropostaControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        // Create a test client for proposals
        $this->clienteId = $this->createTestCliente();
    }

    protected function createTestCliente(): int
    {
        $data = [
            'nome' => 'Cliente Teste',
            'email' => 'cliente.teste@example.com',
            'documento' => '12345678901'
        ];

        $result = $this->post('/api/v1/clientes', $data);
        return $result->getJSON()->data->id;
    }

    public function testCreatePropostaWithValidData(): void
    {
        $data = [
            'cliente_id' => $this->clienteId,
            'produto' => 'Plano Premium',
            'valor_mensal' => 199.90,
            'origem' => 'API'
        ];

        $result = $this->post('/api/v1/propostas', $data);

        $result->assertStatus(201);
        $result->assertJSONFragment(['message' => 'Proposta criada com sucesso']);
        $result->assertJSONFragment(['status' => 'DRAFT']);
        $result->assertJSONFragment(['versao' => 0]);
    }

    public function testCreatePropostaWithInvalidClienteId(): void
    {
        $data = [
            'cliente_id' => 99999,
            'produto' => 'Plano Premium',
            'valor_mensal' => 199.90,
            'origem' => 'API'
        ];

        $result = $this->post('/api/v1/propostas', $data);

        $result->assertStatus(422);
    }

    public function testIdempotencyPreventsCreatingDuplicatePropostas(): void
    {
        $idempotencyKey = 'test-idempotency-' . uniqid();

        $data = [
            'cliente_id' => $this->clienteId,
            'produto' => 'Plano Idempotente',
            'valor_mensal' => 299.90,
            'origem' => 'API'
        ];

        // First request
        $result1 = $this->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post('/api/v1/propostas', $data);

        $result1->assertStatus(201);
        $proposta1 = $result1->getJSON()->data;

        // Second request with same idempotency key
        $result2 = $this->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post('/api/v1/propostas', $data);

        $result2->assertStatus(201);
        $proposta2 = $result2->getJSON()->data;

        // Should return the same proposal
        $this->assertEquals($proposta1->id, $proposta2->id);
    }

    public function testOptimisticLockingPreventsConflictingUpdates(): void
    {
        // Create a proposta
        $data = [
            'cliente_id' => $this->clienteId,
            'produto' => 'Plano Locking Test',
            'valor_mensal' => 399.90,
            'origem' => 'API'
        ];

        $createResult = $this->post('/api/v1/propostas', $data);
        $proposta = $createResult->getJSON()->data;

        // Update with correct version
        $updateData = [
            'produto' => 'Plano Updated',
            'valor_mensal' => 499.90,
            'versao' => 0
        ];

        $result1 = $this->put('/api/v1/propostas/' . $proposta->id, $updateData);
        $result1->assertStatus(200);

        // Try to update again with old version (should fail)
        $updateData2 = [
            'produto' => 'Plano Conflict',
            'valor_mensal' => 599.90,
            'versao' => 0  // Old version
        ];

        $result2 = $this->put('/api/v1/propostas/' . $proposta->id, $updateData2);
        $result2->assertStatus(409);
        $result2->assertJSONFragment(['error' => 'Conflito de versão. A proposta foi modificada por outro processo.']);
    }

    public function testStatusTransitionFromDraftToSubmitted(): void
    {
        // Create proposal
        $data = [
            'cliente_id' => $this->clienteId,
            'produto' => 'Plano Status Test',
            'valor_mensal' => 199.90,
            'origem' => 'API'
        ];

        $createResult = $this->post('/api/v1/propostas', $data);
        $proposta = $createResult->getJSON()->data;

        // Submit proposal
        $submitData = ['versao' => 0];
        $result = $this->withHeaders(['Idempotency-Key' => 'submit-' . uniqid()])
            ->post('/api/v1/propostas/' . $proposta->id . '/submit', $submitData);

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'SUBMITTED']);
        $result->assertJSONFragment(['versao' => 1]);
    }

    public function testStatusTransitionFromSubmittedToApproved(): void
    {
        // Create and submit proposal
        $data = [
            'cliente_id' => $this->clienteId,
            'produto' => 'Plano Approve Test',
            'valor_mensal' => 299.90,
            'origem' => 'API'
        ];

        $createResult = $this->post('/api/v1/propostas', $data);
        $proposta = $createResult->getJSON()->data;

        // Submit first
        $this->withHeaders(['Idempotency-Key' => 'submit-' . uniqid()])
            ->post('/api/v1/propostas/' . $proposta->id . '/submit', ['versao' => 0]);

        // Approve
        $result = $this->post('/api/v1/propostas/' . $proposta->id . '/approve');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'APPROVED']);
    }

    public function testInvalidStatusTransitionFromDraftToApproved(): void
    {
        // Create proposal (DRAFT status)
        $data = [
            'cliente_id' => $this->clienteId,
            'produto' => 'Plano Invalid Transition',
            'valor_mensal' => 199.90,
            'origem' => 'API'
        ];

        $createResult = $this->post('/api/v1/propostas', $data);
        $proposta = $createResult->getJSON()->data;

        // Try to approve directly (should fail)
        $result = $this->post('/api/v1/propostas/' . $proposta->id . '/approve');

        $result->assertStatus(400);
    }

    public function testListPropostasWithPagination(): void
    {
        $result = $this->get('/api/v1/propostas?page=1&per_page=10');

        $result->assertStatus(200);
        
        $json = $result->getJSON();
        $this->assertObjectHasProperty('data', $json);
        $this->assertObjectHasProperty('pagination', $json->data);
    }

    public function testListPropostasWithStatusFilter(): void
    {
        $result = $this->get('/api/v1/propostas?status=DRAFT');

        $result->assertStatus(200);
    }

    public function testGetAuditTrail(): void
    {
        // Create and update a proposal
        $data = [
            'cliente_id' => $this->clienteId,
            'produto' => 'Plano Audit Test',
            'valor_mensal' => 199.90,
            'origem' => 'API'
        ];

        $createResult = $this->post('/api/v1/propostas', $data);
        $proposta = $createResult->getJSON()->data;

        // Get audit trail
        $result = $this->get('/api/v1/propostas/' . $proposta->id . '/auditoria');

        $result->assertStatus(200);
        
        $json = $result->getJSON();
        $this->assertIsArray($json->data);
        $this->assertGreaterThan(0, count($json->data));
    }
}
