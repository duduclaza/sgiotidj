<?php
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'] ?? '';

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
        'href' => '/cadastro-defeitos'
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
            <div class="hidden md:block">
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-1">Data de hoje</p>
                    <p class="text-lg font-bold text-slate-700 dark:text-slate-200" id="current-date-display"><?= date('d \d\e F, Y') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Ações Rápidas -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-6 flex items-center gap-3">
            <span class="p-2 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-xl">
                <i class="ph ph-lightning text-xl"></i>
            </span>
            Ações Rápidas & Favoritos
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($allowedModules as $mod): ?>
                <a href="<?= $mod['href'] ?>" class="group block p-6 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/50 hover:border-blue-500 dark:hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300 relative overflow-hidden">
                    <!-- Brilho ao hover -->
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-<?= $mod['color'] ?>-500/5 rounded-full blur-2xl group-hover:bg-<?= $mod['color'] ?>-500/20 transition-all duration-500"></div>
                    
                    <div class="flex items-start gap-5">
                        <div class="p-4 bg-<?= $mod['color'] ?>-50 dark:bg-<?= $mod['color'] ?>-900/30 text-<?= $mod['color'] ?>-600 dark:text-<?= $mod['color'] ?>-400 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="ph <?= $mod['icon'] ?> text-2xl font-bold"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-slate-800 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors uppercase tracking-tight"><?= $mod['label'] ?></h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed"><?= $mod['desc'] ?></p>
                        </div>
                        <div class="text-slate-300 dark:text-slate-600 group-hover:text-blue-500 transition-colors mt-1">
                            <i class="ph ph-arrow-right font-bold"></i>
                        </div>
                    </div>
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
