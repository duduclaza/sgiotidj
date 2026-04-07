<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold">Cadastro de Toners</h1>
    <button onclick="openImportModal()" class="px-4 py-2 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700 flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200 font-medium">
      <span>📊</span>
      Importar
    </button>
  </div>
  
  <!-- Formulário de Cadastro -->
  <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/50 rounded-xl p-6 shadow-sm transition-all">
    <div class="flex items-center gap-3 mb-6">
        <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Cadastrar Novo Toner</h2>
    </div>
    <form method="post" action="/toners/cadastro" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="formCadastroToner">
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Modelo *</label>
        <input type="text" name="modelo" placeholder="Ex: HP CF280A" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" required>
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peso Cheio (g) <span class="text-slate-400 font-normal lowercase">(opcional)</span></label>
        <input type="number" step="0.01" name="peso_cheio" placeholder="Ex: 850.50" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" oninput="calcularCampos()" onchange="calcularCampos()">
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peso Vazio (g) <span class="text-slate-400 font-normal lowercase">(opcional)</span></label>
        <input type="number" step="0.01" name="peso_vazio" placeholder="Ex: 120.30" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" oninput="calcularCampos()" onchange="calcularCampos()">
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">📊 Gramatura (g)</label>
        <input type="number" step="0.01" name="gramatura" value="" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 bg-slate-100 dark:bg-slate-900/50 text-green-600 dark:text-green-400 font-bold transition-all outline-none" readonly>
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Capacidade de Folhas *</label>
        <input type="number" name="capacidade_folhas" placeholder="Ex: 2700" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" required oninput="calcularCampos()" onchange="calcularCampos()">
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Preço do Toner (R$) *</label>
        <input type="number" step="0.01" name="preco_toner" placeholder="Ex: 89.90" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" required oninput="calcularCampos()" onchange="calcularCampos()">
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">📊 Gramatura por Folha (g)</label>
        <input type="number" step="0.0001" name="gramatura_por_folha" value="" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 bg-slate-100 dark:bg-slate-900/50 text-green-600 dark:text-green-400 font-bold transition-all outline-none" readonly>
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">📊 Custo por Folha (R$)</label>
        <input type="number" step="0.0001" name="custo_por_folha" value="" class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 bg-slate-100 dark:bg-slate-900/50 text-green-600 dark:text-green-400 font-bold transition-all outline-none" readonly>
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cor *</label>
        <select name="cor" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none appearance-none" required>
          <option value="">Selecione a cor</option>
          <option value="Yellow">Yellow</option>
          <option value="Magenta">Magenta</option>
          <option value="Cyan">Cyan</option>
          <option value="Black">Black</option>
        </select>
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo *</label>
        <select name="tipo" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none appearance-none" required>
          <option value="">Selecione o tipo</option>
          <option value="Original">Original</option>
          <option value="Compativel">Compatível</option>
          <option value="Remanufaturado">Remanufaturado</option>
        </select>
      </div>
      
      <div class="md:col-span-2 lg:col-span-3 pt-4 border-t border-slate-100 dark:border-slate-700/50 flex justify-end">
        <button type="submit" id="btnSalvarToner" class="px-8 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg shadow-blue-500/25 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Salvar Toner
        </button>
      </div>
    </form>
  </div>

  <!-- Lista/Grid -->
  <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/50 rounded-xl shadow-sm transition-all overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-col md:flex-row justify-between items-center gap-4">
      <div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Toners Cadastrados</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1" id="resultsCount">
          Carregando...
        </p>
      </div>
      <button onclick="exportToExcel(event)" class="px-4 py-2 text-sm rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 flex items-center gap-2 transition-all shadow-md shadow-emerald-500/10 active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <span>Exportar Excel</span>
      </button>
    </div>
    
    <!-- Campo de Busca -->
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/50 backdrop-blur-md">
      <div class="flex flex-col md:flex-row gap-4 items-center">
        <!-- Dropdown de Coluna -->
        <select 
          id="searchColumn" 
          class="w-full md:w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm text-slate-700 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all appearance-none"
        >
          <option value="all">Todas as colunas</option>
          <option value="0">Modelo</option>
          <option value="8">Cor</option>
          <option value="9">Tipo</option>
        </select>
        
        <!-- Campo de Busca -->
        <div class="relative flex-1 w-full max-w-lg">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input 
            type="text" 
            id="searchToners" 
            placeholder="Digite para buscar modelo, cor ou tipo..." 
            class="w-full pl-11 pr-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all"
          >
        </div>

        <div class="flex gap-2 w-full md:w-auto">
            <button type="button" id="searchActionBtn" onclick="window.searchToners && window.searchToners()"
                    class="flex-1 md:flex-none px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold shadow-lg shadow-blue-500/10 transition-all hover:-translate-y-0.5 active:translate-y-0">
              Buscar
            </button>
            
            <button type="button" id="clearSearchBtn" onclick="window.clearSearch && window.clearSearch()"
                    class="flex-1 md:flex-none px-6 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600 font-bold transition-all active:scale-95">
              Limpar
            </button>
        </div>
      </div>
    </div>
    <div class="overflow-x-auto ring-1 ring-slate-200 dark:ring-slate-700/50 rounded-xl mx-6 mb-6">
      <table class="min-w-full text-sm divide-y divide-slate-100 dark:divide-slate-700/50">
        <thead class="bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Modelo</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peso Cheio</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peso Vazio</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gramatura</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cap. Folhas</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Preço</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gram/Folha</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Custo/Folha</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cor</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
              <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Última Atualização</span>
              </div>
            </th>
            <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-slate-700 dark:text-slate-300" id="tonersTbody">
          <?php if (empty($toners)): ?>
            <tr>
              <td colspan="12" class="px-4 py-8 text-center text-gray-500">Nenhum toner cadastrado</td>
            </tr>
          <?php else: ?>
            <?php foreach ($toners as $t): ?>
              <?php 
                // Verificar se o cadastro está incompleto (sem peso_cheio ou peso_vazio)
                $cadastroIncompleto = empty($t['peso_cheio']) || empty($t['peso_vazio']);
                $rowClass = $cadastroIncompleto ? 'bg-red-50/50 dark:bg-red-900/10 border-l-4 border-l-red-500 dark:border-l-red-800' : 'hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors';
              ?>
              <tr class="<?= $rowClass ?> group" data-toner-id="<?= $t['id'] ?>" <?= $cadastroIncompleto ? 'title="Cadastro incompleto: Peso Cheio e Peso Vazio não preenchidos"' : '' ?>>
                <td class="px-3 py-2">
                  <span class="edit-display-modelo-<?= $t['id'] ?>"><?= e($t['modelo']) ?></span>
                  <input type="text" class="edit-input-modelo-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-full text-xs" value="<?= e($t['modelo']) ?>">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-peso_cheio-<?= $t['id'] ?>">
                    <?php if (empty($t['peso_cheio'])): ?>
                      <span class="text-red-600 dark:text-red-400 font-medium text-xs">⚠️ Não informado</span>
                    <?php else: ?>
                      <?= number_format($t['peso_cheio'], 2) ?>g
                    <?php endif; ?>
                  </span>
                  <input type="number" step="0.01" class="edit-input-peso_cheio-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['peso_cheio'] ?? '' ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-peso_vazio-<?= $t['id'] ?>">
                    <?php if (empty($t['peso_vazio'])): ?>
                      <span class="text-red-600 dark:text-red-400 font-medium text-xs">⚠️ Não informado</span>
                    <?php else: ?>
                      <?= number_format($t['peso_vazio'], 2) ?>g
                    <?php endif; ?>
                  </span>
                  <input type="number" step="0.01" class="edit-input-peso_vazio-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['peso_vazio'] ?? '' ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-gramatura-<?= $t['id'] ?>">
                    <?php 
                      $g_calc = null;
                      if (!empty($t['peso_cheio']) && !empty($t['peso_vazio'])) {
                        $g_calc = (float)$t['peso_cheio'] - (float)$t['peso_vazio'];
                      }
                      $g_value = $t['gramatura'] ?? null;
                      if ($g_value === null || $g_value === '' ) {
                        $g_value = $g_calc;
                      }
                    ?>
                    <?php if ($g_value === null || $g_value === '' ): ?>
                      <span class="text-gray-400 text-xs">-</span>
                    <?php else: ?>
                      <?= number_format((float)$g_value, 2) ?>g
                    <?php endif; ?>
                  </span>
                  <input type="number" step="0.01" class="edit-input-gramatura-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs bg-gray-100" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-capacidade_folhas-<?= $t['id'] ?>"><?= number_format($t['capacidade_folhas']) ?></span>
                  <input type="number" class="edit-input-capacidade_folhas-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['capacidade_folhas'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-preco_toner-<?= $t['id'] ?>">R$ <?= number_format($t['preco_toner'], 2, ',', '.') ?></span>
                  <input type="number" step="0.01" class="edit-input-preco_toner-<?= $t['id'] ?> border rounded px-2 py-1 hidden w-20 text-xs" value="<?= $t['preco_toner'] ?>" onchange="calcularEdicao(<?= $t['id'] ?>)">
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-gramatura_por_folha-<?= $t['id'] ?>">
                    <?php 
                      $gpf_value = $t['gramatura_por_folha'] ?? null;
                      if (($gpf_value === null || $gpf_value === '') && !empty($g_value) && !empty($t['capacidade_folhas'])) {
                        $cap = (int)$t['capacidade_folhas'];
                        if ($cap > 0) { $gpf_value = ((float)$g_value) / $cap; }
                      }
                    ?>
                    <?php if ($gpf_value === null || $gpf_value === '' ): ?>
                      <span class="text-gray-400 text-xs">-</span>
                    <?php else: ?>
                      <?= number_format((float)$gpf_value, 4) ?>g
                    <?php endif; ?>
                  </span>
                  <input type="number" step="0.0001" class="edit-input-gramatura_por_folha-<?= $t['id'] ?> w-20 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs hidden" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-custo_por_folha-<?= $t['id'] ?>">
                    <?php if (empty($t['custo_por_folha'])): ?>
                      <span class="text-gray-400 text-xs">-</span>
                    <?php else: ?>
                      R$ <?= number_format($t['custo_por_folha'], 4, ',', '.') ?>
                    <?php endif; ?>
                  </span>
                   <input type="number" step="0.0001" class="edit-input-custo_por_folha-<?= $t['id'] ?> w-20 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 text-xs hidden" readonly>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-cor-<?= $t['id'] ?>"><?= e($t['cor']) ?></span>
                  <select class="edit-input-cor-<?= $t['id'] ?> border rounded px-2 py-1 hidden text-xs">
                    <option value="Yellow" <?= $t['cor'] === 'Yellow' ? 'selected' : '' ?>>Yellow</option>
                    <option value="Magenta" <?= $t['cor'] === 'Magenta' ? 'selected' : '' ?>>Magenta</option>
                    <option value="Cyan" <?= $t['cor'] === 'Cyan' ? 'selected' : '' ?>>Cyan</option>
                    <option value="Black" <?= $t['cor'] === 'Black' ? 'selected' : '' ?>>Black</option>
                  </select>
                </td>
                <td class="px-3 py-2">
                  <span class="edit-display-tipo-<?= $t['id'] ?>"><?= e($t['tipo']) ?></span>
                  <select class="edit-input-tipo-<?= $t['id'] ?> border rounded px-2 py-1 hidden text-xs">
                    <option value="Original" <?= $t['tipo'] === 'Original' ? 'selected' : '' ?>>Original</option>
                    <option value="Compativel" <?= $t['tipo'] === 'Compativel' ? 'selected' : '' ?>>Compatível</option>
                    <option value="Remanufaturado" <?= $t['tipo'] === 'Remanufaturado' ? 'selected' : '' ?>>Remanufaturado</option>
                  </select>
                </td>
                <td class="px-3 py-2">
                  <?php 
                  $updatedTime = strtotime($t['updated_at']);
                  $timeDiff = time() - $updatedTime;
                  $isRecent = $timeDiff < 86400; // 24 horas
                  $textColor = $isRecent ? 'text-green-600' : 'text-gray-600';
                  $iconColor = $isRecent ? 'text-green-500' : 'text-gray-400';
                  
                  // Formato de tempo relativo
                  if ($timeDiff < 3600) { // Menos de 1 hora
                    $timeAgo = 'há ' . floor($timeDiff / 60) . ' min';
                  } elseif ($timeDiff < 86400) { // Menos de 24 horas
                    $timeAgo = 'há ' . floor($timeDiff / 3600) . 'h';
                  } elseif ($timeDiff < 2592000) { // Menos de 30 dias
                    $timeAgo = 'há ' . floor($timeDiff / 86400) . ' dias';
                  } else {
                    $timeAgo = date('d/m/Y', $updatedTime);
                  }
                  ?>
                  <div class="flex items-center space-x-1">
                    <svg class="w-3 h-3 <?= $iconColor ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex flex-col">
                      <span class="text-xs <?= $textColor ?>" title="Última atualização: <?= date('d/m/Y H:i:s', $updatedTime) ?><?= $isRecent ? ' (Recente)' : '' ?>">
                        <?= date('d/m/Y H:i', $updatedTime) ?>
                      </span>
                      <span class="text-xs text-gray-400 italic">
                        <?= $timeAgo ?>
                        <?php if ($isRecent): ?>
                          <span class="inline-block w-1.5 h-1.5 bg-green-400 rounded-full ml-1" title="Atualizado nas últimas 24 horas"></span>
                        <?php endif; ?>
                      </span>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                  <!-- Botões de Exibição -->
                  <div class="flex justify-end gap-2 edit-display-actions-<?= $t['id'] ?>">
                    <button onclick="editToner(<?= $t['id'] ?>)" 
                            class="edit-btn-<?= $t['id'] ?> p-2 text-slate-400 hover:text-blue-600 dark:text-slate-500 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/40 rounded-xl transition-all"
                            title="Editar">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>
                    <button onclick="deleteToner(<?= $t['id'] ?>)" 
                            class="p-2 text-slate-400 hover:text-red-600 dark:text-slate-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-xl transition-all"
                            title="Excluir">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                  </div>
                  
                  <!-- Botões de Edição -->
                  <div class="flex justify-end gap-2 edit-input-actions-<?= $t['id'] ?> hidden">
                    <button onclick="saveToner(<?= $t['id'] ?>)" 
                            class="save-btn-<?= $t['id'] ?> p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 rounded-xl transition-all"
                            title="Salvar">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                    <button onclick="cancelEditToner(<?= $t['id'] ?>)" 
                            class="cancel-btn-<?= $t['id'] ?> p-2 text-slate-400 hover:text-red-600 dark:text-slate-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-xl transition-all"
                            title="Cancelar">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Controles de Paginação -->
    <div id="tonersPagination" class="px-6 py-4 border-t border-slate-100 dark:border-slate-700/50 flex flex-col md:flex-row items-center justify-between gap-4">
      <!-- Seletor de registros por página -->
      <div class="flex items-center gap-2 hidden md:flex">
        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Mostrar:</label>
        <select onchange="window.changeItemsPerPage(this.value)" id="itemsPerPageSelect" class="border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-colors cursor-pointer outline-none">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50" selected>50</option>
          <option value="100">100</option>
        </select>
        <span class="text-sm text-slate-600 dark:text-slate-400 font-medium">por página</span>
      </div>

      <!-- Informação de registros -->
      <div class="text-sm text-slate-700 dark:text-slate-300" id="paginationInfo">
        Mostrando <span class="font-semibold text-slate-900 dark:text-white">1</span> até <span class="font-semibold text-slate-900 dark:text-white">50</span> de <span class="font-semibold text-slate-900 dark:text-white">...</span> registros
      </div>

      <!-- Navegação de páginas -->
      <div class="flex items-center gap-1" id="paginationControls">
        <!-- Botões injetados via JS -->
      </div>
    </div>
  </div>

  <!-- Import Modal -->
  <div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center p-4" style="z-index: 999999; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;">
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md transition-colors" onclick="event.stopPropagation()">
      <!-- Header -->
      <div class="px-6 py-5 border-b border-gray-200 dark:border-slate-700 bg-gradient-to-r from-gray-50 to-white dark:from-slate-900 dark:to-slate-800 rounded-t-lg transition-colors">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">📊 Importar Toners</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Faça upload de um arquivo Excel ou CSV com os dados dos toners</p>
            </div>
          </div>
          <!-- Close Button -->
          <button onclick="console.log('X clicado!'); event.stopPropagation(); closeImportModal();" class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-full flex items-center justify-center transition-colors duration-200 group">
            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Content -->
      <div class="px-6 py-4 space-y-4">
        <!-- File Input -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            📁 Selecione o arquivo Excel ou CSV:
          </label>
          <div class="relative group">
            <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" 
                   class="w-full border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-xl px-4 py-4 text-sm focus:ring-3 focus:ring-blue-200 focus:border-blue-400 hover:border-gray-400 dark:hover:border-slate-500 transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50 bg-white dark:bg-slate-900 text-gray-900 dark:text-white">
            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
              <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
              </svg>
            </div>
          </div>
          <div class="flex items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Formatos aceitos: <span class="font-medium">.xlsx, .xls, .csv</span> • Tamanho máximo: <span class="font-medium">10MB</span>
          </div>
        </div>
        
        <!-- Progress Bar (hidden by default) -->
        <div id="progressContainer" class="hidden">
          <div class="bg-gradient-to-r from-blue-50 to-green-50 dark:from-blue-900/20 dark:to-green-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-4 transition-colors">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 dark:border-blue-400 mr-2"></div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">⚡ Progresso da Importação</span>
              </div>
              <span id="progressText" class="text-sm font-bold text-blue-600 dark:text-blue-400">0%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-4 shadow-inner">
              <div id="progressBar" class="bg-gradient-to-r from-blue-500 via-blue-600 to-green-500 h-4 rounded-full transition-all duration-500 ease-out shadow-sm" style="width: 0%"></div>
            </div>
            <div id="importStatus" class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 rounded-lg p-3 mt-3 border border-gray-200 dark:border-slate-700 shadow-sm">
              Preparando importação...
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer -->
      <div class="px-6 py-4 bg-gray-50 dark:bg-slate-900/50 border-t border-gray-200 dark:border-slate-700 rounded-b-lg transition-colors">
        <!-- Template Download -->
        <div class="mb-3">
          <button onclick="downloadTemplate()" 
                  class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-400 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:from-blue-100 hover:to-blue-200 dark:hover:from-blue-800/50 dark:hover:to-blue-700/50 hover:border-blue-300 dark:hover:border-blue-700 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-900 focus:ring-opacity-50 transition-all duration-200 shadow-sm hover:shadow">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            📥 Baixar Template
          </button>
        </div>
        
        <!-- Import Button -->
        <div>
          <button id="importBtn" onclick="importExcel()" 
                  class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 border border-green-500 rounded-lg hover:from-green-600 hover:to-green-700 hover:border-green-600 focus:ring-2 focus:ring-green-200 focus:ring-opacity-50 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-md">
            <span class="flex items-center justify-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              📤 Importar Dados
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Script de Ações de Toners -->
  <script src="/js/toners-actions.js"></script>
  
  <!-- Script de Busca Inteligente -->
  <script src="/js/toners-search.js"></script>
  
  <!-- Script para inicialização -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
  // Debounce helper
  function debounce(fn, delay = 200) {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(null, args), delay);
    };
  }
    // Inicializar tooltips/popovers apenas se Bootstrap estiver disponível
    if (window.bootstrap) {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-tooltip]'));
      tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
      });

      const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
      popoverTriggerList.map(function(el) {
        return new bootstrap.Popover(el);
      });
    }
  });

  // Calculos automaticos no formulario
  window.calcularCampos = function() {
  try {
    const toNum = (v) => {
      const s = (v ?? '').toString().replace(',', '.');
      const n = parseFloat(s);
      return isNaN(n) ? 0 : n;
    };
    const pesoCheio = toNum(document.querySelector('input[name="peso_cheio"]').value);
    const pesoVazio = toNum(document.querySelector('input[name="peso_vazio"]').value);
    const capacidade = parseInt((document.querySelector('input[name="capacidade_folhas"]').value || '0').replace(/\D/g, '')) || 0;
    const preco = toNum(document.querySelector('input[name="preco_toner"]').value);
    
    const gramaturaInput = document.querySelector('input[name="gramatura"]');
    const gramaturaFolhaInput = document.querySelector('input[name="gramatura_por_folha"]');
    const custoFolhaInput = document.querySelector('input[name="custo_por_folha"]');
    
    // Calcular gramatura
    if (pesoCheio > 0 && pesoVazio > 0) {
      const gramatura = pesoCheio - pesoVazio;
      gramaturaInput.value = gramatura.toFixed(2);
      
      // Calcular gramatura por folha
      if (capacidade > 0) {
        const gramaturaFolha = gramatura / capacidade;
        gramaturaFolhaInput.value = gramaturaFolha.toFixed(6);
      } else {
        gramaturaFolhaInput.value = '';
      }
    } else {
      gramaturaInput.value = '';
      gramaturaFolhaInput.value = '';
    }
    
    // Calcular custo por folha
    if (preco > 0 && capacidade > 0) {
      const custoFolha = preco / capacidade;
      custoFolhaInput.value = custoFolha.toFixed(6);
    } else {
      custoFolhaInput.value = '';
    }
  } catch (e) {
    // Ignorar erros silenciosamente
  }
}

