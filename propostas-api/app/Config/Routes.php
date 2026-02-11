<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API v1 Routes
$routes->group('api/v1', static function ($routes) {
    // Clientes
    $routes->post('clientes', 'Api\V1\ClienteController::create');
    $routes->get('clientes/(:num)', 'Api\V1\ClienteController::show/$1');

    // Propostas
    $routes->post('propostas', 'Api\V1\PropostaController::create', ['filter' => 'idempotency']);
    $routes->put('propostas/(:num)', 'Api\V1\PropostaController::update/$1');
    $routes->get('propostas/(:num)', 'Api\V1\PropostaController::show/$1');
    $routes->get('propostas', 'Api\V1\PropostaController::index');

    // Ações de Proposta
    $routes->post('propostas/(:num)/submit', 'Api\V1\PropostaController::submit/$1', ['filter' => 'idempotency']);
    $routes->post('propostas/(:num)/approve', 'Api\V1\PropostaController::approve/$1');
    $routes->post('propostas/(:num)/reject', 'Api\V1\PropostaController::reject/$1');
    $routes->post('propostas/(:num)/cancel', 'Api\V1\PropostaController::cancel/$1');

    // Auditoria
    $routes->get('propostas/(:num)/auditoria', 'Api\V1\PropostaController::auditoria/$1');
});
