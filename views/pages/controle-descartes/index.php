<?php
// Helpers protegidos contra redeclaração
if (!function_exists('hasPermission')) {
    function hasPermission($module, $action = 'view') {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        // Admin e Super Admin sempre tem acesso
        $userRole = $_SESSION['user_role'] ?? '';
        if (in_array($userRole, ['admin', 'super_admin'])) {
            return true;
        }
        // Fallback via sessão
        $profile = $_SESSION['profile'] ?? ($_SESSION['user_profile']['profile_name'] ?? null);
        if ($profile === 'Administrador') { return true; }

        $permissions = $_SESSION['permissions'] ?? ($_SESSION['user_profile']['permissions'] ?? []);
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if (($permission['module'] ?? null) === $module) {
                    switch ($action) {
                        case 'view': return (bool)$permission['can_view'];
                        case 'edit': return (bool)$permission['can_edit'];
                        case 'delete': return (bool)$permission['can_delete'];
                        case 'import': return (bool)$permission['can_import'];
                        case 'export': return (bool)$permission['can_export'];
                    }
                }
            }
        }

        // Fallback final: consultar serviço
        try {
            $map = ['view'=>'view','edit'=>'edit','delete'=>'delete','import'=>'import','export'=>'export'];
            $actionKey = $map[$action] ?? 'view';
            return \App\Services\PermissionService::hasPermission((int)$_SESSION['user_id'], $module, $actionKey);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('e')) {
    function e($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}

// Verificar permissões
$canEdit = hasPermission('controle_descartes', 'edit');
$canDelete = hasPermission('controle_descartes', 'delete');
$canImport = hasPermission('controle_descartes', 'import');
$canExport = hasPermission('controle_descartes', 'export');

// Verificar se é admin/super_admin para exibir Log de Ações
$userRole = $_SESSION['user_role'] ?? '';
$isAdmin = in_array($userRole, ['admin', 'super_admin']);

// Verificar se pode alterar status (admin ou perfil qualidade)
$canAlterarStatus = false;
if ($isAdmin) {
    $canAlterarStatus = true;
} else {
    // Verificar se tem perfil qualidade
    try {
        $db = \App\Config\Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.nome 
            FROM user_profiles up
            JOIN profiles p ON up.profile_id = p.id
            WHERE up.user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $perfis = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $canAlterarStatus = in_array('Qualidade', $perfis) || in_array('qualidade', $perfis);
    } catch (\Exception $e) {
        $canAlterarStatus = false;
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Controle de Descartes</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Gerenciamento de descartes de equipamentos</p>
            </div>
            <div class="flex space-x-2 flex-wrap gap-2">
                <?php if ($isAdmin): ?>
                <button onclick="abrirModalLogs()" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1.5 text-sm rounded-md flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Log
                </button>
                <?php endif; ?>
                <?php if ($canExport): ?>
                <button onclick="exportarDescartes()" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 text-sm rounded-md flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exportar
                </button>
                <?php endif; ?>
                <?php if ($canImport): ?>
                <button onclick="abrirModalImportacao()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-sm rounded-md flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Importar
                </button>
                <?php endif; ?>
                <?php if ($canEdit): ?>
                <button onclick="abrirModalDescarte()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 text-sm rounded-md flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Novo
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filtros de Busca</h3>
        <!-- Primeira linha de filtros -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de Série</label>
                <input type="text" id="filtro-numero-serie" placeholder="Digite o número de série" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código do Produto</label>
                <input type="text" id="filtro-codigo-produto" placeholder="Buscar por código" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número da OS</label>
                <input type="text" 
                       id="f-os-number" 
                       name="os_f_<?= time() ?>" 
                       readonly 
                       onfocus="this.removeAttribute('readonly');"
                       placeholder="Digite o número da OS" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filial</label>
                <select id="filtro-filial" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as filiais</option>
                    <?php foreach ($filiais as $filial): ?>
                        <option value="<?= $filial['id'] ?>"><?= e($filial['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <!-- Segunda linha de filtros -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Início</label>
                <input type="date" id="filtro-data-inicio" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Fim</label>
                <input type="date" id="filtro-data-fim" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Andamento</label>
                <select id="filtro-andamento" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="Em aberto">🔄 Em aberto</option>
                    <option value="Concluído">✅ Concluído</option>
                </select>
            </div>
            <div class="flex items-end justify-end space-x-3">
                <button id="btn-limpar" onclick="limparFiltros()" class="px-4 py-2 text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-md transition-all">
                    Limpar
                </button>
                <button id="btn-filtrar" onclick="aplicarFiltros()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-all shadow-sm hover:shadow-blue-500/20">
                    Buscar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabela de Descartes -->
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 overflow-hidden transition-colors">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/50">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Lista de Descartes</h3>
        </div>
        <!-- Barra de rolagem superior -->
        <div id="scroll-top-container" class="overflow-x-auto" style="overflow-y: hidden;">
            <div id="scroll-top-content" style="height: 1px;"></div>
        </div>
        <div id="tabela-scroll-container" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                <thead class="bg-gray-50 dark:bg-slate-900/80 sticky top-0">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Número de Série</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Filial</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Data Descarte</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Responsável</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">OS</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Andamento</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Anexo</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-descartes" class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700">
                    <!-- Dados carregados via JavaScript -->
                </tbody>
            </table>
        </div>
        <!-- Paginação -->
        <div id="paginacao-container" class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 flex items-center justify-between hidden bg-gray-50/50 dark:bg-slate-900/50">
            <div class="flex items-center space-x-2">
                <label class="text-sm text-gray-600 dark:text-slate-400 font-medium">Itens por página:</label>
                <select id="per-page-select" onchange="alterarItensPorPagina()" class="border border-gray-300 dark:border-slate-600 rounded-lg px-2 py-1 text-sm bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <span id="paginacao-info" class="text-sm text-gray-600 dark:text-slate-400 font-medium"></span>
            </div>
            <div class="flex items-center space-x-2" id="pag-buttons">
                <button onclick="irParaPagina(1)" id="btn-primeira" class="px-3 py-1 border border-gray-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-30 transition-colors" disabled>
                    ««
                </button>
                <button onclick="paginaAnterior()" id="btn-anterior" class="px-3 py-1 border border-gray-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-30 transition-colors" disabled>
                    « Anterior
                </button>
                <span id="paginacao-numeros" class="flex items-center space-x-1"></span>
                <button onclick="proximaPagina()" id="btn-proximo" class="px-3 py-1 border border-gray-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-30 transition-colors" disabled>
                    Próximo »
                </button>
                <button onclick="irParaPagina(paginacao.total_pages)" id="btn-ultima" class="px-3 py-1 border border-gray-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-30 transition-colors" disabled>
                    »»
                </button>
            </div>
        </div>
        <div id="no-data" class="text-center py-20 hidden">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-900 rounded-full flex items-center justify-center text-slate-300 dark:text-slate-600">
                    <i class="ph ph-recycle text-5xl"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">Nenhum descarte encontrado</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">Não encontramos registros com os filtros aplicados ou ainda não há descartes nesta categoria.</p>
                </div>
                <button onclick="abrirModalDescarte()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-lg hover:shadow-blue-500/20">
                    <i class="ph ph-plus-circle text-lg"></i>
                    Novo Descarte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Importação -->
<div id="modal-importacao" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 transition-all">
    <div class="relative top-20 mx-auto border w-11/12 md:w-2/3 lg:w-1/2 shadow-2xl rounded-2xl bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Importar Descartes via Excel</h3>
            <button onclick="fecharModalImportacao()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            
            <div class="mb-6">
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                    <h4 class="font-medium text-blue-900 dark:text-blue-200 mb-2">📋 Instruções:</h4>
                    <ol class="list-decimal list-inside text-sm text-blue-800 dark:text-blue-300 space-y-1">
                        <li>Clique em "Baixar Template" para obter o modelo Excel</li>
                        <li>Preencha os dados seguindo o exemplo incluído</li>
                        <li>Salve o arquivo e faça o upload abaixo</li>
                        <li>Os dados serão validados antes da importação</li>
                    </ol>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                    <h4 class="font-medium text-yellow-900 dark:text-yellow-200 mb-2">⚠️ Campos obrigatórios:</h4>
                    <p class="text-sm text-yellow-800 dark:text-yellow-300">Número de Série, Filial, Código do Produto, Descrição do Produto, Responsável Técnico</p>
                </div>

                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selecione o arquivo Excel:</label>
                <input type="file" id="arquivo-importacao" accept=".xlsx,.xls,.csv" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <small class="text-gray-500 dark:text-gray-400">Formatos aceitos: .xlsx, .xls, .csv</small>
            </div>

            <div id="preview-importacao" class="hidden mb-4">
                <h4 class="font-medium text-gray-900 mb-2">Preview dos Dados:</h4>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-h-64 overflow-y-auto">
                    <p id="preview-count" class="text-sm text-gray-600 mb-2"></p>
                    <div id="preview-content" class="text-sm"></div>
                </div>
            </div>

            <div id="progress-importacao" class="hidden mb-4">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full transition-all" style="width: 0%"></div>
                </div>
                <p id="progress-text" class="text-sm text-gray-600 mt-2 text-center"></p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="fecharModalImportacao()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Cancelar
                </button>
                <button type="button" onclick="baixarTemplate()" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-md">
                    Baixar Template
                </button>
                <button type="button" id="btn-importar" onclick="processarImportacao()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md" disabled>
                    Importar Dados
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Descarte -->
<div id="modal-descarte" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modal-titulo">Novo Descarte</h3>
                <button onclick="fecharModalDescarte()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="form-descarte" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" id="descarte-id" name="id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número de Série *</label>
                        <input type="text" id="numero-serie" name="numero_serie" required autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filial *</label>
                        <select id="filial-id" name="filial_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione uma filial</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option value="<?= $filial['id'] ?>"><?= e($filial['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código do Produto *</label>
                        <input type="text" id="codigo-produto" name="codigo_produto" required autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data do Descarte</label>
                        <input type="date" id="data-descarte" name="data_descarte" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <small class="text-gray-500 dark:text-gray-400">Se não informada, será considerado hoje</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descrição do Produto *</label>
                    <textarea id="descricao-produto" name="descricao_produto" required rows="3" autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Número da OS</label>
                        <input type="text" id="numero-os" name="numero_os" autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Responsável Técnico *</label>
                        <input type="text" id="responsavel-tecnico" name="responsavel_tecnico" required autocomplete="off" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Anexo da OS Assinada</label>
                    <input type="file" id="anexo-os" name="anexo_os" accept=".png,.jpg,.jpeg,.pdf" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <small class="text-gray-500 dark:text-gray-400">Formatos aceitos: PNG, JPEG, PDF. Máximo 10MB</small>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="ph ph-bell mr-1 text-amber-500"></i>Notificar Pessoas
                        <span class="text-xs font-normal text-gray-400 ml-1">(opcional)</span>
                    </label>
                    <select id="notificar-usuarios" name="notificar_usuarios[]" multiple placeholder="Buscar pessoas para notificar...">
                        <?php foreach ($usuariosNotificacao as $usuario): ?>
                        <option value="<?= $usuario['id'] ?>">
                            <?= htmlspecialchars($usuario['name']) ?> (<?= htmlspecialchars($usuario['email']) ?>)<?php if (in_array($usuario['role'], ['admin', 'super_admin'])): ?> · Admin<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">
                        Se nenhuma pessoa for selecionada, ninguém será notificado por e-mail.
                    </p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalDescarte()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                    <button type="button" id="btn-salvar-descarte" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal OS Duplicada -->
<div id="modal-os-duplicada" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-[70]" onclick="if (event.target === this) fecharModalOsDuplicada()">
    <div class="relative top-24 mx-auto w-11/12 md:w-[28rem] shadow-2xl rounded-2xl bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-slate-700 bg-red-50 dark:bg-red-900/20">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-red-600 dark:text-red-300">
                        <i class="ph ph-warning-circle text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">OS ja cadastrada</h3>
                        <p id="os-duplicada-texto" class="text-sm text-gray-600 dark:text-gray-300 mt-1">Olha, esta OS ja foi registrada.</p>
                    </div>
                </div>
                <button type="button" onclick="fecharModalOsDuplicada()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50 p-4 space-y-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Numero da OS</p>
                    <p id="os-duplicada-numero" class="text-base font-bold text-gray-900 dark:text-white mt-1">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Numero de serie</p>
                    <p id="os-duplicada-serie" class="text-base font-bold text-gray-900 dark:text-white mt-1">-</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">Filial</p>
                    <p id="os-duplicada-filial" class="text-base font-bold text-gray-900 dark:text-white mt-1">-</p>
                </div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Confira os dados antes de salvar novamente ou use outra OS para este descarte.
            </p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/40 flex justify-end">
            <button type="button" onclick="fecharModalOsDuplicada()" class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white rounded-xl font-medium transition-colors">
                Entendi
            </button>
        </div>
    </div>
</div>

<!-- Modal Alterar Status -->
<div id="modal-alterar-status" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Alterar Status do Descarte</h3>
                <button onclick="fecharModalAlterarStatus()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="form-alterar-status">
                <input type="hidden" id="status-descarte-id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Atual:</label>
                    <p id="status-atual-display" class="text-sm text-gray-600 dark:text-gray-400 mb-4"></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Novo Status: *</label>
                    <select id="novo-status" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione...</option>
                        <option value="Aguardando Descarte">⏳ Aguardando Descarte</option>
                        <option value="Itens Descartados">✅ Itens Descartados</option>
                        <option value="Descartes Reprovados">❌ Descartes Reprovados</option>
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Justificativa:</label>
                    <textarea id="justificativa-status" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Ex: Aprovado após conferência física..."></textarea>
                    <small class="text-gray-500 dark:text-gray-400">Opcional, mas recomendado</small>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalAlterarStatus()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                    <button type="button" onclick="salvarNovoStatus()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Salvar Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Alterar Status de Andamento -->
<div id="modal-alterar-andamento" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    <span class="text-orange-500">🔧</span> Status de Andamento
                </h3>
                <button onclick="fecharModalAlterarAndamento()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>💡 Dica:</strong> Use este campo para a área técnica acompanhar o andamento do descarte.
                </p>
            </div>
            
            <form id="form-alterar-andamento">
                <input type="hidden" id="andamento-descarte-id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Atual:</label>
                    <p id="andamento-atual-display" class="text-sm text-gray-600 dark:text-gray-400 mb-4"></p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Selecione o novo status: *</label>
                    <select id="novo-andamento" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">Selecione...</option>
                        <option value="Em aberto">🔄 Em aberto</option>
                        <option value="Concluído">✅ Concluído</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalAlterarAndamento()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                    <button type="button" onclick="salvarNovoAndamento()" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md">
                        Salvar Andamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Log de Ações -->
<div id="modal-logs" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 backdrop-blur-sm">
    <div class="relative top-5 mx-auto border-0 w-11/12 md:w-4/5 lg:w-3/4 xl:w-2/3 shadow-2xl rounded-xl bg-white dark:bg-gray-800 mb-10 border-gray-200 dark:border-gray-700">
        <!-- Header com gradiente -->
        <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-t-xl px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-white">Log de Ações</h3>
                        <p class="text-gray-300 text-sm">Histórico de alterações no módulo</p>
                    </div>
                </div>
                <button onclick="fecharModalLogs()" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Filtros do Log -->
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Ação</label>
                        <select id="log-filtro-acao" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400">
                            <option value="">Todas</option>
                            <option value="INSERT">➕ Inserção</option>
                            <option value="UPDATE">✏️ Alteração</option>
                            <option value="DELETE">🗑️ Exclusão</option>
                            <option value="DELETE_FAILED">⚠️ Exclusão Falha</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Início</label>
                        <input type="date" id="log-filtro-data-inicio" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data Fim</label>
                        <input type="date" id="log-filtro-data-fim" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400">
                    </div>
                    <div class="flex items-end">
                        <button onclick="carregarLogs()" class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Tabela de Logs -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-100 dark:bg-gray-900 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Ação</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Usuário</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Descrição</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Detalhes</th>
                            </tr>
                        </thead>
                        <tbody id="tabela-logs" class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            <!-- Dados carregados via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div id="logs-no-data" class="text-center py-12 hidden">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-500 text-lg">Nenhum log encontrado</p>
                <p class="text-gray-400 text-sm mt-1">Crie, edite ou exclua registros para ver o histórico aqui</p>
            </div>
            
            <!-- Paginação Logs -->
            <div id="logs-paginacao" class="flex justify-between items-center mt-4 pt-4 border-t hidden">
                <span id="logs-paginacao-info" class="text-sm text-gray-600"></span>
                <div class="flex space-x-2">
                    <button onclick="paginaAnteriorLogs()" id="logs-btn-anterior" class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
                        ← Anterior
                    </button>
                    <button onclick="proximaPaginaLogs()" id="logs-btn-proximo" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
                        Próximo →
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Senha Admin para Exclusão -->
<div id="modal-senha-admin" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">🔐 Confirmação de Exclusão</h3>
                <button onclick="fecharModalSenhaAdmin()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800">
                    <strong>⚠️ Atenção:</strong> Esta ação é irreversível. Para continuar, insira a senha de um administrador do sistema.
                </p>
            </div>
            
            <input type="hidden" id="excluir-descarte-id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Senha do Administrador *</label>
                <input type="password" id="admin-password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Digite a senha do admin">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="fecharModalSenhaAdmin()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Cancelar
                </button>
                <button type="button" onclick="confirmarExclusao()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                    🗑️ Confirmar Exclusão
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const podeAlterarStatusGlobal = <?= $canAlterarStatus ? 'true' : 'false' ?>;
const canEdit   = <?= $canEdit   ? 'true' : 'false' ?>;
const canDelete = <?= $canDelete ? 'true' : 'false' ?>;
const isAdmin   = <?= $isAdmin   ? 'true' : 'false' ?>;
let descartes = [];
let paginacao = { page: 1, per_page: 10, total: 0, total_pages: 0 };
let logsPaginacao = { page: 1, per_page: 20, total: 0, total_pages: 0 };

// Carregar dados ao inicializar
document.addEventListener('DOMContentLoaded', function() {
    carregarDescartes();

    // Inicializar TomSelect para notificações
    const selectNotificar = document.getElementById('notificar-usuarios');
    if (selectNotificar && typeof TomSelect !== 'undefined') {
        window.tsNotificar = new TomSelect('#notificar-usuarios', {
            plugins: ['remove_button'],
            maxOptions: null,
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results px-3 py-2 text-sm text-gray-500">Nenhum usuário encontrado para "' + escape(data.input) + '"</div>';
                }
            }
        });
    }
    
    // Debounce para busca em tempo real
    let debounceTimer = null;
    const debounce = (callback, delay = 400) => {
        return (...args) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => callback.apply(this, args), delay);
        };
    };
    
    // Event listeners para filtros
    document.getElementById('filtro-numero-serie').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') aplicarFiltros();
    });
    document.getElementById('f-os-number').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') aplicarFiltros();
    });
    
    // Busca em tempo real por código do produto
    document.getElementById('filtro-codigo-produto').addEventListener('input', debounce(function() {
        aplicarFiltros();
    }, 400));
    
    // Enter também funciona para código do produto
    document.getElementById('filtro-codigo-produto').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            clearTimeout(debounceTimer);
            aplicarFiltros();
        }
    });
    
    // Sincronizar scroll superior com a tabela
    const scrollTop = document.getElementById('scroll-top-container');
    const scrollTable = document.getElementById('tabela-scroll-container');
    
    if (scrollTop && scrollTable) {
        // Sincronizar quando rolar a barra superior
        scrollTop.addEventListener('scroll', function() {
            scrollTable.scrollLeft = scrollTop.scrollLeft;
        });
        
        // Sincronizar quando rolar a tabela
        scrollTable.addEventListener('scroll', function() {
            scrollTop.scrollLeft = scrollTable.scrollLeft;
        });
        
        // Atualizar largura do conteúdo da barra superior quando a tabela carregar
        const updateTopScrollWidth = () => {
            const table = scrollTable.querySelector('table');
            if (table) {
                document.getElementById('scroll-top-content').style.width = table.scrollWidth + 'px';
            }
        };
        
        // Observar mudanças na tabela
        const observer = new MutationObserver(updateTopScrollWidth);
        observer.observe(document.getElementById('tabela-descartes'), { childList: true });
        
        // Atualizar ao redimensionar
        window.addEventListener('resize', updateTopScrollWidth);
    }

    // Forçar limpeza do campo OS após um pequeno delay (definitivo contra alguns browsers)
    setTimeout(() => {
        const osFilter = document.getElementById('f-os-number');
        if (osFilter && osFilter.value.includes('@')) {
            osFilter.value = '';
            osFilter.removeAttribute('readonly');
        }
    }, 500);
});


