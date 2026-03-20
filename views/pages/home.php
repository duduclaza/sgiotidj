<style>
  .typing-effect-home {
    border-right: 2px solid #9ca3af;
    animation: blink-home 0.7s step-end infinite;
    display: inline-block;
    min-height: 1.75rem;
  }
  @keyframes blink-home {
    from, to { border-color: transparent; }
    50% { border-color: #9ca3af; }
  }
</style>

<section class="space-y-8">
  <!-- Cabeçalho de Boas-vindas -->
  <div class="text-center">
    <div class="mb-6">
      <h1 class="text-6xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-emerald-600 dark:from-blue-400 dark:to-emerald-400 mb-4 tracking-tight">SGI <span class="text-gray-900 dark:text-white">OTI</span></h1>
      <p class="text-xl text-gray-600 dark:text-gray-300" id="typingTextHome"></p>
    </div>
    
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700 rounded-lg p-6 mb-8 border border-transparent dark:border-slate-600 shadow-sm">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Olá, <?= e($userName) ?>!</h2>
      <p class="text-gray-700 dark:text-gray-300">Perfil: <span class="font-medium text-blue-600 dark:text-blue-400"><?= e($userProfile) ?></span></p>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Utilize o menu lateral para navegar pelos módulos disponíveis para seu perfil.</p>
    </div>
  </div>


  <!-- Últimas Atualizações -->
  <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
      <div class="flex items-center">
        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center mr-3">
          <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Últimas Atualizações</h3>
      </div>
    </div>
    <div class="p-6">
      <div class="space-y-6">
        <?php foreach ($updates as $index => $update): ?>
        <div class="<?= $index > 0 ? 'border-t border-gray-100 dark:border-slate-700 pt-6' : '' ?>">
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $update['type'] === 'Correção Crítica' ? 'bg-red-100 dark:bg-red-900/30' : ($update['type'] === 'Correção' ? 'bg-yellow-100 dark:bg-yellow-900/30' : ($update['type'] === 'Ajuste' ? 'bg-purple-100 dark:bg-purple-900/30' : ($update['type'] === 'Investigação' ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-blue-100 dark:bg-blue-900/30'))) ?>">
                <?php if ($update['type'] === 'Correção Crítica'): ?>
                  <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                <?php elseif ($update['type'] === 'Correção'): ?>
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                <?php elseif ($update['type'] === 'Ajuste'): ?>
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                  </svg>
                <?php elseif ($update['type'] === 'Investigação'): ?>
                  <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                <?php else: ?>
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                <?php endif; ?>
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $update['type'] === 'Correção Crítica' ? 'bg-red-100 text-red-800 dark:text-red-300 dark:bg-red-900/40' : ($update['type'] === 'Correção' ? 'bg-yellow-100 text-yellow-800 dark:text-yellow-300 dark:bg-yellow-900/40' : ($update['type'] === 'Ajuste' ? 'bg-purple-100 text-purple-800 dark:text-purple-300 dark:bg-purple-900/40' : ($update['type'] === 'Investigação' ? 'bg-orange-100 text-orange-800 dark:text-orange-300 dark:bg-orange-900/40' : 'bg-blue-100 text-blue-800 dark:text-blue-300 dark:bg-blue-900/40'))) ?>">
                  <?= e($update['type']) ?>
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">v<?= e($update['version']) ?></span>
                <span class="text-sm text-gray-400 dark:text-slate-600">•</span>
                <span class="text-sm text-gray-500 dark:text-gray-400"><?= e($update['date']) ?></span>
              </div>
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1"><?= e($update['title']) ?></h4>
              <p class="text-sm text-gray-600 dark:text-gray-300 mb-3"><?= e($update['description']) ?></p>
              <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                <?php foreach ($update['items'] as $item): ?>
                <li class="flex items-start">
                  <svg class="w-4 h-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <?= e($item) ?>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<script>
  // Efeito de digitação na página inicial
  const textHome = 'Sistema de Gestão Integrada';
  const typingElementHome = document.getElementById('typingTextHome');
  let indexHome = 0;
  let isDeletingHome = false;
  
  function typeWriterHome() {
    if (!isDeletingHome && indexHome <= textHome.length) {
      typingElementHome.textContent = textHome.substring(0, indexHome);
      indexHome++;
      setTimeout(typeWriterHome, 100);
    } else if (isDeletingHome && indexHome >= 0) {
      typingElementHome.textContent = textHome.substring(0, indexHome);
      indexHome--;
      setTimeout(typeWriterHome, 50);
    } else if (indexHome > textHome.length) {
      setTimeout(() => {
        isDeletingHome = true;
        typeWriterHome();
      }, 2000);
    } else {
      isDeletingHome = false;
      indexHome = 0;
      setTimeout(typeWriterHome, 500);
    }
  }
  
  typeWriterHome();
</script>