// Cálculos na edição
function calcularEdicao(id) {
  const toNum = (v) => {
    const s = (v ?? '').toString().replace(',', '.');
    const n = parseFloat(s);
    return isNaN(n) ? 0 : n;
  };
  const pesocheio = toNum(document.querySelector('.edit-input-peso_cheio-' + id).value);
  const pesovazio = toNum(document.querySelector('.edit-input-peso_vazio-' + id).value);
  const capacidade = parseInt((document.querySelector('.edit-input-capacidade_folhas-' + id).value || '0').replace(/\D/g, '')) || 0;
  const preco = toNum(document.querySelector('.edit-input-preco_toner-' + id).value);
  
  const gramatura = pesocheio - pesovazio;
  document.querySelector('.edit-input-gramatura-' + id).value = gramatura.toFixed(2);
  
  if (capacidade > 0) {
    const gramaturaFolha = gramatura / capacidade;
    document.querySelector('.edit-input-gramatura_por_folha-' + id).value = gramaturaFolha.toFixed(4);
    const custoFolha = preco / capacidade;
    document.querySelector('.edit-input-custo_por_folha-' + id).value = custoFolha.toFixed(4);
  }
}

// Edição inline
function editTonerInline(id) {
  const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
  fields.forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.add('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.remove('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.add('hidden');
  document.querySelector('.save-btn-' + id).classList.remove('hidden');
  document.querySelector('.cancel-btn-' + id).classList.remove('hidden');
}

function cancelEditTonerInline(id) {
  const fields = ['modelo', 'peso_cheio', 'peso_vazio', 'gramatura', 'capacidade_folhas', 'preco_toner', 'gramatura_por_folha', 'custo_por_folha', 'cor', 'tipo'];
  fields.forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.remove('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.add('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.remove('hidden');
  document.querySelector('.save-btn-' + id).classList.add('hidden');
  document.querySelector('.cancel-btn-' + id).classList.add('hidden');
}

function saveTonerInline(id) {
  const modelo = document.querySelector('.edit-input-modelo-' + id).value.trim();
  const pesocheio = document.querySelector('.edit-input-peso_cheio-' + id).value;
  const pesovazio = document.querySelector('.edit-input-peso_vazio-' + id).value;
  const capacidade = document.querySelector('.edit-input-capacidade_folhas-' + id).value;
  const preco = document.querySelector('.edit-input-preco_toner-' + id).value;
  const cor = document.querySelector('.edit-input-cor-' + id).value;
  const tipo = document.querySelector('.edit-input-tipo-' + id).value;
  
  if (!modelo || !pesocheio || !pesovazio || !capacidade || !preco || !cor || !tipo) {
    alert('Todos os campos são obrigatórios');
    return;
  }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/toners/update';
  form.innerHTML = `
    <input type="hidden" name="id" value="${id}">
    <input type="hidden" name="modelo" value="${modelo}">
    <input type="hidden" name="peso_cheio" value="${pesocheio}">
    <input type="hidden" name="peso_vazio" value="${pesovazio}">
    <input type="hidden" name="capacidade_folhas" value="${capacidade}">
    <input type="hidden" name="preco_toner" value="${preco}">
    <input type="hidden" name="cor" value="${cor}">
    <input type="hidden" name="tipo" value="${tipo}">
  `;
  document.body.appendChild(form);
  form.submit();
}

function deleteTonerInline(id) {
  if (!confirm('Tem certeza que deseja excluir este toner?')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/toners/delete';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
  document.body.appendChild(form);
  form.submit();
}

// Função de teste
function testModal() {
  alert('Teste de JavaScript funcionando!');
  console.log('Teste de JavaScript funcionando!');
  
  // Criar um modal de teste simples
  const testDiv = document.createElement('div');
  testDiv.innerHTML = `
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center;">
      <div style="background: white; padding: 20px; border-radius: 8px; max-width: 400px;">
        <h3>Modal de Teste</h3>
        <p>Se você está vendo isso, o JavaScript está funcionando!</p>
        <button onclick="this.closest('div').parentElement.remove()" style="background: #dc2626; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Fechar</button>
      </div>
    </div>
  `;
  document.body.appendChild(testDiv);
}

// Função para forçar modal manualmente (use no Console se necessário)
function forceShowModal() {
  const modal = document.getElementById('importModal');
  if (modal) {
    // Remover todas as classes
    modal.className = '';
    
    // Aplicar estilos brutalmente
    modal.setAttribute('style', `
      display: flex !important;
      position: fixed !important;
      top: 0px !important;
      left: 0px !important;
      width: 100vw !important;
      height: 100vh !important;
      z-index: 999999 !important;
      background: rgba(255, 0, 0, 0.9) !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 16px !important;
      visibility: visible !important;
      opacity: 1 !important;
    `);
    
    console.log('Modal forçado a aparecer com fundo vermelho!');
    return true;
  }
  return false;
}

// Modal functions
function openImportModal() {
  let modal = document.getElementById('importModal');
  
  // Se o modal existe, mover para o body principal para aparecer por cima de tudo
  if (modal) {
    // Mover modal para o body principal (fora do iframe)
    if (modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
    // Remover hidden e deixar que as classes Tailwind cuidem do resto
    modal.classList.remove('hidden');
    modal.classList.add('flex'); // Garantir que seja flex para o centralizamento
    
    // Garantir que o modal apareça por cima de tudo e o fundo seja escuro
    document.body.style.overflow = 'hidden'; // Impede scroll da página
    
    // Garantir que o conteúdo interno seja visível (já deve estar pelo HTML que editamos)
    const modalContent = modal.querySelector('.bg-white');
    if (modalContent) {
      // Remover estilos inline agressivos que impedem dark mode
      modalContent.style.cssText = '';
    }
    
    // Adicionar evento de clique no overlay para fechar
    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeImportModal();
      }
    });
    
  } else {
    // Fallback: criar modal dinamicamente no body principal
    createFullScreenModal();
  }
}

// Função para criar modal em tela cheia
function createFullScreenModal() {
  // Remover modal existente se houver
  const existingModal = document.getElementById('fullScreenImportModal');
  if (existingModal) {
    existingModal.remove();
  }
  
  // Bloquear scroll da página
  document.body.style.overflow = 'hidden';
  
  const modalHTML = `
    <div id="fullScreenImportModal" class="fixed inset-0 bg-black bg-opacity-90 z-[999999] flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-md transition-colors" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200 dark:border-slate-700 bg-gradient-to-r from-gray-50 to-white dark:from-slate-900 dark:to-slate-800 rounded-t-xl flex items-center justify-between transition-colors">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-0">📊 Importar Toners</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 mb-0">Faça upload de um arquivo Excel ou CSV com os dados dos toners</p>
            </div>
          </div>
          <button onclick="closeFullScreenModal()" class="w-8 h-8 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-full flex items-center justify-center cursor-pointer transition-colors border-none group">
            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <!-- Content -->
        <div class="p-6 flex flex-col gap-4">
          <!-- File Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
              📁 Selecione o arquivo Excel ou CSV:
            </label>
            <input type="file" id="fullScreenFileInput" accept=".xlsx,.xls,.csv" 
                   class="w-full border-2 border-dashed border-gray-300 dark:border-slate-600 rounded-xl p-4 text-sm focus:ring-3 focus:ring-blue-200 focus:border-blue-400 bg-white dark:bg-slate-900 text-gray-900 dark:text-white transition-all duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100 dark:hover:file:bg-blue-900/50">
            <div class="flex items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Formatos aceitos: <span class="font-medium">.xlsx, .xls, .csv</span> • Tamanho máximo: <span class="font-medium">10MB</span>
            </div>
          </div>
          
          <!-- Progress Container -->
          <div id="fullScreenProgressContainer" class="hidden">
            <div class="bg-gradient-to-r from-blue-50 to-green-50 dark:from-blue-900/20 dark:to-green-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 transition-colors">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                  <div class="w-5 h-5 border-2 border-blue-600 dark:border-blue-400 border-t-transparent rounded-full animate-spin mr-2"></div>
                  <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">⚡ Progresso da Importação</span>
                </div>
                <span id="fullScreenProgressText" class="text-sm font-bold text-blue-600 dark:text-blue-400">0%</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-4 shadow-inner">
                <div id="fullScreenProgressBar" class="bg-gradient-to-r from-blue-500 to-green-500 h-4 rounded-full transition-all duration-500 w-0"></div>
              </div>
              <div id="fullScreenImportStatus" class="text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-900 rounded-lg p-3 mt-3 border border-gray-200 dark:border-slate-700">
                Preparando importação...
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 pb-6 pt-4 bg-gray-50 dark:bg-slate-900/50 border-t border-gray-200 dark:border-slate-700 rounded-b-xl transition-colors">
          <!-- Template Download -->
          <div class="mb-3">
            <button onclick="downloadTemplate()" 
                    class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-blue-700 dark:text-blue-400 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 border border-blue-200 dark:border-blue-800 rounded-lg cursor-pointer transition-colors shadow-sm border-none">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              📥 Baixar Template
            </button>
          </div>
          
          <!-- Import Button -->
          <div>
            <button onclick="importFullScreenExcel()" 
                    class="w-full py-3 px-4 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-green-600 border-none rounded-lg cursor-pointer transition-all shadow-md hover:shadow-lg flex items-center justify-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              📤 Importar Dados
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <style>
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    </style>
  `;
  
  // Adicionar ao body principal
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Adicionar event listener para fechar ao clicar no overlay
  document.getElementById('fullScreenImportModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeFullScreenModal();
    }
  });
}

