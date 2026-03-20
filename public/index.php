<?php
// Sistema SGQ OTI DJ - Versão Corrigida
date_default_timezone_set('America/Sao_Paulo');
session_start();

// No-cache headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Middleware\PermissionMiddleware;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
try {
    $dotenv->load();
}
catch (Exception $e) {
    die("Erro ao carregar .env: " . $e->getMessage());
}

// Error reporting with IP whitelist for development
$isDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

// Security: Only allow debug mode for whitelisted IPs
if ($isDebug) {
    $allowedDebugIPs = [
        '127.0.0.1', // Localhost IPv4
        '::1', // Localhost IPv6
        // Adicione IPs de desenvolvimento autorizados aqui:
        // '192.168.1.100',
    ];

    $clientIP = $_SERVER['REMOTE_ADDR'] ?? '';

    // Se não estiver na whitelist, desabilita debug mesmo que APP_DEBUG=true
    if (!in_array($clientIP, $allowedDebugIPs)) {
        $isDebug = false;
        error_log("Debug mode tentado de IP não autorizado: {$clientIP}");
    }
}

// Configure error reporting
if ($isDebug) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// Migrations system removed - using direct queries now

// Create router
$router = new Router(__DIR__);

// Do NOT run migrations on every request to avoid DB connection/timeout issues in production

// Auth routes (match AuthController methods: login = show page, authenticate = process)
$router->get('/login', [App\Controllers\AuthController::class , 'login']);
$router->post('/auth/login', [App\Controllers\AuthController::class , 'authenticate']);
$router->get('/logout', [App\Controllers\AuthController::class , 'logout']);
$router->get('/register', [App\Controllers\AuthController::class , 'register']);
$router->post('/auth/register', [App\Controllers\AuthController::class , 'processRegister']);

// Password Reset routes
$router->get('/password-reset/request', [App\Controllers\PasswordResetController::class , 'requestResetPage']);
$router->post('/password-reset/request', [App\Controllers\PasswordResetController::class , 'requestReset']);
$router->get('/password-reset/verify', [App\Controllers\PasswordResetController::class , 'verifyCodePage']);
$router->post('/password-reset/verify-code', [App\Controllers\PasswordResetController::class , 'verifyCode']);
$router->get('/password-reset/new', [App\Controllers\PasswordResetController::class , 'resetPasswordPage']);
$router->post('/password-reset/reset', [App\Controllers\PasswordResetController::class , 'resetPassword']);

// Access Request routes
$router->get('/request-access', [App\Controllers\AccessRequestController::class , 'requestAccess']);
$router->post('/access-request/process', [App\Controllers\AccessRequestController::class , 'processRequest']);
$router->get('/access-request/filiais', [App\Controllers\AccessRequestController::class , 'getFiliais']);
$router->get('/access-request/departamentos', [App\Controllers\AccessRequestController::class , 'getDepartamentos']);

// Admin Access Request routes
$router->get('/admin/access-requests', [App\Controllers\AccessRequestController::class , 'index']);
$router->get('/admin/access-requests/list', [App\Controllers\AccessRequestController::class , 'listPendingRequests']);
$router->get('/admin/access-requests/profiles', [App\Controllers\AccessRequestController::class , 'listProfiles']);
$router->post('/admin/access-requests/approve', [App\Controllers\AccessRequestController::class , 'approveRequest']);
$router->post('/admin/access-requests/reject', [App\Controllers\AccessRequestController::class , 'rejectRequest']);

// Lightweight root: redirect unauthenticated users to /login to avoid heavy controller
$router->get('/', function () {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    // Verificar se tem permissão para dashboard
    if (\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
        // Tem permissão: mostrar dashboard
        (new App\Controllers\AdminController())->dashboard();
    }
    else {
        // Não tem permissão: redirecionar para página inicial
        header('Location: /inicio');
        exit;
    }
});

// Home/Início route - acessível a todos os usuários autenticados
$router->get('/inicio', [App\Controllers\HomeController::class , 'index']);

// Dashboard em manutenção - página informativa bonita
$router->get('/dashboard-manutencao', function () {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    $title = 'Dashboard em Manutenção - SGQ OTI DJ';
    $viewFile = __DIR__ . '/../views/pages/dashboard-manutencao.php';
    include __DIR__ . '/../views/layouts/main.php';
});

// Dashboard route - painel administrativo completo (com verificação de permissão)
$router->get('/dashboard', function () {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    // Verificar se tem permissão para dashboard
    if (\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
        (new App\Controllers\AdminController())->dashboard();
    }
    else {
        // Sem permissão: redirecionar para página inicial
        header('Location: /inicio');
        exit;
    }
});

// Dashboard 2.0
$router->get('/dashboard-2', [App\Controllers\AdminController::class, 'dashboard2']);
$router->get('/dashboard-2/triagem', [App\Controllers\AdminController::class, 'dashboard2Triagem']);
$router->get('/dashboard-2/triagem/data', [App\Controllers\AdminController::class, 'dashboard2TriagemData']);
$router->get('/dashboard-2/triagem/reprovados', [App\Controllers\AdminController::class, 'dashboard2TriagemReprovados']);

// Rota de diagnóstico POPs (apenas para admins)
$router->get('/admin/diagnostico/pops-pendentes', [App\Controllers\PopItsController::class , 'diagnosticoPendentes']);

// Rota de diagnóstico de permissões (apenas para admins)
$router->get('/admin/diagnostico/permissoes-usuario', [App\Controllers\AdminController::class , 'diagnosticoPermissoes']);

// Admin routes (com verificação de permissão)
$router->get('/admin', function () {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    // Verificar se tem permissão para dashboard/admin
    if (\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
        (new App\Controllers\AdminController())->dashboard();
    }
    else {
        // Sem permissão: redirecionar para página inicial
        header('Location: /inicio');
        exit;
    }
});
$router->get('/admin/dashboard/data', [App\Controllers\AdminController::class , 'getDashboardData']);
$router->get('/admin/dashboard/ranking-clientes', [App\Controllers\AdminController::class , 'getRankingClientes']);
$router->get('/admin/dashboard/retornados-por-clientes', [App\Controllers\AdminController::class , 'getRetornadosPorClientes']);
$router->get('/admin/dashboard/exportar-retornados-clientes', [App\Controllers\AdminController::class , 'exportRetornadosPorClientes']);
$router->get('/admin/dashboard/toners-por-cliente', [App\Controllers\AdminController::class , 'getTonersPorCliente']);
$router->get('/admin/dashboard/amostragens-data', [App\Controllers\AdminController::class , 'getAmostragemsDashboardData']);
$router->get('/admin/dashboard/fornecedores-data', [App\Controllers\AdminController::class , 'fornecedoresData']);
$router->get('/admin/fornecedor-itens', [App\Controllers\AdminController::class , 'fornecedorItens']);
$router->get('/admin/amostragens-reprovadas-mes', [App\Controllers\AdminController::class , 'amostragemReprovadasMes']);
$router->get('/admin/dashboard/melhorias-data', [App\Controllers\AdminController::class , 'getMelhoriasData']);
$router->get('/admin/dashboard/nao-conformidades-data', [App\Controllers\AdminController::class , 'getNaoConformidadesData']);
$router->get('/admin/dashboard/departamentos', [App\Controllers\AdminController::class , 'getDepartamentos']);
$router->get('/admin/melhorias/por-departamento', [App\Controllers\AdminController::class , 'getMelhoriasPorDepartamento']);
// Rota de diagnóstico detalhado do dashboard (apenas para debug)
$router->get('/admin/dashboard/diagnostico', [App\Controllers\AdminController::class , 'diagnosticoDashboard']);
$router->get('/admin/users', [App\Controllers\AdminController::class , 'users']);
$router->get('/admin/invitations', [App\Controllers\AdminController::class , 'invitations']);
$router->post('/admin/users/create', [App\Controllers\AdminController::class , 'createUser']);
$router->post('/admin/users/update', [App\Controllers\AdminController::class , 'updateUser']);
$router->post('/admin/users/delete', [App\Controllers\AdminController::class , 'deleteUser']);
$router->post('/admin/users/send-credentials', [App\Controllers\AdminController::class , 'sendCredentials']);
$router->get('/admin/users/{id}/permissions', [App\Controllers\AdminController::class , 'userPermissions']);
$router->post('/admin/users/{id}/permissions', [App\Controllers\AdminController::class , 'updateUserPermissions']);

