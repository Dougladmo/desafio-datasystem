<?php

namespace App\Controllers;

class DocsController extends BaseController
{
    /**
     * Exibe a interface Swagger UI
     */
    public function index()
    {
        $baseUrl = base_url();

        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Gestão de Propostas</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui.css">
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5.10.5/favicon-32x32.png" sizes="32x32">
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            padding: 0;
        }
        .swagger-ui .topbar {
            background-color: #2c3e50;
        }
        .swagger-ui .topbar .download-url-wrapper .download-url-button {
            background-color: #3498db;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "{$baseUrl}/openapi.yaml",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                docExpansion: "list",
                filter: true,
                persistAuthorization: true,
                tryItOutEnabled: true,
                supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                onComplete: function() {
                    console.log('Swagger UI loaded successfully');
                }
            });

            window.ui = ui;
        };
    </script>
</body>
</html>
HTML;

        return $this->response
            ->setContentType('text/html')
            ->setBody($html);
    }

    /**
     * Retorna o arquivo OpenAPI em formato JSON
     */
    public function json()
    {
        $yamlFile = FCPATH . 'openapi.yaml';

        if (!file_exists($yamlFile)) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'error' => 'OpenAPI specification not found'
                ]);
        }

        // Lê o YAML e converte para JSON
        $yaml = file_get_contents($yamlFile);

        // Parse YAML para array (requer extensão yaml ou biblioteca)
        // Como CodeIgniter não tem YAML nativo, vamos apenas retornar uma mensagem
        // ou você pode instalar symfony/yaml via composer

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'message' => 'OpenAPI YAML available at /openapi.yaml',
                'swagger_ui' => base_url('api/docs')
            ]);
    }
}
