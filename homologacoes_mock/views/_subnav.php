<?php
$u = getUsuarioLogado();
$perfisSidebar = [
    'compras' => [
        ['url' => 'index.php', 'label' => 'Painel Geral', 'icon' => 'ph-chart-pie-slice'],
        ['url' => 'nova_homologacao.php', 'label' => 'Nova Homologação', 'icon' => 'ph-plus-circle'],
        ['url' => 'monitoramento.php', 'label' => 'Monitorar Entregas', 'icon' => 'ph-magnifying-glass'],
        ['url' => 'gerenciar_cadastros.php', 'label' => 'Gerenciar', 'icon' => 'ph-gear'],
    ],
    'logistica' => [
        ['url' => 'logistica.php', 'label' => 'Painel Logística', 'icon' => 'ph-truck'],
    ],
    'qualidade' => [
        ['url' => 'minha_fila.php', 'label' => 'Minhas Homologações', 'icon' => 'ph-list-checks'],
        ['url' => 'nova_homologacao.php', 'label' => 'Nova Rehomologação', 'icon' => 'ph-plus-circle'],
    ],
    'tecnico' => [
        ['url' => 'minha_fila.php', 'label' => 'Minhas Homologações', 'icon' => 'ph-list-checks'],
    ],
    'responsavel' => [
        ['url' => 'minha_fila.php', 'label' => 'Minhas Homologações', 'icon' => 'ph-list-checks'],
    ]
];

$linksAtuais = $perfisSidebar[$u['perfil']] ?? [];
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Banner de Testes -->
<div class="bg-rose-600 text-white text-center py-2 px-4 rounded-xl mb-4 font-bold tracking-wider animate-pulse shadow-lg flex items-center justify-center gap-2">
    <i class="ph-bold ph-warning-octagon text-xl"></i>
    MODULO EM FASE DE TESTES
    <i class="ph-bold ph-warning-octagon text-xl"></i>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
    <!-- Links Módulo -->
    <div class="flex flex-wrap gap-2">
        <?php foreach ($linksAtuais as $link): ?>
            <?php $active = ($currentPage === $link['url']); ?>
            <a href="<?= $link['url'] ?>" 
               class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $active ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700/50' ?>">
                <i class="ph <?= $link['icon'] ?> text-lg"></i> <?= $link['label'] ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Seletor de Perfil Mock (Compacto) -->
    <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-2.5 shrink-0">
        <span class="text-[10px] text-slate-400 font-semibold uppercase whitespace-nowrap">Simular:</span>
        <form method="POST" action="" class="m-0 p-0">
            <select name="usuario_logado_id" onchange="this.form.submit()" class="bg-transparent border-0 text-xs font-medium text-slate-700 dark:text-slate-200 focus:ring-0 cursor-pointer py-0">
                <?php foreach ($_SESSION['mock_usuarios'] as $mockUser): ?>
                    <?php 
                        $perfilTraduzido = [
                            'compras' => 'Compras',
                            'logistica' => 'Logistica',
                            'responsavel' => 'Responsável'
                        ];
                        $labelPerfil = $perfilTraduzido[$mockUser['perfil']] ?? ucfirst($mockUser['perfil']);
                    ?>
                    <option value="<?= $mockUser['id'] ?>" <?= $mockUser['id'] == $u['id'] ? 'selected' : '' ?> class="text-slate-800 dark:text-slate-900">
                        <?= $labelPerfil ?> (<?= $mockUser['nome'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="trocar_usuario" value="1">
        </form>
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