// Toners routes
$router->get('/toners/cadastro', [App\Controllers\TonersController::class , 'cadastro']);
$router->post('/toners/cadastro', [App\Controllers\TonersController::class , 'store']);
$router->post('/toners/update', [App\Controllers\TonersController::class , 'update']);
$router->post('/toners/delete', [App\Controllers\TonersController::class , 'delete']);
$router->delete('/toners/{id}', [App\Controllers\TonersController::class , 'deleteAjax']);
$router->get('/toners/template', [App\Controllers\TonersController::class , 'downloadTemplate']);
$router->get('/toners/retornados', [App\Controllers\TonersController::class , 'retornados']);
$router->post('/toners/retornados', [App\Controllers\TonersController::class , 'storeRetornado']);
$router->delete('/toners/retornados/delete/{id}', [App\Controllers\TonersController::class , 'deleteRetornado']);
$router->get('/toners/retornados/export', [App\Controllers\TonersController::class , 'exportRetornados']);
$router->post('/toners/retornados/import', [App\Controllers\TonersController::class , 'importRetornados']);
$router->get('/toners/retornados/{id}', [App\Controllers\TonersController::class , 'getRetornado']);
$router->post('/toners/retornados/update', [App\Controllers\TonersController::class , 'updateRetornado']);
$router->post('/toners/import', [App\Controllers\TonersController::class , 'import']);
$router->get('/toners/export', [App\Controllers\TonersController::class , 'exportExcelAdvanced']);
// Toners com Defeito
$router->get('/toners/defeitos', [App\Controllers\TonersController::class , 'defeitos']);
$router->post('/toners/defeitos/store', [App\Controllers\TonersController::class , 'storeDefeito']);
$router->get('/toners/defeitos/{id}/foto/{n}', [App\Controllers\TonersController::class , 'downloadFotoDefeito']);
$router->post('/toners/defeitos/delete', [App\Controllers\TonersController::class , 'deleteDefeito']);
$router->post('/toners/defeitos/devolutiva/store', [App\Controllers\TonersController::class , 'storeDevolutiva']);
$router->get('/toners/defeitos/{id}/devolutiva-foto/{n}', [App\Controllers\TonersController::class , 'downloadFotoDevolutiva']);

// Triagem de Toners
$router->get('/triagem-toners', [App\Controllers\TriagemTonersController::class, 'index']);
$router->get('/triagem-toners/list', [App\Controllers\TriagemTonersController::class, 'list']);
$router->get('/triagem-toners/template', [App\Controllers\TriagemTonersController::class, 'downloadTemplate']);
$router->post('/triagem-toners/importar', [App\Controllers\TriagemTonersController::class, 'importar']);
$router->post('/triagem-toners/calcular', [App\Controllers\TriagemTonersController::class, 'calcular']);
$router->post('/triagem-toners/store', [App\Controllers\TriagemTonersController::class, 'store']);
$router->post('/triagem-toners/update', [App\Controllers\TriagemTonersController::class, 'update']);
$router->post('/triagem-toners/duplicate', [App\Controllers\TriagemTonersController::class, 'duplicate']);
$router->post('/triagem-toners/delete', [App\Controllers\TriagemTonersController::class, 'delete']);
$router->get('/triagem-toners/defeitos-codigo', [App\Controllers\TriagemTonersController::class, 'getDefeitosPorCodigo']);
$router->get('/triagem-toners/parametros', [App\Controllers\TriagemTonersController::class, 'getParametrosApi']);
$router->post('/triagem-toners/parametros/save', [App\Controllers\TriagemTonersController::class, 'saveParametros']);


// Melhoria Contínua 2.0 routes
$router->get('/melhoria-continua-2', [App\Controllers\MelhoriaContinua2Controller::class , 'index']);
$router->post('/melhoria-continua-2/store', [App\Controllers\MelhoriaContinua2Controller::class , 'store']);
$router->post('/melhoria-continua-2/update', [App\Controllers\MelhoriaContinua2Controller::class , 'update']);
$router->post('/melhoria-continua-2/update-status', [App\Controllers\MelhoriaContinua2Controller::class , 'updateStatus']);
$router->post('/melhoria-continua-2/{id}/update-status', [App\Controllers\MelhoriaContinua2Controller::class , 'updateStatus']);
$router->post('/melhoria-continua-2/{id}/update-pontuacao', [App\Controllers\MelhoriaContinua2Controller::class , 'updatePontuacao']);
$router->get('/melhoria-continua-2/{id}/details', [App\Controllers\MelhoriaContinua2Controller::class , 'details']);
$router->get('/melhoria-continua-2/{id}/view', [App\Controllers\MelhoriaContinua2Controller::class , 'view']);
$router->post('/melhoria-continua-2/delete', [App\Controllers\MelhoriaContinua2Controller::class , 'delete']);
$router->get('/melhoria-continua-2/export', [App\Controllers\MelhoriaContinua2Controller::class , 'exportExcel']);

// Não Conformidades routes
$router->get('/nao-conformidades', [App\Controllers\NaoConformidadesController::class , 'index']);
$router->post('/nao-conformidades/criar', [App\Controllers\NaoConformidadesController::class , 'criar']);
$router->get('/nao-conformidades/por-departamento', [App\Controllers\NaoConformidadesController::class , 'porDepartamento']);
$router->post('/nao-conformidades/{id}/registrar-acao', [App\Controllers\NaoConformidadesController::class , 'registrarAcao']);
$router->post('/nao-conformidades/{id}/mover-em-andamento', [App\Controllers\NaoConformidadesController::class , 'moverParaEmAndamento']);
$router->post('/nao-conformidades/{id}/marcar-solucionada', [App\Controllers\NaoConformidadesController::class , 'marcarSolucionada']);
$router->post('/nao-conformidades/{id}/excluir', [App\Controllers\NaoConformidadesController::class , 'excluir']);
$router->get('/nao-conformidades/{id}/download-anexo', [App\Controllers\NaoConformidadesController::class , 'downloadAnexo']);

// Amostragens 2.0 routes
$router->get('/amostragens-2', [App\Controllers\Amostragens2Controller::class , 'index']);
$router->post('/amostragens-2/store', [App\Controllers\Amostragens2Controller::class , 'store']);
$router->get('/amostragens-2/{id}/editar-resultados', [App\Controllers\Amostragens2Controller::class , 'editarResultados']);
$router->get('/amostragens-2/{id}/download-nf', [App\Controllers\Amostragens2Controller::class , 'downloadNf']);
$router->get('/amostragens-2/{id}/details', [App\Controllers\Amostragens2Controller::class , 'details']);
$router->get('/amostragens-2/{id}/details-json', [App\Controllers\Amostragens2Controller::class , 'getDetailsJson']);
$router->get('/amostragens-2/{id}/evidencias', [App\Controllers\Amostragens2Controller::class , 'getEvidencias']);
$router->get('/amostragens-2/{id}/download-evidencia/{evidenciaId}', [App\Controllers\Amostragens2Controller::class , 'downloadEvidencia']);
$router->post('/amostragens-2/update', [App\Controllers\Amostragens2Controller::class , 'update']);
$router->post('/amostragens-2/update-status', [App\Controllers\Amostragens2Controller::class , 'updateStatus']);
$router->post('/amostragens-2/delete', [App\Controllers\Amostragens2Controller::class , 'delete']);
$router->post('/amostragens-2/enviar-email', [App\Controllers\Amostragens2Controller::class , 'enviarEmailDetalhes']);
$router->get('/amostragens-2/export', [App\Controllers\Amostragens2Controller::class , 'exportExcel']);
$router->get('/amostragens-2/graficos', [App\Controllers\Amostragens2Controller::class , 'graficos']);

