<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class HealthController extends ResourceController
{
    protected $format = 'json';

    /**
     * Health check endpoint
     *
     * GET /health
     */
    public function index(): ResponseInterface
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'services' => []
        ];

        // Check Database
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            $health['services']['database'] = [
                'status' => 'up',
                'type' => 'MySQL'
            ];
        } catch (\Exception $e) {
            $health['status'] = 'degraded';
            $health['services']['database'] = [
                'status' => 'down',
                'error' => $e->getMessage()
            ];
        }

        // Check Redis
        try {
            $redis = \Config\Services::cache();
            $redis->save('health_check', 'ok', 5);
            $test = $redis->get('health_check');

            $redisStatus = $test === 'ok' ? 'up' : 'down';
            $health['services']['redis'] = [
                'status' => $redisStatus,
                'type' => 'Redis'
            ];

            if ($redisStatus === 'down') {
                $health['status'] = 'degraded';
            }
        } catch (\Exception $e) {
            $health['status'] = 'degraded';
            $health['services']['redis'] = [
                'status' => 'down',
                'type' => 'Redis',
                'error' => $e->getMessage()
            ];
        }

        // Determine HTTP status code
        $httpStatus = 200;
        if ($health['status'] === 'degraded') {
            $httpStatus = 503; // Service Unavailable
        }

        return $this->respond($health, $httpStatus);
    }
}
