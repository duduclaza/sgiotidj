<?php
/**
 * Rotas do Módulo Toners
 * 
 * Inclui:
 * - Cadastro de toners
 * - Toners retornados
 * - Import/Export
 * - Amostragens (legado)
 */

use App\Controllers\TonersController;
use App\Controllers\AmostragemController;

// ===== CADASTRO DE TONERS =====

$router->get('/toners/cadastro', [TonersController::class , 'cadastro']);
$router->post('/toners/cadastro', [TonersController::class , 'store']);
$router->post('/toners/update', [TonersController::class , 'update']);
$router->post('/toners/delete', [TonersController::class , 'delete']);
$router->delete('/toners/{id}', [TonersController::class , 'deleteAjax']);

// Toners com Defeito
$router->get('/toners/defeitos', [TonersController::class , 'defeitos']);

// Import/Export
$router->get('/toners/template', [TonersController::class , 'downloadTemplate']);
$router->post('/toners/import', [TonersController::class , 'import']);
$router->get('/toners/export', [TonersController::class , 'exportExcelAdvanced']);

// ===== TONERS RETORNADOS =====

$router->get('/toners/retornados', [TonersController::class , 'retornados']);
$router->post('/toners/retornados', [TonersController::class , 'storeRetornado']);
$router->delete('/toners/retornados/delete/{id}', [TonersController::class , 'deleteRetornado']);
$router->get('/toners/retornados/export', [TonersController::class , 'exportRetornados']);
$router->post('/toners/retornados/import', [TonersController::class , 'importRetornados']);

// ===== AMOSTRAGENS (LEGADO) =====

$router->get('/toners/amostragens', [AmostragemController::class , 'index']);
$router->get('/toners/amostragens/list', [AmostragemController::class , 'list']);
$router->post('/toners/amostragens', [AmostragemController::class , 'store']);
$router->post('/toners/amostragens/test', [AmostragemController::class , 'testStore']);
$router->post('/toners/amostragens/{id}/update', [AmostragemController::class , 'update']);
$router->delete('/toners/amostragens/{id}', [AmostragemController::class , 'delete']);
$router->get('/toners/amostragens/{id}/pdf', [AmostragemController::class , 'show']);
$router->get('/toners/amostragens/{id}/evidencias', [AmostragemController::class , 'getEvidencias']);
$router->get('/toners/amostragens/{id}/evidencia/{evidenciaId}', [AmostragemController::class , 'evidencia']);