// Cadastro de Máquinas routes
$router->get('/cadastro-maquinas', [App\Controllers\CadastroMaquinasController::class , 'index']);
$router->post('/cadastro-maquinas/store', [App\Controllers\CadastroMaquinasController::class , 'store']);
$router->post('/cadastro-maquinas/update', [App\Controllers\CadastroMaquinasController::class , 'update']);
$router->post('/cadastro-maquinas/delete', [App\Controllers\CadastroMaquinasController::class , 'delete']);

// Cadastro de Peças routes
$router->get('/cadastro-pecas', [App\Controllers\CadastroPecasController::class , 'index']);
$router->post('/cadastro-pecas/store', [App\Controllers\CadastroPecasController::class , 'store']);
$router->post('/cadastro-pecas/update', [App\Controllers\CadastroPecasController::class , 'update']);
$router->post('/cadastro-pecas/delete', [App\Controllers\CadastroPecasController::class , 'delete']);
$router->post('/cadastro-pecas/import', [App\Controllers\CadastroPecasController::class , 'import']);

// Cadastro de Defeitos routes
$router->get('/cadastro-defeitos', [App\Controllers\CadastroDefeitosController::class , 'index']);
$router->post('/cadastro-defeitos/store', [App\Controllers\CadastroDefeitosController::class , 'store']);
$router->post('/cadastro-defeitos/update', [App\Controllers\CadastroDefeitosController::class , 'update']);
$router->post('/cadastro-defeitos/delete', [App\Controllers\CadastroDefeitosController::class , 'delete']);
$router->get('/cadastro-defeitos/{id}/imagem', [App\Controllers\CadastroDefeitosController::class , 'imagem']);

// Financeiro routes
$router->get('/financeiro', [App\Controllers\FinanceiroController::class , 'index']);
$router->post('/financeiro/anexar-comprovante', [App\Controllers\FinanceiroController::class , 'anexarComprovante']);
$router->get('/financeiro/{id}/download-comprovante', [App\Controllers\FinanceiroController::class , 'downloadComprovante']);

// Master routes
$router->get('/master/login', [App\Controllers\MasterController::class , 'loginPage']);
$router->post('/master/auth', [App\Controllers\MasterController::class , 'authenticate']);
$router->get('/master/dashboard', [App\Controllers\MasterController::class , 'dashboard']);
$router->post('/master/aprovar-pagamento', [App\Controllers\MasterController::class , 'aprovarPagamento']);
$router->get('/master/logout', [App\Controllers\MasterController::class , 'logout']);

// Other routes
// $router->get('/homologacoes', [App\Controllers\PageController::class, 'homologacoes']); // REMOVIDO - Agora usa HomologacoesKanbanController (linha 373)
// $router->get('/fluxogramas', [App\Controllers\PageController::class, 'fluxogramas']); // REMOVIDO - Agora usa FluxogramasController (linha 326)

// Controle de RC routes
$router->get('/controle-de-rc', [App\Controllers\ControleRcController::class , 'index']);
$router->get('/controle-rc', [App\Controllers\ControleRcController::class , 'index']);
$router->get('/controle-rc/list', [App\Controllers\ControleRcController::class , 'list']);
$router->post('/controle-rc/create', [App\Controllers\ControleRcController::class , 'create']);
$router->post('/controle-rc/update', [App\Controllers\ControleRcController::class , 'update']);
$router->post('/controle-rc/update-status', [App\Controllers\ControleRcController::class , 'updateStatus']);
$router->post('/controle-rc/delete', [App\Controllers\ControleRcController::class , 'delete']);
$router->post('/controle-rc/alterar-status', [App\Controllers\ControleRcController::class , 'alterarStatus']);
$router->get('/controle-rc/{id}', [App\Controllers\ControleRcController::class , 'show']);
$router->get('/controle-rc/{id}/print', [App\Controllers\ControleRcController::class , 'print']);
$router->post('/controle-rc/export', [App\Controllers\ControleRcController::class , 'exportReport']);
$router->get('/controle-rc/evidencia/{id}', [App\Controllers\ControleRcController::class , 'downloadEvidencia']);
$router->get('/toners/amostragens', [App\Controllers\AmostragemController::class , 'index']);
// Amostragens actions
$router->post('/toners/amostragens', [App\Controllers\AmostragemController::class , 'store']);
$router->post('/toners/amostragens/test', [App\Controllers\AmostragemController::class , 'testStore']);
$router->post('/toners/amostragens/{id}/update', [App\Controllers\AmostragemController::class , 'update']);
$router->delete('/toners/amostragens/{id}', [App\Controllers\AmostragemController::class , 'delete']);
$router->get('/toners/amostragens/{id}/pdf', [App\Controllers\AmostragemController::class , 'show']);
$router->get('/toners/amostragens/{id}/evidencias', [App\Controllers\AmostragemController::class , 'getEvidencias']);
$router->get('/toners/amostragens/{id}/evidencia/{evidenciaId}', [App\Controllers\AmostragemController::class , 'evidencia']);
// Garantias routes
$router->get('/garantias', [App\Controllers\GarantiasController::class , 'index']);
$router->post('/garantias', [App\Controllers\GarantiasController::class , 'create']); // Rota para o formulário
$router->get('/garantias/list', [App\Controllers\GarantiasController::class , 'list']);
$router->get('/garantias/fornecedores', [App\Controllers\GarantiasController::class , 'listFornecedores']);
$router->post('/garantias/create', [App\Controllers\GarantiasController::class , 'create']);
$router->get('/garantias/{id}/detalhes', [App\Controllers\GarantiasController::class , 'detalhes']);
$router->get('/garantias/{id}', [App\Controllers\GarantiasController::class , 'show']);
$router->post('/garantias/{id}/update', [App\Controllers\GarantiasController::class , 'update']);
$router->post('/garantias/{id}/update-status', [App\Controllers\GarantiasController::class , 'updateStatus']);
$router->post('/garantias/{id}/update-tratativa', [App\Controllers\GarantiasController::class , 'updateTratativa']);
$router->post('/garantias/{id}/delete', [App\Controllers\GarantiasController::class , 'delete']);
$router->get('/garantias/anexo/{id}', [App\Controllers\GarantiasController::class , 'downloadAnexo']);
$router->get('/garantias/{id}/anexos/download-all', [App\Controllers\GarantiasController::class , 'downloadAllAnexos']);
$router->post('/garantias/anexo/{id}/delete', [App\Controllers\GarantiasController::class , 'deleteAnexo']);
$router->get('/garantias/ficha', [App\Controllers\GarantiasController::class , 'ficha']);
$router->post('/garantias/gerar-ticket', [App\Controllers\GarantiasController::class , 'gerarTicket']);
$router->get('/garantias/requisicao', [App\Controllers\GarantiasController::class , 'requisicao']);
$router->post('/garantias/requisicao/criar', [App\Controllers\GarantiasController::class , 'criarRequisicao']);
$router->get('/garantias/pendentes', [App\Controllers\GarantiasController::class , 'pendentes']);
$router->get('/garantias/consulta', [App\Controllers\GarantiasController::class , 'consulta']);
$router->get('/garantias/consulta/buscar', [App\Controllers\GarantiasController::class , 'buscarGarantia']);
$router->get('/garantias/requisicoes/list', [App\Controllers\GarantiasController::class , 'listarRequisicoes']);
$router->get('/garantias/requisicoes/{id}', [App\Controllers\GarantiasController::class , 'getRequisicao']);
$router->post('/garantias/requisicoes/{id}/processar', [App\Controllers\GarantiasController::class , 'marcarRequisicaoProcessada']);
$router->post('/garantias/requisicoes/{id}/excluir', [App\Controllers\GarantiasController::class , 'excluirRequisicao']);

