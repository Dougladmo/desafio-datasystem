<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class IdempotencyFilter implements FilterInterface
{
    protected $cache;

    public function __construct()
    {
        $this->cache = Services::cache();
    }

    /**
     * Check if request has been processed before
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Only apply to POST requests
        if ($request->getMethod() !== 'post') {
            return;
        }

        $idempotencyKey = $request->getHeader('Idempotency-Key');

        if (!$idempotencyKey || !$idempotencyKey->getValue()) {
            return;
        }

        $key = 'idempotency:' . $idempotencyKey->getValue();

        // Check if this key has been processed before
        $cachedResponse = $this->cache->get($key);

        if ($cachedResponse) {
            // Return cached response
            $response = Services::response();
            $response->setJSON($cachedResponse['body']);
            $response->setStatusCode($cachedResponse['status']);
            return $response;
        }

        // Store the key to mark as processing
        $request->idempotencyKey = $key;
    }

    /**
     * Cache the response for future identical requests
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Only cache successful POST responses
        if ($request->getMethod() !== 'post') {
            return;
        }

        if (!isset($request->idempotencyKey)) {
            return;
        }

        $statusCode = $response->getStatusCode();

        // Only cache successful responses (2xx)
        if ($statusCode >= 200 && $statusCode < 300) {
            $cacheData = [
                'status' => $statusCode,
                'body' => $response->getBody()
            ];

            // Cache for 24 hours
            $this->cache->save($request->idempotencyKey, $cacheData, 86400);
        }
    }
}
