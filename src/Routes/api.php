<?php
/**
 * Rotas de API
 * 
 * Endpoints de API REST e integrações externas
 */

use App\Controllers\ApiController;
use App\Controllers\UsersController;
use App\Controllers\ProfilesController;
use App\Controllers\TonersController;
use App\Controllers\RegistrosController;
use App\Controllers\MaquinasController;
use App\Controllers\PecasController;
use App\Controllers\ProfileController;
use App\Controllers\NotificationsController;

// ===== APIS INTERNAS =====

$router->get('/api/users', [UsersController::class, 'getUsers']);
$router->get('/api/profiles', [ProfilesController::class, 'getProfilesList']);
$router->get('/api/toner', [TonersController::class, 'getTonerData']);
$router->get('/api/setores', [RegistrosController::class, 'getDepartamentos']);
$router->get('/api/filiais', [RegistrosController::class, 'getFiliais']);
$router->get('/api/parametros', [RegistrosController::class, 'getParametros']);

// API para seleção de produtos (Amostragens 2.0 e Garantias)
$router->get('/api/toners', [TonersController::class, 'apiListToners']);
$router->get('/api/maquinas', [MaquinasController::class, 'apiListMaquinas']);
$router->get('/api/pecas', [PecasController::class, 'apiListPecas']);

// ===== PROFILE API =====

$router->get('/api/profile', [ProfileController::class, 'getProfile']);
$router->post('/api/profile/password', [ProfileController::class, 'changePassword']);
$router->post('/api/profile/photo', [ProfileController::class, 'uploadPhoto']);
$router->post('/api/profile/notifications', [ProfileController::class, 'updateNotifications']);

// ===== NOTIFICATIONS API =====

$router->get('/api/notifications', [NotificationsController::class, 'getNotifications']);
$router->post('/api/notifications/{id}/read', [NotificationsController::class, 'markAsRead']);
$router->post('/api/notifications/read-all', [NotificationsController::class, 'markAllAsRead']);
$router->post('/api/notifications/clear-history', [NotificationsController::class, 'clearHistory']);
$router->get('/notifications/{id}/redirect', [NotificationsController::class, 'redirect']);
