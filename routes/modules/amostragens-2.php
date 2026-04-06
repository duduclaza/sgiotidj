<?php
/**
 * Rotas do Módulo Amostragens 2.0
 * 
 * Sistema moderno de gestão de amostragens
 */

use App\Controllers\Amostragens2Controller;

// ===== PRINCIPAL =====

$router->get('/amostragens-2', [Amostragens2Controller::class, 'index']);
$router->post('/amostragens-2/store', [Amostragens2Controller::class, 'store']);

// ===== EDIÇÃO E VISUALIZAÇÃO =====

$router->get('/amostragens-2/{id}/editar-resultados', [Amostragens2Controller::class, 'editarResultados']);
$router->get('/amostragens-2/{id}/download-nf', [Amostragens2Controller::class, 'downloadNf']);
$router->get('/amostragens-2/{id}/details', [Amostragens2Controller::class, 'details']);
$router->get('/amostragens-2/{id}/details-json', [Amostragens2Controller::class, 'getDetailsJson']);

// ===== EVIDÊNCIAS =====

$router->get('/amostragens-2/{id}/evidencias', [Amostragens2Controller::class, 'getEvidencias']);
$router->get('/amostragens-2/{id}/download-evidencia/{evidenciaId}', [Amostragens2Controller::class, 'downloadEvidencia']);

// ===== ATUALIZAÇÃO E EXCLUSÃO =====

$router->post('/amostragens-2/update', [Amostragens2Controller::class, 'update']);
$router->post('/amostragens-2/update-status', [Amostragens2Controller::class, 'updateStatus']);
$router->post('/amostragens-2/delete', [Amostragens2Controller::class, 'delete']);

// ===== EMAIL E RELATÓRIOS =====
 
$router->post('/amostragens-2/enviar-email', [Amostragens2Controller::class, 'enviarEmailDetalhes']);
$router->get('/amostragens-2/export', [Amostragens2Controller::class, 'exportExcel']);
$router->get('/amostragens-2/graficos', [Amostragens2Controller::class, 'graficos']);

// ===== IMPORTAÇÃO XML =====

$router->post('/amostragens-2/import-xml/parse', [Amostragens2Controller::class, 'parseXml']);
$router->post('/amostragens-2/import-xml/store', [Amostragens2Controller::class, 'storeImported']);
