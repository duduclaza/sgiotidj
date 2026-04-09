<?php
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';

// Fetch pending counts for badges
$db = \App\Config\Database::getInstance();
$pendingDefeitos = $db->query("SELECT COUNT(*) FROM toners_defeitos WHERE (devolutiva_resultado IS NULL OR devolutiva_resultado = '')")->fetchColumn();

// Helper function locally if not already defined (though it should be in sidebar)
if (!function_exists('hasPermission')) {
    function hasPermission($module, $action = 'view') {
        if (!isset($_SESSION['user_id'])) return false;
        if (in_array($_SESSION['user_role'], ['admin', 'super_admin'])) return true;
        return \App\Services\PermissionService::hasPermission($_SESSION['user_id'], $module, $action);
    }
}

// Map of modules for the dashboard
$dashboardModules = [
    [
        'id' => 'triagem_toners',
        'label' => 'Triagem de Toners',
        'desc' => 'Realizar triagem e conferência de toners recebidos.',
        'icon' => 'ph-magnifying-glass',
        'color' => 'blue',
        'href' => '/triagem-toners'
    ],
    [
        'id' => 'amostragens_2',
        'label' => 'Amostragens 2.0',
        'desc' => 'Gerenciar testes e amostragens de lotes.',
        'icon' => 'ph-flask',
        'color' => 'emerald',
        'href' => '/amostragens-2'
    ],
    [
        'id' => 'controle_descartes',
        'label' => 'Controle de Descartes',
        'desc' => 'Registrar e monitorar descarte de materiais.',
        'icon' => 'ph-recycle',
        'color' => 'amber',
        'href' => '/controle-descartes'
    ],
    [
        'id' => 'cadastro_defeitos',
        'label' => 'Cadastro de Defeitos',
        'desc' => 'Gerenciar biblioteca de defeitos conhecidos.',
        'icon' => 'ph-puzzle-piece',
        'color' => 'red',
        'href' => '/cadastro-defeitos',
        'badge' => $pendingDefeitos > 0 ? $pendingDefeitos : null
    ],
    [
        'id' => 'garantias',
        'label' => 'Gestão de Garantias',
        'desc' => 'Processar requisições e consultas de garantia.',
        'icon' => 'ph-shield-check',
        'color' => 'indigo',
        'href' => '/garantias'
    ],
    [
        'id' => 'homologacoes',
        'label' => 'Homologações',
        'desc' => 'Acompanhar processos de homologação de produtos.',
        'icon' => 'ph-traffic-cone',
        'color' => 'orange',
        'href' => '/homologacoes'
    ],
    [
        'id' => 'pops_its_visualizacao',
        'label' => 'POPs e ITs',
        'desc' => 'Consultar procedimentos e instruções de trabalho.',
        'icon' => 'ph-books',
        'color' => 'slate',
        'href' => '/pops-e-its'
    ],
    [
        'id' => 'elearning_gestor',
        'label' => 'E-Learning Professor',
        'desc' => 'Criar cursos, aulas, provas, certificados e relatórios acadêmicos.',
        'icon' => 'ph-chalkboard-teacher',
        'color' => 'sky',
        'href' => '/elearning/gestor'
    ],
    [
        'id' => 'elearning_colaborador',
        'label' => 'E-Learning Aluno',
        'desc' => 'Assistir aulas, realizar provas, acompanhar progresso e emitir certificados.',
        'icon' => 'ph-student',
        'color' => 'emerald',
        'href' => '/elearning/colaborador'
    ],
    [
        'id' => 'admin_usuarios',
        'label' => 'Administração',
        'desc' => 'Gerenciar usuários, perfis e solicitações.',
        'icon' => 'ph-gear',
        'color' => 'indigo',
        'href' => '/admin/users',
        'admin_only' => true
    ]
];

// Group filters
$allowedModules = array_filter($dashboardModules, function($m) use ($userRole) {
    if (isset($m['admin_only']) && $m['admin_only'] && !in_array($userRole, ['admin', 'super_admin'])) return false;
    return hasPermission($m['id']);
});
?>

<section class="max-w-6xl mx-auto py-6">
    <!-- Header de Boas-vindas Premium -->
    <div class="mb-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-slate-800 p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700/50">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">
                    Olá, <span class="text-blue-600 dark:text-blue-400"><?= e($userName) ?></span>! 👋
                </h1>
                <p class="text-slate-500 dark:text-slate-400 font-medium">
                    Bem-vindo ao <span class="font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">SGI OTI</span>. 
                    Seu perfil é <span class="bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-md text-sm"><?= e($userProfile['name'] ?? 'Usuário') ?></span>.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-blue-600 dark:bg-blue-500 p-3 rounded-2xl shadow-lg shadow-blue-500/30 text-white">
                    <i class="ph-fill ph-calendar-check text-2xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none mb-1">Data de hoje</p>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200"><?= date('d \d\e F \d\e Y') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Módulos Principal -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-sm font-bold text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em]">Canais de Gestão</h2>
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800 ml-6"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($allowedModules as $m): ?>
                <a href="<?= e($m['href']) ?>" class="group block bg-white dark:bg-slate-800/60 hover:bg-slate-50 dark:hover:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700/50 hover:border-slate-400 dark:hover:border-slate-500 transition-all duration-300 relative overflow-hidden">
                    <?php if (isset($m['badge'])): ?>
                        <div class="absolute top-4 right-4 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ring-4 ring-white dark:ring-slate-800 animate-pulse">
                            <?= e($m['badge']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex flex-col h-full">
                        <div class="mb-5 flex items-center justify-between">
                            <div class="p-4 bg-<?= $m['color'] ?>-100 dark:bg-<?= $m['color'] ?>-900/30 text-<?= $m['color'] ?>-600 dark:text-<?= $m['color'] ?>-400 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                                <i class="ph <?= e($m['icon']) ?> text-2xl"></i>
                            </div>
                            <i class="ph ph-arrow-right text-slate-300 dark:text-slate-600 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2 leading-tight">
                            <?= e($m['label']) ?>
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                            <?= e($m['desc']) ?>
                        </p>
                    </div>
                    
                    <!-- Efeito de brilho no hover -->
                    <div class="absolute -bottom-12 -right-12 w-24 h-24 bg-<?= $m['color'] ?>-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Empty State se não tiver nenhum módulo (improvável mas necessário para UX) -->
    <?php if (empty($allowedModules)): ?>
        <div class="text-center py-20 bg-slate-50 dark:bg-slate-900/50 rounded-3xl border border-dashed border-slate-300 dark:border-slate-700">
            <div class="p-6 bg-white dark:bg-slate-800 rounded-full inline-block shadow-sm mb-4">
                <i class="ph ph-lock-laminated text-4xl text-slate-400"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">Acesso Restrito</h3>
            <p class="text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                Você ainda não possui acesso a módulos específicos. Entre em contato com o administrador para solicitar permissões.
            </p>
        </div>
    <?php endif; ?>
</section>

<script>
    // Tradução básica de meses para o display de data
    const meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    const data = new Date();
    const dia = data.getDate();
    const mes = meses[data.getMonth()];
    const ano = data.getFullYear();
    const display = document.getElementById('current-date-display');
    if (display) display.textContent = `${dia} de ${mes}, ${ano}`;
</script>
