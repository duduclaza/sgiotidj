<?php

namespace App\Middleware;

use App\Services\PermissionService;

class PermissionMiddleware
{
    /**
     * Mapeamento de rotas para módulos de permissão
     */
    private static $routeModuleMap = [
        // Dashboard
        '/' => 'dashboard',
        
        // Toners
        '/toners/cadastro' => 'toners_cadastro',
        '/toners/import' => 'toners_cadastro',
        '/toners/export' => 'toners_cadastro',
        '/toners/retornados' => 'toners_retornados',
        '/toners/retornados/export' => 'toners_retornados',
        '/toners/retornados/import' => 'toners_retornados',
        '/toners/retornados/delete' => 'toners_retornados',
        
        // Toners com Defeito
        '/toners/defeitos' => 'toners_defeitos',
        '/toners/defeitos/store' => 'toners_defeitos',
        '/toners/defeitos/delete' => 'toners_defeitos',
        '/toners/defeitos/{id}/foto/{n}' => 'toners_defeitos',

        // Atendimento - Calculadora de Envio
        '/atendimento/calculadora-toners' => 'calculadora_toners',
        '/atendimento/calculadora-toners/buscar' => 'calculadora_toners',
        '/atendimento/calculadora-toners/config' => 'calculadora_toners',

        // Cadastro de Defeitos (Cadastros Gerais)
        '/cadastro-defeitos' => 'cadastro_defeitos',
        '/cadastro-defeitos/store' => 'cadastro_defeitos',
        '/cadastro-defeitos/update' => 'cadastro_defeitos',
        '/cadastro-defeitos/delete' => 'cadastro_defeitos',
        '/cadastro-defeitos/{id}/imagem' => 'cadastro_defeitos',
        
        // Módulos principais
        '/homologacoes' => 'homologacoes',
        '/homologacoes/store' => 'homologacoes',
        '/homologacoes/update-status' => 'homologacoes',
        '/homologacoes/upload-anexo' => 'homologacoes',
        '/homologacoes/anexo' => 'homologacoes',
        '/homologacoes/delete' => 'homologacoes',
        // Certificados
        '/certificados' => 'certificados',
        '/certificados/store' => 'certificados',
        '/certificados/download' => 'certificados',
        '/certificados/delete' => 'certificados',
        '/toners/amostragens' => 'amostragens',
        '/amostragens-2' => 'amostragens_2',
        '/controle-de-descartes' => 'controle_descartes',
        '/controle-descartes' => 'controle_descartes',
        '/auditorias' => 'auditorias',
        '/femea' => 'femea',
        '/pops-e-its' => 'pops_its_visualizacao', // Rota principal vai para visualização (todos podem ver)
        '/fluxogramas' => 'fluxogramas', // Usando módulo genérico
        
        // POPs e ITs - Rotas específicas por aba
        '/pops-its/titulo/create' => 'pops_its_cadastro_titulos',
        '/pops-its/titulos/list' => 'pops_its_cadastro_titulos',
        '/pops-its/titulos/search' => 'pops_its_cadastro_titulos',
        '/pops-its/titulo/delete' => 'pops_its_cadastro_titulos',
        '/pops-its/registro/create' => 'pops_its_meus_registros',
        '/pops-its/registros/meus' => 'pops_its_meus_registros',
        '/pops-its/registro/editar' => 'pops_its_meus_registros',
        '/pops-its/arquivo' => 'pops_its_visualizacao', // Download de arquivos
        '/pops-its/visualizacao/list' => 'pops_its_visualizacao',
        '/pops-its/solicitacao/create' => 'pops_its_meus_registros', // Solicitação de exclusão
        // '/pops-its/logs/visualizacao' => 'pops_its_logs_visualizacao', // Removido - verificação feita no controller
        
        // Fluxogramas - Usando módulo genérico 'fluxogramas' para todas as rotas
        '/fluxogramas/titulo/create' => 'fluxogramas',
        '/fluxogramas/titulos/list' => 'fluxogramas',
        '/fluxogramas/titulos/search' => 'fluxogramas',
        '/fluxogramas/titulo/delete' => 'fluxogramas',
        '/fluxogramas/registro/create' => 'fluxogramas',
        '/fluxogramas/registros/meus' => 'fluxogramas',
        '/fluxogramas/registro/editar' => 'fluxogramas',
        '/fluxogramas/arquivo' => 'fluxogramas',
        '/fluxogramas/visualizacao/list' => 'fluxogramas',
        '/fluxogramas/solicitacao/create' => 'fluxogramas',
        '/fluxogramas/pendentes/list' => 'fluxogramas',
        '/fluxogramas/solicitacoes/list' => 'fluxogramas',
        '/fluxogramas/solicitacao/aprovar' => 'fluxogramas',
        '/fluxogramas/solicitacao/reprovar' => 'fluxogramas',
        '/fluxogramas/registro/aprovar' => 'fluxogramas',
        '/fluxogramas/registro/reprovar' => 'fluxogramas',
        '/fluxogramas/logs/visualizacao' => 'fluxogramas',
        '/fluxogramas/visualizar' => 'fluxogramas',
        '/fluxogramas/titulo/delete' => 'fluxogramas',

        // NPS (Net Promoter Score)
        '/nps' => 'nps',
        '/nps/dashboard' => 'nps',
        '/nps/listar' => 'nps',
        '/nps/criar' => 'nps',
        '/nps/editar' => 'nps',
        '/nps/toggle-status' => 'nps',
        '/nps/excluir' => 'nps',
        '/nps/excluir-resposta' => 'nps',
        '/nps/{id}/detalhes' => 'nps',
        '/nps/{id}/respostas' => 'nps',
        '/nps/{id}/exportar-excel' => 'nps',
        '/nps/debug' => 'nps',

        // Melhoria Contínua - DESABILITADO (usar Melhoria Contínua 2.0)
        // '/melhoria-continua' => 'melhoria_continua',
        // '/melhoria-continua/list' => 'melhoria_continua',
        // '/melhoria-continua/departamentos' => 'melhoria_continua',
        // '/melhoria-continua/usuarios' => 'melhoria_continua',
        // '/melhoria-continua/store' => 'melhoria_continua',
        
        // Controle de RC
        '/controle-de-rc' => 'controle_rc',
        '/controle-rc/list' => 'controle_rc',
        '/controle-rc/create' => 'controle_rc',
        '/controle-rc/update' => 'controle_rc',
        '/controle-rc/update-status' => 'controle_rc',
        '/controle-rc/delete' => 'controle_rc',
        '/controle-rc/export' => 'controle_rc',
        '/controle-rc/evidencia' => 'controle_rc',
        
        // Auditorias
        '/auditorias' => 'auditorias',
        '/auditorias/list' => 'auditorias',
        '/auditorias/create' => 'auditorias',
        '/auditorias/update' => 'auditorias',
        '/auditorias/delete' => 'auditorias',
        '/auditorias/relatorios' => 'auditorias',
        '/auditorias/anexo' => 'auditorias',
        
        // Não Conformidades
        '/nao-conformidades' => 'nao_conformidades',
        
        // 5W2H - Planos de Ação
        '/5w2h' => '5w2h',
        '/5w2h/list' => '5w2h',
        '/5w2h/create' => '5w2h',
        '/5w2h/update' => '5w2h',
        '/5w2h/delete' => '5w2h',
        '/5w2h/details' => '5w2h',
        '/5w2h/print' => '5w2h',
        '/5w2h/anexos' => '5w2h',
        '/5w2h/anexo' => '5w2h',
        '/5w2h/relatorios' => '5w2h',
        
        // Registros
        '/registros/filiais' => 'registros_filiais',
        '/registros/departamentos' => 'registros_departamentos',
        '/registros/fornecedores' => 'registros_fornecedores',
        '/registros/parametros' => 'registros_parametros',
        
        // Configurações
        '/configuracoes' => 'configuracoes_gerais',
        '/admin/users' => 'admin_usuarios',
        '/admin/profiles' => 'admin_perfis',
        '/admin/invitations' => 'admin_convites',
        '/admin' => 'admin_painel',
        '/admin/dashboard/data' => 'dashboard',
        
        // Perfil - removido pois é rota pública para usuários logados
    ];
    
