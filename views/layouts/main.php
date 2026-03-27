<?php
// Forçar cabeçalhos de não-cache no PHP (mais forte que HTML meta tags)
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

$title = $title ?? 'SGQ OTI - DJ';
$viewFile = $viewFile ?? __DIR__ . '/../pages/home.php';
$sidebar = __DIR__ . '/../partials/sidebar.php';
// Versão dinâmica para evitar cache (time() força atualização a cada reload)
// Em produção, isso pode ser alterado para uma string fixa para performance
$assetVersion = time();
// Safe helper fallbacks in case global helpers are not loaded
if (!function_exists('e')) {
  function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('flash')) {
  function flash($key) { return null; }
}

// Background Dinâmico por Perfil
$userRole = $_SESSION['user_role'] ?? 'guest';
$bgImage = 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=2072&auto=format&fit=crop'; // Default

if ($userRole === 'super_admin' || $userRole === 'admin') {
    $bgImage = 'file:///C:/Users/djkyk/.gemini/antigravity/brain/807ab793-0c7d-4d15-b4e1-8ad010530f9d/neural_admin_bg_1774541701240.png';
} elseif ($userRole === 'tecnico' || $userRole === 'producao') {
    $bgImage = 'file:///C:/Users/djkyk/.gemini/antigravity/brain/807ab793-0c7d-4d15-b4e1-8ad010530f9d/neural_operation_bg_1774541723584.png';
} else {
    $bgImage = 'file:///C:/Users/djkyk/.gemini/antigravity/brain/807ab793-0c7d-4d15-b4e1-8ad010530f9d/auth_bg_1774540185692.png';
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Meta tags de cache (reforço) -->
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <link rel="icon" href="data:,">
  <title><?= e($title) ?></title>
  <script>if(window.console){const o=console.warn;console.warn=(...a)=>{if(a[0]&&String(a[0]).includes('cdn.tail'))return;o.apply(console,a)}}</script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <!-- TomSelect — multi-select premium (global) -->
  <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
  <style>
    /* TomSelect dark mode overrides */
    .dark .ts-wrapper .ts-control {
      background-color: rgb(30,41,59) !important;
      border-color: rgb(71,85,105) !important;
      color: rgb(241,245,249) !important;
    }
    .dark .ts-dropdown {
      background-color: rgb(30,41,59) !important;
      border-color: rgb(71,85,105) !important;
      color: rgb(241,245,249) !important;
    }
    .dark .ts-dropdown .option:hover,
    .dark .ts-dropdown .option.active {
      background-color: rgb(51,65,85) !important;
    }
    .dark .ts-wrapper.multi .ts-control .item {
      background-color: rgb(37,99,235) !important;
      color: #fff !important;
      border-color: transparent !important;
    }
  </style>
  <script>
    // Configurar tema inicial
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
  <link rel="stylesheet" href="/src/Support/modal-styles.css?v=<?= urlencode($assetVersion) ?>">
  <script src="/src/Support/modal-utils.js?v=<?= urlencode($assetVersion) ?>"></script>
  <script>
    // ===== TOGGLE SUBMENU - GLOBAL FUNCTION =====
    // Definir PRIMEIRO, antes de qualquer outra coisa
    window.toggleSubmenu = function(button) {
      // console.log('toggleSubmenu global chamada!', button);
      const submenu = button.parentElement.querySelector('.submenu');
      const arrow = button.querySelector('.submenu-arrow');
      if (submenu && arrow) {
        submenu.classList.toggle('hidden');
        arrow.style.transform = submenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        // console.log('Submenu toggled - hidden:', submenu.classList.contains('hidden'));
      } else {
        // console.error('ERRO: Submenu ou arrow não encontrado!', {submenu, arrow, parent: button.parentElement});
      }
    }
    // console.log('[LAYOUT] toggleSubmenu definida:', typeof window.toggleSubmenu);
    
    // User permissions for frontend
    window.userPermissions = <?= json_encode($_SESSION['user_permissions'] ?? []) ?>;
  </script>
  <script>
    // Tailwind config with dark theme
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81'
            },
          }
        }
      }
    }
  </script>
  <style>
    /* Page transition styles */
    .page-transition {
      opacity: 0;
      transform: translateY(20px);
      transition: all 0.3s ease-in-out;
    }
    .page-transition.loaded {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Smooth scrolling */
    html {
      scroll-behavior: smooth;
    }
    
    /* Loading overlay removido - causava problemas globais */
  </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 transition-colors duration-300 min-h-screen">
  <!-- Fundo Dinâmico IA -->
  <div class="fixed inset-0 z-0 opacity-[0.03] dark:opacity-[0.06] pointer-events-none transition-all duration-700" 
       style="background-image: url('<?= $bgImage ?>'); background-size: cover; background-position: center; background-attachment: fixed;"></div>

  <div class="flex h-screen bg-transparent relative z-10 transition-colors duration-300">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header/Navbar com Breadcrumb -->
      <header class="bg-white dark:bg-slate-800 shadow-sm border-b border-gray-200 dark:border-slate-700/50 transition-colors duration-300">
        <div class="flex items-center justify-between px-6 py-3">

          <!-- Breadcrumb -->
          <?php
            $routeMap = [
              'inicio'                    => ['label' => 'Início',                   'icon' => 'ph-house'],
              'dashboard'                 => ['label' => 'Dashboard',                'icon' => 'ph-chart-bar'],
              'dashboard-2'               => ['label' => 'Dashboard 2.0',            'icon' => 'ph-compass'],
              'toners'                    => ['label' => 'Toners',                   'icon' => 'ph-drop'],
              'cadastro'                  => ['label' => 'Cadastro de Toners',       'icon' => 'ph-drop'],
              'triagem-toners'            => ['label' => 'Triagem de Toners',        'icon' => 'ph-magnifying-glass'],
              'cadastro-maquinas'         => ['label' => 'Cadastro de Máquinas',     'icon' => 'ph-printer'],
              'cadastro-pecas'            => ['label' => 'Cadastro de Peças',        'icon' => 'ph-wrench'],
              'cadastro-defeitos'         => ['label' => 'Cadastro de Defeitos',     'icon' => 'ph-puzzle-piece'],
              'controle-descartes'        => ['label' => 'Controle de Descartes',    'icon' => 'ph-recycle'],
              'precificacao-coleta-descartes' => ['label' => 'Precificação de Coleta', 'icon' => 'ph-currency-dollar'],
              'amostragens-2'             => ['label' => 'Amostragens 2.0',          'icon' => 'ph-flask'],
              'homologacoes'              => ['label' => 'Homologações',             'icon' => 'ph-traffic-cone'],
              'certificados'              => ['label' => 'Certificados',             'icon' => 'ph-scroll'],
              'fmea'                      => ['label' => 'FMEA',                     'icon' => 'ph-trend-up'],
              'pops-e-its'                => ['label' => 'POPs e ITs',              'icon' => 'ph-books'],
              'fluxogramas'               => ['label' => 'Fluxogramas',              'icon' => 'ph-git-merge'],
              'auditorias'                => ['label' => 'Auditorias',              'icon' => 'ph-magnifying-glass'],
              'nao-conformidades'         => ['label' => 'Não Conformidades',        'icon' => 'ph-warning'],
              'melhoria-continua-2'       => ['label' => 'Melhoria Contínua',        'icon' => 'ph-rocket-launch'],
              'controle-de-rc'            => ['label' => 'Controle de RC',           'icon' => 'ph-folders'],
              'garantias'                 => ['label' => 'Garantias',               'icon' => 'ph-shield-check'],
              'nps'                       => ['label' => 'Formulários Online',       'icon' => 'ph-chart-bar'],
              'toners/defeitos'           => ['label' => 'Toners com Defeito',       'icon' => 'ph-warning'],
              'atendimento'               => ['label' => 'Atendimento',             'icon' => 'ph-phone'],
              'admin'                     => ['label' => 'Administrativo',           'icon' => 'ph-gear'],
              'registros'                 => ['label' => 'Registros',               'icon' => 'ph-folder'],
              'cadastros'                 => ['label' => 'Cadastros',               'icon' => 'ph-note-pencil'],
              'perfil'                    => ['label' => 'Meu Perfil',              'icon' => 'ph-user-circle'],
              'profile'                   => ['label' => 'Meu Perfil',              'icon' => 'ph-user-circle'],
              'usabilidade'               => ['label' => 'Usabilidade',             'icon' => 'ph-chart-bar'],
            ];

            $rawPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
            $segments = array_values(array_filter(explode('/', ltrim($rawPath, '/'))));

            // Tentar match com rota completa (2 segmentos) primeiro
            $fullKey = implode('/', array_slice($segments, 0, 2));
            $module  = $routeMap[$fullKey] ?? $routeMap[$segments[0] ?? ''] ?? null;
          ?>
          <div class="flex items-center gap-2 text-sm">
            <a href="/inicio" class="flex items-center gap-1.5 text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
              <i class="ph ph-house text-base"></i>
              <span class="hidden sm:inline">Início</span>
            </a>
            <?php if ($module): ?>
              <i class="ph ph-caret-right text-slate-300 dark:text-slate-600 text-xs"></i>
              <span class="flex items-center gap-1.5 text-slate-700 dark:text-slate-200 font-semibold">
                <i class="ph <?= e($module['icon']) ?> text-base text-blue-500 dark:text-blue-400"></i>
                <?= e($module['label']) ?>
              </span>
            <?php endif; ?>
          </div>

          <!-- Lado direito: usuário logado -->
          <div class="flex items-center gap-3">
            <a href="/profile" class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
              <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
              </div>
              <span class="hidden md:inline font-medium"><?= e(explode(' ', $_SESSION['user_name'] ?? 'Usuário')[0]) ?></span>
            </a>
          </div>

        </div>
      </header>
      
      <!-- Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-slate-900 p-6 transition-colors duration-300">
        <!-- Aviso de migração de email removido - Resend API ativo -->
        
        <?php if ($msg = flash('success')): ?>
          <div class="mb-4 rounded-md border border-green-200 bg-green-50 text-green-800 px-4 py-2 text-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
          <div class="mb-4 rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-2 text-sm"><?= e($msg) ?></div>
        <?php endif; ?>
        <div class="page-transition">
          <?php include $viewFile; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Container para modais globais -->
  <div id="global-modals-container"></div>

  <!-- ===== GLOBAL TOAST SYSTEM ===== -->
  <div id="global-toast-stack" class="fixed top-5 right-5 z-[99999] flex flex-col gap-2 pointer-events-none" style="max-width:380px;"></div>

  <!-- ===== GLOBAL CONFIRM MODAL ===== -->
  <div id="global-confirm-overlay" class="hidden fixed inset-0 z-[99998] flex items-center justify-center p-4" style="background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);">
    <div id="global-confirm-box" class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-slate-700 w-full max-w-sm p-6 text-center transform transition-all scale-95 opacity-0" style="transition:transform .2s ease,opacity .2s ease;">
      <div id="global-confirm-icon" class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 bg-red-100 dark:bg-red-900/30">
        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <h3 id="global-confirm-title" class="text-lg font-bold text-gray-900 dark:text-white mb-2">Confirmar ação</h3>
      <p id="global-confirm-msg" class="text-sm text-gray-500 dark:text-gray-400 mb-6">Tem certeza que deseja continuar?</p>
      <div class="flex gap-3 justify-center">
        <button id="global-confirm-cancel" class="flex-1 px-4 py-2.5 text-sm font-bold bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-slate-600 transition-all">
          Cancelar
        </button>
        <button id="global-confirm-ok" class="flex-1 px-4 py-2.5 text-sm font-bold bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all shadow-lg hover:shadow-red-500/20">
          Confirmar
        </button>
      </div>
    </div>
  </div>

  <style>
    /* Page transition */
    .page-transition { opacity: 0; transform: translateY(10px); transition: all 0.4s ease-out; }
    .page-transition.loaded { opacity: 1; transform: translateY(0); }
  </style>

  <?php include __DIR__ . '/../partials/ui-feedback.php'; ?>

  <script>
  <?php include __DIR__ . '/../partials/ui-scripts.php'; ?>

  <script>
    // Page transition
    document.addEventListener('DOMContentLoaded', function() {
      const pageContent = document.querySelector('.page-transition');
      if (pageContent) setTimeout(() => pageContent.classList.add('loaded'), 100);
    });
  </script>
  </script>
  
  <!-- Debug Panel (só se debug estiver ativo) -->
  <?php 
  $showDebug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true' || isset($_GET['debug']);
  if ($showDebug): 
      include __DIR__ . '/../partials/debug-panel.php'; 
  endif; 
  ?>
</body>
</html>
