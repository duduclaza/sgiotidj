<?php
$perfisSidebar = [
    'compras' => [
        ['url' => '/homologacoes', 'label' => 'Painel Geral', 'icon' => 'ph-chart-pie-slice'],
        ['url' => '/homologacoes/nova', 'label' => 'Nova Homologação', 'icon' => 'ph-plus-circle'],
        ['url' => '/homologacoes/monitoramento', 'label' => 'Monitorar Entregas', 'icon' => 'ph-magnifying-glass'],
        ['url' => '/homologacoes/gerenciar', 'label' => 'Gerenciar', 'icon' => 'ph-gear'],
    ],
    'logistica' => [
        ['url' => '/homologacoes/logistica', 'label' => 'Painel Logística', 'icon' => 'ph-truck'],
    ],
    'qualidade' => [
        ['url' => '/homologacoes/minha-fila', 'label' => 'Minhas Homologações', 'icon' => 'ph-list-checks'],
        ['url' => '/homologacoes/nova', 'label' => 'Nova Rehomologação', 'icon' => 'ph-plus-circle'],
    ],
    'tecnico' => [
        ['url' => '/homologacoes/minha-fila', 'label' => 'Minhas Homologações', 'icon' => 'ph-list-checks'],
    ],
    'admin' => [
        ['url' => '/homologacoes', 'label' => 'Painel Geral', 'icon' => 'ph-chart-pie-slice'],
        ['url' => '/homologacoes/nova', 'label' => 'Nova Homologação', 'icon' => 'ph-plus-circle'],
        ['url' => '/homologacoes/logistica', 'label' => 'Painel Logística', 'icon' => 'ph-truck'],
        ['url' => '/homologacoes/minha-fila', 'label' => 'Minha Fila', 'icon' => 'ph-list-checks'],
        ['url' => '/homologacoes/monitoramento', 'label' => 'Monitoramento', 'icon' => 'ph-magnifying-glass'],
        ['url' => '/homologacoes/gerenciar', 'label' => 'Gerenciar', 'icon' => 'ph-gear'],
    ],
];

$linksAtuais = $perfisSidebar[$u['perfil']] ?? $perfisSidebar['tecnico'];
$currentPage = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
$perfilLabel = ucfirst($u['perfil']);
if (in_array(strtolower((string) ($u['role'] ?? '')), ['super_admin', 'superadmin'], true)) {
    $perfilLabel = 'Super Admin';
} elseif (strtolower((string) ($u['role'] ?? '')) === 'admin') {
    $perfilLabel = 'Admin';
}
?>

<div class="bg-emerald-600 text-white text-center py-2 px-4 rounded-xl mb-4 font-bold tracking-wider shadow-lg flex items-center justify-center gap-2">
    <i class="ph-bold ph-check-circle text-xl"></i>
    MODULO OPERANDO COM DADOS REAIS
    <i class="ph-bold ph-database text-xl"></i>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <div class="flex flex-wrap gap-2">
        <?php foreach ($linksAtuais as $link): ?>
            <?php $active = ($currentPage === rtrim($link['url'], '/')); ?>
            <a href="<?= $link['url'] ?>"
               class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $active ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/50' ?>">
                <i class="ph <?= $link['icon'] ?> text-lg"></i> <?= $link['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 shrink-0">
        <span class="text-[10px] text-slate-400 font-semibold uppercase whitespace-nowrap">Perfil:</span>
        <span class="text-xs font-bold text-slate-700 dark:text-slate-200"><?= htmlspecialchars($perfilLabel) ?></span>
        <span class="text-[11px] text-slate-400">•</span>
        <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($u['nome']) ?></span>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <?php
        $t = $_SESSION['flash_message']['type'];
        $css = 'bg-slate-100 text-slate-800 border-slate-200';
        if ($t === 'success') $css = 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800';
        if ($t === 'warning') $css = 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-800';
        if ($t === 'danger') $css = 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-900/20 dark:text-rose-300 dark:border-rose-800';
        if ($t === 'info') $css = 'bg-cyan-50 text-cyan-800 border-cyan-200 dark:bg-cyan-900/20 dark:text-cyan-300 dark:border-cyan-800';
    ?>
    <div class="border px-4 py-3 rounded-lg mb-6 flex justify-between items-center <?= $css ?>">
        <span><?= $_SESSION['flash_message']['text'] ?></span>
        <button type="button" onclick="this.parentElement.remove()" class="opacity-50 hover:opacity-100"><i class="ph ph-x"></i></button>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>