// Controle de Descartes routes
$router->get('/controle-descartes', [App\Controllers\ControleDescartesController::class , 'index']);
$router->get('/controle-descartes/list', [App\Controllers\ControleDescartesController::class , 'listDescartes']);
$router->post('/controle-descartes/create', [App\Controllers\ControleDescartesController::class , 'create']);
$router->post('/controle-descartes/update', [App\Controllers\ControleDescartesController::class , 'update']);
$router->post('/controle-descartes/delete', [App\Controllers\ControleDescartesController::class , 'delete']);
$router->post('/controle-descartes/alterar-status', [App\Controllers\ControleDescartesController::class , 'alterarStatus']);
$router->post('/controle-descartes/alterar-status-andamento', [App\Controllers\ControleDescartesController::class , 'alterarStatusAndamento']);
$router->get('/controle-descartes/exportar', [App\Controllers\ControleDescartesController::class , 'exportar']);
$router->get('/controle-descartes/{id}', [App\Controllers\ControleDescartesController::class , 'getDescarte']);
$router->get('/controle-descartes/anexo/{id}', [App\Controllers\ControleDescartesController::class , 'downloadAnexo']);
$router->get('/controle-descartes/template', [App\Controllers\ControleDescartesController::class , 'downloadTemplate']);
$router->post('/controle-descartes/importar', [App\Controllers\ControleDescartesController::class , 'importar']);
$router->get('/controle-descartes/relatorios', [App\Controllers\ControleDescartesController::class , 'relatorios']);

// Precificação de Coleta de Descartes
$router->get('/precificacao-coleta-descartes', [App\Controllers\PrecificacaoColetaDescartesController::class , 'index']);
$router->get('/precificacao-coleta-descartes/list', [App\Controllers\PrecificacaoColetaDescartesController::class , 'list']);
$router->post('/precificacao-coleta-descartes/create', [App\Controllers\PrecificacaoColetaDescartesController::class , 'create']);
$router->post('/precificacao-coleta-descartes/update', [App\Controllers\PrecificacaoColetaDescartesController::class , 'update']);
$router->post('/precificacao-coleta-descartes/delete', [App\Controllers\PrecificacaoColetaDescartesController::class , 'delete']);

// Auditorias routes
$router->get('/auditorias', [App\Controllers\AuditoriasController::class , 'index']);
$router->get('/auditorias/list', [App\Controllers\AuditoriasController::class , 'listAuditorias']);
$router->post('/auditorias/create', [App\Controllers\AuditoriasController::class , 'create']);
$router->post('/auditorias/update', [App\Controllers\AuditoriasController::class , 'update']);
$router->post('/auditorias/delete', [App\Controllers\AuditoriasController::class , 'delete']);
$router->get('/auditorias/{id}', [App\Controllers\AuditoriasController::class , 'getAuditoria']);
$router->get('/auditorias/anexo/{id}', [App\Controllers\AuditoriasController::class , 'downloadAnexo']);
$router->get('/auditorias/relatorios', [App\Controllers\AuditoriasController::class , 'relatorios']);

// 5W2H routes
$router->get('/5w2h', [App\Controllers\Planos5W2HController::class , 'index']);
$router->get('/5w2h/list', [App\Controllers\Planos5W2HController::class , 'listPlanos']);
$router->post('/5w2h/create', [App\Controllers\Planos5W2HController::class , 'create']);
$router->post('/5w2h/update', [App\Controllers\Planos5W2HController::class , 'update']);
$router->post('/5w2h/delete', [App\Controllers\Planos5W2HController::class , 'delete']);
$router->get('/5w2h/{id}', [App\Controllers\Planos5W2HController::class , 'getPlano']);
$router->get('/5w2h/details/{id}', [App\Controllers\Planos5W2HController::class , 'details']);
$router->get('/5w2h/print/{id}', [App\Controllers\Planos5W2HController::class , 'printPlano']);
$router->get('/5w2h/anexos/{id}', [App\Controllers\Planos5W2HController::class , 'anexos']);
$router->get('/5w2h/anexo/{id}', [App\Controllers\Planos5W2HController::class , 'downloadAnexo']);
$router->get('/5w2h/relatorios', [App\Controllers\Planos5W2HController::class , 'relatorios']);

// Não Conformidades routes
$router->get('/nao-conformidades', [App\Controllers\NaoConformidadesController::class , 'index']);
$router->post('/nao-conformidades/criar', [App\Controllers\NaoConformidadesController::class , 'criar']);
$router->get('/nao-conformidades/detalhes/{id}', [App\Controllers\NaoConformidadesController::class , 'detalhes']);
$router->post('/nao-conformidades/registrar-acao/{id}', [App\Controllers\NaoConformidadesController::class , 'registrarAcao']);
$router->post('/nao-conformidades/mover-em-andamento/{id}', [App\Controllers\NaoConformidadesController::class , 'moverParaEmAndamento']);
$router->post('/nao-conformidades/marcar-solucionada/{id}', [App\Controllers\NaoConformidadesController::class , 'marcarSolucionada']);
$router->get('/nao-conformidades/anexo/{id}', [App\Controllers\NaoConformidadesController::class , 'downloadAnexo']);
$router->post('/nao-conformidades/excluir/{id}', [App\Controllers\NaoConformidadesController::class , 'excluir']);

// NPS routes
$router->get('/nps', [App\Controllers\NpsController::class , 'index']);
$router->get('/nps/dashboard', [App\Controllers\NpsController::class , 'dashboard']);
$router->get('/nps/dashboard/data', [App\Controllers\NpsController::class , 'getDashboardData']);
$router->get('/nps/exportar-csv', [App\Controllers\NpsController::class , 'exportarCSV']);
$router->get('/nps/contar-orfas', [App\Controllers\NpsController::class , 'contarRespostasOrfas']);
$router->post('/nps/limpar-orfas', [App\Controllers\NpsController::class , 'limparRespostasOrfas']);
$router->get('/nps/listar', [App\Controllers\NpsController::class , 'listar']);
$router->post('/nps/criar', [App\Controllers\NpsController::class , 'criar']);
$router->post('/nps/editar', [App\Controllers\NpsController::class , 'editar']);
$router->post('/nps/toggle-status', [App\Controllers\NpsController::class , 'toggleStatus']);
$router->post('/nps/excluir', [App\Controllers\NpsController::class , 'excluir']);
$router->post('/nps/duplicar', [App\Controllers\NpsController::class , 'duplicar']);
$router->get('/nps/{id}/detalhes', [App\Controllers\NpsController::class , 'detalhes']);
$router->get('/nps/{id}/respostas', [App\Controllers\NpsController::class , 'verRespostas']);
$router->post('/nps/excluir-resposta', [App\Controllers\NpsController::class , 'excluirResposta']);
// Rotas públicas (SEM autenticação)
$router->get('/nps/responder/{id}', [App\Controllers\NpsController::class , 'responder']);
$router->post('/nps/salvar-resposta', [App\Controllers\NpsController::class , 'salvarResposta']);

// Suporte routes (Admin e Super Admin)
$router->get('/suporte', [App\Controllers\SuporteController::class , 'index']);
$router->post('/suporte/store', [App\Controllers\SuporteController::class , 'store']);
$router->post('/suporte/update-status', [App\Controllers\SuporteController::class , 'updateStatus']);
$router->post('/suporte/delete', [App\Controllers\SuporteController::class , 'delete']);
$router->get('/suporte/{id}/details', [App\Controllers\SuporteController::class , 'details']);
$router->get('/suporte/anexo/{anexoId}', [App\Controllers\SuporteController::class , 'downloadAnexo']);

// Admin/Config maintenance endpoints
$router->post('/admin/db/patch-amostragens', [App\Controllers\ConfigController::class , 'patchAmostragens']);
$router->post('/admin/db/run-migrations', [App\Controllers\ConfigController::class , 'runMigrations']);
$router->get('/admin/db/run-migrations', [App\Controllers\ConfigController::class , 'runMigrations']);
// Admin: sincronizar permissões do Administrador
$router->post('/admin/sync-admin-permissions', [App\Controllers\ConfigController::class , 'syncAdminPermissions']);
$router->get('/admin/sync-admin-permissions', [App\Controllers\ConfigController::class , 'syncAdminPermissions']);