function carregarDescartes(page = 1) {
    const params = new URLSearchParams();
    const numeroSerie = document.getElementById('filtro-numero-serie').value;
    const codigoProduto = document.getElementById('filtro-codigo-produto').value;
    const numeroOs = document.getElementById('f-os-number').value;
    const filialId = document.getElementById('filtro-filial').value;
    const dataInicio = document.getElementById('filtro-data-inicio').value;
    const dataFim = document.getElementById('filtro-data-fim').value;
    const statusAndamento = document.getElementById('filtro-andamento').value;
    
    if (numeroSerie) params.append('numero_serie', numeroSerie);
    if (codigoProduto) params.append('codigo_produto', codigoProduto);
    if (numeroOs) params.append('numero_os', numeroOs);
    if (filialId) params.append('filial_id', filialId);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    if (statusAndamento) params.append('status_andamento', statusAndamento);
    
    params.append('page', page);
    params.append('per_page', paginacao.per_page);

    // Feedback visual de carregamento
    document.getElementById('tabela-descartes').innerHTML = '<tr><td colspan="10" class="px-6 py-4 text-center">Carregando...</td></tr>';

    return fetch(`/controle-descartes/list?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                descartes = data.data;
                paginacao = data.pagination;
                renderizarTabela();
                atualizarControlesPaginacao();
            } else {
                alert('Erro ao carregar descartes: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showToast('Erro ao carregar descartes. Tente atualizar a página.', 'error');
        });
}


function renderizarTabela() {
    const tbody = document.getElementById('tabela-descartes');
    const noData = document.getElementById('no-data');
    
    if (descartes.length === 0) {
        tbody.innerHTML = '';
        noData.classList.remove('hidden');
        return;
    }
    
    noData.classList.add('hidden');
    
    tbody.innerHTML = descartes.map(descarte => `
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                ${escapeHtml(descarte.numero_serie)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-slate-400">
                ${escapeHtml(descarte.filial_nome || '')}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-slate-400">
                <span class="font-mono bg-gray-100 dark:bg-slate-900/60 border border-gray-200 dark:border-slate-700 px-2 py-1 rounded text-xs dark:text-slate-200">${escapeHtml(descarte.codigo_produto || '-')}</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-slate-400">
                <div class="truncate max-w-xs" title="${escapeHtml(descarte.descricao_produto || '')}">${escapeHtml(descarte.descricao_produto || '-')}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-slate-400">
                ${formatarData(descarte.data_descarte)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-slate-400">
                ${escapeHtml(descarte.responsavel_tecnico)}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-slate-400">
                ${descarte.numero_os ? `<span class="bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded border border-blue-100 dark:border-blue-800/50">${escapeHtml(descarte.numero_os)}</span>` : '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button onclick="abrirModalAlterarAndamento(${descarte.id}, '${escapeHtml(descarte.status_andamento || 'Em aberto')}')" 
                        class="cursor-pointer hover:opacity-80 transition-opacity" 
                        title="Clique para alterar o status de andamento">
                    ${getStatusAndamentoBadge(descarte.status_andamento || 'Em aberto')}
                </button>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                ${descarte.tem_anexo ? 
                    `<a href="/controle-descartes/anexo/${descarte.id}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors" title="Baixar anexo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </a>` : '-'
            }
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex space-x-2">
                    ${canEdit ? `
                    <button onclick="editarDescarte(${descarte.id})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors" title="Editar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>` : ''}
                    ${canDelete ? `
                    <button onclick="excluirDescarte(${descarte.id})" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors" title="Excluir">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>` : ''}
                    ${podeAlterarStatusGlobal ? `
                    <button onclick="abrirModalAlterarStatus(${descarte.id}, '${escapeHtml(descarte.status)}')" 
                            class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 transition-colors" title="Alterar Status Final">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

// Aplicar filtros
function aplicarFiltros() {
    const btn = event?.target?.closest('button');
    if (btn && btn.id === 'btn-filtrar') setButtonLoading(btn, true);
    
    paginacao.page = 1; // Resetar para primeira página
    carregarDescartes().finally(() => {
        if (btn && btn.id === 'btn-filtrar') setButtonLoading(btn, false);
    });
}

// Limpar filtros
function limparFiltros() {
    const btn = event?.target?.closest('button');
    if (btn) setButtonLoading(btn, true);

    document.getElementById('filtro-numero-serie').value = '';
    document.getElementById('filtro-codigo-produto').value = '';
    document.getElementById('f-os-number').value = '';
    document.getElementById('filtro-filial').value = '';
    document.getElementById('filtro-data-fim').value = '';
    document.getElementById('filtro-andamento').value = '';
    
    paginacao.page = 1; // Resetar para primeira página
    
    carregarDescartes().finally(() => {
        if (btn) setButtonLoading(btn, false);
        showToast('Filtros limpos com sucesso', 'info');
    });
}

// Abrir modal para novo descarte
function abrirModalDescarte() {
    document.getElementById('modal-titulo').textContent = 'Novo Descarte';
    document.getElementById('form-descarte').reset();
    document.getElementById('descarte-id').value = '';
    
    // Limpar seleção do select múltiplo (TomSelect)
    if (window.tsNotificar) {
        window.tsNotificar.clear();
    }
    
    document.getElementById('modal-descarte').classList.remove('hidden');
}

// Fechar modal
function fecharModalDescarte() {
    document.getElementById('modal-descarte').classList.add('hidden');
}

function abrirModalOsDuplicada(response) {
    const duplicate = response?.duplicate || {};
    const numeroOs = duplicate.numero_os || document.getElementById('numero-os')?.value || '-';
    const numeroSerie = duplicate.numero_serie || '-';
    const filial = duplicate.filial_nome || '-';

    document.getElementById('os-duplicada-texto').textContent = response?.message || 'Olha, esta OS ja foi registrada.';
    document.getElementById('os-duplicada-numero').textContent = numeroOs;
    document.getElementById('os-duplicada-serie').textContent = numeroSerie;
    document.getElementById('os-duplicada-filial').textContent = filial;
    document.getElementById('modal-os-duplicada').classList.remove('hidden');
}

function fecharModalOsDuplicada() {
    document.getElementById('modal-os-duplicada').classList.add('hidden');

    const campoOs = document.getElementById('numero-os');
    if (campoOs) {
        campoOs.focus();
        if (typeof campoOs.select === 'function') {
            campoOs.select();
        }
    }
}

// Editar descarte
function editarDescarte(id) {
    const descarte = descartes.find(d => d.id == id);
    if (!descarte) return;
    
    document.getElementById('modal-titulo').textContent = 'Editar Descarte';
    document.getElementById('descarte-id').value = descarte.id;
    document.getElementById('numero-serie').value = descarte.numero_serie;
    document.getElementById('filial-id').value = descarte.filial_id;
    document.getElementById('codigo-produto').value = descarte.codigo_produto;
    document.getElementById('descricao-produto').value = descarte.descricao_produto;
    document.getElementById('data-descarte').value = descarte.data_descarte;
    document.getElementById('numero-os').value = descarte.numero_os || '';
    document.getElementById('responsavel-tecnico').value = descarte.responsavel_tecnico;
    document.getElementById('observacoes').value = descarte.observacoes || '';
    
    document.getElementById('modal-descarte').classList.remove('hidden');
}

// Excluir descarte - agora abre modal para senha de admin
function excluirDescarte(id) {
    document.getElementById('excluir-descarte-id').value = id;
    document.getElementById('admin-password').value = '';
    document.getElementById('modal-senha-admin').classList.remove('hidden');
}

// Fechar modal de senha admin
function fecharModalSenhaAdmin() {
    document.getElementById('modal-senha-admin').classList.add('hidden');
    document.getElementById('admin-password').value = '';
}

// Confirmar exclusão com senha de admin
function confirmarExclusao() {
    const id = document.getElementById('excluir-descarte-id').value;
    const senha = document.getElementById('admin-password').value;
    
    if (!senha) {
        showToast('Por favor, insira a senha do administrador', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('admin_password', senha);
    
    fetch('/controle-descartes/delete', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharModalSenhaAdmin();
            mostrarNotificacao('Descarte excluído com sucesso!', 'success');
            carregarDescartes();
        } else {
            showToast('Erro: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao excluir descarte', 'error');
    });
}

// Ação explícita de salvar (evita submissão nativa e qualquer navegação)
document.getElementById('btn-salvar-descarte').addEventListener('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const btn = this;
    const originalText = btn.innerHTML;
    
    const form = document.getElementById('form-descarte');
    
    // Validação básica frontend
    if (!form.checkVisibility()) return; // Ignorar se não visível
    
    const formData = new FormData(form);
    const isEdit = document.getElementById('descarte-id').value !== '';
    const url = isEdit ? '/controle-descartes/update' : '/controle-descartes/create';

    // Desabilitar botão para evitar double-click
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Salvando...';

    fetch(url, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        redirect: 'manual' // evita seguir qualquer redirect (ex: login)
    })
    .then(async (response) => {
        // Se o servidor tentar redirecionar (ex: sessão expirada), response.type pode ser 'opaqueredirect'
        if (response.type === 'opaqueredirect' || (response.status >= 300 && response.status < 400)) {
            showToast('Sua sessão pode ter expirado. Por favor, faça login novamente.', 'error');
            return { success: false };
        }
        const raw = await response.text();

        if (!raw || !raw.trim()) {
            return { success: false, message: 'Nao foi possivel processar a resposta do servidor.' };
        }

        try {
            return JSON.parse(raw);
        } catch (error) {
            console.error('Resposta nao JSON em controle-descartes:', raw);
            return { success: false, message: 'Nao foi possivel processar a resposta do servidor.' };
        }
        try { return await response.json(); } catch (_) { return { success: false, message: 'Resposta inválida do servidor' }; }
    })
    .then(data => {
        if (data && data.success) {
            showToast(data.message || 'Registro salvo com sucesso!', 'success');
            fecharModalDescarte();
            carregarDescartes();
        } else if (data && data.error_code === 'duplicate_os') {
            abrirModalOsDuplicada(data);
        } else if (data && data.message) {
            showToast('Erro: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao salvar descarte', 'error');
    })
    .finally(() => {
        // Reabilitar botão
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Funções auxiliares
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatarData(data) {
    if (!data) return '-';
    const date = new Date(data + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
}

function exportarDescartes() {
    const params = new URLSearchParams();

    const numeroSerie = document.getElementById('filtro-numero-serie')?.value || '';
    const codigoProduto = document.getElementById('filtro-codigo-produto')?.value || '';
    const numeroOs = document.getElementById('f-os-number')?.value || '';
    const filialId = document.getElementById('filtro-filial')?.value || '';
    const dataInicio = document.getElementById('filtro-data-inicio')?.value || '';
    const dataFim = document.getElementById('filtro-data-fim')?.value || '';
    const statusAndamento = document.getElementById('filtro-status-andamento')?.value || '';

    if (numeroSerie) params.append('numero_serie', numeroSerie);
    if (codigoProduto) params.append('codigo_produto', codigoProduto);
    if (numeroOs) params.append('numero_os', numeroOs);
    if (filialId) params.append('filial_id', filialId);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    if (statusAndamento) params.append('status_andamento', statusAndamento);
    params.append('export', 'csv');

    window.location.href = '/controle-descartes/exportar?' + params.toString();
}

// Verificar se pode alterar status
function podeAlterarStatus() {
    return podeAlterarStatusGlobal;
}

// Obter badge de status colorido
function getStatusBadge(status) {
    const badges = {
        'Aguardando Descarte': '<span class="px-2 py-1 text-xs font-bold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800/50">⏳ Aguardando</span>',
        'Itens Descartados': '<span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800/50">✅ Descartados</span>',
        'Descartes Reprovados': '<span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800/50">❌ Reprovados</span>'
    };
    return badges[status] || badges['Aguardando Descarte'];
}

// Obter badge de status de andamento (para área técnica)
function getStatusAndamentoBadge(status) {
    const badges = {
        'Em aberto': '<span class="px-2 py-1 text-xs font-bold rounded-full bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-200 border border-orange-300 dark:border-orange-800/50">🔄 Em aberto</span>',
        'Concluído': '<span class="px-2 py-1 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-800/50">✅ Concluído</span>'
    };
    return badges[status] || badges['Em aberto'];
}

// Abrir modal para alterar status de andamento
function abrirModalAlterarAndamento(descarteId, statusAtual) {
    document.getElementById('andamento-descarte-id').value = descarteId;
    document.getElementById('andamento-atual-display').innerHTML = getStatusAndamentoBadge(statusAtual);
    // Selecionar o status atual no dropdown
    document.getElementById('novo-andamento').value = statusAtual;
    document.getElementById('modal-alterar-andamento').classList.remove('hidden');
}

// Fechar modal de alterar andamento
function fecharModalAlterarAndamento() {
    document.getElementById('modal-alterar-andamento').classList.add('hidden');
}

// Salvar novo status de andamento
function salvarNovoAndamento() {
    const descarteId = document.getElementById('andamento-descarte-id').value;
    const novoStatus = document.getElementById('novo-andamento').value;
    
    if (!novoStatus) {
        alert('Selecione um status de andamento');
        return;
    }
    
    const formData = new FormData();
    formData.append('id', descarteId);
    formData.append('status_andamento', novoStatus);
    
    fetch('/controle-descartes/alterar-status-andamento', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar o status localmente para feedback imediato
            const descarte = descartes.find(d => d.id == descarteId);
            if (descarte) {
                descarte.status_andamento = novoStatus;
            }
            fecharModalAlterarAndamento();
            renderizarTabela();
            // Pequena notificação de sucesso
            mostrarNotificacao(data.message, 'success');
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao alterar status de andamento');
    });
}

// Mostrar notificação temporária
function mostrarNotificacao(mensagem, tipo = 'info') {
    const container = document.createElement('div');
    container.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-0 ${
        tipo === 'success' ? 'bg-green-500 text-white' : 
        tipo === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    container.textContent = mensagem;
    document.body.appendChild(container);
    
    setTimeout(() => {
        container.classList.add('opacity-0', 'translate-x-full');
        setTimeout(() => container.remove(), 300);
    }, 3000);
}

// Abrir modal para alterar status
function abrirModalAlterarStatus(descarteId, statusAtual) {
    document.getElementById('status-descarte-id').value = descarteId;
    document.getElementById('status-atual-display').innerHTML = getStatusBadge(statusAtual);
    document.getElementById('novo-status').value = '';
    document.getElementById('justificativa-status').value = '';
    document.getElementById('modal-alterar-status').classList.remove('hidden');
}

// Fechar modal alterar status
function fecharModalAlterarStatus() {
    document.getElementById('modal-alterar-status').classList.add('hidden');
}

// Salvar novo status
function salvarNovoStatus() {
    const descarteId = document.getElementById('status-descarte-id').value;
    const novoStatus = document.getElementById('novo-status').value;
    const justificativa = document.getElementById('justificativa-status').value;
    
    if (!novoStatus) {
        alert('Selecione um status');
        return;
    }
    
    const formData = new FormData();
    formData.append('id', descarteId);
    formData.append('status', novoStatus);
    formData.append('justificativa', justificativa);
    
    fetch('/controle-descartes/alterar-status', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            fecharModalAlterarStatus();
            carregarDescartes();
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao alterar status');
    });
}

// ===== FUNÇÕES DE IMPORTAÇÃO =====

// Abrir modal de importação
function abrirModalImportacao() {
    document.getElementById('modal-importacao').classList.remove('hidden');
    document.getElementById('arquivo-importacao').value = '';
    document.getElementById('preview-importacao').classList.add('hidden');
    document.getElementById('progress-importacao').classList.add('hidden');
    document.getElementById('btn-importar').disabled = true;
}

// Fechar modal de importação
function fecharModalImportacao() {
    document.getElementById('modal-importacao').classList.add('hidden');
}

// Baixar template Excel
function baixarTemplate() {
    window.location.href = '/controle-descartes/template';
}

// Event listener para arquivo selecionado
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('arquivo-importacao');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                previewArquivo(file);
            }
        });
    }
});

// Preview do arquivo
function previewArquivo(file) {
    console.log('Preview arquivo:', file.name);
    
    // Validar tipo de arquivo
    const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                          'application/vnd.ms-excel', 
                          'text/csv'];
    
    if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
        alert('Formato de arquivo inválido. Use .xlsx, .xls ou .csv');
        document.getElementById('arquivo-importacao').value = '';
        return;
    }
    
    // Validar tamanho (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('Arquivo muito grande. Máximo 5MB permitido.');
        document.getElementById('arquivo-importacao').value = '';
        return;
    }
    
    // Mostrar preview simples
    document.getElementById('preview-importacao').classList.remove('hidden');
    document.getElementById('preview-count').textContent = `Arquivo selecionado: ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;
    document.getElementById('preview-content').innerHTML = '<p class="text-gray-600">Clique em "Importar Dados" para processar o arquivo</p>';
    document.getElementById('btn-importar').disabled = false;
}

// Processar importação
function processarImportacao() {
    const fileInput = document.getElementById('arquivo-importacao');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Selecione um arquivo para importar');
        return;
    }
    
    // Mostrar progress bar
    document.getElementById('progress-importacao').classList.remove('hidden');
    document.getElementById('progress-bar').style.width = '0%';
    document.getElementById('progress-text').textContent = 'Enviando arquivo...';
    document.getElementById('btn-importar').disabled = true;
    
    const formData = new FormData();
    formData.append('arquivo', file);
    
    // Simular progresso
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 10;
        if (progress <= 50) {
            document.getElementById('progress-bar').style.width = progress + '%';
        }
    }, 200);
    
    fetch('/controle-descartes/importar', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        clearInterval(progressInterval);
        document.getElementById('progress-bar').style.width = '100%';
        
        if (data.success) {
            document.getElementById('progress-text').textContent = `✅ Sucesso! ${data.imported} registros importados.`;
            
            if (data.errors && data.errors.length > 0) {
                const errorMsg = `\n\nAvisos:\n${data.errors.join('\n')}`;
                alert(`Importação concluída com ${data.imported} registros.${errorMsg}`);
            } else {
                alert(`Importação concluída! ${data.imported} registros importados com sucesso.`);
            }
            
            setTimeout(() => {
                fecharModalImportacao();
                carregarDescartes();
            }, 2000);
        } else {
            document.getElementById('progress-text').textContent = '❌ Erro na importação';
            alert('Erro ao importar: ' + data.message);
            document.getElementById('btn-importar').disabled = false;
        }
    })
    .catch(error => {
        clearInterval(progressInterval);
        console.error('Erro:', error);
        document.getElementById('progress-text').textContent = '❌ Erro ao processar arquivo';
        alert('Erro ao processar importação');
        document.getElementById('btn-importar').disabled = false;
    });
}

// ===== FUNÇÕES DE PAGINAÇÃO =====

function atualizarControlesPaginacao() {
    const container = document.getElementById('paginacao-container');
    
    if (paginacao.total === 0) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    
    // Atualizar info
    const inicio = ((paginacao.page - 1) * paginacao.per_page) + 1;
    const fim = Math.min(paginacao.page * paginacao.per_page, paginacao.total);
    document.getElementById('paginacao-info').textContent = 
        `Mostrando ${inicio}-${fim} de ${paginacao.total} registros`;
    
    // Atualizar seletor de itens por página
    document.getElementById('per-page-select').value = paginacao.per_page;
    
    // Atualizar botões
    document.getElementById('btn-primeira').disabled = paginacao.page <= 1;
    document.getElementById('btn-anterior').disabled = paginacao.page <= 1;
    document.getElementById('btn-proximo').disabled = paginacao.page >= paginacao.total_pages;
    document.getElementById('btn-ultima').disabled = paginacao.page >= paginacao.total_pages;
    
    // Gerar números de página
    const numerosContainer = document.getElementById('paginacao-numeros');
    let html = '';
    const maxVisiveis = 5;
    let inicio_pg = Math.max(1, paginacao.page - Math.floor(maxVisiveis / 2));
    let fim_pg = Math.min(paginacao.total_pages, inicio_pg + maxVisiveis - 1);
    
    if (fim_pg - inicio_pg < maxVisiveis - 1) {
        inicio_pg = Math.max(1, fim_pg - maxVisiveis + 1);
    }
    
    for (let i = inicio_pg; i <= fim_pg; i++) {
        const isActive = i === paginacao.page;
        const ativo = isActive
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-white dark:bg-slate-800 text-gray-700 dark:text-slate-300 border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700';
        html += `<button onclick="irParaPagina(${i})" class="px-3 py-1 border rounded-lg text-sm transition-colors ${ativo}">${i}</button>`;
    }
    
    numerosContainer.innerHTML = html;
}

function irParaPagina(page) {
    if (page < 1 || page > paginacao.total_pages) return;
    carregarDescartes(page);
}

function paginaAnterior() {
    if (paginacao.page > 1) {
        carregarDescartes(paginacao.page - 1);
    }
}

function proximaPagina() {
    if (paginacao.page < paginacao.total_pages) {
        carregarDescartes(paginacao.page + 1);
    }
}

function alterarItensPorPagina() {
    paginacao.per_page = parseInt(document.getElementById('per-page-select').value);
    paginacao.page = 1;
    carregarDescartes();
}

// ===== FUNÇÕES DO MODAL DE LOGS =====

function abrirModalLogs() {
    document.getElementById('modal-logs').classList.remove('hidden');
    logsPaginacao.page = 1;
    carregarLogs();
}

function fecharModalLogs() {
    document.getElementById('modal-logs').classList.add('hidden');
}

function carregarLogs() {
    const params = new URLSearchParams();
    const acao = document.getElementById('log-filtro-acao').value;
    const dataInicio = document.getElementById('log-filtro-data-inicio').value;
    const dataFim = document.getElementById('log-filtro-data-fim').value;
    
    if (acao) params.append('acao', acao);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    params.append('page', logsPaginacao.page);
    params.append('per_page', logsPaginacao.per_page);
    
    fetch(`/controle-descartes/action-logs?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                logsPaginacao = data.pagination || logsPaginacao;
                renderizarTabelaLogs(data.data);
                atualizarControlesPaginacaoLogs();
            } else {
                alert('Erro ao carregar logs: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar logs');
        });
}

function renderizarTabelaLogs(logs) {
    const tbody = document.getElementById('tabela-logs');
    const noData = document.getElementById('logs-no-data');
    
    if (logs.length === 0) {
        tbody.innerHTML = '';
        noData.classList.remove('hidden');
        return;
    }
    
    noData.classList.add('hidden');
    
    tbody.innerHTML = logs.map(log => {
        const data = new Date(log.created_at);
        const dataFormatada = data.toLocaleString('pt-BR');
        
        const badgeAcao = getAcaoBadge(log.acao);
        
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-500">${dataFormatada}</td>
                <td class="px-4 py-2 text-sm">${badgeAcao}</td>
                <td class="px-4 py-2 text-sm text-gray-700">${escapeHtml(log.usuario_nome)}</td>
                <td class="px-4 py-2 text-sm text-gray-600">${escapeHtml(log.descricao || '-')}</td>
                <td class="px-4 py-2 text-sm">
                    ${log.dados_anteriores || log.dados_novos ? 
                        `<button onclick='verDetalhesLog(${JSON.stringify(log)})' class="text-blue-600 hover:underline text-xs">Ver detalhes</button>` 
                        : '-'}
                </td>
            </tr>
        `;
    }).join('');
}

