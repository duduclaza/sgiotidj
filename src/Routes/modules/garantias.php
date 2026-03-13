<?php
/**
 * Rotas do Módulo Garantias
 * 
 * Sistema de gestão de garantias e tickets
 */

use App\Controllers\GarantiasController;

// ===== LISTAGEM E CRUD =====

$router->get('/garantias', [GarantiasController::class, 'index']);
$router->post('/garantias', [GarantiasController::class, 'create']);
$router->get('/garantias/list', [GarantiasController::class, 'list']);
$router->get('/garantias/fornecedores', [GarantiasController::class, 'listFornecedores']);
$router->post('/garantias/create', [GarantiasController::class, 'create']);

// ===== DETALHES E VISUALIZAÇÃO =====

$router->get('/garantias/{id}/detalhes', [GarantiasController::class, 'detalhes']);
$router->get('/garantias/{id}', [GarantiasController::class, 'show']);

// ===== ATUALIZAÇÃO =====

$router->post('/garantias/{id}/update', [GarantiasController::class, 'update']);
$router->post('/garantias/{id}/update-status', [GarantiasController::class, 'updateStatus']);
$router->post('/garantias/{id}/update-tratativa', [GarantiasController::class, 'updateTratativa']);
$router->post('/garantias/{id}/delete', [GarantiasController::class, 'delete']);

// ===== ANEXOS =====

$router->get('/garantias/anexo/{id}', [GarantiasController::class, 'downloadAnexo']);
$router->get('/garantias/{id}/anexos/download-all', [GarantiasController::class, 'downloadAllAnexos']);
$router->post('/garantias/anexo/{id}/delete', [GarantiasController::class, 'deleteAnexo']);

// ===== TICKETS E REQUISIÇÕES =====

$router->get('/garantias/ficha', [GarantiasController::class, 'ficha']);
$router->post('/garantias/gerar-ticket', [GarantiasController::class, 'gerarTicket']);
$router->get('/garantias/requisicao', [GarantiasController::class, 'requisicao']);
$router->post('/garantias/requisicao/criar', [GarantiasController::class, 'criarRequisicao']);

// ===== PENDENTES E CONSULTA =====

$router->get('/garantias/pendentes', [GarantiasController::class, 'pendentes']);
$router->get('/garantias/consulta', [GarantiasController::class, 'consulta']);
$router->get('/garantias/consulta/buscar', [GarantiasController::class, 'buscarGarantia']);

// ===== REQUISIÇÕES - LISTAGEM E GESTÃO =====

$router->get('/garantias/requisicoes/list', [GarantiasController::class, 'listarRequisicoes']);
$router->get('/garantias/requisicoes/{id}', [GarantiasController::class, 'getRequisicao']);
$router->post('/garantias/requisicoes/{id}/processar', [GarantiasController::class, 'marcarRequisicaoProcessada']);
$router->post('/garantias/requisicoes/{id}/excluir', [GarantiasController::class, 'excluirRequisicao']);
