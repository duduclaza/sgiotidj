<?php
/**
 * Rotas do Módulo Homologações (Kanban)
 * 
 * Sistema Kanban para gestão de home logações
 */

use App\Controllers\HomologacoesKanbanController;
use App\Controllers\ChecklistsController;
use App\Controllers\HomologacoesTiposController;

// ===== HOMOLOGAÇÕES =====

$router->get('/homologacoes', [HomologacoesKanbanController::class, 'index']);
$router->post('/homologacoes/store', [HomologacoesKanbanController::class, 'store']);
$router->post('/homologacoes/update-status', [HomologacoesKanbanController::class, 'updateStatus']);
$router->post('/homologacoes/{id}/status', [HomologacoesKanbanController::class, 'updateStatusById']);
$router->post('/homologacoes/{id}/contadores', [HomologacoesKanbanController::class, 'updateContadores']);
$router->get('/homologacoes/{id}/details', [HomologacoesKanbanController::class, 'details']);
$router->post('/homologacoes/upload-anexo', [HomologacoesKanbanController::class, 'uploadAnexo']);
$router->get('/homologacoes/anexo/{id}', [HomologacoesKanbanController::class, 'downloadAnexo']);
$router->post('/homologacoes/delete', [HomologacoesKanbanController::class, 'delete']);
$router->post('/homologacoes/registrar-dados-etapa', [HomologacoesKanbanController::class, 'registrarDadosEtapa']);
$router->get('/homologacoes/{id}/relatorio', [HomologacoesKanbanController::class, 'gerarRelatorio']);
$router->get('/homologacoes/{id}/logs', [HomologacoesKanbanController::class, 'buscarLogs']);
$router->get('/homologacoes/{id}/logs/export', [HomologacoesKanbanController::class, 'exportarLogs']);

// ===== TIPOS DE PRODUTO =====

$router->get('/homologacoes/tipos', [HomologacoesTiposController::class, 'index']);
$router->post('/homologacoes/tipos/store', [HomologacoesTiposController::class, 'store']);
$router->post('/homologacoes/tipos/update', [HomologacoesTiposController::class, 'update']);
$router->post('/homologacoes/tipos/delete', [HomologacoesTiposController::class, 'delete']);
$router->get('/api/homologacoes/tipos', [HomologacoesTiposController::class, 'listApi']);

// ===== CHECKLISTS =====

$router->post('/homologacoes/checklists/create', [ChecklistsController::class, 'create']);
$router->get('/homologacoes/checklists/list', [ChecklistsController::class, 'list']);
$router->get('/homologacoes/checklists/{id}', [ChecklistsController::class, 'show']);
$router->post('/homologacoes/checklists/{id}/update', [ChecklistsController::class, 'update']);
$router->delete('/homologacoes/checklists/{id}', [ChecklistsController::class, 'delete']);
$router->post('/homologacoes/checklists/salvar-respostas', [ChecklistsController::class, 'salvarRespostas']);
$router->get('/homologacoes/checklists/respostas/{id}', [ChecklistsController::class, 'buscarRespostas']);
