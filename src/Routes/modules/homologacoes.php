<?php
/**
 * Rotas do Módulo Homologações
 * 
 * Sistema atual para gestão de homologações
 */

use App\Controllers\Homologacoes2Controller;

// ===== HOMOLOGAÇÕES =====

$router->get('/homologacoes', [Homologacoes2Controller::class, 'index']);
$router->post('/homologacoes', [Homologacoes2Controller::class, 'index']);
$router->get('/homologacoes/nova', [Homologacoes2Controller::class, 'create']);
$router->post('/homologacoes/nova', [Homologacoes2Controller::class, 'create']);
$router->get('/homologacoes/minha-fila', [Homologacoes2Controller::class, 'queue']);
$router->get('/homologacoes/logistica', [Homologacoes2Controller::class, 'logistics']);
$router->post('/homologacoes/logistica', [Homologacoes2Controller::class, 'logistics']);
$router->get('/homologacoes/monitoramento', [Homologacoes2Controller::class, 'monitoring']);
$router->post('/homologacoes/monitoramento', [Homologacoes2Controller::class, 'monitoring']);
$router->get('/homologacoes/gerenciar', [Homologacoes2Controller::class, 'manage']);
$router->post('/homologacoes/gerenciar', [Homologacoes2Controller::class, 'manage']);
$router->get('/homologacoes/public/{token}', [Homologacoes2Controller::class, 'publicChecklist']);
$router->post('/homologacoes/public/{token}', [Homologacoes2Controller::class, 'publicChecklist']);
$router->get('/homologacoes/{id}', [Homologacoes2Controller::class, 'detail']);
$router->post('/homologacoes/{id}', [Homologacoes2Controller::class, 'detail']);
