<?php

namespace Tests\Unit\Api;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ClienteControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    public function testCreateClienteWithValidData(): void
    {
        $data = [
            'nome' => 'Test Cliente',
            'email' => 'teste@example.com',
            'documento' => '12345678901'
        ];

        $result = $this->post('/api/v1/clientes', $data);

        $result->assertStatus(201);
        $result->assertJSONFragment(['message' => 'Cliente criado com sucesso']);
    }

    public function testCreateClienteWithInvalidEmail(): void
    {
        $data = [
            'nome' => 'Test Cliente',
            'email' => 'invalid-email',
            'documento' => '12345678901'
        ];

        $result = $this->post('/api/v1/clientes', $data);

        $result->assertStatus(422);
        $result->assertJSONFragment(['error' => 'Erro de validação']);
    }

    public function testCreateClienteWithDuplicateEmail(): void
    {
        $data = [
            'nome' => 'Cliente 1',
            'email' => 'duplicate@example.com',
            'documento' => '12345678901'
        ];

        // First creation
        $this->post('/api/v1/clientes', $data);

        // Try to create with same email
        $result = $this->post('/api/v1/clientes', $data);

        $result->assertStatus(422);
    }

    public function testGetClienteById(): void
    {
        // Create a client first
        $data = [
            'nome' => 'Get Test Cliente',
            'email' => 'get.test@example.com',
            'documento' => '98765432100'
        ];

        $createResult = $this->post('/api/v1/clientes', $data);
        $cliente = $createResult->getJSON()->data;

        // Get the client
        $result = $this->get('/api/v1/clientes/' . $cliente->id);

        $result->assertStatus(200);
        $result->assertJSONFragment(['nome' => 'Get Test Cliente']);
    }

    public function testGetNonExistentCliente(): void
    {
        $result = $this->get('/api/v1/clientes/99999');

        $result->assertStatus(404);
        $result->assertJSONFragment(['error' => 'Cliente não encontrado']);
    }
}
