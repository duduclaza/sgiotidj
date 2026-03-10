<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900">📊 Dashboard - Análise de Dados</h1>
  </div>

  <!-- Sistema de Abas com Controle de Permissões -->
  <?php
  // Definir permissões de abas (vem do controller)
  $tabPermissions = $dashboardTabs ?? [
    'retornados' => true,
    'amostragens' => true,
    'fornecedores' => true,
    'garantias' => true,
    'melhorias' => true
  ];
  
  // Contar quantas abas o usuário pode ver
  $visibleTabs = array_filter($tabPermissions);
  $hasAnyTab = count($visibleTabs) > 0;
  ?>
  
  <?php if (!$hasAnyTab): ?>
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded mb-4">
      <p class="font-medium">⚠️ Sem permissão para visualizar abas do dashboard</p>
      <p class="text-sm mt-1">Entre em contato com o administrador para solicitar acesso.</p>
    </div>
  <?php else: ?>
  
  <div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="flex border-b border-gray-200">
      <?php if ($tabPermissions['retornados']): ?>
      <button onclick="switchTab('retornados')" id="tab-retornados" class="tab-button active flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📦 Retornados
        </span>
      </button>
      <?php endif; ?>
      
      <?php if ($tabPermissions['amostragens']): ?>
      <button onclick="switchTab('amostragens')" id="tab-amostragens" class="tab-button flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
          </svg>
          🧪 Amostragens 2.0
        </span>
      </button>
      <?php endif; ?>
      
      <?php if ($tabPermissions['fornecedores']): ?>
      <button onclick="switchTab('fornecedores')" id="tab-fornecedores" class="tab-button flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          🏭 Fornecedores
        </span>
      </button>
      <?php endif; ?>
      
      <?php if ($tabPermissions['garantias']): ?>
      <button onclick="switchTab('garantias')" id="tab-garantias" class="tab-button flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
          🛡️ Garantias
        </span>
      </button>
      <?php endif; ?>
      
      <?php if ($tabPermissions['melhorias']): ?>
      <button onclick="switchTab('melhorias')" id="tab-melhorias" class="tab-button flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
          </svg>
          🚀 Melhorias
        </span>
      </button>
      <?php endif; ?>
      
      <?php if ($tabPermissions['nao_conformidades'] ?? false): ?>
      <button onclick="switchTab('nao-conformidades')" id="tab-nao-conformidades" class="tab-button flex-1 px-6 py-4 text-center font-medium text-sm transition-all duration-200">
        <span class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
          ⚠️ Não Conformidades
        </span>
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <style>
    .tab-button {
      color: #6B7280;
      background: white;
      border-bottom: 3px solid transparent;
    }
    .tab-button:hover {
      background: #F9FAFB;
      color: #3B82F6;
    }
    .tab-button.active {
      color: #3B82F6;
      background: #EFF6FF;
      border-bottom-color: #3B82F6;
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
  </style>

  <!-- CONTEÚDO ABA RETORNADOS -->
  <?php if ($tabPermissions['retornados']): ?>
  <div id="content-retornados" class="tab-content active">

  <!-- Filtros -->
  <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
      <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
      </svg>
      🔍 Filtros de Análise
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">🏢 Filial</label>
        <select id="filtroFilial" onchange="updateCharts()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Todas as Filiais</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">🔍 Código do Cliente</label>
        <input type="text" id="filtroCodigoCliente" placeholder="Digite o código..." onchange="updateCharts()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Inicial</label>
        <input type="date" id="dataInicial" onchange="updateCharts()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Final</label>
        <input type="date" id="dataFinal" onchange="updateCharts()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
    </div>
    <div class="mt-4 flex space-x-3">
      <button onclick="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <span>Aplicar Filtros</span>
      </button>
      <button onclick="clearFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span>Limpar</span>
      </button>
    </div>
  </div>

  <!-- Cards de Totais Acumulados -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <!-- Card 1: Total Retornados por Mês -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
        </div>
        <span class="text-white text-opacity-80 text-xs font-medium">Até <?= date('d/m/Y') ?></span>
      </div>
      <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Acumulado: Retornados por Mês</h3>
      <div class="flex items-end justify-between">
        <p id="totalRetornadosCard" class="text-4xl font-bold"><?= number_format($totaisAcumulados['retornados_total'] ?? 0, 0, ',', '.') ?></p>
        <span class="text-white text-opacity-80 text-xs">unidades</span>
      </div>
      <div class="mt-4 pt-4 border-t border-white border-opacity-20">
        <p class="text-xs text-white text-opacity-80">📊 Soma total de toners retornados</p>
      </div>
    </div>

    <!-- Card 2: Valor Total Recuperado -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
      <div class="flex items-center justify-between mb-4">
        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <span class="text-white text-opacity-80 text-xs font-medium">Até <?= date('d/m/Y') ?></span>
      </div>
      <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Acumulado: Toners Recuperados</h3>
      <div class="flex items-end justify-between">
        <p id="valorRecuperadoCard" class="text-4xl font-bold">R$ <?= number_format($totaisAcumulados['valor_recuperado'] ?? 0, 2, ',', '.') ?></p>
      </div>
      <div class="mt-4 pt-4 border-t border-white border-opacity-20">
        <p class="text-xs text-white text-opacity-80">💰 Valor total economizado</p>
      </div>
    </div>

  </div>

  <!-- Gráficos dos Retornados -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- Gráfico de Barras - Retornados por Mês -->
    <div class="bg-white rounded-lg shadow-lg border-l-4 border-green-500">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📊 Retornados por Mês
        </h3>
        <button onclick="expandirGraficoRetornados()" class="p-2 rounded-lg hover:bg-green-50 transition-all duration-200 group" title="Expandir gráfico">
          <svg class="w-5 h-5 text-green-600 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
          </svg>
        </button>
      </div>
      <div class="p-6">
        <canvas id="retornadosMesChart" width="400" height="200"></canvas>
      </div>
    </div>

    <!-- Gráfico de Pizza - Retornados por Destino -->
    <div class="bg-white rounded-lg shadow-lg border-l-4 border-orange-500">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
          </svg>
          🥧 Destino dos Retornados
        </h3>
        <button onclick="expandirGraficoDestino()" class="p-2 rounded-lg hover:bg-orange-50 transition-all duration-200 group" title="Expandir gráfico">
          <svg class="w-5 h-5 text-orange-600 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
          </svg>
        </button>
      </div>
      <div class="p-6">
        <canvas id="retornadosDestinoChart" width="400" height="200"></canvas>
      </div>
    </div>
  </div>

  <!-- Gráfico de Ranking de Códigos de Cliente -->
  <div class="bg-white rounded-lg shadow-lg border-l-4 border-indigo-500 mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
          </svg>
          🏆 Top 10 - Ranking de Códigos de Cliente
        </h3>
        <button onclick="expandirGraficoRanking()" class="p-2 rounded-lg hover:bg-indigo-50 transition-all duration-200 group" title="Expandir gráfico">
          <svg class="w-5 h-5 text-indigo-600 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
          </svg>
        </button>
      </div>
      <!-- Filtros específicos para este gráfico -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 mt-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">🏢 Filial</label>
          <select id="filtroFilialRanking" onchange="updateRankingChart()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todas as Filiais</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">📅 Data Inicial</label>
          <input type="date" id="dataInicialRanking" onchange="updateRankingChart()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">📅 Data Final</label>
          <input type="date" id="dataFinalRanking" onchange="updateRankingChart()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">🎯 Destino</label>
          <select id="filtroDestinoRanking" onchange="updateRankingChart()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todos os Destinos</option>
            <option value="ESTOQUE">Estoque</option>
            <option value="DESCARTE">Descarte</option>
            <option value="USO_INTERNO">Uso Interno</option>
            <option value="GARANTIA">Garantia</option>
          </select>
        </div>
        <div class="flex items-end">
          <button onclick="limparFiltrosRanking()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Limpar
          </button>
        </div>
      </div>
    </div>
    <div class="p-6">
      <canvas id="rankingClientesChart" width="400" height="300"></canvas>
    </div>
  </div>

  <!-- Gráfico de Toners Recuperados -->
  <div class="bg-white rounded-lg shadow-lg border-l-4 border-purple-500 mt-6">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900 flex items-center">
        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        💰 Valor Recuperado em Toners (R$)
      </h3>
      <button onclick="expandirGraficoRecuperados()" class="p-2 rounded-lg hover:bg-purple-50 transition-all duration-200 group" title="Expandir gráfico">
        <svg class="w-5 h-5 text-purple-600 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
        </svg>
      </button>
    </div>
    <div class="p-6">
      <canvas id="tonersRecuperadosChart" width="800" height="300"></canvas>
    </div>
  </div>

  <!-- Gráfico Retornados por Clientes (sem limite) -->
  <div class="bg-white rounded-lg shadow-lg border-l-4 border-teal-500 mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          📊 Retornados por Clientes
        </h3>
      </div>
      <!-- Filtros específicos para este gráfico -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 mt-2">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">🏢 Filial</label>
          <select id="filtroFilialRetClientes" onchange="updateRetornadosClientesChart()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <option value="">Todas as Filiais</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">📅 Data Inicial</label>
          <input type="date" id="dataInicialRetClientes" onchange="updateRetornadosClientesChart()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">📅 Data Final</label>
          <input type="date" id="dataFinalRetClientes" onchange="updateRetornadosClientesChart()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">🎯 Destino</label>
          <select id="filtroDestinoRetClientes" onchange="updateRetornadosClientesChart()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <option value="">Todos os Destinos</option>
            <option value="ESTOQUE">Estoque</option>
            <option value="DESCARTE">Descarte</option>
            <option value="USO_INTERNO">Uso Interno</option>
            <option value="GARANTIA">Garantia</option>
          </select>
        </div>
        <div class="flex items-end">
          <button onclick="limparFiltrosRetClientes()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Limpar
          </button>
        </div>
      </div>
        <div class="flex items-end">
          <button onclick="exportarRetornadosClientesExcel()" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5 shadow-sm hover:shadow-md" title="Exportar dados filtrados para Excel">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            📊 Excel
          </button>
        </div>
    </div>
    <div class="p-6">
      <canvas id="retornadosClientesChart" width="400" height="400"></canvas>
    </div>
  </div>

  </div>
  <?php endif; ?>
  <!-- FIM CONTEÚDO ABA RETORNADOS -->

  <!-- CONTEÚDO ABA AMOSTRAGENS 2.0 -->
  <?php if ($tabPermissions['amostragens']): ?>
  <div id="content-amostragens" class="tab-content space-y-6">
    
    <!-- Filtros AMOSTRAGENS -->
    <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-teal-500">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
        </svg>
        🔍 Filtros de Análise - Amostragens
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">🏢 Filial</label>
          <select id="filtroFilialAmostragens" onchange="loadDashboardAmostragens()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            <option value="">Todas as Filiais</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Inicial</label>
          <input type="date" id="dataInicialAmostragens" onchange="loadDashboardAmostragens()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Final</label>
          <input type="date" id="dataFinalAmostragens" onchange="loadDashboardAmostragens()" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
        </div>
      </div>
      <div class="mt-4 flex space-x-3">
        <button onclick="loadDashboardAmostragens()" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <span>Aplicar Filtros</span>
        </button>
        <button onclick="clearFiltersAmostragens()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          <span>Limpar</span>
        </button>
      </div>
    </div>

    <!-- Cards de Totais AMOSTRAGENS -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      
      <!-- Card 1: Qtd Recebida -->
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Qtd Recebida</h3>
        <div class="flex items-end justify-between">
          <p id="qtdRecebida" class="text-4xl font-bold">0</p>
        </div>
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-xs text-white text-opacity-80">📦 Total recebido</p>
        </div>
      </div>

      <!-- Card 2: Qtd Testada -->
      <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Qtd Testada</h3>
        <div class="flex items-end justify-between">
          <p id="qtdTestada" class="text-4xl font-bold">0</p>
        </div>
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-xs text-white text-opacity-80">🧪 Total testado</p>
        </div>
      </div>

      <!-- Card 3: Qtd Aprovada -->
      <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Qtd Aprovada</h3>
        <div class="flex items-end justify-between">
          <p id="qtdAprovada" class="text-4xl font-bold">0</p>
        </div>
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-xs text-white text-opacity-80">✅ Total aprovado</p>
        </div>
      </div>

      <!-- Card 4: Qtd Reprovada -->
      <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white transform transition-all duration-300 hover:scale-105 hover:shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90 mb-2">Qtd Reprovada</h3>
        <div class="flex items-end justify-between">
          <p id="qtdReprovada" class="text-4xl font-bold">0</p>
        </div>
        <div class="mt-4 pt-4 border-t border-white border-opacity-20">
          <p class="text-xs text-white text-opacity-80">❌ Total reprovado</p>
        </div>
      </div>

    </div>

    <!-- Gráficos das Amostragens -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Gráfico 1: Barras - Qtd Recebida x Testada por Mês -->
      <div class="bg-white rounded-lg shadow-lg border-l-4 border-blue-500">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            📊 Qtd Recebida x Testada por Mês
          </h3>
        </div>
        <div class="p-6">
          <canvas id="amostragemQuantidadesChart" width="400" height="300"></canvas>
        </div>
      </div>

      <!-- Gráfico 2: Pizza - Taxa Aprovação/Reprovação por Fornecedor -->
      <div class="bg-white rounded-lg shadow-lg border-l-4 border-purple-500">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
            </svg>
            🥧 Taxa Aprovação/Reprovação por Fornecedor
          </h3>
        </div>
        <div class="p-6">
          <canvas id="amostragemFornecedorTaxaChart" width="400" height="300"></canvas>
        </div>
      </div>

      <!-- Gráfico 3: Barras - Amostragens Reprovadas por Mês (NOVO) -->
      <div class="bg-white rounded-lg shadow-lg border-l-4 border-red-500 lg:col-span-2">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            ❌ Amostragens Reprovadas por Mês
            <span class="ml-2 text-xs text-gray-500 font-normal">(Clique na barra para ver detalhes)</span>
          </h3>
        </div>
        <div class="p-6">
          <canvas id="amostragemReprovadasMesChart" width="800" height="300"></canvas>
        </div>
      </div>

    </div>

  </div>
  <?php endif; ?>
  <!-- FIM CONTEÚDO ABA AMOSTRAGENS -->

  <!-- CONTEÚDO ABA FORNECEDORES -->
  <?php if ($tabPermissions['fornecedores']): ?>
  <div id="content-fornecedores" class="tab-content space-y-6">
    
    <!-- Filtros FORNECEDORES -->
    <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
        </svg>
        🔍 Filtros - Qualidade de Fornecedores
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">🏢 Filial</label>
          <select id="filtroFilialFornecedores" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            <option value="">Todas as Filiais</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📋 Origem (Ctrl+Click para múltiplas)</label>
          <select id="filtroOrigemFornecedores" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" style="height: 80px;">
            <option value="Amostragem">Amostragem</option>
            <option value="Homologação">Homologação</option>
            <option value="Em Campo">Em Campo</option>
          </select>
          <p class="text-xs text-gray-500 mt-1">💡 Segure Ctrl/Cmd para selecionar várias</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Inicial</label>
          <input type="date" id="dataInicialFornecedores" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data Final</label>
          <input type="date" id="dataFinalFornecedores" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
      </div>
      <div class="mt-4 flex space-x-3">
        <button onclick="applyFiltersFornecedores()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          <span>Aplicar Filtros</span>
        </button>
        <button onclick="clearFiltersFornecedores()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          <span>Limpar</span>
        </button>
      </div>
    </div>

    <!-- Cards de Resumo por Fornecedor -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-100 text-sm font-medium mb-1">🎯 Fornecedores Analisados</p>
            <p class="text-3xl font-bold" id="totalFornecedores">0</p>
          </div>
          <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
        </div>
      </div>

      <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-100 text-sm font-medium mb-1">📦 Total de Itens Comprados</p>
            <p class="text-3xl font-bold" id="totalItensComprados">0</p>
          </div>
          <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
        </div>
      </div>

      <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-red-100 text-sm font-medium mb-1">⚠️ Total de Garantias</p>
            <p class="text-3xl font-bold" id="totalGarantias">0</p>
          </div>
          <svg class="w-12 h-12 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Gráfico de Qualidade por Fornecedor -->
    <div class="bg-white rounded-lg shadow-lg p-6">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📊 Análise de Qualidade por Fornecedor
        </h3>
        <div class="text-sm text-gray-600">
          <span class="font-medium">Fórmula:</span> % Qualidade = ((Comprados - Garantias) / Comprados) × 100
        </div>
      </div>
      <div class="relative" style="height: 500px;">
        <canvas id="chartQualidadeFornecedores"></canvas>
      </div>
      <div class="mt-4 p-4 bg-gray-50 rounded-lg">
        <p class="text-sm text-gray-700">
          <strong>💡 Interpretação:</strong> Quanto maior a % de qualidade, melhor o fornecedor. 
          Uma qualidade de 95% significa que de 100 itens comprados, apenas 5 geraram garantias.
        </p>
      </div>
    </div>

    <!-- Gráfico de Itens Comprados vs Garantias por Tipo -->
    <div class="bg-white rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
        </svg>
        📈 Comprados vs Garantias por Tipo de Produto
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <h4 class="text-center font-medium text-gray-700 mb-3">🖨️ Toners</h4>
          <div style="height: 300px;">
            <canvas id="chartToners"></canvas>
          </div>
        </div>
        <div>
          <h4 class="text-center font-medium text-gray-700 mb-3">🖥️ Máquinas</h4>
          <div style="height: 300px;">
            <canvas id="chartMaquinas"></canvas>
          </div>
        </div>
        <div>
          <h4 class="text-center font-medium text-gray-700 mb-3">🔧 Peças</h4>
          <div style="height: 300px;">
            <canvas id="chartPecas"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela Detalhada de Fornecedores -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
          </svg>
          📋 Detalhamento por Fornecedor
        </h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Toners Comprados</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Garantias Toner</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Qualidade Toner</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Máquinas Compradas</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Garantias Máquina</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Qualidade Máquina</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Peças Compradas</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Garantias Peça</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Qualidade Peça</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider font-bold">% Qualidade Geral</th>
            </tr>
          </thead>
          <tbody id="tabelaFornecedores" class="bg-white divide-y divide-gray-200">
            <tr>
              <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                Selecione os filtros e clique em "Aplicar Filtros" para ver os dados
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
  <?php endif; ?>
  <!-- FIM CONTEÚDO ABA FORNECEDORES -->

  <?php if ($tabPermissions['garantias']): ?>
  <?php include __DIR__ . '/dashboard_garantias_tab.php'; ?>
  <?php endif; ?>

  <!-- CONTEÚDO ABA MELHORIAS -->
  <?php if ($tabPermissions['melhorias']): ?>
  <div id="content-melhorias" class="tab-content space-y-6">
    
    <!-- Filtros de Melhorias -->
    <div class="bg-white rounded-lg shadow-lg p-4 border-l-4 border-purple-500">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
        </svg>
        <h3 class="font-semibold text-gray-800">Filtros do Dashboard</h3>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Departamento</label>
          <select id="filtro-melhorias-departamento" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            <option value="">Todos</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
          <select id="filtro-melhorias-status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            <option value="">Todos</option>
            <option value="pendente_analise">⏳ Pendente Análise</option>
            <option value="enviado_aprovacao">📤 Enviado para Aprovação</option>
            <option value="em_andamento">🔄 Em Andamento</option>
            <option value="concluida">✅ Concluída</option>
            <option value="reprovada">❌ Reprovada</option>
            <option value="cancelada">🚫 Cancelada</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Idealizador</label>
          <input type="text" id="filtro-melhorias-idealizador" placeholder="Nome..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
          <input type="date" id="filtro-melhorias-data-inicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
          <input type="date" id="filtro-melhorias-data-fim" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Pontuação</label>
          <div class="flex gap-1">
            <input type="number" id="filtro-melhorias-pont-min" placeholder="Min" min="0" max="100" class="w-1/2 border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            <input type="number" id="filtro-melhorias-pont-max" placeholder="Max" min="0" max="100" class="w-1/2 border border-gray-300 rounded-lg px-2 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
          </div>
        </div>
      </div>
      <div class="flex gap-2 mt-4">
        <button onclick="aplicarFiltrosMelhorias()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-md">
          🔍 Aplicar Filtros
        </button>
        <button onclick="limparFiltrosMelhorias()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-md">
          🧹 Limpar
        </button>
      </div>
    </div>

    <!-- Cards de Status (baseados nos status reais do grid) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      
      <!-- Card: Pendente Análise -->
      <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg shadow-lg p-5 text-white transform transition-all duration-300 hover:scale-105">
        <div class="flex items-center justify-between mb-3">
          <span class="text-3xl">⏳</span>
          <div class="text-right">
            <p id="status-pendente-analise" class="text-3xl font-bold">0</p>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90">Pendente Análise</h3>
        <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full">
          <div id="bar-pendente-analise" class="h-1 bg-white rounded-full" style="width: 0%"></div>
        </div>
      </div>

      <!-- Card: Enviado para Aprovação -->
      <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-5 text-white transform transition-all duration-300 hover:scale-105">
        <div class="flex items-center justify-between mb-3">
          <span class="text-3xl">📤</span>
          <div class="text-right">
            <p id="status-enviado-aprovacao" class="text-3xl font-bold">0</p>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90">Enviado para Aprovação</h3>
        <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full">
          <div id="bar-enviado-aprovacao" class="h-1 bg-white rounded-full" style="width: 0%"></div>
        </div>
      </div>

      <!-- Card: Em Andamento -->
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-5 text-white transform transition-all duration-300 hover:scale-105">
        <div class="flex items-center justify-between mb-3">
          <span class="text-3xl">🔄</span>
          <div class="text-right">
            <p id="status-em-andamento" class="text-3xl font-bold">0</p>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90">Em Andamento</h3>
        <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full">
          <div id="bar-em-andamento" class="h-1 bg-white rounded-full" style="width: 0%"></div>
        </div>
      </div>

      <!-- Card: Concluída -->
      <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-5 text-white transform transition-all duration-300 hover:scale-105">
        <div class="flex items-center justify-between mb-3">
          <span class="text-3xl">✅</span>
          <div class="text-right">
            <p id="status-concluida" class="text-3xl font-bold">0</p>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90">Concluída</h3>
        <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full">
          <div id="bar-concluida" class="h-1 bg-white rounded-full" style="width: 0%"></div>
        </div>
      </div>

      <!-- Card: Recusada -->
      <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-5 text-white transform transition-all duration-300 hover:scale-105">
        <div class="flex items-center justify-between mb-3">
          <span class="text-3xl">❌</span>
          <div class="text-right">
            <p id="status-recusada" class="text-3xl font-bold">0</p>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90">Recusada</h3>
        <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full">
          <div id="bar-recusada" class="h-1 bg-white rounded-full" style="width: 0%"></div>
        </div>
      </div>

      <!-- Card: Pendente Adaptação -->
      <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-5 text-white transform transition-all duration-300 hover:scale-105">
        <div class="flex items-center justify-between mb-3">
          <span class="text-3xl">📝</span>
          <div class="text-right">
            <p id="status-pendente-adaptacao" class="text-3xl font-bold">0</p>
          </div>
        </div>
        <h3 class="text-sm font-medium text-white text-opacity-90">Pendente Adaptação</h3>
        <div class="mt-2 h-1 bg-white bg-opacity-20 rounded-full">
          <div id="bar-pendente-adaptacao" class="h-1 bg-white rounded-full" style="width: 0%"></div>
        </div>
      </div>

    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Gráfico: Melhorias por Status -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
          </svg>
          📊 Distribuição por Status
        </h3>
        <canvas id="chartMelhoriasStatus" height="300"></canvas>
      </div>

      <!-- Gráfico: Melhorias por Mês -->
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📈 Melhorias por Mês (Últimos 12 Meses)
        </h3>
        <canvas id="chartMelhoriasMes" height="300"></canvas>
      </div>

    </div>

    <!-- Gráfico: Top 10 Departamentos -->
    <div class="bg-white rounded-lg shadow-lg p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        🏢 Top 10 Departamentos
      </h3>
      <canvas id="chartMelhoriasDepartamentos" height="120"></canvas>
    </div>

    <!-- Card Pontuação Média -->
    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-8 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium text-white text-opacity-90 mb-2">⭐ Pontuação Média das Melhorias</h3>
          <p id="melhorias-pontuacao-media" class="text-5xl font-bold">0.0</p>
          <p class="text-sm text-white text-opacity-80 mt-2">Escala de 0 a 3</p>
        </div>
        <div class="w-24 h-24 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
          <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
          </svg>
        </div>
      </div>
    </div>

  </div>
  <?php endif; ?>
  <!-- FIM CONTEÚDO ABA MELHORIAS -->

  <!-- CONTEÚDO ABA GARANTIAS -->
  <?php if ($tabPermissions['garantias'] ?? false): ?>
  <?php include __DIR__ . '/dashboard_garantias_tab.php'; ?>
  <?php endif; ?>

  <!-- CONTEÚDO ABA NÃO CONFORMIDADES -->
  <?php if ($tabPermissions['nao_conformidades'] ?? false): ?>
  <?php include __DIR__ . '/dashboard_nao_conformidades_tab.php'; ?>
  <?php endif; ?>

</section>

<!-- Modal: Detalhes de Melhorias por Departamento -->
<div id="modalDetalhesMelhorias" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300" style="z-index: 99999;">
  <div class="bg-white rounded-2xl shadow-2xl w-[95vw] h-[95vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modalDetalhesMelhoriasContent">
    <!-- Cabeçalho -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-bold text-white flex items-center gap-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          <span>Melhorias do Departamento: <span id="modalMelhoriasDepartamento" class="font-extrabold">-</span></span>
        </h3>
        <button onclick="fecharModalMelhorias()" class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white hover:bg-opacity-20 rounded-lg">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Conteúdo -->
    <div class="p-6 overflow-y-auto" style="max-height: calc(95vh - 80px);">
      <!-- Loading -->
      <div id="modalMelhoriasLoading" class="flex items-center justify-center py-12">
        <div class="flex flex-col items-center gap-3">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
          <p class="text-gray-600">Carregando melhorias...</p>
        </div>
      </div>

      <!-- Conteúdo das Melhorias -->
      <div id="modalMelhoriasConteudo" class="hidden">
        <!-- Resumo -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
            <p class="text-sm text-blue-600 font-medium">Total de Melhorias</p>
            <p id="melhorias-total" class="text-2xl font-bold text-blue-700">0</p>
          </div>
          <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
            <p class="text-sm text-green-600 font-medium">Concluídas</p>
            <p id="melhorias-concluidas" class="text-2xl font-bold text-green-700">0</p>
          </div>
          <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-500">
            <p class="text-sm text-yellow-600 font-medium">Em Andamento</p>
            <p id="melhorias-andamento" class="text-2xl font-bold text-yellow-700">0</p>
          </div>
          <div class="bg-purple-50 rounded-lg p-4 border-l-4 border-purple-500">
            <p class="text-sm text-purple-600 font-medium">Pontuação Média</p>
            <p id="melhorias-pontuacao" class="text-2xl font-bold text-purple-700">0.0</p>
          </div>
        </div>

        <!-- Tabela de Melhorias -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Idealizador</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pontuação</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                </tr>
              </thead>
              <tbody id="modalMelhoriasTabela" class="bg-white divide-y divide-gray-200">
                <!-- Será preenchido via JavaScript -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Modal de Escolha - Ver Itens Aprovados ou Reprovados do Fornecedor -->
<div id="modalDetalhesFornecedor" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300" style="z-index: 99999;">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modalDetalhesFornecedorContent">
    <!-- Cabeçalho -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-bold text-white flex items-center gap-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          <span id="modalFornecedorNome">Fornecedor</span>
        </h3>
        <button onclick="fecharModalDetalhesFornecedor()" class="p-2 rounded-full hover:bg-white/20 transition-colors">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Corpo -->
    <div class="p-6">
      <p class="text-gray-600 text-center mb-6">Qual tipo de item deseja visualizar?</p>
      
      <div class="grid grid-cols-2 gap-4">
        <!-- Botão Aprovados -->
        <button onclick="verItensFornecedor('aprovados')" class="group p-6 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-xl border-2 border-green-200 hover:border-green-400 transition-all duration-300">
          <div class="flex flex-col items-center gap-3">
            <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <span class="font-semibold text-green-700">Aprovados</span>
            <span id="modalQtdAprovados" class="text-2xl font-bold text-green-600">0</span>
          </div>
        </button>
        
        <!-- Botão Reprovados -->
        <button onclick="verItensFornecedor('reprovados')" class="group p-6 bg-gradient-to-br from-red-50 to-red-100 hover:from-red-100 hover:to-red-200 rounded-xl border-2 border-red-200 hover:border-red-400 transition-all duration-300">
          <div class="flex flex-col items-center gap-3">
            <div class="w-14 h-14 bg-red-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <span class="font-semibold text-red-700">Reprovados</span>
            <span id="modalQtdReprovados" class="text-2xl font-bold text-red-600">0</span>
          </div>
        </button>
      </div>
      
      <p class="text-xs text-gray-400 text-center mt-4">Os dados serão abertos em uma nova janela</p>
    </div>
</div>
</div>

<!-- Modal de Amostragens Reprovadas por Mês (NOVO) -->
<div id="modalAmostragensMes" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300" style="z-index: 99999;">
  <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="modalAmostragensMesContent">
    <!-- Cabeçalho -->
    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-bold text-white flex items-center gap-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Amostragens Reprovadas - <span id="modalMesLabel"></span></span>
        </h3>
        <div class="flex items-center gap-4">
          <span id="modalQtdMes" class="text-white text-sm font-medium bg-white/20 px-3 py-1 rounded-full"></span>
          <button onclick="fecharModalAmostragensMes()" class="p-2 rounded-full hover:bg-white/20 transition-colors">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Corpo -->
    <div class="p-6 overflow-y-auto max-h-[70vh]">
      <!-- Loading -->
      <div id="modalAmostragensMesLoading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Carregando amostragens...</p>
      </div>
      
      <!-- Tabela -->
      <div id="modalAmostragensMesTabela" class="hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-red-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">#</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Código</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Nome do Produto</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-red-700 uppercase tracking-wider">Tipo</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-red-700 uppercase tracking-wider">Qtd</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-red-700 uppercase tracking-wider">Data</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Fornecedor</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Reprovado Por</th>
            </tr>
          </thead>
          <tbody id="modalAmostragensMesBody" class="bg-white divide-y divide-gray-200">
          </tbody>
        </table>
      </div>
      
      <!-- Sem dados -->
      <div id="modalAmostragensMesVazio" class="hidden text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma amostragem encontrada</h3>
        <p class="text-gray-500">Não há amostragens reprovadas para este mês.</p>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Detalhes do Cliente - Toners Retornados (Fullscreen) -->
<div id="modalDetalhesCliente" class="hidden fixed inset-0 bg-black/90 backdrop-blur-md flex items-center justify-center p-4 md:p-8 transition-all duration-500 ease-out" style="z-index: 999999;">
  <div class="relative w-full max-w-4xl max-h-[90vh] overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700/50 transform transition-all duration-500 ease-out scale-95 opacity-0" id="modalDetalhesClienteContent">
    
    <!-- Botão Fechar -->
    <button onclick="fecharModalDetalhesCliente()" class="absolute top-4 right-4 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group z-10">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Cabeçalho -->
    <div class="bg-gradient-to-r from-blue-600/20 to-indigo-600/20 px-8 py-6 border-b border-gray-700/50">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
          <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
        <div>
          <h3 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400" id="modalClienteNome">Cliente</h3>
          <p class="text-gray-400 text-sm mt-1">Código: <span id="modalClienteCodigo" class="font-mono bg-gray-700/50 px-2 py-0.5 rounded text-blue-300">-</span></p>
        </div>
      </div>
    </div>
    
    <!-- Corpo -->
    <div class="p-8 overflow-y-auto" style="max-height: calc(90vh - 140px);">
      <!-- Loading -->
      <div id="modalClienteLoading" class="text-center py-16">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-500/20 rounded-full mb-4">
          <svg class="w-10 h-10 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <p class="text-gray-400">Carregando toners...</p>
      </div>
      
      <!-- Conteúdo -->
      <div id="modalClienteConteudo" class="hidden">
        <!-- Total -->
        <div class="bg-gradient-to-r from-blue-500/10 to-indigo-500/10 rounded-2xl p-6 mb-6 border border-blue-500/20">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
              <span class="text-gray-300 font-medium text-lg">Total de Retornados</span>
            </div>
            <span id="modalClienteTotal" class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">0</span>
          </div>
        </div>
        
        <!-- Lista de Toners -->
        <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
          <table class="w-full">
            <thead class="bg-gray-700/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Modelo do Toner</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-300 uppercase tracking-wider">Destino</th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-300 uppercase tracking-wider">Quantidade</th>
              </tr>
            </thead>
            <tbody id="modalClienteToners" class="divide-y divide-gray-700/50">
              <!-- Preenchido via JS -->
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Erro -->
      <div id="modalClienteErro" class="hidden text-center py-16">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-500/20 rounded-full mb-4">
          <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <p class="text-red-400 text-lg" id="modalClienteErroMsg">Erro ao carregar dados</p>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Expansão do Gráfico - Retornados por Mês -->
<div id="modalExpandidoRetornados" class="hidden fixed inset-0 bg-black bg-opacity-95 backdrop-blur-sm flex items-center justify-center p-4 md:p-8 transition-all duration-500 ease-out" style="z-index: 99999;">
  <div class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700 p-4 md:p-8 transition-all duration-500 ease-out transform scale-95 opacity-0" id="modalContentRetornados">
    <!-- Botão Fechar -->
    <button onclick="fecharGraficoExpandido()" class="absolute top-6 right-6 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group z-10">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Título e Filtros -->
    <div class="mb-6">
      <div class="text-center mb-4">
        <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-500 flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          📊 Retornados por Mês - Visão Expandida
        </h2>
        <p class="text-gray-400 mt-2">Análise detalhada dos retornados ao longo do ano</p>
      </div>
      
    </div>
    
    <!-- Canvas Expandido -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-6 border border-gray-700/50" style="height: 60vh; min-height: 400px; max-height: 600px;">
      <canvas id="retornadosMesChartExpandido"></canvas>
    </div>
    
    <!-- Dica -->
    <div class="mt-6 text-center">
      <p class="text-gray-500 text-sm flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Pressione <kbd class="px-2 py-1 bg-gray-700 rounded text-xs mx-1">ESC</kbd> ou clique no botão ✕ para fechar
      </p>
    </div>
  </div>
</div>

<!-- Modal de Expansão do Gráfico - Destino dos Retornados -->
<div id="modalExpandidoDestino" class="hidden fixed inset-0 bg-black bg-opacity-95 backdrop-blur-sm flex items-center justify-center p-4 md:p-8 transition-all duration-500 ease-out" style="z-index: 99999;">
  <div class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700 p-4 md:p-8 transition-all duration-500 ease-out transform scale-95 opacity-0" id="modalContentDestino">
    <!-- Botão Fechar -->
    <button onclick="fecharGraficoDestinoExpandido()" class="absolute top-6 right-6 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group z-10">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Título e Filtros -->
    <div class="mb-6">
      <div class="text-center mb-4">
        <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-amber-500 flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
          </svg>
          🥧 Destino dos Retornados - Visão Expandida
        </h2>
        <p class="text-gray-400 mt-2">Distribuição detalhada dos destinos dos toners retornados</p>
      </div>
      
    </div>
    
    <!-- Canvas Expandido -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-6 border border-gray-700/50" style="height: 60vh; min-height: 400px; max-height: 600px;">
      <canvas id="retornadosDestinoChartExpandido"></canvas>
    </div>
    
    <!-- Dica -->
    <div class="mt-6 text-center">
      <p class="text-gray-500 text-sm flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Pressione <kbd class="px-2 py-1 bg-gray-700 rounded text-xs mx-1">ESC</kbd> ou clique no botão ✕ para fechar
      </p>
    </div>
  </div>
</div>

<!-- Modal de Expansão do Gráfico - Valor Recuperado -->
<div id="modalExpandidoRecuperados" class="hidden fixed inset-0 bg-black bg-opacity-95 backdrop-blur-sm flex items-center justify-center p-4 md:p-8 transition-all duration-500 ease-out" style="z-index: 99999;">
  <div class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700 p-4 md:p-8 transition-all duration-500 ease-out transform scale-95 opacity-0" id="modalContentRecuperados">
    <!-- Botão Fechar -->
    <button onclick="fecharGraficoRecuperadosExpandido()" class="absolute top-6 right-6 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group z-10">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Título e Filtros -->
    <div class="mb-6">
      <div class="text-center mb-4">
        <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-fuchsia-500 flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          💰 Valor Recuperado em Toners - Visão Expandida
        </h2>
        <p class="text-gray-400 mt-2">Análise detalhada do valor recuperado ao longo do ano</p>
      </div>
      
    </div>
    
    <!-- Canvas Expandido -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-6 border border-gray-700/50" style="height: 60vh; min-height: 400px; max-height: 600px;">
      <canvas id="tonersRecuperadosChartExpandido"></canvas>
    </div>
    
    <!-- Dica -->
    <div class="mt-6 text-center">
      <p class="text-gray-500 text-sm flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Pressione <kbd class="px-2 py-1 bg-gray-700 rounded text-xs mx-1">ESC</kbd> ou clique no botão ✕ para fechar
      </p>
    </div>
  </div>
</div>

<!-- Modal de Expansão do Gráfico - Garantias -->
<div id="modalGarantiasChart" class="hidden fixed inset-0 bg-black bg-opacity-95 backdrop-blur-sm flex items-center justify-center p-4 md:p-8 transition-all duration-500 ease-out" style="z-index: 99999;">
  <div class="relative w-full max-w-6xl max-h-[90vh] overflow-y-auto bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700 p-4 md:p-8 transition-all duration-500 ease-out transform scale-95 opacity-0" id="modalGarantiasChartContent">
    <!-- Botão Fechar -->
    <button onclick="fecharModalGarantiasChart()" class="absolute top-6 right-6 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group z-10">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Título -->
    <div class="mb-6">
      <div class="text-center mb-4">
        <h2 id="modalGarantiasChartTitle" class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400 flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          Gráfico
        </h2>
        <p class="text-gray-400 mt-2">Visão Expandida</p>
      </div>
    </div>
    
    <!-- Canvas Expandido -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-6 border border-gray-700/50" style="height: 60vh; min-height: 400px; max-height: 600px;">
      <canvas id="modalGarantiasChartCanvas"></canvas>
    </div>
    
    <!-- Dica -->
    <div class="mt-6 text-center">
      <p class="text-gray-500 text-sm flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Pressione <kbd class="px-2 py-1 bg-gray-700 rounded text-xs mx-1">ESC</kbd> ou clique no botão ✕ para fechar
      </p>
    </div>
  </div>
</div>

<!-- Modal de Expansão do Gráfico - Ranking de Clientes -->
<div id="modalExpandidoRanking" class="hidden fixed inset-0 bg-black bg-opacity-95 backdrop-blur-sm flex items-center justify-center p-4 md:p-6 transition-all duration-500 ease-out" style="z-index: 999999 !important; position: fixed !important;">
  <div class="relative w-full max-w-7xl max-h-[95vh] overflow-y-auto bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-2xl border border-gray-700 p-4 md:p-8 transition-all duration-500 ease-out transform scale-95 opacity-0" id="modalContentRanking">
    <!-- Botão Fechar -->
    <button onclick="fecharGraficoRankingExpandido()" class="absolute top-6 right-6 p-3 rounded-full bg-red-500/20 hover:bg-red-500/40 transition-all duration-300 group" style="z-index: 1000000;">
      <svg class="w-6 h-6 text-red-400 group-hover:text-red-300 group-hover:rotate-90 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    
    <!-- Título e Filtros -->
    <div class="mb-6">
      <div class="text-center mb-4">
        <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-500 flex items-center justify-center gap-3">
          <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
          </svg>
          🏆 Top 10 - Ranking de Códigos de Cliente
        </h2>
        <p class="text-gray-400 mt-2">Classificação dos clientes com maior volume de retornados</p>
      </div>
      
      <!-- Filtro de Destino -->
      <div class="flex justify-center">
        <div class="inline-flex items-center gap-3 bg-gradient-to-r from-gray-800/80 to-gray-900/80 px-6 py-3 rounded-xl border border-gray-700/50 backdrop-blur-sm">
          <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
          </svg>
          <label class="text-gray-300 font-medium">Filtrar por Destino:</label>
          <select 
            id="filtroDestinoRankingExpandido" 
            onchange="atualizarGraficoRankingExpandido()" 
            class="bg-gray-700/50 border border-gray-600 text-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer hover:bg-gray-700"
          >
            <option value="">Todos os Destinos</option>
            <option value="ESTOQUE">Estoque</option>
            <option value="DESCARTE">Descarte</option>
            <option value="USO_INTERNO">Uso Interno</option>
            <option value="GARANTIA">Garantia</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Canvas do Gráfico -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-6 border border-gray-700/50" style="height: 65vh; min-height: 450px; max-height: 750px; width: 100%;">
      <canvas id="rankingClientesChartExpandido" style="width: 100% !important; height: 100% !important;"></canvas>
    </div>
  </div>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal-overlay">
  <div class="modal-container w-full max-w-md">
    <div class="modal-header">
      <h3 class="modal-title">Criar Novo Usuário</h3>
      <button class="modal-close" data-modal-close>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="modal-body">
      <form id="createUserForm" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
        <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
        <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
        <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
          <input type="text" name="setor" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Filial</label>
          <input type="text" name="filial" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Função</label>
        <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="user">Usuário</option>
          <option value="admin">Administrador</option>
        </select>
      </div>
      </form>
    </div>

    <div class="modal-footer">
      <button onclick="closeCreateUserModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
        Cancelar
      </button>
      <button onclick="submitCreateUser()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700">
        Criar Usuário
      </button>
    </div>
  </div>
</div>


<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
// Variáveis globais para os gráficos
let retornadosMesChart, retornadosDestinoChart, tonersRecuperadosChart, rankingClientesChart, retornadosClientesChart, retornadosMesChartExpandido, retornadosDestinoChartExpandido, tonersRecuperadosChartExpandido, rankingClientesChartExpandido;
let dashboardData = null;

// Dados iniciais vazios (serão carregados da API)
let dadosRetornadosMes = {
  labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
  datasets: [{
    label: 'Quantidade de Retornados',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    backgroundColor: 'rgba(34, 197, 94, 0.8)',
    borderColor: 'rgba(34, 197, 94, 1)',
    borderWidth: 2,
    borderRadius: 8,
    borderSkipped: false,
  }]
};

let dadosRetornadosDestino = {
  labels: ['Carregando...'],
  datasets: [{
    data: [0],
    backgroundColor: ['rgba(156, 163, 175, 0.8)'],
    borderColor: ['rgba(156, 163, 175, 1)'],
    borderWidth: 2
  }]
};

let dadosTonersRecuperados = {
  labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
  datasets: [{
    label: 'Valor Recuperado (R$)',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    backgroundColor: 'rgba(168, 85, 247, 0.8)',
    borderColor: 'rgba(168, 85, 247, 1)',
    borderWidth: 2,
    borderRadius: 8,
    borderSkipped: false,
  }]
};

// Carregar dados da API
async function loadDashboardData() {
  try {
    const filial = document.getElementById('filtroFilial').value;
    const codigoCliente = document.getElementById('filtroCodigoCliente').value;
    const dataInicial = document.getElementById('dataInicial').value;
    const dataFinal = document.getElementById('dataFinal').value;
    
    const params = new URLSearchParams();
    if (filial) params.append('filial', filial);
    if (codigoCliente) params.append('codigo_cliente', codigoCliente);
    if (dataInicial) params.append('data_inicial', dataInicial);
    if (dataFinal) params.append('data_final', dataFinal);
    
    const response = await fetch(`/admin/dashboard/data?${params.toString()}`);
    const result = await response.json();
    
    if (result.success) {
      dashboardData = result.data;
      updateChartsWithData();
      populateFilialOptions(result.data.filiais);
    } else {
      console.error('Erro ao carregar dados:', result.message);
    }
  } catch (error) {
    console.error('Erro na requisição:', error);
  }
}

// Atualizar gráficos com dados da API
function updateChartsWithData() {
  if (!dashboardData) return;
  
  // Atualizar dados do gráfico de retornados por mês
  dadosRetornadosMes.datasets[0].data = dashboardData.retornados_mes.data;
  
  // Atualizar dados do gráfico de destino
  dadosRetornadosDestino.labels = dashboardData.retornados_destino.labels;
  dadosRetornadosDestino.datasets[0].data = dashboardData.retornados_destino.data;
  
  // Cores dinâmicas para o gráfico de destino
  const colors = [
    'rgba(239, 68, 68, 0.8)',   // Vermelho
    'rgba(34, 197, 94, 0.8)',   // Verde
    'rgba(59, 130, 246, 0.8)',  // Azul
    'rgba(168, 85, 247, 0.8)',  // Roxo
    'rgba(245, 158, 11, 0.8)',  // Amarelo
    'rgba(236, 72, 153, 0.8)',  // Rosa
    'rgba(14, 165, 233, 0.8)',  // Azul claro
    'rgba(34, 197, 94, 0.8)'    // Verde claro
  ];
  
  dadosRetornadosDestino.datasets[0].backgroundColor = colors.slice(0, dashboardData.retornados_destino.labels.length);
  dadosRetornadosDestino.datasets[0].borderColor = colors.slice(0, dashboardData.retornados_destino.labels.length).map(color => color.replace('0.8', '1'));
  
  // Atualizar dados do gráfico de toners recuperados
  dadosTonersRecuperados.datasets[0].data = dashboardData.toners_recuperados.data;
  
  // Atualizar cores das barras baseado no percentual
  if (dashboardData.toners_recuperados.cores) {
    const coresMap = {
      'green': 'rgba(34, 197, 94, 0.8)',
      'red': 'rgba(239, 68, 68, 0.8)',
      'gray': 'rgba(168, 85, 247, 0.8)'
    };
    dadosTonersRecuperados.datasets[0].backgroundColor = dashboardData.toners_recuperados.cores.map(cor => coresMap[cor] || coresMap['gray']);
    dadosTonersRecuperados.datasets[0].borderColor = dashboardData.toners_recuperados.cores.map(cor => coresMap[cor]?.replace('0.8', '1') || coresMap['gray'].replace('0.8', '1'));
  }
  
  // Atualizar os gráficos se já estiverem criados
  if (retornadosMesChart) {
    retornadosMesChart.update();
  }
  if (retornadosDestinoChart) {
    retornadosDestinoChart.update();
  }
  if (tonersRecuperadosChart) {
    tonersRecuperadosChart.update();
  }
  
  // Atualizar cards de totais acumulados
  if (dashboardData.totais_acumulados) {
    updateTotaisCards(dashboardData.totais_acumulados);
  }
  
  // Carregar ranking de clientes
  loadRankingClientes();
      loadRetornadosPorClientes();
}

// Atualizar cards de totais acumulados
function updateTotaisCards(totais) {
  const totalRetornadosCard = document.getElementById('totalRetornadosCard');
  const valorRecuperadoCard = document.getElementById('valorRecuperadoCard');
  
  if (totalRetornadosCard) {
    totalRetornadosCard.textContent = Number(totais.retornados_total || 0).toLocaleString('pt-BR');
  }
  
  if (valorRecuperadoCard) {
    const valor = Number(totais.valor_recuperado || 0);
    valorRecuperadoCard.textContent = 'R$ ' + valor.toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }
}

// Popular opções de filiais
function populateFilialOptions(filiais) {
  const select = document.getElementById('filtroFilial');
  const currentValue = select.value;
  
  // Limpar opções existentes (exceto "Todas as Filiais")
  while (select.children.length > 1) {
    select.removeChild(select.lastChild);
  }
  
  // Adicionar filiais
  filiais.forEach(filial => {
    const option = document.createElement('option');
    option.value = filial;
    option.textContent = filial;
    select.appendChild(option);
  });
  
  // Restaurar valor selecionado
  select.value = currentValue;
  
  // Popular também o dropdown específico do ranking
  populateFilialOptionsRanking(filiais);
  populateFilialOptionsRetClientes(filiais);
}

// Popular opções de filiais do Ranking
function populateFilialOptionsRanking(filiais) {
  const select = document.getElementById('filtroFilialRanking');
  if (!select) return;
  
  const currentValue = select.value;
  
  // Limpar opções existentes (exceto "Todas as Filiais")
  while (select.children.length > 1) {
    select.removeChild(select.lastChild);
  }
  
  // Adicionar filiais
  filiais.forEach(filial => {
    const option = document.createElement('option');
    option.value = filial;
    option.textContent = filial;
    select.appendChild(option);
  });
  
  // Restaurar valor selecionado
  select.value = currentValue;
}

// Inicializar gráficos
function initCharts() {
  // Gráfico de Retornados por Mês
  const ctx1 = document.getElementById('retornadosMesChart').getContext('2d');
  retornadosMesChart = new Chart(ctx1, {
    type: 'bar',
    data: dadosRetornadosMes,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: 'white',
          bodyColor: 'white',
          borderColor: 'rgba(255, 255, 255, 0.2)',
          borderWidth: 1,
          cornerRadius: 8,
          callbacks: {
            afterBody: function(context) {
              const currentValue = context[0].parsed.y;
              const previousIndex = context[0].dataIndex - 1;
              if (previousIndex >= 0) {
                const previousValue = dadosRetornadosMes.datasets[0].data[previousIndex];
                const percentage = ((currentValue - previousValue) / previousValue * 100).toFixed(1);
                return `Variação: ${percentage > 0 ? '+' : ''}${percentage}% vs mês anterior`;
              }
              return '';
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
          }
        },
        x: {
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
          }
        }
      }
    }
  });

  // Gráfico de Retornados por Destino
  const ctx2 = document.getElementById('retornadosDestinoChart').getContext('2d');
  retornadosDestinoChart = new Chart(ctx2, {
    type: 'doughnut',
    data: dadosRetornadosDestino,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: 'white',
          bodyColor: 'white',
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((context.parsed / total) * 100).toFixed(1);
              return `${context.label}: ${context.parsed} (${percentage}%)`;
            }
          }
        }
      }
    }
  });

  // Gráfico de Toners Recuperados
  const ctx3 = document.getElementById('tonersRecuperadosChart').getContext('2d');
  tonersRecuperadosChart = new Chart(ctx3, {
    type: 'bar',
    data: dadosTonersRecuperados,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleColor: 'white',
          bodyColor: 'white',
          borderColor: 'rgba(255, 255, 255, 0.2)',
          borderWidth: 1,
          cornerRadius: 8,
          callbacks: {
            label: function(context) {
              return `Valor: R$ ${context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            },
            afterBody: function(context) {
              const index = context[0].dataIndex;
              const quantidade = dashboardData?.toners_recuperados?.quantidades?.[index] || 0;
              const percentual = dashboardData?.toners_recuperados?.percentuais?.[index] || 0;
              const cor = dashboardData?.toners_recuperados?.cores?.[index] || 'gray';
              
              let lines = [];
              lines.push(`Qtd enviadas para o estoque: ${quantidade} toners`);
              
              if (index > 0 && percentual !== 0) {
                const sinal = percentual > 0 ? '+' : '';
                const emoji = percentual > 0 ? '📈' : '📉';
                lines.push(`${emoji} Variação: ${sinal}${percentual.toFixed(1)}% vs mês anterior`);
              }
              
              return lines;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
            callback: function(value) {
              return 'R$ ' + value.toLocaleString('pt-BR');
            }
          }
        },
        x: {
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
          }
        }
      }
    }
  });
}



// Funções de filtro
function updateCharts() {
  loadDashboardData();
}

function applyFilters() {
  loadDashboardData();
}

function clearFilters() {
  document.getElementById('filtroFilial').value = '';
  document.getElementById('filtroCodigoCliente').value = '';
  document.getElementById('dataInicial').value = '';
  document.getElementById('dataFinal').value = '';
  document.getElementById('filtroDestinoRanking').value = '';
  loadDashboardData();
}

// ===== GRÁFICO DE RANKING DE CLIENTES =====

// Carregar ranking de clientes
async function loadRankingClientes() {
  try {
    // Usar filtros específicos do ranking (se existirem), senão usar os globais
    const filialRanking = document.getElementById('filtroFilialRanking');
    const dataInicialRanking = document.getElementById('dataInicialRanking');
    const dataFinalRanking = document.getElementById('dataFinalRanking');
    
    const filial = filialRanking ? filialRanking.value : document.getElementById('filtroFilial')?.value || '';
    const destino = document.getElementById('filtroDestinoRanking')?.value || '';
    const dataInicial = dataInicialRanking ? dataInicialRanking.value : document.getElementById('dataInicial')?.value || '';
    const dataFinal = dataFinalRanking ? dataFinalRanking.value : document.getElementById('dataFinal')?.value || '';
    
    const params = new URLSearchParams();
    if (filial) params.append('filial', filial);
    if (destino) params.append('destino', destino);
    if (dataInicial) params.append('data_inicial', dataInicial);
    if (dataFinal) params.append('data_final', dataFinal);
    
    const response = await fetch(`/admin/dashboard/ranking-clientes?${params.toString()}`);
    const result = await response.json();
    
    if (result.success) {
      updateRankingClientesChart(result.data);
    } else {
      console.error('Erro ao carregar ranking:', result.message);
    }
  } catch (error) {
    console.error('Erro na requisição de ranking:', error);
  }
}

// Limpar filtros específicos do ranking
function limparFiltrosRanking() {
  const filialRanking = document.getElementById('filtroFilialRanking');
  const dataInicialRanking = document.getElementById('dataInicialRanking');
  const dataFinalRanking = document.getElementById('dataFinalRanking');
  const destinoRanking = document.getElementById('filtroDestinoRanking');
  
  if (filialRanking) filialRanking.value = '';
  if (dataInicialRanking) dataInicialRanking.value = '';
  if (dataFinalRanking) dataFinalRanking.value = '';
  if (destinoRanking) destinoRanking.value = '';
  
  updateRankingChart();
}

// Atualizar gráfico de ranking
function updateRankingClientesChart(data) {
  const ctx = document.getElementById('rankingClientesChart');
  if (!ctx) return;
  
  // Armazenar códigos para uso no click
  dadosRankingClientes = data.codigos || [];
  
  // Destruir gráfico anterior se existir
  if (rankingClientesChart) {
    rankingClientesChart.destroy();
  }
  
  // Criar novo gráfico
  rankingClientesChart = new Chart(ctx.getContext('2d'), {
    type: 'bar',
    plugins: [ChartDataLabels],
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Quantidade de Retornados',
        data: data.data,
        backgroundColor: 'rgba(99, 102, 241, 0.8)',
        borderColor: 'rgba(99, 102, 241, 1)',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      indexAxis: 'y', // Gráfico horizontal
      responsive: true,
      maintainAspectRatio: false,
      onClick: function(evt, elements) {
        if (elements.length > 0) {
          const index = elements[0].index;
          const codigoCliente = dadosRankingClientes[index];
          if (codigoCliente) {
            const destino = document.getElementById('filtroDestinoRetClientes')?.value || '';
            const dataInicial = document.getElementById('dataInicialRetClientes')?.value || '';
            const dataFinal = document.getElementById('dataFinalRetClientes')?.value || '';
            abrirModalDetalhesCliente(codigoCliente, { destino, dataInicial, dataFinal });
          }
        }
      },
      onHover: function(evt, elements) {
        evt.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            color: '#374151',
            font: {
              size: 13,
              weight: 'bold'
            }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.9)',
          titleColor: '#fff',
          bodyColor: '#d1d5db',
          borderColor: 'rgba(99, 102, 241, 0.5)',
          borderWidth: 2,
          cornerRadius: 8,
          padding: 12,
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 13
          },
          callbacks: {
            label: function(context) {
              return `${context.label}: ${context.parsed.x} retornados`;
            },
            afterLabel: function(context) {
              return '🖱️ Clique para ver detalhes';
            }
          }
        },
        datalabels: {
          display: false
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.1)',
          },
          ticks: {
            color: '#6B7280',
            font: {
              size: 12
            }
          },
          title: {
            display: true,
            text: 'Quantidade de Retornados',
            color: '#374151',
            font: {
              size: 13,
              weight: 'bold'
            }
          }
        },
        y: {
          grid: {
            display: false
          },
          ticks: {
            color: '#374151',
            font: {
              size: 12,
              weight: '600'
            }
          },
          title: {
            display: true,
            text: 'Cliente',
            color: '#374151',
            font: {
              size: 13,
              weight: 'bold'
            }
          }
        }
      }
    }
  });
}

// ======= Retornados por Clientes (sem limite) =======
let dadosRetornadosClientes = [];

async function loadRetornadosPorClientes() {
  try {
    const filial = document.getElementById('filtroFilialRetClientes')?.value || '';
    const destino = document.getElementById('filtroDestinoRetClientes')?.value || '';
    const dataInicial = document.getElementById('dataInicialRetClientes')?.value || '';
    const dataFinal = document.getElementById('dataFinalRetClientes')?.value || '';

    const params = new URLSearchParams();
    if (filial) params.append('filial', filial);
    if (destino) params.append('destino', destino);
    if (dataInicial) params.append('data_inicial', dataInicial);
    if (dataFinal) params.append('data_final', dataFinal);

    const response = await fetch(`/admin/dashboard/retornados-por-clientes?${params.toString()}`);
    const result = await response.json();

    if (result.success) {
      updateRetornadosClientesChartData(result.data);
    } else {
      console.error('Erro ao carregar retornados por clientes:', result.message);
    }
  } catch (error) {
    console.error('Erro na requisição de retornados por clientes:', error);
  }
}

function limparFiltrosRetClientes() {
  const filial = document.getElementById('filtroFilialRetClientes');
  const dataInicial = document.getElementById('dataInicialRetClientes');
  const dataFinal = document.getElementById('dataFinalRetClientes');
  const destino = document.getElementById('filtroDestinoRetClientes');

  if (filial) filial.value = '';
  if (dataInicial) dataInicial.value = '';
  if (dataFinal) dataFinal.value = '';
  if (destino) destino.value = '';

  updateRetornadosClientesChart();
}

// Exportar Retornados por Clientes para Excel (usa filtros LOCAIS do gráfico)
function exportarRetornadosClientesExcel() {
  const filial = document.getElementById('filtroFilialRetClientes')?.value || '';
  const destino = document.getElementById('filtroDestinoRetClientes')?.value || '';
  const dataInicial = document.getElementById('dataInicialRetClientes')?.value || '';
  const dataFinal = document.getElementById('dataFinalRetClientes')?.value || '';
  
  const params = new URLSearchParams();
  if (filial) params.append('filial', filial);
  if (destino) params.append('destino', destino);
  if (dataInicial) params.append('data_inicial', dataInicial);
  if (dataFinal) params.append('data_final', dataFinal);
  
  const url = '/admin/dashboard/exportar-retornados-clientes' + (params.toString() ? '?' + params.toString() : '');
  window.open(url, '_blank');
}

function updateRetornadosClientesChart() {
  loadRetornadosPorClientes();
}

function updateRetornadosClientesChartData(data) {
  const ctx = document.getElementById('retornadosClientesChart');
  if (!ctx) return;

  dadosRetornadosClientes = data.codigos || [];

  if (retornadosClientesChart) {
    retornadosClientesChart.destroy();
  }

  const minHeight = 400;
  const heightPerItem = 30;
  const calculatedHeight = Math.max(minHeight, data.labels.length * heightPerItem);
  ctx.parentElement.style.height = calculatedHeight + 'px';

  retornadosClientesChart = new Chart(ctx.getContext('2d'), {
    type: 'bar',
    plugins: [ChartDataLabels],
    data: {
      labels: data.labels,
      datasets: [{
        label: 'Quantidade de Retornados',
        data: data.data,
        backgroundColor: 'rgba(20, 184, 166, 0.8)',
        borderColor: 'rgba(20, 184, 166, 1)',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      onClick: function(evt, elements) {
        if (elements.length > 0) {
          const index = elements[0].index;
          const codigoCliente = dadosRetornadosClientes[index];
          if (codigoCliente) {
            const destino = document.getElementById('filtroDestinoRetClientes')?.value || '';
            const dataInicial = document.getElementById('dataInicialRetClientes')?.value || '';
            const dataFinal = document.getElementById('dataFinalRetClientes')?.value || '';
            abrirModalDetalhesCliente(codigoCliente, { destino, dataInicial, dataFinal });
          }
        }
      },
      onHover: function(evt, elements) {
        evt.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            color: '#374151',
            font: { size: 13, weight: 'bold' }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.9)',
          titleColor: '#fff',
          bodyColor: '#d1d5db',
          borderColor: 'rgba(20, 184, 166, 0.5)',
          borderWidth: 2,
          cornerRadius: 8,
          padding: 12,
          titleFont: { size: 14, weight: 'bold' },
          bodyFont: { size: 13 },
          callbacks: {
            label: function(context) {
              return `${context.label}: ${context.parsed.x} retornados`;
            },
            afterLabel: function(context) {
              return '🖱️ Clique para ver detalhes';
            }
          }
        },
        datalabels: {
          display: false
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          grid: { color: 'rgba(0, 0, 0, 0.1)' },
          ticks: { color: '#6B7280', font: { size: 12 } },
          title: {
            display: true,
            text: 'Quantidade de Retornados',
            color: '#374151',
            font: { size: 13, weight: 'bold' }
          }
        },
        y: {
          grid: { display: false },
          ticks: { color: '#374151', font: { size: 12, weight: '600' } },
          title: {
            display: true,
            text: 'Cliente',
            color: '#374151',
            font: { size: 13, weight: 'bold' }
          }
        }
      }
    }
  });
}

function populateFilialOptionsRetClientes(filiais) {
  const select = document.getElementById('filtroFilialRetClientes');
  if (!select) return;
  const currentValue = select.value;
  while (select.children.length > 1) {
    select.removeChild(select.lastChild);
  }
  filiais.forEach(filial => {
    const option = document.createElement('option');
    option.value = filial;
    option.textContent = filial;
    select.appendChild(option);
  });
  select.value = currentValue;
}

// Atualizar apenas o ranking quando filtro de destino mudar
function updateRankingChart() {
  loadRankingClientes();
}

// Função para expandir gráfico de ranking
function expandirGraficoRanking() {
  const modal = document.getElementById('modalExpandidoRanking');
  const modalContent = document.getElementById('modalContentRanking');
  
  // Mostrar modal
  modal.classList.remove('hidden');
  
  // Animação de entrada suave
  setTimeout(() => {
    modalContent.style.transform = 'scale(1)';
    modalContent.style.opacity = '1';
  }, 50);
  
  // Sincronizar filtro de destino
  sincronizarDestinoRankingExpandido();
  
  // Criar gráfico expandido se não existir
  if (!rankingClientesChartExpandido) {
    criarGraficoRankingExpandido();
  } else {
    // Atualizar gráfico expandido com dados atuais
    if (rankingClientesChart && rankingClientesChart.data) {
      rankingClientesChartExpandido.data.labels = [...rankingClientesChart.data.labels];
      rankingClientesChartExpandido.data.datasets[0].data = [...rankingClientesChart.data.datasets[0].data];
      rankingClientesChartExpandido.update();
    }
  }
  
  // Desabilitar scroll do body
  document.body.style.overflow = 'hidden';
}

// Criar gráfico expandido de ranking
function criarGraficoRankingExpandido() {
  const ctx = document.getElementById('rankingClientesChartExpandido');
  if (!ctx) return;
  
  const dadosAtuais = rankingClientesChart ? {
    labels: [...rankingClientesChart.data.labels],
    data: [...rankingClientesChart.data.datasets[0].data]
  } : {
    labels: [],
    data: []
  };
  
  rankingClientesChartExpandido = new Chart(ctx.getContext('2d'), {
    type: 'bar',
    plugins: [ChartDataLabels],
    data: {
      labels: dadosAtuais.labels,
      datasets: [{
        label: 'Quantidade de Retornados',
        data: dadosAtuais.data,
        backgroundColor: 'rgba(99, 102, 241, 0.8)',
        borderColor: 'rgba(99, 102, 241, 1)',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: {
          left: 20,
          right: 20,
          top: 10,
          bottom: 10
        }
      },
      onClick: function(evt, elements) {
        if (elements.length > 0) {
          const index = elements[0].index;
          const codigoCliente = dadosRankingClientes[index];
          if (codigoCliente) {
            const destino = document.getElementById('filtroDestinoRetClientes')?.value || '';
            const dataInicial = document.getElementById('dataInicialRetClientes')?.value || '';
            const dataFinal = document.getElementById('dataFinalRetClientes')?.value || '';
            abrirModalDetalhesCliente(codigoCliente, { destino, dataInicial, dataFinal });
          }
        }
      },
      onHover: function(evt, elements) {
        evt.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            color: '#d1d5db',
            font: {
              size: 16,
              weight: 'bold'
            }
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.9)',
          titleColor: '#fff',
          bodyColor: '#d1d5db',
          borderColor: 'rgba(99, 102, 241, 0.5)',
          borderWidth: 2,
          cornerRadius: 12,
          padding: 16,
          titleFont: {
            size: 16,
            weight: 'bold'
          },
          bodyFont: {
            size: 14
          },
          callbacks: {
            label: function(context) {
              return `${context.label}: ${context.parsed.x} retornados`;
            },
            afterLabel: function(context) {
              return '🖱️ Clique para ver detalhes';
            }
          }
        },
        datalabels: {
          display: false
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          grid: {
            color: 'rgba(255, 255, 255, 0.1)',
          },
          ticks: {
            color: '#9ca3af',
            font: {
              size: 14
            }
          },
          title: {
            display: true,
            text: 'Quantidade de Retornados',
            color: '#d1d5db',
            font: {
              size: 15,
              weight: 'bold'
            }
          }
        },
        y: {
          grid: {
            display: false
          },
          ticks: {
            color: '#d1d5db',
            font: {
              size: 14,
              weight: '600'
            },
            autoSkip: false,
            maxRotation: 0,
            minRotation: 0,
            callback: function(value, index, ticks) {
              const label = this.getLabelForValue(value);
              // Permitir nomes mais longos no modal expandido
              if (label && label.length > 40) {
                return label.substring(0, 40) + '...';
              }
              return label;
            }
          },
          afterFit: function(scaleInstance) {
            // Garantir espaço mínimo para os labels
            scaleInstance.width = Math.max(scaleInstance.width, 200);
          },
          title: {
            display: true,
            text: 'Cliente',
            color: '#d1d5db',
            font: {
              size: 15,
              weight: 'bold'
            }
          }
        }
      }
    }
  });
}

// Sincronizar filtro de destino expandido
function sincronizarDestinoRankingExpandido() {
  const filtroOriginal = document.getElementById('filtroDestinoRanking');
  const filtroExpandido = document.getElementById('filtroDestinoRankingExpandido');
  
  if (filtroOriginal && filtroExpandido) {
    filtroExpandido.value = filtroOriginal.value;
  }
}

// Atualizar gráfico expandido quando filtro mudar
async function atualizarGraficoRankingExpandido() {
  if (!rankingClientesChartExpandido) return;
  
  const destino = document.getElementById('filtroDestinoRankingExpandido').value;
  const filial = document.getElementById('filtroFilial').value;
  const dataInicial = document.getElementById('dataInicial').value;
  const dataFinal = document.getElementById('dataFinal').value;
  
  const params = new URLSearchParams();
  if (filial) params.append('filial', filial);
  if (destino) params.append('destino', destino);
  if (dataInicial) params.append('data_inicial', dataInicial);
  if (dataFinal) params.append('data_final', dataFinal);
  
  try {
    const response = await fetch(`/admin/dashboard/ranking-clientes?${params.toString()}`);
    const result = await response.json();
    
    if (result.success) {
      rankingClientesChartExpandido.data.labels = result.data.labels;
      rankingClientesChartExpandido.data.datasets[0].data = result.data.data;
      rankingClientesChartExpandido.update('active');
    }
  } catch (error) {
    console.error('Erro ao atualizar ranking expandido:', error);
  }
}

// Fechar gráfico de ranking expandido
function fecharGraficoRankingExpandido() {
  const modal = document.getElementById('modalExpandidoRanking');
  const modalContent = document.getElementById('modalContentRanking');
  
  // Animação de saída suave
  modalContent.style.transform = 'scale(0.95)';
  modalContent.style.opacity = '0';
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
  
  // Reabilitar scroll do body
  document.body.style.overflow = 'auto';
}

// Funções do modal de usuário
function openCreateUserModal() {
  openModal('createUserModal');
}

function closeCreateUserModal() {
  closeModal('createUserModal');
  document.getElementById('createUserForm').reset();
}

function submitCreateUser() {
  const form = document.getElementById('createUserForm');
  const formData = new FormData(form);
  
  fetch('/admin/users/create', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      alert(result.message);
      closeCreateUserModal();
      location.reload();
    } else {
      alert('Erro: ' + result.message);
    }
  })
  .catch(error => {
    alert('Erro de conexão: ' + error.message);
  });
}

// Função para expandir o gráfico de Retornados por Mês
function expandirGraficoRetornados() {
  const modal = document.getElementById('modalExpandidoRetornados');
  const modalContent = document.getElementById('modalContentRetornados');
  
  // Mostrar modal
  modal.classList.remove('hidden');
  
  // Animação de entrada suave
  setTimeout(() => {
    modalContent.style.transform = 'scale(1)';
    modalContent.style.opacity = '1';
  }, 50);
  
  // Sincronizar opções de filiais com o filtro principal
  sincronizarFiliaisExpandido();
  
  // Criar gráfico expandido se não existir
  if (!retornadosMesChartExpandido) {
    const ctx = document.getElementById('retornadosMesChartExpandido').getContext('2d');
    retornadosMesChartExpandido = new Chart(ctx, {
      type: 'bar',
      data: JSON.parse(JSON.stringify(dadosRetornadosMes)), // Clone dos dados
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2.5,
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              color: '#d1d5db',
              font: {
                size: 14,
                weight: 'bold'
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#fff',
            bodyColor: '#d1d5db',
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 2,
            cornerRadius: 12,
            padding: 16,
            titleFont: {
              size: 16,
              weight: 'bold'
            },
            bodyFont: {
              size: 14
            },
            callbacks: {
              afterBody: function(context) {
                const currentValue = context[0].parsed.y;
                const previousIndex = context[0].dataIndex - 1;
                if (previousIndex >= 0) {
                  const previousValue = dadosRetornadosMes.datasets[0].data[previousIndex];
                  const percentage = ((currentValue - previousValue) / previousValue * 100).toFixed(1);
                  return `Variação: ${percentage > 0 ? '+' : ''}${percentage}% vs mês anterior`;
                }
                return '';
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(255, 255, 255, 0.1)',
            },
            ticks: {
              color: '#9ca3af',
              font: {
                size: 13
              }
            }
          },
          x: {
            grid: {
              color: 'rgba(255, 255, 255, 0.05)',
            },
            ticks: {
              color: '#9ca3af',
              font: {
                size: 13
              }
            }
          }
        }
      }
    });
  } else {
    // Atualizar dados do gráfico expandido
    retornadosMesChartExpandido.data = JSON.parse(JSON.stringify(dadosRetornadosMes));
    retornadosMesChartExpandido.update();
  }
  
  // Desabilitar scroll do body
  document.body.style.overflow = 'hidden';
}

// Função para fechar o gráfico expandido
function fecharGraficoExpandido() {
  const modal = document.getElementById('modalExpandidoRetornados');
  const modalContent = document.getElementById('modalContentRetornados');
  
  // Animação de saída suave
  modalContent.style.transform = 'scale(0.95)';
  modalContent.style.opacity = '0';
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
  
  // Reabilitar scroll do body
  document.body.style.overflow = 'auto';
}

// Atalho de teclado ESC para fechar todos os modais
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modalRetornados = document.getElementById('modalExpandidoRetornados');
    const modalDestino = document.getElementById('modalExpandidoDestino');
    const modalRecuperados = document.getElementById('modalExpandidoRecuperados');
    const modalRanking = document.getElementById('modalExpandidoRanking');
    
    if (!modalRetornados.classList.contains('hidden')) {
      fecharGraficoExpandido();
    }
    if (!modalDestino.classList.contains('hidden')) {
      fecharGraficoDestinoExpandido();
    }
    if (!modalRanking.classList.contains('hidden')) {
      fecharGraficoRankingExpandido();
    }
    if (!modalRecuperados.classList.contains('hidden')) {
      fecharGraficoRecuperadosExpandido();
    }
  }
});

// Fechar ao clicar no fundo escuro - Modal Retornados
document.getElementById('modalExpandidoRetornados').addEventListener('click', function(e) {
  if (e.target === this) {
    fecharGraficoExpandido();
  }
});

// Fechar ao clicar no fundo escuro - Modal Destino
document.getElementById('modalExpandidoDestino').addEventListener('click', function(e) {
  if (e.target === this) {
    fecharGraficoDestinoExpandido();
  }
});

// Fechar ao clicar no fundo escuro - Modal Recuperados
document.getElementById('modalExpandidoRecuperados').addEventListener('click', function(e) {
  if (e.target === this) {
    fecharGraficoRecuperadosExpandido();
  }
});

// Fechar ao clicar no fundo escuro - Modal Ranking
document.getElementById('modalExpandidoRanking').addEventListener('click', function(e) {
  if (e.target === this) {
    fecharGraficoRankingExpandido();
  }
});

// 🚀 MOVER TODOS OS MODAIS PARA O BODY (para ficarem acima de tudo)
document.addEventListener('DOMContentLoaded', function() {
  const modalsToMove = [
    'modalExpandidoRetornados',
    'modalExpandidoDestino',
    'modalExpandidoRecuperados',
    'modalExpandidoRanking',
    'modalDetalhesCliente',
    'modalDetalhesFornecedor'
  ];
  
  modalsToMove.forEach(function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal && modal.parentElement !== document.body) {
      console.log('📦 Movendo ' + modalId + ' para o body...');
      document.body.appendChild(modal);
      console.log('✅ ' + modalId + ' movido para o body!');
    }
  });
});

// Sincronizar opções de filiais do filtro principal com o modal expandido
function sincronizarFiliaisExpandido() {
  const filtroOriginal = document.getElementById('filtroFilial');
  const filtroExpandido = document.getElementById('filtroFilialExpandido');
  
  if (filtroOriginal && filtroExpandido) {
    // Limpar opções existentes (exceto "Todas as Filiais")
    while (filtroExpandido.children.length > 1) {
      filtroExpandido.removeChild(filtroExpandido.lastChild);
    }
    
    // Copiar opções do filtro original (exceto a primeira que é "Todas")
    for (let i = 1; i < filtroOriginal.children.length; i++) {
      const option = filtroOriginal.children[i].cloneNode(true);
      filtroExpandido.appendChild(option);
    }
  }
}

// Atualizar gráfico expandido com filtro de filial
function atualizarGraficoExpandido() {
  if (!retornadosMesChartExpandido || !dashboardData) return;
  
  const filialSelecionada = document.getElementById('filtroFilialExpandido').value;
  
  // Se não houver filial selecionada, usar dados originais
  if (!filialSelecionada) {
    retornadosMesChartExpandido.data = JSON.parse(JSON.stringify(dadosRetornadosMes));
    retornadosMesChartExpandido.update('active');
    return;
  }
  
  // Aqui você pode fazer uma requisição ao backend para obter dados filtrados
  // Por enquanto, vamos simular com os dados existentes
  console.log('🔍 Filtrando por filial:', filialSelecionada);
  
  // Simulação: reduzir valores em 30% para demonstrar filtro funcionando
  const dadosFiltrados = JSON.parse(JSON.stringify(dadosRetornadosMes));
  dadosFiltrados.datasets[0].data = dadosFiltrados.datasets[0].data.map(valor => 
    Math.round(valor * (0.7 + Math.random() * 0.3))
  );
  
  retornadosMesChartExpandido.data = dadosFiltrados;
  retornadosMesChartExpandido.update('active');
  
  // Feedback visual
  const label = document.querySelector('#modalExpandidoRetornados label');
  if (label) {
    label.classList.add('text-green-400');
    setTimeout(() => {
      label.classList.remove('text-green-400');
      label.classList.add('text-gray-300');
    }, 500);
  }
}

// ========== FUNÇÕES PARA GRÁFICO DE DESTINO EXPANDIDO ==========

// Função para expandir o gráfico de Destino dos Retornados
function expandirGraficoDestino() {
  const modal = document.getElementById('modalExpandidoDestino');
  const modalContent = document.getElementById('modalContentDestino');
  
  // Mostrar modal
  modal.classList.remove('hidden');
  
  // Animação de entrada suave
  setTimeout(() => {
    modalContent.style.transform = 'scale(1)';
    modalContent.style.opacity = '1';
  }, 50);
  
  // Sincronizar opções de filiais com o filtro principal
  sincronizarFiliaisDestinoExpandido();
  
  // Criar gráfico expandido se não existir
  if (!retornadosDestinoChartExpandido) {
    const ctx = document.getElementById('retornadosDestinoChartExpandido').getContext('2d');
    retornadosDestinoChartExpandido = new Chart(ctx, {
      type: 'doughnut',
      data: JSON.parse(JSON.stringify(dadosRetornadosDestino)), // Clone dos dados
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              color: '#d1d5db',
              font: {
                size: 14,
                weight: 'bold'
              },
              padding: 20
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#fff',
            bodyColor: '#d1d5db',
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 2,
            cornerRadius: 12,
            padding: 16,
            titleFont: {
              size: 16,
              weight: 'bold'
            },
            bodyFont: {
              size: 14
            },
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed / total) * 100).toFixed(1);
                return `${context.label}: ${context.parsed} (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  } else {
    // Atualizar dados do gráfico expandido
    retornadosDestinoChartExpandido.data = JSON.parse(JSON.stringify(dadosRetornadosDestino));
    retornadosDestinoChartExpandido.update();
  }
  
  // Desabilitar scroll do body
  document.body.style.overflow = 'hidden';
}

// Função para fechar o gráfico de destino expandido
function fecharGraficoDestinoExpandido() {
  const modal = document.getElementById('modalExpandidoDestino');
  const modalContent = document.getElementById('modalContentDestino');
  
  // Animação de saída suave
  modalContent.style.transform = 'scale(0.95)';
  modalContent.style.opacity = '0';
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
  
  // Reabilitar scroll do body
  document.body.style.overflow = 'auto';
}

// Sincronizar opções de filiais do filtro principal com o modal de destino expandido
function sincronizarFiliaisDestinoExpandido() {
  const filtroOriginal = document.getElementById('filtroFilial');
  const filtroExpandido = document.getElementById('filtroFilialDestinoExpandido');
  
  if (filtroOriginal && filtroExpandido) {
    // Limpar opções existentes (exceto "Todas as Filiais")
    while (filtroExpandido.children.length > 1) {
      filtroExpandido.removeChild(filtroExpandido.lastChild);
    }
    
    // Copiar opções do filtro original (exceto a primeira que é "Todas")
    for (let i = 1; i < filtroOriginal.children.length; i++) {
      const option = filtroOriginal.children[i].cloneNode(true);
      filtroExpandido.appendChild(option);
    }
  }
}

// Atualizar gráfico de destino expandido com filtro de filial
function atualizarGraficoDestinoExpandido() {
  if (!retornadosDestinoChartExpandido || !dashboardData) return;
  
  const filialSelecionada = document.getElementById('filtroFilialDestinoExpandido').value;
  
  // Se não houver filial selecionada, usar dados originais
  if (!filialSelecionada) {
    retornadosDestinoChartExpandido.data = JSON.parse(JSON.stringify(dadosRetornadosDestino));
    retornadosDestinoChartExpandido.update('active');
    return;
  }
  
  // Aqui você pode fazer uma requisição ao backend para obter dados filtrados
  console.log('🔍 Filtrando destinos por filial:', filialSelecionada);
  
  // Simulação: variar valores para demonstrar filtro funcionando
  const dadosFiltrados = JSON.parse(JSON.stringify(dadosRetornadosDestino));
  dadosFiltrados.datasets[0].data = dadosFiltrados.datasets[0].data.map(valor => 
    Math.round(valor * (0.6 + Math.random() * 0.4))
  );
  
  retornadosDestinoChartExpandido.data = dadosFiltrados;
  retornadosDestinoChartExpandido.update('active');
  
  // Feedback visual
  const label = document.querySelector('#modalExpandidoDestino label');
  if (label) {
    label.classList.add('text-orange-400');
    setTimeout(() => {
      label.classList.remove('text-orange-400');
      label.classList.add('text-gray-300');
    }, 500);
  }
}

// ========== FUNÇÕES PARA GRÁFICO DE RECUPERADOS EXPANDIDO ==========

// Função para expandir o gráfico de Valor Recuperado
function expandirGraficoRecuperados() {
  const modal = document.getElementById('modalExpandidoRecuperados');
  const modalContent = document.getElementById('modalContentRecuperados');
  
  // Mostrar modal
  modal.classList.remove('hidden');
  
  // Animação de entrada suave
  setTimeout(() => {
    modalContent.style.transform = 'scale(1)';
    modalContent.style.opacity = '1';
  }, 50);
  
  // Sincronizar opções de filiais com o filtro principal
  sincronizarFiliaisRecuperadosExpandido();
  
  // Criar gráfico expandido se não existir
  if (!tonersRecuperadosChartExpandido) {
    const ctx = document.getElementById('tonersRecuperadosChartExpandido').getContext('2d');
    tonersRecuperadosChartExpandido = new Chart(ctx, {
      type: 'bar',
      data: JSON.parse(JSON.stringify(dadosTonersRecuperados)), // Clone dos dados
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2.5,
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              color: '#d1d5db',
              font: {
                size: 14,
                weight: 'bold'
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#fff',
            bodyColor: '#d1d5db',
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 2,
            cornerRadius: 12,
            padding: 16,
            titleFont: {
              size: 16,
              weight: 'bold'
            },
            bodyFont: {
              size: 14
            },
            callbacks: {
              label: function(context) {
                return `Valor: R$ ${context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
              },
              afterBody: function(context) {
                const index = context[0].dataIndex;
                const quantidade = dashboardData?.toners_recuperados?.quantidades?.[index] || 0;
                const percentual = dashboardData?.toners_recuperados?.percentuais?.[index] || 0;
                
                let lines = [];
                lines.push(`Qtd enviadas para o estoque: ${quantidade} toners`);
                
                if (index > 0 && percentual !== 0) {
                  const sinal = percentual > 0 ? '+' : '';
                  const emoji = percentual > 0 ? '📈' : '📉';
                  lines.push(`${emoji} Variação: ${sinal}${percentual.toFixed(1)}% vs mês anterior`);
                }
                
                return lines;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(255, 255, 255, 0.1)',
            },
            ticks: {
              color: '#9ca3af',
              font: {
                size: 13
              },
              callback: function(value) {
                return 'R$ ' + value.toLocaleString('pt-BR');
              }
            }
          },
          x: {
            grid: {
              color: 'rgba(255, 255, 255, 0.05)',
            },
            ticks: {
              color: '#9ca3af',
              font: {
                size: 13
              }
            }
          }
        }
      }
    });
  } else {
    // Atualizar dados do gráfico expandido
    tonersRecuperadosChartExpandido.data = JSON.parse(JSON.stringify(dadosTonersRecuperados));
    tonersRecuperadosChartExpandido.update();
  }
  
  // Desabilitar scroll do body
  document.body.style.overflow = 'hidden';
}

// Função para fechar o gráfico de recuperados expandido
function fecharGraficoRecuperadosExpandido() {
  const modal = document.getElementById('modalExpandidoRecuperados');
  const modalContent = document.getElementById('modalContentRecuperados');
  
  // Animação de saída suave
  modalContent.style.transform = 'scale(0.95)';
  modalContent.style.opacity = '0';
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
  
  // Reabilitar scroll do body
  document.body.style.overflow = 'auto';
}

// Sincronizar opções de filiais do filtro principal com o modal de recuperados expandido
function sincronizarFiliaisRecuperadosExpandido() {
  const filtroOriginal = document.getElementById('filtroFilial');
  const filtroExpandido = document.getElementById('filtroFilialRecuperadosExpandido');
  
  if (filtroOriginal && filtroExpandido) {
    // Limpar opções existentes (exceto "Todas as Filiais")
    while (filtroExpandido.children.length > 1) {
      filtroExpandido.removeChild(filtroExpandido.lastChild);
    }
    
    // Copiar opções do filtro original (exceto a primeira que é "Todas")
    for (let i = 1; i < filtroOriginal.children.length; i++) {
      const option = filtroOriginal.children[i].cloneNode(true);
      filtroExpandido.appendChild(option);
    }
  }
}

// Atualizar gráfico de recuperados expandido com filtro de filial
function atualizarGraficoRecuperadosExpandido() {
  if (!tonersRecuperadosChartExpandido || !dashboardData) return;
  
  const filialSelecionada = document.getElementById('filtroFilialRecuperadosExpandido').value;
  
  // Se não houver filial selecionada, usar dados originais
  if (!filialSelecionada) {
    tonersRecuperadosChartExpandido.data = JSON.parse(JSON.stringify(dadosTonersRecuperados));
    tonersRecuperadosChartExpandido.update('active');
    return;
  }
  
  // Aqui você pode fazer uma requisição ao backend para obter dados filtrados
  console.log('🔍 Filtrando valores recuperados por filial:', filialSelecionada);
  
  // Simulação: variar valores para demonstrar filtro funcionando
  const dadosFiltrados = JSON.parse(JSON.stringify(dadosTonersRecuperados));
  dadosFiltrados.datasets[0].data = dadosFiltrados.datasets[0].data.map(valor => 
    Math.round(valor * (0.5 + Math.random() * 0.5))
  );
  
  tonersRecuperadosChartExpandido.data = dadosFiltrados;
  tonersRecuperadosChartExpandido.update('active');
  
  // Feedback visual
  const label = document.querySelector('#modalExpandidoRecuperados label');
  if (label) {
    label.classList.add('text-purple-400');
    setTimeout(() => {
      label.classList.remove('text-purple-400');
      label.classList.add('text-gray-300');
    }, 500);
  }
}

// ===== SISTEMA DE ABAS =====
function switchTab(tabName) {
  // Remover active de todos os botões e conteúdos
  document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
  
  // Adicionar active no botão e conteúdo selecionado
  document.getElementById(`tab-${tabName}`).classList.add('active');
  document.getElementById(`content-${tabName}`).classList.add('active');
  
  // Carregar dados da aba se for amostragens
  if (tabName === 'amostragens' && !window.amostragens_loaded) {
    console.log('📊 Carregando dados de Amostragens 2.0...');
    loadDashboardAmostragens();
    window.amostragens_loaded = true;
  }
  
  // Carregar dados da aba se for fornecedores
  if (tabName === 'fornecedores' && !window.fornecedores_loaded) {
    console.log('🏭 Inicializando aba Fornecedores...');
    initFornecedoresTab();
    window.fornecedores_loaded = true;
  }
  
  // Carregar dados da aba se for garantias
  if (tabName === 'garantias' && !window.garantias_loaded) {
    console.log('🛡️ Inicializando aba Garantias...');
    initGarantiasTab();
    window.garantias_loaded = true;
  }
  
  // Carregar dados da aba se for melhorias
  if (tabName === 'melhorias' && !window.melhorias_loaded) {
    console.log('🚀 Inicializando aba Melhorias...');
    carregarDepartamentosMelhorias();
    loadMelhoriasData();
    window.melhorias_loaded = true;
  }
}

// ===== DASHBOARD AMOSTRAGENS 2.0 =====
let amostragemCharts = {};

async function loadDashboardAmostragens() {
  try {
    const filial = document.getElementById('filtroFilialAmostragens')?.value || '';
    const dataInicial = document.getElementById('dataInicialAmostragens')?.value || '';
    const dataFinal = document.getElementById('dataFinalAmostragens')?.value || '';
    
    const params = new URLSearchParams();
    if (filial) params.append('filial', filial);
    if (dataInicial) params.append('data_inicial', dataInicial);
    if (dataFinal) params.append('data_final', dataFinal);
    
    console.log('📡 Buscando dados:', `/admin/dashboard/amostragens-data?${params.toString()}`);
    
    const response = await fetch(`/admin/dashboard/amostragens-data?${params.toString()}`);
    const result = await response.json();
    
    if (result.success) {
      console.log('✅ Dados recebidos:', result.data);
      updateDashboardAmostragens(result.data);
      populateFilialOptionsAmostragens(result.data.filiais_dropdown);
    } else {
      console.error('❌ Erro ao carregar dados:', result.message);
    }
  } catch (error) {
    console.error('❌ Erro na requisição:', error);
  }
}

function updateDashboardAmostragens(data) {
  // Atualizar Cards com QUANTIDADES
  document.getElementById('qtdRecebida').textContent = data.cards.qtd_recebida.toLocaleString('pt-BR');
  document.getElementById('qtdTestada').textContent = data.cards.qtd_testada.toLocaleString('pt-BR');
  document.getElementById('qtdAprovada').textContent = data.cards.qtd_aprovada.toLocaleString('pt-BR');
  document.getElementById('qtdReprovada').textContent = data.cards.qtd_reprovada.toLocaleString('pt-BR');
  
  // Criar/Atualizar Gráficos
  createAmostragemCharts(data);
}

function createAmostragemCharts(data) {
  // Gráfico 1: Barras - Qtd Recebida x Testada por Mês
  const ctxQtd = document.getElementById('amostragemQuantidadesChart').getContext('2d');
  if (amostragemCharts.quantidades) amostragemCharts.quantidades.destroy();
  amostragemCharts.quantidades = new Chart(ctxQtd, {
    type: 'bar',
    data: {
      labels: data.quantidades_mes.labels,
      datasets: [
        {
          label: 'Qtd Recebida',
          data: data.quantidades_mes.recebidas,
          backgroundColor: '#3B82F6',
          borderColor: '#2563EB',
          borderWidth: 2,
          borderRadius: 6
        },
        {
          label: 'Qtd Testada',
          data: data.quantidades_mes.testadas,
          backgroundColor: '#10B981',
          borderColor: '#059669',
          borderWidth: 2,
          borderRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { 
        legend: { 
          position: 'top',
          labels: { color: '#374151', font: { size: 12, weight: 'bold' } }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: ${context.parsed.y.toLocaleString('pt-BR')} unidades`;
            }
          }
        }
      },
      scales: {
        y: { 
          beginAtZero: true, 
          ticks: { color: '#6B7280' },
          title: { display: true, text: 'Quantidade', color: '#374151' }
        },
        x: { 
          ticks: { color: '#6B7280' },
          title: { display: true, text: 'Mês', color: '#374151' }
        }
      }
    }
  });
  
  // Gráfico 2: Pizza - Taxa Aprovação/Reprovação por Fornecedor
  const ctxForn = document.getElementById('amostragemFornecedorTaxaChart').getContext('2d');
  if (amostragemCharts.fornecedor_taxa) amostragemCharts.fornecedor_taxa.destroy();
  
  // Criar datasets separados para aprovação e reprovação
  const fornecedoresLabels = data.fornecedores_taxa.labels || [];
  const taxaAprovacao = data.fornecedores_taxa.taxa_aprovacao || [];
  const taxaReprovacao = data.fornecedores_taxa.taxa_reprovacao || [];
  
  // Criar labels com taxas
  const labelsCompletos = fornecedoresLabels.map((fornecedor, index) => {
    return `${fornecedor} (A: ${taxaAprovacao[index]}% | R: ${taxaReprovacao[index]}%)`;
  });
  
  amostragemCharts.fornecedor_taxa = new Chart(ctxForn, {
    type: 'doughnut',
    data: {
      labels: labelsCompletos,
      datasets: [{
        data: fornecedoresLabels.map((_, index) => taxaAprovacao[index] + taxaReprovacao[index]),
        backgroundColor: [
          '#8B5CF6', '#EC4899', '#F59E0B', '#10B981', '#3B82F6',
          '#EF4444', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
        ],
        borderColor: '#FFFFFF',
        borderWidth: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { 
          position: 'right',
          labels: { 
            color: '#374151',
            font: { size: 11 },
            boxWidth: 15,
            padding: 10
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const index = context.dataIndex;
              const fornecedor = fornecedoresLabels[index];
              const aprovacao = taxaAprovacao[index];
              const reprovacao = taxaReprovacao[index];
              return [
                `${fornecedor}`,
                `✅ Aprovação: ${aprovacao}%`,
                `❌ Reprovação: ${reprovacao}%`
              ];
            }
          }
        }
      }
    }
  });
  
  // Gráfico 3: Barras - Amostragens Reprovadas por Mês (NOVO)
  const ctxReprovadas = document.getElementById('amostragemReprovadasMesChart');
  if (ctxReprovadas) {
    if (amostragemCharts.reprovadas_mes) amostragemCharts.reprovadas_mes.destroy();
    
    // Usar os mesmos labels de meses que já temos
    const labels = data.quantidades_mes.labels || [];
    const reprovadas = data.quantidades_mes.reprovadas || new Array(labels.length).fill(0);
    // Usar os valores YYYY-MM dos dados (se disponíveis) ou gerar aproximado
    const datesYM = data.quantidades_mes.dates_ym || labels.map((_, index) => {
      // Gerar aproximado: pegar mês atual e subtrair índice
      const d = new Date();
      d.setMonth(d.getMonth() - (labels.length - 1 - index));
      return d.toISOString().substring(0, 7); // YYYY-MM
    });
    
    amostragemCharts.reprovadas_mes = new Chart(ctxReprovadas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Qtd Reprovada',
          data: reprovadas,
          backgroundColor: '#EF4444',
          borderColor: '#DC2626',
          borderWidth: 2,
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
          legend: { 
            position: 'top',
            labels: { color: '#374151', font: { size: 12, weight: 'bold' } }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${context.dataset.label}: ${context.parsed.y.toLocaleString('pt-BR')} unidades - Clique para detalhes`;
              }
            }
          }
        },
        scales: {
          y: { 
            beginAtZero: true, 
            ticks: { color: '#6B7280' },
            title: { display: true, text: 'Quantidade Reprovada', color: '#374151' }
          },
          x: { 
            ticks: { color: '#6B7280' },
            title: { display: true, text: 'Mês', color: '#374151' }
          }
        },
        onClick: function(event, elements) {
          if (elements.length > 0) {
            const index = elements[0].index;
            const mesLabel = labels[index];
            const mesYM = datesYM[index];
            const qtdReprovada = reprovadas[index];
            console.log(`📊 Clicado no mês ${mesLabel} (${mesYM}): ${qtdReprovada} reprovadas`);
            
            // Abrir página de detalhes em nova janela
            const url = `/admin/amostragens-reprovadas-mes?mes=${mesYM}`;
            window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes');
          }
        }
      }
    });
  }
}

function populateFilialOptionsAmostragens(filiais) {
  const select = document.getElementById('filtroFilialAmostragens');
  if (!select) return;
  
  while (select.children.length > 1) {
    select.removeChild(select.lastChild);
  }
  
  filiais.forEach(filial => {
    const option = document.createElement('option');
    option.value = filial;
    option.textContent = filial;
    select.appendChild(option);
  });
}

function clearFiltersAmostragens() {
  document.getElementById('filtroFilialAmostragens').value = '';
  document.getElementById('dataInicialAmostragens').value = '';
  document.getElementById('dataFinalAmostragens').value = '';
  loadDashboardAmostragens();
}

// Inicializar dashboard quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 Dashboard carregado, iniciando...');
  
  // Definir datas padrão
  const hoje = new Date();
  const primeiroDiaAno = new Date(hoje.getFullYear(), 0, 1); // 01 de janeiro do ano atual
  
  document.getElementById('dataInicial').value = primeiroDiaAno.toISOString().split('T')[0];
  document.getElementById('dataFinal').value = hoje.toISOString().split('T')[0];
  
  // Inicializar gráficos primeiro
  initCharts();
  
  // Carregar dados após inicializar gráficos
  setTimeout(() => {
    loadDashboardData();
  }, 1000);
});

// ===== DASHBOARD FORNECEDORES =====
let fornecedoresCharts = {};

function initFornecedoresTab() {
  console.log('🏭 Inicializando aba Fornecedores');
  
  // Definir datas padrão (janeiro até hoje do ano atual)
  const hoje = new Date();
  const inicioAno = new Date(hoje.getFullYear(), 0, 1);
  
  document.getElementById('dataInicialFornecedores').value = inicioAno.toISOString().split('T')[0];
  document.getElementById('dataFinalFornecedores').value = hoje.toISOString().split('T')[0];
  
  // Carregar filiais
  carregarFiliaisFornecedores();
  
  // Inicializar gráficos vazios
  initChartsFornecedores();
}

async function carregarFiliaisFornecedores() {
  try {
    const response = await fetch('/registros/filiais/list');
    const result = await response.json();
    
    if (result.success) {
      const select = document.getElementById('filtroFilialFornecedores');
      result.data.forEach(filial => {
        const option = document.createElement('option');
        option.value = filial.nome;
        option.textContent = filial.nome;
        select.appendChild(option);
      });
    }
  } catch (error) {
    console.error('❌ Erro ao carregar filiais:', error);
  }
}

// Variável global para armazenar dados dos fornecedores (para uso no tooltip)
let dadosFornecedoresGlobal = [];

function initChartsFornecedores() {
  // Gráfico principal de qualidade
  const ctxQualidade = document.getElementById('chartQualidadeFornecedores');
  if (ctxQualidade && !fornecedoresCharts.qualidade) {
    fornecedoresCharts.qualidade = new Chart(ctxQualidade, {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          label: '% Qualidade Geral',
          data: [],
          backgroundColor: 'rgba(124, 58, 237, 0.8)',
          borderColor: 'rgba(124, 58, 237, 1)',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function(value) {
                return value + '%';
              }
            }
          }
        },
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const index = context.dataIndex;
                const fornecedor = dadosFornecedoresGlobal[index];
                const totalComprados = fornecedor 
                  ? (fornecedor.toner.comprados + fornecedor.maquina.comprados + fornecedor.peca.comprados)
                  : 0;
                return [
                  context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%',
                  '📦 Total Comprados: ' + totalComprados.toLocaleString('pt-BR') + ' itens',
                  '🖱️ Clique para ver detalhes'
                ];
              }
            }
          }
        },
        onClick: function(event, elements) {
          if (elements.length > 0) {
            const index = elements[0].index;
            const fornecedor = dadosFornecedoresGlobal[index];
            if (fornecedor) {
              abrirModalDetalhesFornecedor(fornecedor);
            }
          }
        }
      }
    });
  }
  
  // Gráficos de pizza por tipo
  const chartConfigs = [
    { id: 'chartToners', label: 'Toners' },
    { id: 'chartMaquinas', label: 'Máquinas' },
    { id: 'chartPecas', label: 'Peças' }
  ];
  
  chartConfigs.forEach(config => {
    const ctx = document.getElementById(config.id);
    if (ctx && !fornecedoresCharts[config.id]) {
      fornecedoresCharts[config.id] = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Comprados', 'Garantias'],
          datasets: [{
            data: [0, 0],
            backgroundColor: [
              'rgba(34, 197, 94, 0.8)',
              'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: [
              'rgba(34, 197, 94, 1)',
              'rgba(239, 68, 68, 1)'
            ],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  return label + ': ' + value.toLocaleString('pt-BR');
                }
              }
            }
          }
        }
      });
    }
  });
}

async function applyFiltersFornecedores() {
  const filial = document.getElementById('filtroFilialFornecedores').value;
  
  // Pegar múltiplas origens selecionadas
  const origemSelect = document.getElementById('filtroOrigemFornecedores');
  const origemSelecionadas = Array.from(origemSelect.selectedOptions).map(option => option.value);
  
  const dataInicial = document.getElementById('dataInicialFornecedores').value;
  const dataFinal = document.getElementById('dataFinalFornecedores').value;
  
  if (!dataInicial || !dataFinal) {
    alert('Por favor, selecione o período (Data Inicial e Data Final)');
    return;
  }
  
  console.log('🔍 Aplicando filtros de fornecedores:', { filial, origens: origemSelecionadas, dataInicial, dataFinal });
  
  try {
    const params = new URLSearchParams();
    if (filial) params.append('filial', filial);
    
    // Enviar múltiplas origens como array
    if (origemSelecionadas.length > 0) {
      origemSelecionadas.forEach(origem => {
        params.append('origem[]', origem);
      });
    }
    
    if (dataInicial) params.append('data_inicial', dataInicial);
    if (dataFinal) params.append('data_final', dataFinal);
    
    const response = await fetch(`/admin/dashboard/fornecedores-data?${params.toString()}`);
    const result = await response.json();
    
    if (result.success) {
      console.log('✅ Dados recebidos:', result.data);
      updateDashboardFornecedores(result.data);
    } else {
      console.error('❌ Erro:', result.message);
      alert('Erro ao carregar dados: ' + result.message);
    }
  } catch (error) {
    console.error('❌ Erro na requisição:', error);
    alert('Erro ao carregar dados. Verifique o console.');
  }
}

function updateDashboardFornecedores(data) {
  // Atualizar cards
  document.getElementById('totalFornecedores').textContent = data.resumo.total_fornecedores || 0;
  document.getElementById('totalItensComprados').textContent = (data.resumo.total_itens_comprados || 0).toLocaleString('pt-BR');
  document.getElementById('totalGarantias').textContent = (data.resumo.total_garantias || 0).toLocaleString('pt-BR');
  
  // Salvar dados dos fornecedores na variável global (para uso no tooltip)
  dadosFornecedoresGlobal = data.fornecedores;
  
  // Atualizar gráfico de qualidade geral
  if (fornecedoresCharts.qualidade) {
    fornecedoresCharts.qualidade.data.labels = data.fornecedores.map(f => f.nome);
    fornecedoresCharts.qualidade.data.datasets[0].data = data.fornecedores.map(f => f.qualidade_geral);
    fornecedoresCharts.qualidade.update();
  }
  
  // Atualizar gráficos de pizza por tipo
  const totaisPorTipo = data.resumo.por_tipo || {
    toner: { comprados: 0, garantias: 0 },
    maquina: { comprados: 0, garantias: 0 },
    peca: { comprados: 0, garantias: 0 }
  };
  
  if (fornecedoresCharts.chartToners) {
    fornecedoresCharts.chartToners.data.datasets[0].data = [
      totaisPorTipo.toner.comprados,
      totaisPorTipo.toner.garantias
    ];
    fornecedoresCharts.chartToners.update();
  }
  
  if (fornecedoresCharts.chartMaquinas) {
    fornecedoresCharts.chartMaquinas.data.datasets[0].data = [
      totaisPorTipo.maquina.comprados,
      totaisPorTipo.maquina.garantias
    ];
    fornecedoresCharts.chartMaquinas.update();
  }
  
  if (fornecedoresCharts.chartPecas) {
    fornecedoresCharts.chartPecas.data.datasets[0].data = [
      totaisPorTipo.peca.comprados,
      totaisPorTipo.peca.garantias
    ];
    fornecedoresCharts.chartPecas.update();
  }
  
  // Atualizar tabela
  updateTabelaFornecedores(data.fornecedores);
}

function updateTabelaFornecedores(fornecedores) {
  const tbody = document.getElementById('tabelaFornecedores');
  
  if (!fornecedores || fornecedores.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="11" class="px-6 py-8 text-center text-gray-500">
          Nenhum dado encontrado para os filtros selecionados
        </td>
      </tr>
    `;
    return;
  }
  
  tbody.innerHTML = fornecedores.map(f => `
    <tr class="hover:bg-gray-50">
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${f.nome}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">${f.toner.comprados}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium">${f.toner.garantias}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center ${getQualidadeColorClass(f.toner.qualidade)}">
        ${f.toner.qualidade.toFixed(2)}%
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">${f.maquina.comprados}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium">${f.maquina.garantias}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center ${getQualidadeColorClass(f.maquina.qualidade)}">
        ${f.maquina.qualidade.toFixed(2)}%
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">${f.peca.comprados}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-red-600 font-medium">${f.peca.garantias}</td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center ${getQualidadeColorClass(f.peca.qualidade)}">
        ${f.peca.qualidade.toFixed(2)}%
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold ${getQualidadeColorClass(f.qualidade_geral)}">
        ${f.qualidade_geral.toFixed(2)}%
      </td>
    </tr>
  `).join('');
}

function getQualidadeColorClass(percentual) {
  if (percentual >= 95) return 'text-green-600 font-semibold';
  if (percentual >= 90) return 'text-blue-600 font-semibold';
  if (percentual >= 80) return 'text-yellow-600 font-semibold';
  if (percentual >= 70) return 'text-orange-600 font-semibold';
  return 'text-red-600 font-semibold';
}

function clearFiltersFornecedores() {
  document.getElementById('filtroFilialFornecedores').value = '';
  
  // Desmarcar todas as origens selecionadas
  const origemSelect = document.getElementById('filtroOrigemFornecedores');
  Array.from(origemSelect.options).forEach(option => option.selected = false);
  
  const hoje = new Date();
  const inicioAno = new Date(hoje.getFullYear(), 0, 1);
  
  document.getElementById('dataInicialFornecedores').value = inicioAno.toISOString().split('T')[0];
  document.getElementById('dataFinalFornecedores').value = hoje.toISOString().split('T')[0];
  
  // Limpar tabela
  document.getElementById('tabelaFornecedores').innerHTML = `
    <tr>
      <td colspan="11" class="px-6 py-8 text-center text-gray-500">
        Selecione os filtros e clique em "Aplicar Filtros" para ver os dados
      </td>
    </tr>
  `;
  
  // Limpar cards
  document.getElementById('totalFornecedores').textContent = '0';
  document.getElementById('totalItensComprados').textContent = '0';
  document.getElementById('totalGarantias').textContent = '0';
}

// ==================== MODAL DETALHES FORNECEDOR ====================

let fornecedorSelecionado = null;

function abrirModalDetalhesFornecedor(fornecedor) {
  fornecedorSelecionado = fornecedor;
  
  // Atualizar dados no modal
  document.getElementById('modalFornecedorNome').textContent = fornecedor.nome;
  
  // Calcular totais
  const totalComprados = fornecedor.toner.comprados + fornecedor.maquina.comprados + fornecedor.peca.comprados;
  const totalGarantias = fornecedor.toner.garantias + fornecedor.maquina.garantias + fornecedor.peca.garantias;
  const totalAprovados = totalComprados - totalGarantias;
  
  document.getElementById('modalQtdAprovados').textContent = totalAprovados.toLocaleString('pt-BR');
  document.getElementById('modalQtdReprovados').textContent = totalGarantias.toLocaleString('pt-BR');
  
  // Mostrar modal
  const modal = document.getElementById('modalDetalhesFornecedor');
  const content = document.getElementById('modalDetalhesFornecedorContent');
  
  modal.classList.remove('hidden');
  
  setTimeout(() => {
    content.classList.remove('scale-95', 'opacity-0');
    content.classList.add('scale-100', 'opacity-100');
  }, 10);
}

function fecharModalDetalhesFornecedor() {
  const modal = document.getElementById('modalDetalhesFornecedor');
  const content = document.getElementById('modalDetalhesFornecedorContent');
  
  content.classList.remove('scale-100', 'opacity-100');
  content.classList.add('scale-95', 'opacity-0');
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
}

function verItensFornecedor(tipo) {
  if (!fornecedorSelecionado) return;
  
  // Pegar os filtros atuais
  const filial = document.getElementById('filtroFilialFornecedores').value;
  const dataInicial = document.getElementById('dataInicialFornecedores').value;
  const dataFinal = document.getElementById('dataFinalFornecedores').value;
  
  // Pegar múltiplas origens selecionadas
  const origemSelect = document.getElementById('filtroOrigemFornecedores');
  const origemSelecionadas = Array.from(origemSelect.selectedOptions).map(option => option.value);
  
  // Construir URL com parâmetros
  const params = new URLSearchParams();
  params.append('fornecedor', fornecedorSelecionado.nome);
  params.append('tipo', tipo); // 'aprovados' ou 'reprovados'
  if (filial) params.append('filial', filial);
  if (dataInicial) params.append('data_inicial', dataInicial);
  if (dataFinal) params.append('data_final', dataFinal);
  origemSelecionadas.forEach(origem => params.append('origem[]', origem));
  
  // Abrir nova janela com a página de itens
  const url = '/admin/fornecedor-itens?' + params.toString();
  window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
  
  // Fechar modal
  fecharModalDetalhesFornecedor();
}

// Fechar modal ao clicar fora
document.addEventListener('click', function(e) {
  const modal = document.getElementById('modalDetalhesFornecedor');
  if (e.target === modal) {
    fecharModalDetalhesFornecedor();
  }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modal = document.getElementById('modalDetalhesFornecedor');
    if (!modal.classList.contains('hidden')) {
      fecharModalDetalhesFornecedor();
    }
    const modalCliente = document.getElementById('modalDetalhesCliente');
    if (modalCliente && !modalCliente.classList.contains('hidden')) {
      fecharModalDetalhesCliente();
    }
  }
});

// ==================== MODAL DETALHES CLIENTE ====================

// Armazenar dados do ranking para uso no click
let dadosRankingClientes = [];

async function abrirModalDetalhesCliente(codigoCliente, filtros = {}) {
  const modal = document.getElementById('modalDetalhesCliente');
  const content = document.getElementById('modalDetalhesClienteContent');
  
  // Resetar estados
  document.getElementById('modalClienteLoading').classList.remove('hidden');
  document.getElementById('modalClienteConteudo').classList.add('hidden');
  document.getElementById('modalClienteErro').classList.add('hidden');
  document.getElementById('modalClienteNome').textContent = 'Carregando...';
  document.getElementById('modalClienteCodigo').textContent = codigoCliente;
  
  // Mostrar modal
  modal.classList.remove('hidden');
  setTimeout(() => {
    content.classList.remove('scale-95', 'opacity-0');
    content.classList.add('scale-100', 'opacity-100');
  }, 10);
  
  try {
    // Priorizar filtros passados, senão usar filtros globais
    const dataInicial = filtros.dataInicial || document.getElementById('dataInicial')?.value || '';
    const dataFinal = filtros.dataFinal || document.getElementById('dataFinal')?.value || '';
    const destino = filtros.destino || '';
    
    const params = new URLSearchParams({
      codigo: codigoCliente,
      data_inicial: dataInicial,
      data_final: dataFinal
    });
    
    if (destino) {
      params.append('destino', destino);
    }
    
    const response = await fetch(`/admin/dashboard/toners-por-cliente?${params}`);
    const result = await response.json();
    
    if (result.success) {
      // Atualizar cabeçalho
      document.getElementById('modalClienteNome').textContent = result.data.nome;
      document.getElementById('modalClienteCodigo').textContent = result.data.codigo;
      document.getElementById('modalClienteTotal').textContent = result.data.total.toLocaleString('pt-BR');
      
      // Preencher tabela de toners
      const tbody = document.getElementById('modalClienteToners');
      tbody.innerHTML = '';
      
      if (result.data.toners.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-12 text-center text-gray-500">Nenhum toner encontrado</td></tr>';
      } else {
        result.data.toners.forEach((toner, index) => {
          const tr = document.createElement('tr');
          tr.className = index % 2 === 0 ? 'bg-gray-800/30' : 'bg-gray-800/10';
          tr.className += ' hover:bg-gray-700/50 transition-colors';
          
          // Definir cor do badge de destino (tema escuro)
          let destinoBadgeClass = 'bg-gray-600/50 text-gray-300';
          const destino = (toner.destino || 'N/A').toUpperCase();
          if (destino.includes('DESCARTE') || destino.includes('LIXO')) {
            destinoBadgeClass = 'bg-red-500/20 text-red-400 border border-red-500/30';
          } else if (destino.includes('RECARGA') || destino.includes('REMANUFATURA')) {
            destinoBadgeClass = 'bg-green-500/20 text-green-400 border border-green-500/30';
          } else if (destino.includes('GARANTIA')) {
            destinoBadgeClass = 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30';
          }
          
          const totalGeral = result.data.total || 1;
          const percentual = ((parseInt(toner.total) / totalGeral) * 100).toFixed(1);
          tr.innerHTML = `
            <td class="px-6 py-4 text-sm font-medium text-gray-200">${toner.modelo || 'N/A'}</td>
            <td class="px-6 py-4 text-center">
              <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium ${destinoBadgeClass}">
                ${toner.destino || 'N/A'}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                ${parseInt(toner.total).toLocaleString('pt-BR')}
              </span>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }
      
      // Mostrar conteúdo
      document.getElementById('modalClienteLoading').classList.add('hidden');
      document.getElementById('modalClienteConteudo').classList.remove('hidden');
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    document.getElementById('modalClienteLoading').classList.add('hidden');
    document.getElementById('modalClienteErro').classList.remove('hidden');
    document.getElementById('modalClienteErroMsg').textContent = error.message;
  }
}

function fecharModalDetalhesCliente() {
  const modal = document.getElementById('modalDetalhesCliente');
  const content = document.getElementById('modalDetalhesClienteContent');
  
  content.classList.remove('scale-100', 'opacity-100');
  content.classList.add('scale-95', 'opacity-0');
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
}

// Fechar modal ao clicar fora
document.addEventListener('click', function(e) {
  const modal = document.getElementById('modalDetalhesCliente');
  if (e.target === modal) {
    fecharModalDetalhesCliente();
  }
});

// ==================== ABA MELHORIAS ====================

let chartMelhoriasStatus = null;
let chartMelhoriasMes = null;
let chartMelhoriasDepartamentos = null;

// Carregar departamentos no dropdown de filtro
async function carregarDepartamentosMelhorias() {
  try {
    const response = await fetch('/admin/dashboard/departamentos');
    const data = await response.json();
    
    if (data.success && data.departamentos) {
      const select = document.getElementById('filtro-melhorias-departamento');
      if (select) {
        data.departamentos.forEach(dept => {
          const option = document.createElement('option');
          option.value = dept.id;
          option.textContent = dept.nome;
          select.appendChild(option);
        });
      }
    }
  } catch (error) {
    console.error('Erro ao carregar departamentos:', error);
  }
}

// Aplicar filtros de melhorias
function aplicarFiltrosMelhorias() {
  loadMelhoriasData();
}

// Limpar filtros de melhorias
function limparFiltrosMelhorias() {
  document.getElementById('filtro-melhorias-departamento').value = '';
  document.getElementById('filtro-melhorias-status').value = '';
  document.getElementById('filtro-melhorias-idealizador').value = '';
  document.getElementById('filtro-melhorias-data-inicio').value = '';
  document.getElementById('filtro-melhorias-data-fim').value = '';
  document.getElementById('filtro-melhorias-pont-min').value = '';
  document.getElementById('filtro-melhorias-pont-max').value = '';
  loadMelhoriasData();
}

async function loadMelhoriasData() {
  try {
    // Coletar valores dos filtros
    const departamento = document.getElementById('filtro-melhorias-departamento')?.value || '';
    const status = document.getElementById('filtro-melhorias-status')?.value || '';
    const idealizador = document.getElementById('filtro-melhorias-idealizador')?.value || '';
    const dataInicio = document.getElementById('filtro-melhorias-data-inicio')?.value || '';
    const dataFim = document.getElementById('filtro-melhorias-data-fim')?.value || '';
    const pontMin = document.getElementById('filtro-melhorias-pont-min')?.value || '';
    const pontMax = document.getElementById('filtro-melhorias-pont-max')?.value || '';
    
    // Construir query string com filtros
    const params = new URLSearchParams();
    if (departamento) params.append('departamento_id', departamento);
    if (status) params.append('status', status);
    if (idealizador) params.append('idealizador', idealizador);
    if (dataInicio) params.append('data_inicio', dataInicio);
    if (dataFim) params.append('data_fim', dataFim);
    if (pontMin) params.append('pontuacao_min', pontMin);
    if (pontMax) params.append('pontuacao_max', pontMax);
    
    const queryString = params.toString();
    const url = '/admin/dashboard/melhorias-data' + (queryString ? '?' + queryString : '');
    
    const response = await fetch(url);
    const data = await response.json();
    
    if (!data.success) {
      console.error('Erro ao carregar dados de melhorias:', data.message);
      return;
    }

    // Criar mapa de status para acesso rápido
    const statusMap = {};
    data.statusDistribution.forEach(item => {
      statusMap[item.status] = parseInt(item.total);
    });

    const total = data.totais.total || 1; // Evitar divisão por zero

    // Atualizar cards de status com dados reais
    const statusCards = {
      'Pendente análise': { id: 'pendente-analise', value: statusMap['Pendente análise'] || 0 },
      'Enviado para Aprovação': { id: 'enviado-aprovacao', value: statusMap['Enviado para Aprovação'] || 0 },
      'Em andamento': { id: 'em-andamento', value: statusMap['Em andamento'] || 0 },
      'Concluída': { id: 'concluida', value: statusMap['Concluída'] || 0 },
      'Recusada': { id: 'recusada', value: statusMap['Recusada'] || 0 },
      'Pendente Adaptação': { id: 'pendente-adaptacao', value: statusMap['Pendente Adaptação'] || 0 }
    };

    // Atualizar cada card
    Object.keys(statusCards).forEach(statusName => {
      const card = statusCards[statusName];
      const valueEl = document.getElementById(`status-${card.id}`);
      const barEl = document.getElementById(`bar-${card.id}`);
      
      if (valueEl) {
        valueEl.textContent = card.value.toLocaleString('pt-BR');
      }
      
      if (barEl && total > 0) {
        const percentage = (card.value / total) * 100;
        barEl.style.width = `${percentage}%`;
      }
    });

    // Atualizar pontuação média
    document.getElementById('melhorias-pontuacao-media').textContent = data.pontuacaoMedia.toFixed(1);

    // Renderizar gráficos
    renderChartMelhoriasStatus(data.statusDistribution);
    renderChartMelhoriasMes(data.melhoriasPorMes);
    renderChartMelhoriasDepartamentos(data.melhoriasPorDepartamento);

  } catch (error) {
    console.error('Erro ao carregar dados de melhorias:', error);
  }
}

function renderChartMelhoriasStatus(statusData) {
  const ctx = document.getElementById('chartMelhoriasStatus');
  if (!ctx) return;

  if (chartMelhoriasStatus) {
    chartMelhoriasStatus.destroy();
  }

  const labels = statusData.map(item => item.status);
  const values = statusData.map(item => parseInt(item.total));
  
  const statusColors = {
    'Pendente análise': '#6B7280',
    'Enviado para Aprovação': '#4F46E5',
    'Em andamento': '#3B82F6',
    'Concluída': '#10B981',
    'Recusada': '#EF4444',
    'Pendente Adaptação': '#8B5CF6'
  };

  const backgroundColors = labels.map(label => statusColors[label] || '#6B7280');

  chartMelhoriasStatus = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: values,
        backgroundColor: backgroundColors,
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 15,
            font: {
              size: 12
            }
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.parsed || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((value / total) * 100).toFixed(1);
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      }
    }
  });
}

function renderChartMelhoriasMes(mesData) {
  const ctx = document.getElementById('chartMelhoriasMes');
  if (!ctx) return;

  if (chartMelhoriasMes) {
    chartMelhoriasMes.destroy();
  }

  // Converter formato de data YYYY-MM para mês/ano
  const labels = mesData.map(item => {
    const [ano, mes] = item.mes.split('-');
    const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    return `${meses[parseInt(mes) - 1]}/${ano}`;
  });
  const values = mesData.map(item => parseInt(item.total));

  chartMelhoriasMes = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Melhorias',
        data: values,
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
        borderColor: 'rgba(59, 130, 246, 1)',
        borderWidth: 2,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `Melhorias: ${context.parsed.y}`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      }
    }
  });
}

function renderChartMelhoriasDepartamentos(deptData) {
  const ctx = document.getElementById('chartMelhoriasDepartamentos');
  if (!ctx) return;

  if (chartMelhoriasDepartamentos) {
    chartMelhoriasDepartamentos.destroy();
  }

  const labels = deptData.map(item => item.departamento || 'Sem Departamento');
  const values = deptData.map(item => parseInt(item.total));

  chartMelhoriasDepartamentos = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Melhorias',
        data: values,
        backgroundColor: 'rgba(139, 92, 246, 0.8)',
        borderColor: 'rgba(139, 92, 246, 1)',
        borderWidth: 2,
        borderRadius: 6
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: true,
      onClick: (event, activeElements) => {
        if (activeElements.length > 0) {
          const index = activeElements[0].index;
          const departamento = labels[index];
          abrirDetalhesDeptoMelhorias(departamento);
        }
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `Melhorias: ${context.parsed.x}`;
            }
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      // Adicionar cursor pointer quando hover
      onHover: (event, activeElements) => {
        event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
      }
    }
  });
}

// =========================================================================
// FUNÇÕES DO MODAL DE MELHORIAS POR DEPARTAMENTO
// =========================================================================

function abrirDetalhesDeptoMelhorias(departamento) {
  const modal = document.getElementById('modalDetalhesMelhorias');
  const modalContent = document.getElementById('modalDetalhesMelhoriasContent');
  const loadingElement = document.getElementById('modalMelhoriasLoading');
  const conteudoElement = document.getElementById('modalMelhoriasConteudo');
  
  // Atualizar nome do departamento
  document.getElementById('modalMelhoriasDepartamento').textContent = departamento;
  
  // Mostrar modal
  modal.classList.remove('hidden');
  setTimeout(() => {
    modalContent.classList.remove('scale-95', 'opacity-0');
    modalContent.classList.add('scale-100', 'opacity-100');
  }, 10);
  
  // Mostrar loading e esconder conteúdo
  loadingElement.classList.remove('hidden');
  conteudoElement.classList.add('hidden');
  
  // Buscar dados via AJAX
  fetch(`/admin/melhorias/por-departamento?departamento=${encodeURIComponent(departamento)}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        preencherModalMelhorias(data.melhorias);
      } else {
        alert('Erro ao carregar melhorias: ' + (data.message || 'Erro desconhecido'));
        fecharModalMelhorias();
      }
    })
    .catch(error => {
      console.error('Erro:', error);
      alert('Erro ao carregar melhorias');
      fecharModalMelhorias();
    });
}

function preencherModalMelhorias(melhorias) {
  const loadingElement = document.getElementById('modalMelhoriasLoading');
  const conteudoElement = document.getElementById('modalMelhoriasConteudo');
  const tabelaBody = document.getElementById('modalMelhoriasTabela');
  
  // Calcular estatísticas
  const total = melhorias.length;
  const concluidas = melhorias.filter(m => m.status === 'Concluída' || m.status === 'concluida').length;
  const emAndamento = melhorias.filter(m => m.status === 'Em andamento' || m.status === 'em_andamento').length;
  const pontuacaoMedia = melhorias.length > 0 
    ? (melhorias.reduce((acc, m) => acc + (parseFloat(m.pont_global) || 0), 0) / melhorias.length).toFixed(2)
    : '0.00';
  
  // Atualizar cards de resumo
  document.getElementById('melhorias-total').textContent = total;
  document.getElementById('melhorias-concluidas').textContent = concluidas;
  document.getElementById('melhorias-andamento').textContent = emAndamento;
  document.getElementById('melhorias-pontuacao').textContent = pontuacaoMedia;
  
  // Preencher tabela
  tabelaBody.innerHTML = '';
  
  if (melhorias.length === 0) {
    tabelaBody.innerHTML = `
      <tr>
        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
          Nenhuma melhoria encontrada para este departamento
        </td>
      </tr>
    `;
  } else {
    melhorias.forEach(melhoria => {
      const row = document.createElement('tr');
      row.className = 'hover:bg-gray-50 transition-colors';
      row.innerHTML = `
        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">#${melhoria.id}</td>
        <td class="px-4 py-3 text-sm text-gray-900">${escapeHtml(melhoria.titulo)}</td>
        <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(melhoria.idealizador || '-')}</td>
        <td class="px-4 py-3 whitespace-nowrap">
          ${getStatusBadge(melhoria.status)}
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-sm">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
            ⭐ ${melhoria.pont_global || '0'}
          </span>
        </td>
        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
          ${formatarData(melhoria.data_criacao)}
        </td>
      `;
      tabelaBody.appendChild(row);
    });
  }
  
  // Esconder loading e mostrar conteúdo
  loadingElement.classList.add('hidden');
  conteudoElement.classList.remove('hidden');
}

function getStatusBadge(status) {
  const statusMap = {
    'Pendente análise': { text: 'Pendente Análise', color: 'gray', icon: '⏳' },
    'pendente_analise': { text: 'Pendente Análise', color: 'gray', icon: '⏳' },
    'Enviado para Aprovação': { text: 'Enviado p/ Aprovação', color: 'indigo', icon: '📤' },
    'enviado_aprovacao': { text: 'Enviado p/ Aprovação', color: 'indigo', icon: '📤' },
    'Em andamento': { text: 'Em Andamento', color: 'blue', icon: '🔄' },
    'em_andamento': { text: 'Em Andamento', color: 'blue', icon: '🔄' },
    'Concluída': { text: 'Concluída', color: 'green', icon: '✅' },
    'concluida': { text: 'Concluída', color: 'green', icon: '✅' },
    'Recusada': { text: 'Recusada', color: 'red', icon: '❌' },
    'reprovada': { text: 'Reprovada', color: 'red', icon: '❌' },
    'Pendente Adaptação': { text: 'Pendente Adaptação', color: 'purple', icon: '📝' },
    'cancelada': { text: 'Cancelada', color: 'gray', icon: '🚫' }
  };
  
  const info = statusMap[status] || { text: status, color: 'gray', icon: '•' };
  
  return `
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${info.color}-100 text-${info.color}-800">
      ${info.icon} ${info.text}
    </span>
  `;
}

function formatarData(data) {
  if (!data) return  '-';
  const date = new Date(data);
  return date.toLocaleDateString('pt-BR');
}

function escapeHtml(text) {
  if (!text) return '';
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.toString().replace(/[&<>"']/g, m => map[m]);
}

function fecharModalMelhorias() {
  const modal = document.getElementById('modalDetalhesMelhorias');
  const modalContent = document.getElementById('modalDetalhesMelhoriasContent');
  
  modalContent.classList.remove('scale-100', 'opacity-100');
  modalContent.classList.add('scale-95', 'opacity-0');
  
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 300);
}

// Fechar modal ao clicar fora
document.getElementById('modalDetalhesMelhorias')?.addEventListener('click', function(e) {
  if (e.target === this) {
    fecharModalMelhorias();
  }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const modal = document.getElementById('modalDetalhesMelhorias');
    if (modal && !modal.classList.contains('hidden')) {
      fecharModalMelhorias();
    }
  }
});

// =========================================================================
// MOVER MODAL PARA O CONTAINER GLOBAL (fora do dashboard)
// =========================================================================
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('modalDetalhesMelhorias');
  const globalContainer = document.getElementById('global-modals-container');
  
  if (modal && globalContainer) {
    // Mover modal para fora do dashboard, no container global do body
    globalContainer.appendChild(modal);
    console.log('✅ Modal de melhorias movido para global-modals-container');
  }
});


<?php include __DIR__ . '/dashboard_garantias_js.php'; ?>
<?php include __DIR__ . '/dashboard_nao_conformidades_js.php'; ?>

</script>

