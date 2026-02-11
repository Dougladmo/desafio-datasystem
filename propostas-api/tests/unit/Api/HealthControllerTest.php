<?php

namespace Tests\Unit\Api;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class HealthControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testHealthEndpointReturnsSuccessResponse(): void
    {
        $result = $this->get('/health');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'healthy']);
    }

    public function testHealthEndpointReturnsVersion(): void
    {
        $result = $this->get('/health');

        $result->assertStatus(200);
        $result->assertJSONFragment(['version' => '1.0.0']);
    }

    public function testHealthEndpointChecksDatabaseStatus(): void
    {
        $result = $this->get('/health');

        $result->assertStatus(200);
        
        $json = $result->getJSON();
        $this->assertObjectHasProperty('services', $json);
        $this->assertObjectHasProperty('database', $json->services);
        $this->assertEquals('MySQL', $json->services->database->type);
    }

    public function testHealthEndpointChecksRedisStatus(): void
    {
        $result = $this->get('/health');

        $result->assertStatus(200);
        
        $json = $result->getJSON();
        $this->assertObjectHasProperty('services', $json);
        $this->assertObjectHasProperty('redis', $json->services);
        $this->assertEquals('Redis', $json->services->redis->type);
    }
}