function getAcaoBadge(acao) {
    const badges = {
        'INSERT': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">➕ Inserção</span>',
        'UPDATE': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">✏️ Alteração</span>',
        'DELETE': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">🗑️ Exclusão</span>',
        'DELETE_FAILED': '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">⚠️ Exclusão Falha</span>'
    };
    return badges[acao] || `<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">${acao}</span>`;
}

function verDetalhesLog(log) {
    let mensagem = `📋 Detalhes do Log\n\nAção: ${log.acao}\nUsuário: ${log.usuario_nome}\nData: ${log.created_at}\n`;
    
    if (log.dados_anteriores) {
        mensagem += '\n--- Dados Anteriores ---\n';
        for (const [key, value] of Object.entries(log.dados_anteriores)) {
            mensagem += `${key}: ${value || '-'}\n`;
        }
    }
    
    if (log.dados_novos) {
        mensagem += '\n--- Dados Novos ---\n';
        for (const [key, value] of Object.entries(log.dados_novos)) {
            mensagem += `${key}: ${value || '-'}\n`;
        }
    }
    
    alert(mensagem);
}

function atualizarControlesPaginacaoLogs() {
    const container = document.getElementById('logs-paginacao');
    
    if (logsPaginacao.total === 0) {
        container.classList.add('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    
    // Atualizar info
    const inicio = ((logsPaginacao.page - 1) * logsPaginacao.per_page) + 1;
    const fim = Math.min(logsPaginacao.page * logsPaginacao.per_page, logsPaginacao.total);
    document.getElementById('logs-paginacao-info').textContent = 
        `Mostrando ${inicio}-${fim} de ${logsPaginacao.total} registros`;
    
    // Atualizar botões
    document.getElementById('logs-btn-anterior').disabled = logsPaginacao.page <= 1;
    document.getElementById('logs-btn-proximo').disabled = logsPaginacao.page >= logsPaginacao.total_pages;
}

function paginaAnteriorLogs() {
    if (logsPaginacao.page > 1) {
        logsPaginacao.page--;
        carregarLogs();
    }
}

function proximaPaginaLogs() {
    if (logsPaginacao.page < logsPaginacao.total_pages) {
        logsPaginacao.page++;
        carregarLogs();
    }
}
</script>