// Profiles routes
$router->get('/admin/profiles', [App\Controllers\ProfilesController::class , 'index']);
$router->post('/admin/profiles/create', [App\Controllers\ProfilesController::class , 'create']);
$router->post('/admin/profiles/update', [App\Controllers\ProfilesController::class , 'update']);
$router->post('/admin/profiles/delete', [App\Controllers\ProfilesController::class , 'delete']);
$router->get('/admin/profiles/{id}/permissions', [App\Controllers\ProfilesController::class , 'getPermissions']);
$router->get('/admin/profiles/{id}/dashboard-tabs', [App\Controllers\ProfilesController::class , 'getDashboardTabPermissions']);

// ===== eLEARNING GESTOR =====
$router->get('/elearning/gestor', [App\Controllers\ELearningGestorController::class, 'dashboard']);
$router->get('/elearning/gestor/cursos', [App\Controllers\ELearningGestorController::class, 'cursos']);
$router->post('/elearning/gestor/cursos/store', [App\Controllers\ELearningGestorController::class, 'storeCurso']);
$router->post('/elearning/gestor/cursos/update', [App\Controllers\ELearningGestorController::class, 'updateCurso']);
$router->post('/elearning/gestor/cursos/delete', [App\Controllers\ELearningGestorController::class, 'deleteCurso']);
$router->get('/elearning/gestor/cursos/thumbnail', [App\Controllers\ELearningGestorController::class, 'thumbnailCurso']);
$router->get('/elearning/gestor/cursos/{id}/aulas', [App\Controllers\ELearningGestorController::class, 'aulas']);
$router->post('/elearning/gestor/aulas/store', [App\Controllers\ELearningGestorController::class, 'storeAula']);
$router->post('/elearning/gestor/aulas/delete', [App\Controllers\ELearningGestorController::class, 'deleteAula']);
$router->post('/elearning/gestor/provas/delete', [App\Controllers\ELearningGestorController::class, 'deleteProva']);

$router->get('/elearning/gestor/diploma/config', [App\Controllers\ELearningGestorController::class, 'diplomaConfig']);
$router->post('/elearning/gestor/diploma/save', [App\Controllers\ELearningGestorController::class, 'saveDiplomaConfig']);
$router->get('/elearning/gestor/diploma/logo', [App\Controllers\ELearningGestorController::class, 'diplomaLogo']);
$router->post('/elearning/gestor/materiais/upload', [App\Controllers\ELearningGestorController::class, 'uploadMaterial']);
$router->post('/elearning/gestor/materiais/delete', [App\Controllers\ELearningGestorController::class, 'deleteMaterial']);
$router->post('/elearning/gestor/materiais/update', [App\Controllers\ELearningGestorController::class, 'updateMaterial']);
$router->get('/elearning/gestor/cursos/{id}/provas', [App\Controllers\ELearningGestorController::class, 'provas']);
$router->post('/elearning/gestor/provas/store', [App\Controllers\ELearningGestorController::class, 'storeProva']);
$router->post('/elearning/gestor/questoes/store', [App\Controllers\ELearningGestorController::class, 'storeQuestao']);
$router->get('/elearning/gestor/cursos/{id}/matriculas', [App\Controllers\ELearningGestorController::class, 'matriculas']);
$router->post('/elearning/gestor/matriculas/store', [App\Controllers\ELearningGestorController::class, 'matricularColaborador']);
$router->get('/elearning/gestor/cursos/{id}/progresso', [App\Controllers\ELearningGestorController::class, 'progressoDashboard']);
$router->post('/elearning/gestor/certificados/emitir', [App\Controllers\ELearningGestorController::class, 'emitirCertificado']);

// ===== eLEARNING COLABORADOR =====
$router->get('/elearning/colaborador', [App\Controllers\ELearningColaboradorController::class, 'meusCursos']);
$router->post('/elearning/colaborador/matricular', [App\Controllers\ELearningColaboradorController::class, 'matricularSe']);
$router->get('/elearning/colaborador/cursos/{id}', [App\Controllers\ELearningColaboradorController::class, 'verCurso']);
$router->get('/elearning/colaborador/materiais/{id}/assistir', [App\Controllers\ELearningColaboradorController::class, 'assistirAula']);
$router->post('/elearning/colaborador/progresso/registrar', [App\Controllers\ELearningColaboradorController::class, 'registrarProgresso']);
$router->get('/elearning/colaborador/provas/{id}/fazer', [App\Controllers\ELearningColaboradorController::class, 'fazerProva']);
$router->post('/elearning/colaborador/provas/submeter', [App\Controllers\ELearningColaboradorController::class, 'submeterProva']);
$router->get('/elearning/colaborador/provas/resultado/{id}', [App\Controllers\ELearningColaboradorController::class, 'resultadoProva']);
$router->get('/elearning/colaborador/certificados', [App\Controllers\ELearningColaboradorController::class, 'meusCertificados']);
$router->get('/elearning/colaborador/certificados/{codigo}', [App\Controllers\ELearningColaboradorController::class, 'downloadCertificado']);


// Melhoria Continua routes - DESABILITADO (usar Melhoria Contínua 2.0)
// $router->get('/melhoria-continua/solicitacoes', [App\Controllers\MelhoriaContinuaController::class, 'index']);
// $router->get('/melhoria-continua/solicitacoes/create', [App\Controllers\MelhoriaContinuaController::class, 'create']);
// $router->post('/melhoria-continua/solicitacoes/store', [App\Controllers\MelhoriaContinuaController::class, 'store']);
// $router->get('/melhoria-continua/solicitacoes/list', [App\Controllers\MelhoriaContinuaController::class, 'list']);
// $router->get('/melhoria-continua/solicitacoes/{id}/details', [App\Controllers\MelhoriaContinuaController::class, 'details']);
// $router->get('/melhoria-continua/solicitacoes/{id}/print', [App\Controllers\MelhoriaContinuaController::class, 'print']);
// $router->post('/melhoria-continua/solicitacoes/update-status', [App\Controllers\MelhoriaContinuaController::class, 'updateStatus']);

// API routes
$router->get('/api/users', [App\Controllers\UsersController::class , 'getUsers']);
$router->get('/api/profiles', [App\Controllers\ProfilesController::class , 'getProfilesList']);
$router->get('/api/toner', [App\Controllers\TonersController::class , 'getTonerData']);
$router->get('/api/setores', [App\Controllers\RegistrosController::class , 'getDepartamentos']);
$router->get('/api/filiais', [App\Controllers\RegistrosController::class , 'getFiliais']);
$router->get('/api/parametros', [App\Controllers\RegistrosController::class , 'getParametros']);

// API routes para seleção de produtos (Amostragens 2.0 e Garantias)
$router->get('/api/toners', [App\Controllers\TonersController::class , 'apiListToners']);
$router->get('/api/maquinas', [App\Controllers\MaquinasController::class , 'apiListMaquinas']);
$router->get('/api/pecas', [App\Controllers\PecasController::class , 'apiListPecas']);

// Profile routes
$router->get('/profile', [App\Controllers\ProfileController::class , 'index']);

// Profile API routes
$router->get('/api/profile', [App\Controllers\ProfileController::class , 'getProfile']);
$router->get('/profile/photo/{userId}', [App\Controllers\ProfileController::class , 'getPhoto']);
$router->post('/api/profile/password', [App\Controllers\ProfileController::class , 'changePassword']);
$router->post('/api/profile/photo', [App\Controllers\ProfileController::class , 'uploadPhoto']);
$router->post('/api/profile/notifications', [App\Controllers\ProfileController::class , 'updateNotifications']);

