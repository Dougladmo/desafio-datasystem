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

        try {
            // Check if this key has been processed before
            $cachedResponse = $this->cache->get($key);

            if ($cachedResponse) {
                // Return cached response
                $response = Services::response();
                $response->setJSON(json_decode($cachedResponse['body'], true));
                $response->setStatusCode($cachedResponse['status']);
                return $response;
            }

            // Store the key to mark as processing
            $request->idempotencyKey = $key;
        } catch (\Exception $e) {
            // Log error but don't fail the request
            log_message('error', 'Idempotency filter error (before): ' . $e->getMessage());
        }
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
            try {
                // Get body as string
                $body = (string)$response->getBody();

                $cacheData = [
                    'status' => $statusCode,
                    'body' => $body
                ];

                // Cache for 24 hours
                $result = $this->cache->save($request->idempotencyKey, $cacheData, 86400);

                if ($result) {
                    log_message('info', 'Idempotency key cached: ' . $request->idempotencyKey);
                } else {
                    log_message('error', 'Failed to cache idempotency key: ' . $request->idempotencyKey);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the response
                log_message('error', 'Idempotency filter error (after): ' . $e->getMessage());
            }
        }
    }
}