// Funções para o modal em tela cheia
function closeFullScreenModal() {
  document.body.style.overflow = '';
  const modal = document.getElementById('fullScreenImportModal');
  if (modal) {
    modal.remove();
  }
}

function importFullScreenExcel() {
  const fileInput = document.getElementById('fullScreenFileInput');
  const file = fileInput.files[0];
  
    showToast('error', 'Arquivo não selecionado', 'Por favor, selecione um arquivo Excel.');
  
  // Mostrar progress
  document.getElementById('fullScreenProgressContainer').style.display = 'block';
  
  const formData = new FormData();
  formData.append('excel_file', file);
  
  updateFullScreenProgress(10, 'Enviando arquivo...');
  
  fetch('/toners/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      updateFullScreenProgress(100, `Concluído! ${result.imported} registros importados`);
      setTimeout(() => {
        closeFullScreenModal();
        alert('Importação concluída com sucesso!');
        location.reload();
      }, 2000);
    } else {
      alert('Erro na importação: ' + result.message);
      document.getElementById('fullScreenProgressContainer').style.display = 'none';
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
    document.getElementById('fullScreenProgressContainer').style.display = 'none';
  });
}

function updateFullScreenProgress(percentage, status) {
  document.getElementById('fullScreenProgressBar').style.width = percentage + '%';
  document.getElementById('fullScreenProgressText').textContent = percentage + '%';
  document.getElementById('fullScreenImportStatus').textContent = status;
}