// Notifications routes
$router->get('/api/notifications', [App\Controllers\NotificationsController::class , 'getNotifications']);
$router->post('/api/notifications/{id}/read', [App\Controllers\NotificationsController::class , 'markAsRead']);
$router->post('/api/notifications/read-all', [App\Controllers\NotificationsController::class , 'markAllAsRead']);
$router->post('/api/notifications/clear-history', [App\Controllers\NotificationsController::class , 'clearHistory']);
$router->get('/notifications/{id}/redirect', [App\Controllers\NotificationsController::class , 'redirect']);

// Chat virtual routes
$router->get('/api/chat/contacts', [App\Controllers\ChatController::class , 'contacts']);
$router->post('/api/chat/heartbeat', [App\Controllers\ChatController::class , 'heartbeat']);
$router->get('/api/chat/messages/{userId}', [App\Controllers\ChatController::class , 'getMessages']);
$router->get('/api/chat/messages/global', [App\Controllers\ChatController::class , 'getGlobalMessages']);
$router->post('/api/chat/send', [App\Controllers\ChatController::class , 'sendMessage']);
$router->post('/api/chat/send-global', [App\Controllers\ChatController::class , 'sendGlobalMessage']);
$router->post('/api/chat/clear-history', [App\Controllers\ChatController::class , 'clearHistory']);

// FMEA routes
$router->get('/fmea', [App\Controllers\FMEAController::class , 'index']);
$router->get('/fmea/list', [App\Controllers\FMEAController::class , 'list']);
$router->post('/fmea/store', [App\Controllers\FMEAController::class , 'store']);
$router->get('/fmea/{id}', [App\Controllers\FMEAController::class , 'show']);
$router->post('/fmea/{id}/update', [App\Controllers\FMEAController::class , 'update']);
$router->delete('/fmea/{id}/delete', [App\Controllers\FMEAController::class , 'delete']);
$router->get('/fmea/charts', [App\Controllers\FMEAController::class , 'chartData']);
$router->get('/fmea/{id}/print', [App\Controllers\FMEAController::class , 'print']);

// POPs e ITs routes
$router->get('/pops-e-its', [App\Controllers\PopItsController::class , 'index']);
$router->get('/pops-its/diagnostico', [App\Controllers\PopItsController::class , 'diagnostico']);
$router->get('/pops-its/teste', [App\Controllers\PopItsController::class , 'testeTitulos']);
// Aba 1: Cadastro de Títulos
$router->post('/pops-its/titulo/create', [App\Controllers\PopItsController::class , 'createTitulo']);
$router->get('/pops-its/titulos/list', [App\Controllers\PopItsController::class , 'listTitulos']);
$router->get('/pops-its/titulos/search', [App\Controllers\PopItsController::class , 'searchTitulos']);
$router->post('/pops-its/titulo/delete', [App\Controllers\PopItsController::class , 'deleteTitulo']);
// Aba 2: Meus Registros
$router->post('/pops-its/registro/create', [App\Controllers\PopItsController::class , 'createRegistro']);
$router->post('/pops-its/registro/editar', [App\Controllers\PopItsController::class , 'editarRegistro']);
$router->get('/pops-its/registros/meus', [App\Controllers\PopItsController::class , 'listMeusRegistros']);
$router->get('/pops-its/arquivo/{id}', [App\Controllers\PopItsController::class , 'downloadArquivo']);
$router->post('/pops-its/registro/update', [App\Controllers\PopItsController::class , 'updateRegistro']);
$router->post('/pops-its/registro/delete', [App\Controllers\PopItsController::class , 'deleteRegistro']);
// Aba 3: Pendente Aprovação
$router->get('/pops-its/pendentes/list', [App\Controllers\PopItsController::class , 'listPendentesAprovacao']);
$router->post('/pops-its/registro/aprovar', [App\Controllers\PopItsController::class , 'aprovarRegistro']);
$router->post('/pops-its/registro/reprovar', [App\Controllers\PopItsController::class , 'reprovarRegistro']);
// Aba 4: Visualização
$router->get('/pops-its/visualizacao/list', [App\Controllers\PopItsController::class , 'listVisualizacao']);
$router->get('/pops-its/visualizar/{id}', [App\Controllers\PopItsController::class , 'visualizarArquivo']);
// Aba 5: Log de Visualizações
$router->get('/pops-its/logs/visualizacao', [App\Controllers\PopItsController::class , 'listLogsVisualizacao']);
$router->post('/pops-its/registrar-log', [App\Controllers\PopItsController::class , 'registrarLog']);
// Rotas de teste removidas - sistema funcionando corretamente
$router->get('/pops-its/teste-notificacoes', [App\Controllers\PopItsController::class , 'testeNotificacoes']);
$router->post('/pops-its/teste-notificacao-manual', [App\Controllers\PopItsController::class , 'testeNotificacaoManual']);
// Endpoint de teste
$router->get('/pops-its/test', [App\Controllers\PopItsController::class , 'testEndpoint']);
// Sistema de Solicitações de Exclusão
$router->post('/pops-its/solicitacao/create', [App\Controllers\PopItsController::class , 'createSolicitacao']);
$router->get('/pops-its/solicitacoes/list', [App\Controllers\PopItsController::class , 'listSolicitacoes']);
$router->post('/pops-its/solicitacao/aprovar', [App\Controllers\PopItsController::class , 'aprovarSolicitacao']);
$router->post('/pops-its/solicitacao/reprovar', [App\Controllers\PopItsController::class , 'reprovarSolicitacao']);

// ===== MÓDULO FLUXOGRAMAS (ATIVADO v2.5.0) =====
$router->get('/fluxogramas', [App\Controllers\FluxogramasController::class , 'index']);
$router->post('/fluxogramas/titulo/create', [App\Controllers\FluxogramasController::class , 'createTitulo']);
$router->get('/fluxogramas/titulos/list', [App\Controllers\FluxogramasController::class , 'listTitulos']);
$router->get('/fluxogramas/titulos/search', [App\Controllers\FluxogramasController::class , 'searchTitulos']);
$router->post('/fluxogramas/titulo/delete', [App\Controllers\FluxogramasController::class , 'deleteTitulo']);
$router->post('/fluxogramas/registro/create', [App\Controllers\FluxogramasController::class , 'createRegistro']);
$router->post('/fluxogramas/registro/editar', [App\Controllers\FluxogramasController::class , 'editarRegistro']);
$router->post('/fluxogramas/registros/atualizar-visibilidade', [App\Controllers\FluxogramasController::class , 'atualizarVisibilidade']);
$router->get('/fluxogramas/registros/meus', [App\Controllers\FluxogramasController::class , 'listMeusRegistros']);
$router->get('/fluxogramas/registros/{id}', [App\Controllers\FluxogramasController::class , 'getRegistro']);
$router->get('/fluxogramas/arquivo/{id}', [App\Controllers\FluxogramasController::class , 'downloadArquivo']);
$router->get('/fluxogramas/visualizar/{id}', [App\Controllers\FluxogramasController::class , 'visualizarArquivo']);
$router->get('/fluxogramas/pendentes/list', [App\Controllers\FluxogramasController::class , 'listPendentes']);
$router->post('/fluxogramas/registro/aprovar', [App\Controllers\FluxogramasController::class , 'aprovarRegistro']);
$router->post('/fluxogramas/registro/reprovar', [App\Controllers\FluxogramasController::class , 'reprovarRegistro']);
$router->post('/fluxogramas/solicitacao/create', [App\Controllers\FluxogramasController::class , 'createSolicitacaoExclusao']);
$router->get('/fluxogramas/solicitacoes/list', [App\Controllers\FluxogramasController::class , 'listSolicitacoes']);
$router->post('/fluxogramas/solicitacao/aprovar', [App\Controllers\FluxogramasController::class , 'aprovarSolicitacao']);
$router->post('/fluxogramas/solicitacao/reprovar', [App\Controllers\FluxogramasController::class , 'reprovarSolicitacao']);
$router->get('/fluxogramas/visualizacao/list', [App\Controllers\FluxogramasController::class , 'listVisualizacao']);
$router->get('/fluxogramas/logs/visualizacao', [App\Controllers\FluxogramasController::class , 'listLogs']);