    /**
     * Verificar permissão para uma rota
     */
    public static function checkRoutePermission(string $route, string $method = 'GET'): bool
    {
        // Rotas que não precisam de verificação de permissão
        $publicRoutes = [
            '/login', '/auth/login', '/register', '/auth/register', '/logout',
            '/request-access', '/access-request/process', '/access-request/filiais', '/access-request/departamentos',
            '/password-reset/request', '/password-reset/verify', '/password-reset/verify-code', '/password-reset/new', '/password-reset/reset',
            '/email/test-connection', '/email/send-test',
            '/profile'  // Perfil próprio deve ser acessível a todos os usuários logados
        ];
        
        // Rotas "Em Breve" - acessíveis a todos os usuários logados (via PageController)
        $comingSoonRoutes = [
            '/controle-de-rc', 
            '/homologacoes',
        ];
        
        // Rotas de API que têm verificação própria
        $apiRoutes = ['/api/', '/admin/users/create', '/admin/users/update', '/admin/users/delete', '/admin/users/send-credentials', '/admin/test-email', '/debug/', '/pops-its/logs/', '/pops-its/teste-'];
        
        // Verificar se é rota pública
        foreach ($publicRoutes as $publicRoute) {
            if (strpos($route, $publicRoute) === 0) {
                return true;
            }
        }
        
        // Verificar se é rota de API (tem verificação própria)
        foreach ($apiRoutes as $apiRoute) {
            if (strpos($route, $apiRoute) === 0) {
                return true;
            }
        }
        
        // Se não está logado, não tem permissão
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Verificar se é rota "Em Breve" - permitir para usuários logados
        foreach ($comingSoonRoutes as $comingSoonRoute) {
            if ($route === $comingSoonRoute) {
                return true; // Permitir acesso para usuários logados
            }
        }
        
        $userId = $_SESSION['user_id'];
        
        // Encontrar o módulo correspondente à rota
        $module = self::getModuleForRoute($route);
        if (!$module) {
            // Se não encontrou módulo específico, permitir (pode ser uma rota dinâmica)
            return true;
        }
        
        // Determinar a ação baseada no método HTTP
        $action = self::getActionForMethod($method);
        
        // Verificar permissão
        return PermissionService::hasPermission($userId, $module, $action);
    }
    
