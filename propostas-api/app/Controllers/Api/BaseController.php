<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;

class BaseController extends ResourceController
{
    protected $format = 'json';

    /**
     * Format error response
     */
    protected function formatError(string $message, int $code = 400, array $errors = []): ResponseInterface
    {
        $response = [
            'error' => $message,
            'code' => $code
        ];

        if (!empty($errors)) {
            $response['details'] = $errors;
        }

        return $this->respond($response, $code);
    }

    /**
     * Format success response
     */
    protected function formatSuccess($data, string $message = 'Success', int $code = 200): ResponseInterface
    {
        return $this->respond([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Format validation errors
     */
    protected function formatValidationErrors($model): array
    {
        $errors = $model->errors();
        $formatted = [];

        foreach ($errors as $field => $error) {
            $formatted[$field] = is_array($error) ? implode(', ', $error) : $error;
        }

        return $formatted;
    }
}