// ===== MÓDULO HOMOLOGAÇÕES (KANBAN) - ATIVADO v3.0.0 =====
$router->get('/homologacoes', [App\Controllers\HomologacoesKanbanController::class , 'index']);
$router->post('/homologacoes/store', [App\Controllers\HomologacoesKanbanController::class , 'store']);
$router->post('/homologacoes/update-status', [App\Controllers\HomologacoesKanbanController::class , 'updateStatus']);
$router->post('/homologacoes/{id}/status', [App\Controllers\HomologacoesKanbanController::class , 'updateStatusById']);
$router->post('/homologacoes/{id}/contadores', [App\Controllers\HomologacoesKanbanController::class , 'updateContadores']);
$router->get('/homologacoes/{id}/details', [App\Controllers\HomologacoesKanbanController::class , 'details']);

// ===== MÓDULO HOMOLOGAÇÕES 2.0 =====
$router->get('/homologacoes-2', [App\Controllers\Homologacoes2Controller::class , 'index']);

// Rotas de Checklists
$router->post('/homologacoes/checklists/create', [App\Controllers\ChecklistsController::class , 'create']);
$router->get('/homologacoes/checklists/list', [App\Controllers\ChecklistsController::class , 'list']);
$router->get('/homologacoes/checklists/{id}', [App\Controllers\ChecklistsController::class , 'show']);
$router->post('/homologacoes/checklists/{id}/update', [App\Controllers\ChecklistsController::class , 'update']);
$router->delete('/homologacoes/checklists/{id}', [App\Controllers\ChecklistsController::class , 'delete']);
$router->post('/homologacoes/checklists/salvar-respostas', [App\Controllers\ChecklistsController::class , 'salvarRespostas']);
$router->get('/homologacoes/checklists/respostas/{id}', [App\Controllers\ChecklistsController::class , 'buscarRespostas']);
$router->post('/homologacoes/upload-anexo', [App\Controllers\HomologacoesKanbanController::class , 'uploadAnexo']);
$router->get('/homologacoes/anexo/{id}', [App\Controllers\HomologacoesKanbanController::class , 'downloadAnexo']);
$router->post('/homologacoes/delete', [App\Controllers\HomologacoesKanbanController::class , 'delete']);
$router->post('/homologacoes/registrar-dados-etapa', [App\Controllers\HomologacoesKanbanController::class , 'registrarDadosEtapa']);
$router->get('/homologacoes/{id}/relatorio', [App\Controllers\HomologacoesKanbanController::class , 'gerarRelatorio']);
$router->get('/homologacoes/{id}/logs', [App\Controllers\HomologacoesKanbanController::class , 'buscarLogs']);
$router->get('/homologacoes/{id}/logs/export', [App\Controllers\HomologacoesKanbanController::class , 'exportarLogs']);

// ===== MÓDULO CERTIFICADOS =====
$router->get('/certificados', [App\Controllers\CertificadosController::class , 'index']);
$router->post('/certificados/store', [App\Controllers\CertificadosController::class , 'store']);
$router->get('/certificados/download/{id}', [App\Controllers\CertificadosController::class , 'download']);
$router->post('/certificados/delete', [App\Controllers\CertificadosController::class , 'delete']);

// Melhoria Contínua routes - DESABILITADO (usar Melhoria Contínua 2.0)
// $router->get('/melhoria-continua', [App\Controllers\MelhoriaContinuaController::class, 'index']);
// $router->get('/melhoria-continua/list', [App\Controllers\MelhoriaContinuaController::class, 'list']);
// $router->get('/melhoria-continua/departamentos', [App\Controllers\MelhoriaContinuaController::class, 'getDepartamentos']);
// $router->get('/melhoria-continua/usuarios', [App\Controllers\MelhoriaContinuaController::class, 'getUsuarios']);
// $router->post('/melhoria-continua/store', [App\Controllers\MelhoriaContinuaController::class, 'store']);
// $router->post('/melhoria-continua/{id}/status', [App\Controllers\MelhoriaContinuaController::class, 'updateStatus']);
// $router->post('/melhoria-continua/{id}/pontuacao', [App\Controllers\MelhoriaContinuaController::class, 'updatePontuacao']);
// $router->post('/melhoria-continua/{id}/observacao', [App\Controllers\MelhoriaContinuaController::class, 'updateObservacao']);
// $router->post('/melhoria-continua/{id}/resultado', [App\Controllers\MelhoriaContinuaController::class, 'updateResultado']);
// $router->delete('/melhoria-continua/{id}/delete', [App\Controllers\MelhoriaContinuaController::class, 'delete']);
// $router->get('/melhoria-continua/{id}/print', [App\Controllers\MelhoriaContinuaController::class, 'print']);
// $router->get('/melhoria-continua/{id}/anexos', [App\Controllers\MelhoriaContinuaController::class, 'getAnexos']);
// $router->get('/melhoria-continua/anexo/{anexoId}', [App\Controllers\MelhoriaContinuaController::class, 'downloadAnexo']);

// Cadastros routes (novo módulo)
$router->get('/cadastros/contratos', function () {
    $viewFile = __DIR__ . '/../views/pages/cadastros/contratos.php';
    include __DIR__ . '/../views/layouts/main.php';
});

// Cadastro de Clientes (Admin Only)
$router->get('/cadastros/clientes', [App\Controllers\ClientesController::class , 'index']);
$router->get('/cadastros/clientes/listar', [App\Controllers\ClientesController::class , 'listar']);
$router->post('/cadastros/clientes/criar', [App\Controllers\ClientesController::class , 'criar']);
$router->post('/cadastros/clientes/atualizar', [App\Controllers\ClientesController::class , 'atualizar']);
$router->post('/cadastros/clientes/excluir', [App\Controllers\ClientesController::class , 'excluir']);
$router->post('/cadastros/clientes/importar', [App\Controllers\ClientesController::class , 'importar']);
$router->get('/cadastros/clientes/template', [App\Controllers\ClientesController::class , 'template']);

// Registros routes
$router->get('/registros/filiais', [App\Controllers\RegistrosController::class , 'filiais']);
$router->get('/registros/departamentos', [App\Controllers\RegistrosController::class , 'departamentos']);
$router->get('/registros/fornecedores', [App\Controllers\RegistrosController::class , 'fornecedores']);
$router->get('/registros/parametros', [App\Controllers\RegistrosController::class , 'parametros']);

// Store routes
$router->post('/registros/filiais/store', [App\Controllers\RegistrosController::class , 'storeFilial']);
$router->post('/registros/departamentos/store', [App\Controllers\RegistrosController::class , 'storeDepartamento']);
$router->post('/registros/fornecedores/store', [App\Controllers\RegistrosController::class , 'storeFornecedor']);
$router->post('/registros/parametros/store', [App\Controllers\RegistrosController::class , 'storeParametro']);

// Update routes
$router->post('/registros/filiais/update', [App\Controllers\RegistrosController::class , 'updateFilial']);
$router->post('/registros/departamentos/update', [App\Controllers\RegistrosController::class , 'updateDepartamento']);
$router->post('/registros/fornecedores/update', [App\Controllers\RegistrosController::class , 'updateFornecedor']);
$router->post('/registros/parametros/update', [App\Controllers\RegistrosController::class , 'updateParametro']);

// Delete routes
$router->post('/registros/filiais/delete', [App\Controllers\RegistrosController::class , 'deleteFilial']);
$router->post('/registros/departamentos/delete', [App\Controllers\RegistrosController::class , 'deleteDepartamento']);
$router->post('/registros/fornecedores/delete', [App\Controllers\RegistrosController::class , 'deleteFornecedor']);
$router->post('/registros/parametros/delete', [App\Controllers\RegistrosController::class , 'deleteParametro']);

