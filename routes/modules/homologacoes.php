<?php
/**
 * Rotas do Módulo Homologações (Kanban)
 * 
 * Sistema Kanban para gestão de home logações
 */

use App\Controllers\HomologacoesKanbanController;
use App\Controllers\ChecklistsController;
use App\Controllers\Homologacoes2Controller;

// ===== MÓDULO HOMOLOGAÇÕES (Consolidado) =====

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

$router->get('/homologacoes/tutorial', function() {
    include __DIR__ . '/../../views/homologacoes/tutorial.php';
});

// ===== CHECKLISTS =====

$router->post('/homologacoes/checklists/create', [ChecklistsController::class, 'create']);
$router->get('/homologacoes/checklists/list', [ChecklistsController::class, 'list']);
$router->get('/homologacoes/checklists/{id}', [ChecklistsController::class, 'show']);
$router->post('/homologacoes/checklists/{id}/update', [ChecklistsController::class, 'update']);
$router->delete('/homologacoes/checklists/{id}', [ChecklistsController::class, 'delete']);
$router->post('/homologacoes/checklists/salvar-respostas', [ChecklistsController::class, 'salvarRespostas']);
$router->get('/homologacoes/checklists/respostas/{id}', [ChecklistsController::class, 'buscarRespostas']);