// Função para criar modal dinamicamente
function createDynamicModal() {
  // Remover modal existente se houver
  const existingModal = document.getElementById('dynamicImportModal');
  if (existingModal) {
    existingModal.remove();
  }
  
  const modalHTML = `
    <div id="dynamicImportModal" class="fixed inset-0 bg-black bg-opacity-80 z-[99999] flex items-center justify-center p-4 transition-all">
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-md transition-colors" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-gray-200 dark:border-slate-700 bg-gradient-to-r from-gray-50 to-white dark:from-slate-900 dark:to-slate-800 rounded-t-xl transition-colors">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-0">Importar Toners</h3>
        </div>
        
        <!-- Content -->
        <div class="p-6 flex flex-col gap-4">
          <!-- File Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              Selecione o arquivo Excel:
            </label>
            <input type="file" id="dynamicExcelFileInput" accept=".xlsx,.xls,.csv" 
                   class="w-full border border-gray-300 dark:border-slate-600 rounded-lg p-3 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 mb-0">Formatos aceitos: .xlsx, .xls, .csv</p>
          </div>
          
          <!-- Progress Container -->
          <div id="dynamicProgressContainer" class="hidden">
            <div class="mb-3">
              <div class="flex justify-between text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                <span>Progresso da Importação</span>
                <span id="dynamicProgressText">0%</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-3">
                <div id="dynamicProgressBar" class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500 w-0"></div>
              </div>
            </div>
            <div id="dynamicImportStatus" class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-slate-900 rounded-lg p-3 border border-gray-100 dark:border-slate-700 italic"></div>
          </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 pb-6 pt-4 bg-gray-50 dark:bg-slate-900/50 border-t border-gray-200 dark:border-slate-700 rounded-b-xl transition-colors">
          <!-- Template Download -->
          <div class="mb-3">
            <button onclick="downloadTemplate()" 
                    class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-lg cursor-pointer transition-colors shadow-sm border-none">
              📥 Baixar Template Excel
            </button>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button onclick="closeDynamicModal()" 
                    class="flex-1 px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-slate-700">
              Cancelar
            </button>
            <button onclick="importDynamicExcel()" 
                    class="flex-1 px-4 py-3 text-sm font-semibold text-white bg-green-600 border-none rounded-lg cursor-pointer transition-colors hover:bg-green-700 shadow-md">
              📤 Importar Dados
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Adicionar event listener para fechar ao clicar no overlay
  document.getElementById('dynamicImportModal').addEventListener('click', closeDynamicModal);
}

// Funções para o modal dinâmico
function closeDynamicModal() {
  const modal = document.getElementById('dynamicImportModal');
  if (modal) {
    modal.remove();
  }
}

function importDynamicExcel() {
  const fileInput = document.getElementById('dynamicExcelFileInput');
  const file = fileInput.files[0];
  
    showToast('error', 'Arquivo não selecionado', 'Por favor, selecione um arquivo Excel.');
  
  // Usar a mesma lógica de importação, mas com IDs dinâmicos
  document.getElementById('dynamicProgressContainer').style.display = 'block';
  
  const formData = new FormData();
  formData.append('excel_file', file);
  
  updateDynamicProgress(10, 'Enviando arquivo...');
  
  fetch('/toners/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      updateDynamicProgress(100, `Concluído! ${result.imported} registros importados`);
      setTimeout(() => {
        closeDynamicModal();
        alert('Importação concluída com sucesso!');
        location.reload();
      }, 2000);
    } else {
      alert('Erro na importação: ' + result.message);
      document.getElementById('dynamicProgressContainer').style.display = 'none';
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
    document.getElementById('dynamicProgressContainer').style.display = 'none';
  });
}

function updateDynamicProgress(percentage, status) {
  document.getElementById('dynamicProgressBar').style.width = percentage + '%';
  document.getElementById('dynamicProgressText').textContent = percentage + '%';
  document.getElementById('dynamicImportStatus').textContent = status;
}

function closeImportModal() {
  console.log('🔥 FORÇANDO FECHAMENTO DO MODAL...');
  
  // Restaurar scroll da página
  document.body.style.overflow = '';
  
  // Buscar e fechar TODOS os modals de importação
  const modalIds = ['importModal', 'dynamicImportModal', 'fullScreenImportModal'];
  
  modalIds.forEach(id => {
    const modal = document.getElementById(id);
    if (modal) {
      // Forçar fechamento brutal
      modal.remove(); // Remove completamente
      console.log('✅ Modal removido:', id);
    }
  });
  
  // Buscar por qualquer elemento com z-index alto (provavelmente modal)
  const allElements = document.querySelectorAll('*');
  allElements.forEach(el => {
    const zIndex = parseInt(window.getComputedStyle(el).zIndex);
    if (zIndex > 99999 && el.style.position === 'fixed') {
      el.remove();
      console.log('✅ Elemento de z-index alto removido');
    }
  });
  
  console.log(' Fechamento concluído!');
}

function downloadTemplate() {
  console.log('📥 Baixando template CSV simples...');
  
  // Download direto do backend (template CSV simples)
  window.location.href = '/toners/template';
  return;
}
  
  // Código antigo comentado (caso queira voltar ao Excel complexo)
  /*
  const data = [
    ['TEMPLATE DE IMPORTAÇÃO DE TONERS - SGQ OTI DJ'],
    [],
    [' INSTRUÇÕES DE PREENCHIMENTO:'],
    ['1. Preencha os dados a partir da linha 9 (abaixo dos cabeçalhos)'],
    ['2. CAMPOS OBRIGATÓRIOS: Modelo, Capacidade Folhas, Preço, Cor, Tipo'],
    ['3. CAMPOS OPCIONAIS: Peso Cheio e Peso Vazio (podem ficar em branco)'],
    ['4. Use ponto (.) para separar decimais, exemplo: 89.90'],
    ['5. Valores de Cor: Yellow, Magenta, Cyan ou Black'],
    ['6. Valores de Tipo: Original, Compativel ou Remanufaturado'],
    [],
    ['Modelo *', 'Peso Cheio (g)', 'Peso Vazio (g)', 'Capacidade Folhas *', 'Preço Toner (R$) *', 'Cor *', 'Tipo *'],
    ['HP CF280A', '850.50', '120.30', '2700', '89.90', 'Black', 'Original'],
    ['Canon 045', '', '', '1300', '75.50', 'Yellow', 'Compativel'],
    ['Brother TN-421', '680.90', '105.10', '1800', '65.00', 'Magenta', 'Remanufaturado'],
    ['Samsung MLT-D104S', '720.00', '98.50', '1500', '55.00', 'Black', 'Original'],
    ['Xerox 106R02773', '', '', '1000', '45.90', 'Cyan', 'Compativel']
  ];
  
  // Criar workbook
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(data);
  
  // Definir larguras das colunas
  ws['!cols'] = [
    {wch: 25}, // Modelo
    {wch: 16}, // Peso Cheio
    {wch: 16}, // Peso Vazio
    {wch: 20}, // Capacidade
    {wch: 20}, // Preço
    {wch: 15}, // Cor
    {wch: 20}  // Tipo
  ];
  
  // Mesclar células do título
  ws['!merges'] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } } // Mesclar título
  ];
  
  // Estilizar célula do título (A1)
  if (!ws['A1'].s) ws['A1'].s = {};
  ws['A1'].s = {
    font: { bold: true, sz: 14, color: { rgb: "FFFFFF" } },
    fill: { fgColor: { rgb: "1E40AF" } },
    alignment: { horizontal: "center", vertical: "center" }
  };
  
  // Estilizar instruções (linhas 3-8)
  for (let row = 2; row <= 7; row++) {
    const cellRef = XLSX.utils.encode_cell({ r: row, c: 0 });
    if (!ws[cellRef]) ws[cellRef] = { v: '', t: 's' };
    ws[cellRef].s = {
      font: { italic: true, sz: 10, color: { rgb: "374151" } },
      fill: { fgColor: { rgb: "F3F4F6" } },
      alignment: { horizontal: "left", vertical: "center" }
    };
  }
  
  // Estilizar cabeçalhos (linha 9)
  for (let col = 0; col < 7; col++) {
    const cellRef = XLSX.utils.encode_cell({ r: 9, c: col });
    if (!ws[cellRef]) ws[cellRef] = { v: '', t: 's' };
    ws[cellRef].s = {
      font: { bold: true, sz: 11, color: { rgb: "FFFFFF" } },
      fill: { fgColor: { rgb: "10B981" } },
      alignment: { horizontal: "center", vertical: "center" },
      border: {
        top: { style: 'thin', color: { rgb: "000000" } },
        bottom: { style: 'thin', color: { rgb: "000000" } },
        left: { style: 'thin', color: { rgb: "000000" } },
        right: { style: 'thin', color: { rgb: "000000" } }
      }
    };
  }
  
  // Estilizar linhas de exemplo (linhas 10-14)
  for (let row = 10; row <= 14; row++) {
    for (let col = 0; col < 7; col++) {
      const cellRef = XLSX.utils.encode_cell({ r: row, c: col });
      if (!ws[cellRef]) continue;
      ws[cellRef].s = {
        alignment: { horizontal: "left", vertical: "center" },
        border: {
          top: { style: 'thin', color: { rgb: "E5E7EB" } },
          bottom: { style: 'thin', color: { rgb: "E5E7EB" } },
          left: { style: 'thin', color: { rgb: "E5E7EB" } },
          right: { style: 'thin', color: { rgb: "E5E7EB" } }
        }
      };
    }
  }
  
  // Adicionar comentários nas células de cabeçalho
  ws['A10'].c = [{ a: "SGQPRO", t: "Campo obrigatório" }];
  ws['D10'].c = [{ a: "SGQPRO", t: "Campo obrigatório" }];
  ws['E10'].c = [{ a: "SGQPRO", t: "Campo obrigatório" }];
  ws['F10'].c = [{ a: "SGQPRO", t: "Campo obrigatório - Valores: Yellow, Magenta, Cyan, Black" }];
  ws['G10'].c = [{ a: "SGQPRO", t: "Campo obrigatório - Valores: Original, Compativel, Remanufaturado" }];
  
  // Congelar painéis (primeira linha de dados)
  ws['!freeze'] = { xSplit: 0, ySplit: 10, topLeftCell: 'A11', activePane: 'bottomLeft' };
  
  // Adicionar à planilha
  XLSX.utils.book_append_sheet(wb, ws, "Cadastro de Toners");
  
  // Criar aba de instruções detalhadas
  const instrData = [
    ['INSTRUÇÕES DETALHADAS - IMPORTAÇÃO DE TONERS'],
    [],
    ['CAMPOS OBRIGATÓRIOS (*)'],
    ['Campo', 'Descrição', 'Exemplo', 'Formato'],
    ['Modelo', 'Código ou nome do modelo do toner', 'HP CF280A', 'Texto'],
    ['Capacidade Folhas', 'Quantidade de folhas que o toner imprime', '2700', 'Número inteiro'],
    ['Preço Toner', 'Valor de compra do toner em reais', '89.90', 'Decimal (use ponto)'],
    ['Cor', 'Cor do toner', 'Black', 'Yellow, Magenta, Cyan ou Black'],
    ['Tipo', 'Tipo de toner', 'Original', 'Original, Compativel ou Remanufaturado'],
    [],
    ['CAMPOS OPCIONAIS'],
    ['Campo', 'Descrição', 'Exemplo', 'Formato'],
    ['Peso Cheio', 'Peso do toner cheio em gramas', '850.50', 'Decimal (use ponto)'],
    ['Peso Vazio', 'Peso do cartucho vazio em gramas', '120.30', 'Decimal (use ponto)'],
    [],
    ['OBSERVAÇÕES IMPORTANTES:'],
    ['• Se informar peso, AMBOS os campos (Peso Cheio e Peso Vazio) devem ser preenchidos'],
    ['• Peso Cheio deve ser maior que Peso Vazio'],
    ['• Use PONTO (.) para separar decimais, não vírgula'],
    ['• Não use símbolos monetários (R$) no campo de preço'],
    ['• A primeira linha com dados é a linha 10 (após os cabeçalhos)'],
    ['• Linhas em branco são ignoradas automaticamente'],
    ['• Campos calculados (Gramatura, Custo/Folha) são gerados automaticamente'],
    [],
    ['VALORES PERMITIDOS:'],
    ['Cor: Yellow, Magenta, Cyan, Black'],
    ['Tipo: Original, Compativel, Remanufaturado'],
    [],
    ['EXEMPLOS DE PREENCHIMENTO:'],
    ['1. Com pesos: HP CF280A | 850.50 | 120.30 | 2700 | 89.90 | Black | Original'],
    ['2. Sem pesos: Canon 045 | (vazio) | (vazio) | 1300 | 75.50 | Yellow | Compativel']
  ];
  
  const wsInstr = XLSX.utils.aoa_to_sheet(instrData);
  wsInstr['!cols'] = [{wch: 20}, {wch: 50}, {wch: 20}, {wch: 30}];
  XLSX.utils.book_append_sheet(wb, wsInstr, "Instruções");
  
  // Download do arquivo
  const fileName = `template_toners_${new Date().toISOString().split('T')[0]}.xlsx`;
  XLSX.writeFile(wb, fileName);
  
  console.log(' Template gerado com sucesso:', fileName);
  
  // Feedback visual
  const btn = event.target;
  const originalText = btn.innerHTML;
  btn.innerHTML = ' Template baixado!';
  btn.disabled = true;
  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
  }, 2000);
}
*/

function importExcel() {
  console.log('Iniciando importação...');
  const fileInput = document.getElementById('excelFileInput');
  const file = fileInput.files[0];
  
  console.log('Arquivo selecionado:', file);
  
    showToast('error', 'Arquivo não selecionado', 'Por favor, selecione um arquivo Excel.');
  
  // Show progress container and hide buttons
  document.getElementById('progressContainer').classList.remove('hidden');
  document.getElementById('importBtn').disabled = true;
  document.getElementById('cancelBtn').disabled = true;
  
  // Read Excel file
  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
      const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
      
      if (jsonData.length <= 1) {
        throw new Error('Arquivo vazio ou sem dados');
      }
      
      // Process data with progress simulation
      processImportData(jsonData);
      
    } catch (error) {
      showImportError('Erro ao ler arquivo: ' + error.message);
    }
  };
  
  reader.onerror = function() {
    showImportError('Erro ao ler o arquivo');
  };
  
  reader.readAsArrayBuffer(file);
}

function processImportData(data) {
  const totalRows = data.length - 1; // Exclude header
  let currentRow = 0;
  
  // Simulate progress with actual data processing
  const processRow = () => {
    if (currentRow >= totalRows) {
      // All rows processed, send to server
      sendDataToServer(data);
      return;
    }
    
    currentRow++;
    const progress = Math.round((currentRow / totalRows) * 50); // First 50% for reading
    updateProgress(progress, `Processando linha ${currentRow} de ${totalRows}...`);
    
    setTimeout(processRow, 50); // Small delay for visual effect
  };
  
  processRow();
}

function sendDataToServer(data) {
  updateProgress(60, 'Enviando dados para o servidor...');
  
  const formData = new FormData();
  
  // Convert data to CSV format for server processing
  const csvContent = data.map(row => row.join(',')).join('\n');
  const blob = new Blob([csvContent], { type: 'text/csv' });
  formData.append('excel_file', blob, 'import.csv');
  
  fetch('/toners/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      // Simulate final progress steps
      updateProgress(80, 'Validando dados...');
      setTimeout(() => {
        updateProgress(90, 'Inserindo no banco de dados...');
        setTimeout(() => {
          updateProgress(100, `Concluído! ${result.imported} registros importados`);
          setTimeout(() => {
            closeImportModal();
            showSuccessMessage(result.message);
            location.reload(); // Refresh to show new data
          }, 1500);
        }, 500);
      }, 500);
    } else {
      showImportError(result.message);
    }
  })
  .catch(error => {
    showImportError('Erro de conexão: ' + error.message);
  });
}

function updateProgress(percentage, status) {
  document.getElementById('progressBar').style.width = percentage + '%';
  document.getElementById('progressText').textContent = percentage + '%';
  document.getElementById('importStatus').textContent = status;
}

function showImportError(message) {
  document.getElementById('progressContainer').classList.add('hidden');
  document.getElementById('importBtn').disabled = false;
  document.getElementById('cancelBtn').disabled = false;
  alert('Erro na importação: ' + message);
}

function showSuccessMessage(message) {
  // Create and show success notification
  const notification = document.createElement('div');
  notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
  notification.textContent = message;
  document.body.appendChild(notification);
  
  setTimeout(() => {
    notification.remove();
  }, 5000);
}

function exportToExcel(e) {
  // Show loading state
  const button = e ? e.target.closest('button') : document.querySelector('button[onclick*="exportToExcel"]');
  const originalContent = button.innerHTML;
  button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg> <span>Exportando...</span>';
  button.disabled = true;
  
  // Create download link and trigger download (usando versão avançada com estatísticas)
  const link = document.createElement('a');
  link.href = '/toners/export';
  link.download = 'toners_relatorio_completo_' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  // Restore button after a short delay
  setTimeout(() => {
    button.innerHTML = originalContent;
    button.disabled = false;
    
    // Show success message
    showNotification('Planilha Excel exportada com sucesso!', 'success');
  }, 2500);
}

function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
  
  notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
  notification.innerHTML = `
    <div class="flex items-center space-x-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
      </svg>
      <span>${message}</span>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  // Animate in
  setTimeout(() => {
    notification.classList.remove('translate-x-full');
  }, 100);
  
  // Animate out and remove
  setTimeout(() => {
    notification.classList.add('translate-x-full');
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 4000);
}

// 🚨 FUNÇÃO DE EMERGÊNCIA - Use no Console se o X não funcionar
window.forceCloseModal = function() {
  console.log('🚨 EMERGÊNCIA: FECHANDO TODOS OS MODALS...');
  
  // Restaurar scroll
  document.body.style.overflow = '';
  
  // Remover TODOS os elementos com position fixed e z-index alto
  document.querySelectorAll('*').forEach(el => {
    const styles = window.getComputedStyle(el);
    const zIndex = parseInt(styles.zIndex);
    
    if (styles.position === 'fixed' && zIndex > 1000) {
      el.remove();
      console.log('🗑️ Removido elemento suspeito:', el.tagName, el.id, el.className);
    }
  });
  
  // Remover elementos por ID que contenham "modal"
  ['importModal', 'dynamicImportModal', 'fullScreenImportModal', 'modal'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.remove();
      console.log('🗑️ Removido por ID:', id);
    }
  });
  
  console.log('✅ EMERGÊNCIA CONCLUÍDA!');
};

// Instrução para o usuário
console.log('💡 DICA: Se o modal não fechar, digite no console: forceCloseModal()');


</script>
</section>