// ===== MÓDULO ÁREA TÉCNICA =====
$router->get('/area-tecnica', [App\Controllers\AreaTecnicaController::class , 'index']);
$router->post('/area-tecnica/ativar-trial', [App\Controllers\AreaTecnicaController::class , 'ativarTrial']);
$router->get('/area-tecnica/trial-status', [App\Controllers\AreaTecnicaController::class , 'getTrialStatus']);
// Checklist Virtual (rota pública - sem login)
$router->get('/area-tecnica/checklist', [App\Controllers\AreaTecnicaController::class , 'checklistPublico']);
$router->post('/area-tecnica/checklist/salvar', [App\Controllers\AreaTecnicaController::class , 'salvarChecklist']);
// Consulta de Checklists
$router->get('/area-tecnica/consulta', [App\Controllers\AreaTecnicaController::class , 'consultaChecklists']);
$router->get('/area-tecnica/checklists/buscar', [App\Controllers\AreaTecnicaController::class , 'buscarChecklists']);
$router->get('/area-tecnica/checklists/listar', [App\Controllers\AreaTecnicaController::class , 'listarTodosChecklists']);
$router->get('/area-tecnica/checklists/{id}', [App\Controllers\AreaTecnicaController::class , 'verChecklist']);

// Rota de Teste de E-mail (Diagnóstico Debug)
$router->get('/teste-smtp-debug', [App\Controllers\TesteEmailController::class , 'index']);
$router->post('/teste-smtp-debug', [App\Controllers\TesteEmailController::class , 'index']);

// ===== MÓDULO RH - RECURSOS HUMANOS =====
$router->get('/rh', [App\Controllers\RhController::class , 'index']);
$router->get('/rh/avaliacao-desempenho', [App\Controllers\RhController::class , 'avaliacaoDesempenho']);
$router->get('/rh/dashboard/stats', [App\Controllers\RhController::class , 'dashboardStats']);
$router->get('/rh/avaliacoes/listar', [App\Controllers\RhController::class , 'listarAvaliacoes']);
$router->post('/rh/avaliacoes/excluir', [App\Controllers\RhController::class , 'excluirAvaliacao']);
$router->get('/rh/colaboradores/listar', [App\Controllers\RhController::class , 'listarColaboradores']);
// Formulários de Avaliação
$router->get('/rh/formularios/listar', [App\Controllers\RhController::class , 'listarFormularios']);
$router->get('/rh/formularios/{id}/detalhes', [App\Controllers\RhController::class , 'detalhesFormulario']);
$router->post('/rh/formularios/criar', [App\Controllers\RhController::class , 'criarFormulario']);
$router->post('/rh/formularios/editar', [App\Controllers\RhController::class , 'editarFormulario']);
$router->post('/rh/formularios/excluir', [App\Controllers\RhController::class , 'excluirFormulario']);
$router->post('/rh/formularios/duplicar', [App\Controllers\RhController::class , 'duplicarFormulario']);
// Páginas públicas de avaliação RH
$router->get('/avaliacao/{token}', [App\Controllers\RhController::class , 'formularioPublico']);
$router->post('/avaliacao/responder', [App\Controllers\RhController::class , 'salvarRespostaPublica']);

// ===== MÓDULO USABILIDADE DO SGQ (SUPER ADMIN ONLY) =====
$router->get('/usabilidade', [App\Controllers\UsabilidadeController::class , 'index']);
$router->get('/usabilidade/api/logins-por-dia', [App\Controllers\UsabilidadeController::class , 'getLoginsPorDia']);
$router->get('/usabilidade/api/historico', [App\Controllers\UsabilidadeController::class , 'getHistorico']);
$router->get('/usabilidade/api/estatisticas', [App\Controllers\UsabilidadeController::class , 'getEstatisticas']);

// ===== MÓDULO CADASTROS 2.0 (SUPER ADMIN ONLY) =====
$router->get('/cadastros-2', [App\Controllers\CadastrosProdutosController::class , 'index']);
$router->get('/cadastros-2/list', [App\Controllers\CadastrosProdutosController::class , 'list']);
$router->get('/cadastros-2/get/{id}', [App\Controllers\CadastrosProdutosController::class , 'get']);
$router->post('/cadastros-2/store', [App\Controllers\CadastrosProdutosController::class , 'store']);
$router->post('/cadastros-2/update', [App\Controllers\CadastrosProdutosController::class , 'update']);
$router->post('/cadastros-2/delete', [App\Controllers\CadastrosProdutosController::class , 'delete']);

// ===== MODULO ATENDIMENTO =====
$router->get('/atendimento/calculadora-toners', [App\Controllers\AtendimentoController::class , 'index']);
$router->get('/atendimento/calculadora-toners/buscar', [App\Controllers\AtendimentoController::class , 'buscarToners']);
$router->get('/atendimento/calculadora-toners/config', [App\Controllers\AtendimentoController::class , 'getConfig']);
$router->post('/atendimento/calculadora-toners/config', [App\Controllers\AtendimentoController::class , 'saveConfig']);

// Dispatch
try {
    $currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Apply middleware only for protected routes
    $isPublicAuthRoute = (
        $currentRoute === '/' || // Rota raiz tem lógica própria de redirecionamento
        $currentRoute === '/inicio' || // Página inicial acessível a todos os logados
        $currentRoute === '/pops-e-its' || // POPs e ITs - visualização acessível a todos os logados
        $currentRoute === '/pops-its/visualizacao/list' || // Listagem POPs aprovados - acessível a todos
        strpos($currentRoute, '/pops-its/visualizar/') === 0 || // Visualizar arquivo POP/IT
        strpos($currentRoute, '/login') === 0 ||
        strpos($currentRoute, '/auth/') === 0 ||
        strpos($currentRoute, '/register') === 0 ||
        strpos($currentRoute, '/logout') === 0 ||
        strpos($currentRoute, '/password-reset') === 0 ||
        strpos($currentRoute, '/request-access') === 0 ||
        strpos($currentRoute, '/access-request') === 0 ||
        strpos($currentRoute, '/nps/responder/') === 0 || // Formulário público NPS
        strpos($currentRoute, '/nps/salvar-resposta') === 0 || // Salvar resposta pública NPS
        strpos($currentRoute, '/area-tecnica/checklist') === 0 || // Checklist Virtual público
        $currentRoute === '/teste-smtp-debug' // Teste debug email
        );

    if (!$isPublicAuthRoute) {
        PermissionMiddleware::handle($currentRoute, $method);
    }

    $router->dispatch();


}
catch (\Exception $e) {
    error_log('Application error: ' . $e->getMessage());

    // Log detalhado em arquivo
    try {
        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';

        $context = [
            'timestamp' => date('Y-m-d H:i:s'),
            'route' => $currentRoute,
            'method' => $method,
            'session_user_id' => $_SESSION['user_id'] ?? null,
            'session_user_email' => $_SESSION['user_email'] ?? null,
            'get' => $_GET ?? [],
            'post_keys' => array_keys($_POST ?? []),
            'exception' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
        ];

        $logEntry = '[' . $context['timestamp'] . '] ' . $context['method'] . ' ' . $context['route'] . ' | User: ' . ($context['session_user_id'] ?? 'guest') . '\n';
        $logEntry .= 'Message: ' . $context['exception']['message'] . ' (' . $context['exception']['file'] . ':' . $context['exception']['line'] . ')\n';
        $logEntry .= 'GET: ' . json_encode($context['get']) . ' | POST_KEYS: ' . json_encode($context['post_keys']) . '\n';
        $logEntry .= str_repeat('-', 80) . "\n";

        error_log($logEntry, 3, $logFile);
    }
    catch (\Throwable $logError) {
        error_log('Erro ao escrever log: ' . $logError->getMessage());
    }

    if ($isDebug) {
        echo '<h1>Erro: ' . htmlspecialchars($e->getMessage()) . '</h1>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    else {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Erro 500</title></head><body>';
        echo '<h1>Erro Interno do Servidor</h1>';
        echo '<p>Tente novamente em alguns minutos.</p>';
        echo '</body></html>';
    }
}
?>