    /**
     * Obter módulo para uma rota
     */
    private static function getModuleForRoute(string $route): ?string
    {
        // Normalizar a rota
        $route = rtrim($route, '/') ?: '/';
        
        // Verificar mapeamento direto
        if (isset(self::$routeModuleMap[$route])) {
            return self::$routeModuleMap[$route];
        }
        
        // Verificar rotas dinâmicas (com parâmetros)
        foreach (self::$routeModuleMap as $pattern => $module) {
            if (self::matchRoute($pattern, $route)) {
                return $module;
            }
        }
        
        return null;
    }
    
    /**
     * Verificar se uma rota corresponde a um padrão
     */
    private static function matchRoute(string $pattern, string $route): bool
    {
        // Converter padrão para regex
        $regex = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
        $regex = '#^' . $regex . '$#';
        
        return preg_match($regex, $route);
    }
    
    /**
     * Obter ação baseada no método HTTP
     */
    private static function getActionForMethod(string $method): string
    {
        switch (strtoupper($method)) {
            case 'GET':
                return 'view';
            case 'POST':
                return 'edit';
            case 'PUT':
            case 'PATCH':
                return 'edit';
            case 'DELETE':
                return 'delete';
            default:
                return 'view';
        }
    }
    
    /**
     * Middleware para verificar permissões
     */
    public static function handle(string $route, string $method = 'GET'): void
    {
        if (!self::checkRoutePermission($route, $method)) {
            // Se é uma requisição AJAX, retornar JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Acesso negado - você não tem permissão para acessar esta funcionalidade',
                    'redirect' => '/login'
                ]);
                exit;
            }
            
            // Se não está logado, redirecionar para login
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }
            
            // Se está tentando acessar a raiz (/) e não tem permissão para dashboard,
            // redirecionar para o primeiro módulo que tem permissão
            if ($route === '/' || $route === '') {
                $redirectUrl = self::findFirstAllowedModule($_SESSION['user_id']);
                if ($redirectUrl) {
                    header('Location: ' . $redirectUrl);
                    exit;
                }
            }
            
            // Se está logado mas não tem permissão, mostrar erro 403
            http_response_code(403);
            $firstAllowedUrl = self::findFirstAllowedModule($_SESSION['user_id']);
            $dashboardButton = $firstAllowedUrl ? 
                '<a href="' . $firstAllowedUrl . '" class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">Ir para Módulos Permitidos</a>' :
                '<a href="/logout" class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">Fazer Logout</a>';
            
            echo '
            <!DOCTYPE html>
            <html lang="pt-br">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Acesso Negado - SGQ OTI DJ</title>
                <script src="https://cdn.tailwindcss.com"></script>
            </head>
            <body class="bg-gray-100 flex items-center justify-center min-h-screen">
                <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6 text-center">
                    <div class="text-red-500 text-6xl mb-4">🚫</div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Acesso Negado</h1>
                    <p class="text-gray-600 mb-6">Você não tem permissão para acessar esta funcionalidade.</p>
                    <div class="space-y-2">
                        ' . $dashboardButton . '
                        <a href="/logout" class="block w-full bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                            Fazer Logout
                        </a>
                    </div>
                </div>
            </body>
            </html>';
            exit;
        }
    }
    
    /**
     * Encontrar o primeiro módulo que o usuário tem permissão
     */
    private static function findFirstAllowedModule(int $userId): ?string
    {
        // Lista de módulos em ordem de prioridade
        $moduleUrls = [
            'toners_cadastro' => '/toners/cadastro',
            '5w2h' => '/5w2h',
            'homologacoes' => '/homologacoes',
            'amostragens' => '/amostragens',
            'garantias' => '/garantias',
            'registros_fornecedores' => '/registros/fornecedores',
            'registros_parametros' => '/registros/parametros',
            'configuracoes_gerais' => '/configuracoes',
        ];
        
        foreach ($moduleUrls as $module => $url) {
            if (PermissionService::hasPermission($userId, $module, 'view')) {
                return $url;
            }
        }
        
        // Se não encontrou nenhum módulo, retornar para início
        return '/inicio';
    }
}
