<?php
if (!function_exists('e')) {
  function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
}

$triagemStats = $triagemStats ?? [
  'total_registros' => 0,
  'media_percentual' => 0,
  'total_estoque' => 0,
  'valor_recuperado' => 0,
  'por_destino' => [],
  'ultimos_registros' => [],
];

$moduloAtual = strtolower(trim((string)($_GET['modulo'] ?? '')));
?>

<?php if ($moduloAtual !== 'triagem'): ?>
<!-- ===== PORTAL MODE ===== -->
<section class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Dashboard 2.0</h1>
      <p class="text-sm text-gray-600 mt-1">Visão executiva dos principais indicadores</p>
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    <a href="/dashboard-2/triagem" class="group bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md hover:border-cyan-300 transition-all">
      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Triagem de Toners</h2>
          <p class="text-sm text-gray-600 mt-1">Acessar indicadores de triagem e valor recuperado.</p>
        </div>
        <span class="text-xl">🧪</span>
      </div>
      <div class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-cyan-700 group-hover:text-cyan-800">
        Entrar no dashboard <span>→</span>
      </div>
    </a>
  </div>
</section>
<?php else: ?>
<!-- ===== TRIAGEM DASHBOARD ===== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<style>
  :root {
    --dash-bg: #0f172a;
    --dash-surface: rgba(255,255,255,0.04);
    --dash-border: rgba(255,255,255,0.08);
    --dash-text: #e2e8f0;
    --dash-muted: #94a3b8;
    --dash-accent: #22d3ee;
    --dash-accent2: #818cf8;
    --dash-green: #34d399;
    --dash-red: #f87171;
    --dash-orange: #fb923c;
    --dash-yellow: #fbbf24;
  }
  .dash-container { background: var(--dash-bg); color: var(--dash-text); min-height: calc(100vh - 60px); }
  .dash-card { background: var(--dash-surface); border: 1px solid var(--dash-border); border-radius: 16px; backdrop-filter: blur(12px); }
  .dash-card-glow { box-shadow: 0 0 0 1px rgba(34,211,238,0.08), 0 8px 32px rgba(0,0,0,0.25); }
  .kpi-value { font-size: 1.85rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1.1; white-space: nowrap; }
  .kpi-label { font-size: 0.68rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: var(--dash-muted); min-height: 2.4em; display: flex; align-items: flex-end; }
  .kpi-card-inner { display: flex; flex-direction: column; justify-content: space-between; height: 100%; }
  .kpi-badge { display: inline-flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 600; padding: 2px 8px; border-radius: 9999px; }
  .filter-input { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: var(--dash-text); border-radius: 10px; padding: 8px 12px; font-size: 0.82rem; transition: border-color 0.2s; outline: none; width: 100%; }
  .filter-input:focus { border-color: var(--dash-accent); box-shadow: 0 0 0 2px rgba(34,211,238,0.15); }
  .filter-input::placeholder { color: var(--dash-muted); }
  .filter-input option { background: #1e293b; color: var(--dash-text); }
  .filter-multi { min-height: 92px; padding-top: 6px; padding-bottom: 6px; }
  .search-dropdown { position: relative; }
  .search-dropdown.open { z-index: 120; }
  .search-dropdown .sd-list { display:none; position:absolute; top:100%; left:0; right:0; z-index:50; max-height:220px; overflow-y:auto; margin-top:4px; border-radius:10px; border:1px solid rgba(255,255,255,0.12); background:#1e293b; box-shadow:0 12px 32px rgba(0,0,0,0.4); }
  .search-dropdown.open .sd-list { display:block; }
  .search-dropdown .sd-item { padding:7px 12px; font-size:0.82rem; color:var(--dash-text); cursor:pointer; transition:background 0.15s; }
  .search-dropdown .sd-item:hover, .search-dropdown .sd-item.active { background:rgba(34,211,238,0.12); color:#fff; }
  .search-dropdown .sd-empty { padding:10px 12px; font-size:0.78rem; color:var(--dash-muted); font-style:italic; }
  .search-dropdown .sd-clear { padding:7px 12px; font-size:0.78rem; color:var(--dash-accent); cursor:pointer; border-bottom:1px solid rgba(255,255,255,0.06); font-weight:600; }
  .search-dropdown .sd-clear:hover { background:rgba(34,211,238,0.08); }
  .chart-wrapper { position: relative; width: 100%; }
  .chart-wrapper canvas { width: 100% !important; }
  .dash-spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid var(--dash-muted); border-top-color: var(--dash-accent); border-radius: 50%; animation: spin 0.6s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
  @keyframes fadeIn { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }
  .dash-animate { animation: fadeIn 0.35s ease-out both; }
  .dash-table th { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--dash-muted); padding: 10px 14px; text-align: left; }
  .dash-table td { padding: 10px 14px; font-size: 0.82rem; border-top: 1px solid var(--dash-border); }
  .dash-table tr:hover td { background: rgba(255,255,255,0.03); }
  .dash-destino-badge { padding: 3px 10px; border-radius: 9999px; font-size: 0.72rem; font-weight: 600; }
  /* Fullscreen chart */
  .chart-expand-btn { cursor:pointer; padding:4px; border-radius:8px; color:var(--dash-muted); transition:all 0.2s; border:none; background:transparent; }
  .chart-expand-btn:hover { color:#fff; background:rgba(255,255,255,0.08); }
  .chart-fullscreen-overlay { display:none; position:fixed; inset:0; z-index:99999; background:#060c1b; padding:0; flex-direction:column; align-items:center; justify-content:center; }
  .chart-fullscreen-overlay.active { display:flex; }
  .chart-fullscreen-overlay .fs-inner { width:90vw; max-width:1100px; height:85vh; display:flex; flex-direction:column; }
  .chart-fullscreen-overlay .fs-header { display:flex; align-items:center; justify-content:space-between; padding:20px 0 16px; flex-shrink:0; }
  .chart-fullscreen-overlay .fs-title { font-size:1.4rem; font-weight:700; color:#fff; }
  .chart-fullscreen-overlay .fs-subtitle { font-size:0.85rem; color:var(--dash-muted); margin-top:2px; }
  .chart-fullscreen-overlay .fs-close { cursor:pointer; padding:10px 20px; border-radius:10px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:#e2e8f0; font-size:0.85rem; font-weight:600; transition:all 0.2s; }
  .chart-fullscreen-overlay .fs-close:hover { background:rgba(255,255,255,0.12); border-color:rgba(255,255,255,0.25); }
  .chart-fullscreen-overlay .fs-body { flex:1; position:relative; min-height:0; border-radius:16px; border:1px solid rgba(255,255,255,0.06); background:rgba(255,255,255,0.03); padding:24px; }
  .chart-fullscreen-overlay .fs-body canvas { width:100% !important; height:100% !important; }
  /* Modal detalhamento reprovados */
  .reprov-overlay { display:none; position:fixed; inset:0; z-index:99998; background:rgba(5,10,25,0.92); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:24px; }
  .reprov-overlay.active { display:flex; }
  .reprov-modal { background:#1e293b; border:1px solid rgba(255,255,255,0.1); border-radius:16px; width:95vw; max-width:950px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,0.5); }
  .reprov-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.06); flex-shrink:0; }
  .reprov-modal-body { flex:1; overflow-y:auto; padding:20px 24px; }
  .reprov-cliente-bar { display:flex; align-items:center; gap:8px; padding:6px 0; font-size:0.82rem; }
  .reprov-cliente-bar .bar { height:8px; border-radius:4px; background:var(--dash-accent); transition:width 0.4s ease; }
</style>

<!-- Fullscreen overlay -->
<div id="chartFullscreen" class="chart-fullscreen-overlay" onclick="if(event.target===this)fecharFullscreen()">
  <div class="fs-inner">
    <div class="fs-header">
      <div>
        <div class="fs-title" id="fsTitle"></div>
        <div class="fs-subtitle" id="fsSubtitle"></div>
      </div>
      <button class="fs-close" onclick="fecharFullscreen()">ESC &nbsp;✕&nbsp; Fechar</button>
    </div>
    <div class="fs-body"><canvas id="chartFullscreenCanvas"></canvas></div>
  </div>
</div>

<!-- Modal detalhamento reprovados -->
<div id="reprovOverlay" class="reprov-overlay" onclick="if(event.target===this)fecharReprovados()">
  <div class="reprov-modal">
    <div class="reprov-modal-header">
      <div>
        <h2 class="text-lg font-bold text-white" id="reprovTitle">Reprovados</h2>
        <p class="text-xs text-slate-400 mt-1" id="reprovSubtitle"></p>
      </div>
      <button onclick="fecharReprovados()" class="px-4 py-2 rounded-lg border border-white/10 bg-white/5 text-slate-200 text-sm font-semibold hover:bg-white/10 transition">✕ Fechar</button>
    </div>
    <div class="reprov-modal-body">
      <div id="reprovClientes" class="mb-5"></div>
      <div class="overflow-auto rounded-xl border border-white/5">
        <table class="w-full dash-table">
          <thead><tr class="bg-white/[0.02]"><th>Cliente</th><th>Fornecedor</th><th>Modelo</th><th>Defeito</th><th>%</th><th>Valor Rec.</th><th>Data</th></tr></thead>
          <tbody id="reprovTabela"><tr><td colspan="7" class="text-center text-slate-500 py-8">Carregando...</td></tr></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<section class="dash-container -m-6 p-6 lg:p-8 rounded-none">
  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 dash-animate">
    <div>
      <div class="flex items-center gap-3 mb-2">
        <span class="inline-flex items-center gap-1.5 rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-widest text-cyan-200">
          <span class="h-1.5 w-1.5 rounded-full bg-cyan-400 animate-pulse"></span> Live Dashboard
        </span>
        <span id="dashLoading" class="dash-spinner hidden"></span>
        <select id="filtroFilialHeader" class="ml-2 rounded-lg border border-slate-600/60 bg-slate-800/90 text-slate-200 text-xs px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-cyan-400/50 min-w-[160px]">
          <option value="">Todas as Filiais</option>
        </select>
      </div>
      <h1 class="text-2xl font-bold text-white tracking-tight">Triagem de Toners</h1>
      <p class="text-sm text-slate-400 mt-1">Painel analítico de performance, volume e qualidade</p>
    </div>
    <a href="/dashboard-2" class="self-start md:self-auto text-sm px-4 py-2 rounded-xl border border-slate-600/60 bg-slate-800/80 text-slate-200 hover:bg-slate-700 hover:border-slate-500 transition-all">
      ← Voltar aos módulos
    </a>
  </div>

  <!-- Filters -->
  <div class="dash-card dash-card-glow p-4 mb-6 dash-animate relative z-[70]" style="animation-delay:0.05s">
    <div class="flex items-center gap-2 mb-3">
      <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
      <span class="text-xs font-semibold uppercase tracking-widest text-slate-300">Filtros Globais</span>
      <button onclick="limparFiltros()" class="ml-auto text-xs text-cyan-400 hover:text-cyan-300 font-medium transition-colors">Limpar filtros</button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7 gap-3 items-start">
      <div class="search-dropdown" id="sdModelo">
        <label class="block text-[11px] font-medium text-slate-400 mb-1">Modelo</label>
        <input type="text" id="filtroModeloInput" class="filter-input" placeholder="Pesquisar modelo..." autocomplete="off">
        <input type="hidden" id="filtroModelo">
        <div class="sd-list" id="sdModeloList"></div>
      </div>
      <div class="search-dropdown" id="sdCliente">
        <label class="block text-[11px] font-medium text-slate-400 mb-1">Cliente</label>
        <input type="text" id="filtroClienteInput" class="filter-input" placeholder="Pesquisar cliente..." autocomplete="off">
        <input type="hidden" id="filtroCliente">
        <div class="sd-list" id="sdClienteList"></div>
      </div>
      <div>
        <label class="block text-[11px] font-medium text-slate-400 mb-1">Defeito</label>
        <select id="filtroDefeito" class="filter-input"><option value="">Todos</option></select>
      </div>
      <div>
        <label class="block text-[11px] font-medium text-slate-400 mb-1">Destino</label>
        <select id="filtroDestino" class="filter-input">
          <option value="">Todos</option>
          <option value="Descarte">Descarte</option>
          <option value="Garantia">Garantia</option>
          <option value="Uso Interno">Uso Interno</option>
          <option value="Estoque">Estoque</option>
        </select>
      </div>
      <div>
        <label class="block text-[11px] font-medium text-slate-400 mb-1">Data Início</label>
        <input type="date" id="filtroDataInicio" class="filter-input">
      </div>
      <div>
        <label class="block text-[11px] font-medium text-slate-400 mb-1">Data Fim</label>
        <input type="date" id="filtroDataFim" class="filter-input">
      </div>
      <div class="xl:col-span-2">
        <label class="block text-[11px] font-medium text-slate-400 mb-1">Faixas de Retorno</label>
        <select id="filtroFaixas" class="filter-input filter-multi" multiple></select>
      </div>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    <div class="dash-card dash-card-glow p-4 dash-animate" style="animation-delay:0.08s">
      <div class="kpi-card-inner">
        <div class="kpi-label">Total Registros</div>
        <div class="kpi-value text-white mt-2" id="kpiTotal">–</div>
      </div>
    </div>
    <div class="dash-card dash-card-glow p-4 dash-animate" style="animation-delay:0.11s">
      <div class="kpi-card-inner">
        <div class="kpi-label">Média % Gramatura</div>
        <div class="kpi-value text-cyan-300 mt-2" id="kpiMedia">–</div>
      </div>
    </div>
    <div class="dash-card dash-card-glow p-4 dash-animate" style="animation-delay:0.14s">
      <div class="kpi-card-inner">
        <div class="kpi-label">Em Estoque</div>
        <div class="kpi-value text-indigo-300 mt-2" id="kpiEstoque">–</div>
      </div>
    </div>
    <div class="dash-card dash-card-glow p-4 dash-animate" style="animation-delay:0.17s">
      <div class="kpi-card-inner">
        <div class="kpi-label">Descartes</div>
        <div class="kpi-value text-red-400 mt-2" id="kpiDescarte">–</div>
      </div>
    </div>
    <div class="dash-card dash-card-glow p-4 dash-animate" style="animation-delay:0.20s">
      <div class="kpi-card-inner">
        <div class="kpi-label">Garantias</div>
        <div class="kpi-value text-orange-300 mt-2" id="kpiGarantia">–</div>
      </div>
    </div>
    <div class="dash-card p-4 dash-animate" style="animation-delay:0.23s; background: linear-gradient(135deg, rgba(52,211,153,0.12), rgba(16,185,129,0.06)); border: 1px solid rgba(52,211,153,0.25);">
      <div class="kpi-card-inner">
        <div class="kpi-label" style="color:#6ee7b7">Valor Recuperado</div>
        <div class="kpi-value mt-2" style="color:#34d399; font-size:clamp(0.88rem, 1.5vw, 1.2rem); letter-spacing:-0.01em" id="kpiValor">–</div>
      </div>
    </div>
  </div>

  <!-- Charts Grid 2x2 -->
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-6">
    <!-- Chart 1: Top Modelos -->
    <div class="dash-card dash-card-glow p-5 dash-animate" style="animation-delay:0.26s">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-sm font-semibold text-white">Top Modelos por Volume</h3>
          <p class="text-xs text-slate-400 mt-0.5">15 modelos mais triados</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="kpi-badge bg-cyan-400/15 text-cyan-300 border border-cyan-400/20">Barras</span>
          <button class="chart-expand-btn" onclick="expandirGrafico('modelos','Top Modelos por Volume','15 modelos mais triados')" title="Expandir">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-wrapper" style="height:320px"><canvas id="chartModelos"></canvas></div>
    </div>

    <!-- Chart 2: Pareto Defeitos -->
    <div class="dash-card dash-card-glow p-5 dash-animate" style="animation-delay:0.29s">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-sm font-semibold text-white">Pareto de Defeitos</h3>
          <p class="text-xs text-slate-400 mt-0.5">Contagem</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="kpi-badge bg-indigo-400/15 text-indigo-300 border border-indigo-400/20">Pareto</span>
          <button class="chart-expand-btn" onclick="expandirGrafico('pareto','Pareto de Defeitos','Contagem')" title="Expandir">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-wrapper" style="height:320px"><canvas id="chartPareto"></canvas></div>
    </div>

    <!-- Chart 3: Faixas de Percentual -->
    <div class="dash-card dash-card-glow p-5 dash-animate" style="animation-delay:0.32s">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-sm font-semibold text-white">Faixas de Retorno (%)</h3>
          <p class="text-xs text-slate-400 mt-0.5">Distribuição por faixa de gramatura</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="kpi-badge bg-amber-400/15 text-amber-300 border border-amber-400/20">Donut</span>
          <button class="chart-expand-btn" onclick="expandirGrafico('faixas','Faixas de Retorno (%)','Distribuição por faixa de gramatura')" title="Expandir">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-wrapper flex justify-center" style="height:320px"><canvas id="chartFaixas"></canvas></div>
    </div>
    <!-- Chart 4: Evolução Mensal -->
    <div class="dash-card dash-card-glow p-5 dash-animate" style="animation-delay:0.35s">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-sm font-semibold text-white">Evolução Mensal de Reprovação</h3>
          <p class="text-xs text-slate-400 mt-0.5">% de garantias (reprovados) por mês · clique no ponto para detalhes</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="kpi-badge bg-rose-400/15 text-rose-300 border border-rose-400/20">Linha</span>
          <button class="chart-expand-btn" onclick="expandirGrafico('evolucao','Evolução Mensal de Reprovação','% de garantias (reprovados) por mês')" title="Expandir">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-wrapper" style="height:320px"><canvas id="chartEvolucao"></canvas></div>
    </div>

    <!-- Chart 5: Valor Recuperado por mês -->
    <div class="dash-card dash-card-glow p-5 dash-animate" style="animation-delay:0.38s">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-sm font-semibold text-white">Valor Recuperado por mês</h3>
          <p class="text-xs text-slate-400 mt-0.5">Total de valor em R$ recuperado (Estoque) por mês</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="kpi-badge bg-emerald-400/15 text-emerald-300 border border-emerald-400/20">Barras</span>
          <button class="chart-expand-btn" onclick="expandirGrafico('valor_mes','Valor Recuperado por mês','Total de valor em R$ recuperado (Estoque) por mês')" title="Expandir">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
          </button>
        </div>
      </div>
      <div class="chart-wrapper" style="height:320px"><canvas id="chartValorMes"></canvas></div>
    </div>
  </div>

  <!-- Bottom: Destino Distribution + Recent Records -->
  <div class="grid grid-cols-1 xl:grid-cols-5 gap-5 mb-6">
    <!-- Donut Destino -->
    <div class="xl:col-span-2 dash-card dash-card-glow p-5 dash-animate" style="animation-delay:0.41s">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-white">Distribuição por Destino</h3>
        <button class="chart-expand-btn" onclick="expandirGrafico('destino','Distribuição por Destino','Proporção de cada destino')" title="Expandir">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
        </button>
      </div>
      <div class="chart-wrapper flex justify-center" style="height:260px"><canvas id="chartDestino"></canvas></div>
      <div id="destinoLegend" class="mt-4 space-y-1.5"></div>
    </div>

    <!-- Triagens -->
    <div class="xl:col-span-3 dash-card dash-card-glow p-5 dash-animate" style="animation-delay:0.44s">
      <h3 class="text-sm font-semibold text-white mb-4">Triagens</h3>
      <div class="overflow-auto rounded-xl border border-white/5" style="max-height:380px">
        <table class="w-full dash-table">
          <thead><tr class="bg-white/[0.02]"><th>Cliente</th><th>Modelo</th><th>%</th><th>Destino</th><th>Valor Rec.</th><th>Data</th></tr></thead>
          <tbody id="tabelaUltimos">
            <tr><td colspan="6" class="text-center text-slate-500 py-8">Carregando dados...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script>
(function() {
  'use strict';

  // ===== State =====
  let chartInstances = {};
  let debounceTimer = null;
  let filterOptionsLoaded = false;

  // ===== Utils =====
  const fmt = (v, d=0) => Number(v||0).toLocaleString('pt-BR', {minimumFractionDigits:d, maximumFractionDigits:d});
  const fmtBRL = v => 'R$ ' + fmt(v, 2);

  const COLORS = {
    cyan:    {bg:'rgba(34,211,238,0.7)',  border:'#22d3ee'},
    indigo:  {bg:'rgba(129,140,248,0.7)', border:'#818cf8'},
    green:   {bg:'rgba(52,211,153,0.7)',  border:'#34d399'},
    red:     {bg:'rgba(248,113,113,0.7)', border:'#f87171'},
    orange:  {bg:'rgba(251,146,60,0.7)',  border:'#fb923c'},
    yellow:  {bg:'rgba(251,191,36,0.7)',  border:'#fbbf24'},
    purple:  {bg:'rgba(192,132,252,0.7)', border:'#c084fc'},
    teal:    {bg:'rgba(45,212,191,0.7)',  border:'#2dd4bf'},
  };
  const PALETTE = Object.values(COLORS);
  const DESTINO_COLORS = {
    'Descarte':    {bg:'rgba(248,113,113,0.75)', border:'#f87171'},
    'Garantia':    {bg:'rgba(251,146,60,0.75)',  border:'#fb923c'},
    'Uso Interno': {bg:'rgba(129,140,248,0.75)', border:'#818cf8'},
    'Estoque':     {bg:'rgba(52,211,153,0.75)',  border:'#34d399'},
  };
  let faixaOptionsCache = [];

  // ===== Chart.js defaults =====
  Chart.defaults.color = '#94a3b8';
  Chart.defaults.font.family = "'Inter','system-ui',sans-serif";
  Chart.defaults.font.size = 11;
  Chart.defaults.plugins.legend.display = false;
  Chart.defaults.responsive = true;
  Chart.defaults.maintainAspectRatio = false;

  // ===== Fetch Data =====
  function getFilters() {
    const faixaIds = Array.from(document.getElementById('filtroFaixas').selectedOptions).map(o => o.value).filter(Boolean);
    return {
      modelo:      document.getElementById('filtroModelo').value,
      cliente:     document.getElementById('filtroCliente').value,
      defeito:     document.getElementById('filtroDefeito').value,
      destino:     document.getElementById('filtroDestino').value,
      filial:      document.getElementById('filtroFilialHeader').value,
      data_inicio: document.getElementById('filtroDataInicio').value,
      data_fim:    document.getElementById('filtroDataFim').value,
      faixa_ids:   faixaIds.join(','),
    };
  }

  async function fetchDashboard() {
    const spinner = document.getElementById('dashLoading');
    spinner.classList.remove('hidden');
    try {
      const f = getFilters();
      const qs = new URLSearchParams(Object.entries(f).filter(([,v]) => v)).toString();
      const resp = await fetch('/dashboard-2/triagem/data' + (qs ? '?' + qs : ''));
      const json = await resp.json();
      if (!json.success) { console.error(json.message); return; }

      renderKPIs(json.kpis);
      renderChartModelos(json.charts.modelos || []);
      renderChartPareto(json.charts.defeitos_pareto || []);
      renderChartFaixas(json.charts.faixas_percentual || []);
      renderChartEvolucao(json.charts.evolucao_mensal || []);
      renderChartValorMes(json.charts.valor_recuperado_mes || []);
      renderChartDestino(json.charts.por_destino || []);
      renderTable(json.ultimos_registros || []);

      if (json.filter_options) {
        populateFilterOptions(json.filter_options);
      }
    } catch(err) {
      console.error('Dashboard fetch error:', err);
    } finally {
      spinner.classList.add('hidden');
    }
  }

  // ===== Populate filter dropdowns =====
  let sdData = { modelo: [], cliente: [] };

  function populateFilterOptions(opts) {
    const shouldInitSearch = !filterOptionsLoaded;
    sdData.modelo = opts.modelos || sdData.modelo;
    sdData.cliente = opts.clientes || sdData.cliente;
    fillSelect('filtroDefeito', opts.defeitos || []);
    fillSelectFilial('filtroFilialHeader', opts.filiais || []);
    fillFaixasSelect('filtroFaixas', opts.faixas_retorno || []);
    if (shouldInitSearch) {
      initSearchDropdown('Modelo', sdData.modelo);
      initSearchDropdown('Cliente', sdData.cliente);
      filterOptionsLoaded = true;
    }
  }
  function fillSelect(id, items) {
    const sel = document.getElementById(id);
    const current = sel.value;
    sel.innerHTML = '<option value="">Todos</option>';
    items.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item; opt.textContent = item;
      sel.appendChild(opt);
    });
    sel.value = current;
  }
  function fillFaixasSelect(id, items) {
    const sel = document.getElementById(id);
    const current = new Set(Array.from(sel.selectedOptions).map(o => o.value));
    faixaOptionsCache = items || [];
    sel.innerHTML = '';
    faixaOptionsCache.forEach(fx => {
      const opt = document.createElement('option');
      opt.value = String(fx.id);
      opt.textContent = fx.label;
      if (current.has(String(fx.id))) opt.selected = true;
      sel.appendChild(opt);
    });
  }
  function fillSelectFilial(id, items) {
    const sel = document.getElementById(id);
    const current = sel.value;
    sel.innerHTML = '<option value="">Todas as Filiais</option>';
    items.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item; opt.textContent = item;
      sel.appendChild(opt);
    });
    sel.value = current;
  }

  // ===== Searchable Dropdown =====
  function initSearchDropdown(name, allItems) {
    const input = document.getElementById('filtro' + name + 'Input');
    const hidden = document.getElementById('filtro' + name);
    const listEl = document.getElementById('sd' + name + 'List');
    const wrapper = document.getElementById('sd' + name);

    function renderList(filter) {
      const q = (filter || '').toLowerCase();
      const filtered = q ? allItems.filter(i => i.toLowerCase().includes(q)) : allItems;
      let html = '<div class="sd-clear" data-val="">Todos (limpar filtro)</div>';
      if (filtered.length === 0) {
        html += '<div class="sd-empty">Nenhum resultado</div>';
      } else {
        filtered.slice(0, 50).forEach(item => {
          const active = item === hidden.value ? ' active' : '';
          html += '<div class="sd-item' + active + '" data-val="' + esc(item) + '">' + esc(item) + '</div>';
        });
        if (filtered.length > 50) html += '<div class="sd-empty">...mais ' + (filtered.length - 50) + ' itens, refine a busca</div>';
      }
      listEl.innerHTML = html;
    }

    input.addEventListener('focus', () => {
      renderList(input.value);
      wrapper.classList.add('open');
    });
    input.addEventListener('input', () => {
      renderList(input.value);
      wrapper.classList.add('open');
    });
    listEl.addEventListener('click', (e) => {
      const item = e.target.closest('[data-val]');
      if (!item) return;
      const val = item.getAttribute('data-val');
      hidden.value = val;
      input.value = val;
      wrapper.classList.remove('open');
      onFilterChange();
    });
    // Fechar ao clicar fora
    document.addEventListener('click', (e) => {
      if (!wrapper.contains(e.target)) wrapper.classList.remove('open');
    });
  }

  // ===== Render KPIs =====
  function renderKPIs(k) {
    document.getElementById('kpiTotal').textContent = fmt(k.total_registros);
    document.getElementById('kpiMedia').textContent = fmt(k.media_percentual, 2) + '%';
    document.getElementById('kpiEstoque').textContent = fmt(k.total_estoque);
    document.getElementById('kpiDescarte').textContent = fmt(k.total_descarte);
    document.getElementById('kpiGarantia').textContent = fmt(k.total_garantia);
    document.getElementById('kpiValor').textContent = fmtBRL(k.valor_recuperado);
  }

  // ===== Destroy & Create Chart Helper =====
  function resetChart(key) {
    if (chartInstances[key]) { chartInstances[key].destroy(); delete chartInstances[key]; }
  }

  // ===== Chart 1: Top Modelos (horizontal bar) =====
  function renderChartModelos(data) {
    resetChart('modelos');
    const ctx = document.getElementById('chartModelos').getContext('2d');
    const labels = data.map(d => d.label.length > 22 ? d.label.substring(0,20) + '…' : d.label);
    const values = data.map(d => parseInt(d.total));
    chartInstances['modelos'] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          data: values,
          backgroundColor: data.map((_,i) => PALETTE[i % PALETTE.length].bg),
          borderColor: data.map((_,i) => PALETTE[i % PALETTE.length].border),
          borderWidth: 1,
          borderRadius: 6,
          maxBarThickness: 32,
        }]
      },
      options: {
        indexAxis: 'y',
        scales: {
          x: { grid: {color:'rgba(255,255,255,0.04)'}, ticks: {precision:0} },
          y: { grid: {display:false} }
        },
        plugins: {
          tooltip: { callbacks: { label: ctx => `  ${ctx.parsed.x} triagens` } }
        }
      }
    });
  }

  // ===== Chart 2: Pareto (bar + line) =====
  function renderChartPareto(data) {
    resetChart('pareto');
    const ctx = document.getElementById('chartPareto').getContext('2d');
    const labels = data.map(d => d.label.length > 18 ? d.label.substring(0,16) + '…' : d.label);
    chartInstances['pareto'] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            type: 'bar',
            label: 'Quantidade',
            data: data.map(d => d.total),
            backgroundColor: 'rgba(129,140,248,0.65)',
            borderColor: '#818cf8',
            borderWidth: 1,
            borderRadius: 5,
            maxBarThickness: 36,
            yAxisID: 'y',
          },
          {
            type: 'line',
            label: '% Acumulado',
            data: data.map(d => d.pct_acumulado),
            borderColor: '#fbbf24',
            backgroundColor: 'rgba(251,191,36,0.15)',
            borderWidth: 2.5,
            pointRadius: 3,
            pointBackgroundColor: '#fbbf24',
            tension: 0.3,
            fill: true,
            yAxisID: 'y1',
          }
        ]
      },
      options: {
        scales: {
          x: { grid: {display:false}, ticks: {maxRotation:45, minRotation:30} },
          y: { position:'left', grid:{color:'rgba(255,255,255,0.04)'}, ticks:{precision:0}, title:{display:true, text:'Qtd', color:'#818cf8'} },
          y1: { display:false, position:'right', min:0, max:100, grid:{display:false}, ticks:{callback:v=>v+'%'}, title:{display:true, text:'% Acum.', color:'#fbbf24'} },
        },
        plugins: {
          legend: { display: true, position: 'top', labels: {boxWidth:12, padding:16, filter: (item) => item.text === 'Quantidade'} },
          tooltip: {
            callbacks: {
              label: ctx => ctx.dataset.type === 'line'
                ? `  ${ctx.parsed.y}% acumulado`
                : `  ${ctx.parsed.y} ocorrências`
            }
          }
        }
      }
    });
  }

  // ===== Chart 3: Faixas (doughnut) =====
  function renderChartFaixas(data) {
    resetChart('faixas');
    const ctx = document.getElementById('chartFaixas').getContext('2d');
    const total = data.reduce((s,d) => s + d.total, 0);
    const faixaColors = data.map((_, i) => PALETTE[i % PALETTE.length]);
    chartInstances['faixas'] = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: data.map(d => d.label),
        datasets: [{
          data: data.map(d => d.total),
          backgroundColor: faixaColors.map(c => c.bg),
          borderColor: faixaColors.map(c => c.border),
          borderWidth: 2,
          hoverOffset: 8,
        }]
      },
      options: {
        cutout: '62%',
        plugins: {
          legend: { display: true, position: 'bottom', labels: {boxWidth:10, padding:14, usePointStyle:true, pointStyle:'circle'} },
          tooltip: {
            callbacks: {
              label: ctx => {
                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                return `  ${ctx.label}: ${ctx.parsed} (${pct}%)`;
              }
            }
          }
        }
      }
    });
  }

  // ===== Chart 4: Evolução Mensal (line) =====
  let evolucaoRawData = [];
  function renderChartEvolucao(data) {
    resetChart('evolucao');
    evolucaoRawData = data;
    const ctx = document.getElementById('chartEvolucao').getContext('2d');
    const labels = data.map(d => {
      const [y,m] = d.mes.split('-');
      const meses = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
      return meses[parseInt(m)] + '/' + y.substring(2);
    });
    chartInstances['evolucao'] = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: '% Reprovação (Garantia)',
            data: data.map(d => d.pct_reprovacao),
            borderColor: '#f87171',
            backgroundColor: 'rgba(248,113,113,0.1)',
            borderWidth: 2.5,
            pointRadius: 5,
            pointBackgroundColor: '#f87171',
            pointHoverRadius: 8,
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: '#f87171',
            pointHoverBorderWidth: 3,
            tension: 0.35,
            fill: true,
            yAxisID: 'y',
          },
          {
            label: 'Total Avaliados',
            data: data.map(d => d.total_avaliados),
            borderColor: '#94a3b8',
            borderWidth: 1.5,
            pointRadius: 2,
            borderDash: [5,3],
            tension: 0.35,
            fill: false,
            yAxisID: 'y1',
          }
        ]
      },
      options: {
        onClick: (evt, elements) => {
          if (elements.length > 0) {
            const idx = elements[0].index;
            const mesData = evolucaoRawData[idx];
            if (mesData && mesData.mes) abrirReprovados(mesData.mes);
          }
        },
        scales: {
          x: { grid:{display:false} },
          y: { position:'left', min:0, grid:{color:'rgba(255,255,255,0.04)'}, ticks:{callback:v=>v+'%'}, title:{display:true, text:'% Reprovação', color:'#f87171'} },
          y1: { position:'right', min:0, grid:{display:false}, ticks:{precision:0}, title:{display:true, text:'Avaliados', color:'#94a3b8'} },
        },
        plugins: {
          legend: { display: true, position: 'top', labels: {boxWidth:12, padding:16} },
          tooltip: {
            callbacks: {
              label: ctx => ctx.datasetIndex === 0
                ? `  ${ctx.parsed.y}% reprovados (garantia)`
                : `  ${ctx.parsed.y} avaliados`
            },
            footer: () => ['', 'Clique para ver detalhes']
          }
        }
      }
    });
  }

  // ===== Chart 5: Valor Recuperado por mês (bar) =====
  function renderChartValorMes(data) {
    resetChart('valor_mes');
    const ctx = document.getElementById('chartValorMes').getContext('2d');
    const labels = data.map(d => {
      const [y,m] = d.mes.split('-');
      const meses = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
      return meses[parseInt(m)] + '/' + y.substring(2);
    });
    chartInstances['valor_mes'] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Valor Recuperado',
          data: data.map(d => parseFloat(d.total)),
          backgroundColor: 'rgba(52,211,153,0.6)',
          borderColor: '#34d399',
          borderWidth: 1.5,
          borderRadius: 6,
          maxBarThickness: 45,
        }]
      },
      options: {
        scales: {
          x: { grid:{display:false} },
          y: { 
            beginAtZero: true,
            grid:{color:'rgba(255,255,255,0.04)'}, 
            ticks:{
              callback: v => 'R$ ' + fmt(v, 0)
            },
            title:{display:true, text:'Valor (R$)', color:'#34d399'} 
          },
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: ctx => `  ${fmtBRL(ctx.parsed.y)}`
            }
          }
        }
      }
    });
  }

  // ===== Modal Reprovados =====
  async function abrirReprovados(mes) {
    const overlay = document.getElementById('reprovOverlay');
    const [y, m] = mes.split('-');
    const meses = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    const mesNome = meses[parseInt(m)] + ' / ' + y;

    document.getElementById('reprovTitle').textContent = 'Reprovados (Garantia) — ' + mesNome;
    document.getElementById('reprovSubtitle').textContent = 'Carregando...';
    document.getElementById('reprovClientes').innerHTML = '';
    document.getElementById('reprovTabela').innerHTML = '<tr><td colspan="7" class="text-center text-slate-500 py-8"><span class="dash-spinner"></span> Buscando dados...</td></tr>';
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';

    try {
      const resp = await fetch('/dashboard-2/triagem/reprovados?mes=' + encodeURIComponent(mes));
      const json = await resp.json();
      if (!json.success) { document.getElementById('reprovSubtitle').textContent = json.message || 'Erro'; return; }

      document.getElementById('reprovSubtitle').textContent = json.total + ' toner(s) reprovado(s) neste mês';

      // Barras por cliente
      const maxCli = json.por_cliente.length > 0 ? json.por_cliente[0].total : 1;
      let cliHtml = '<p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">Reprovações por cliente</p>';
      json.por_cliente.forEach(c => {
        const pct = Math.round((c.total / maxCli) * 100);
        cliHtml += '<div class="reprov-cliente-bar"><span class="text-slate-300 w-40 truncate shrink-0" title="' + esc(c.cliente) + '">' + esc(c.cliente) + '</span><div class="bar" style="width:' + pct + '%;min-width:4px"></div><span class="text-slate-400 text-xs font-semibold">' + c.total + '</span></div>';
      });
      document.getElementById('reprovClientes').innerHTML = cliHtml;

      // Tabela detalhada
      if (json.registros.length === 0) {
        document.getElementById('reprovTabela').innerHTML = '<tr><td colspan="7" class="text-center text-slate-500 py-8">Nenhum registro encontrado</td></tr>';
      } else {
        let html = '';
        json.registros.forEach(r => {
          const pct = Number(r.percentual_calculado || 0).toFixed(2);
          const val = 'R$ ' + Number(r.valor_recuperado || 0).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
          const dt = r.created_at ? r.created_at.substring(0,10).split('-').reverse().join('/') : '-';
          html += '<tr>';
          html += '<td class="text-slate-200">' + esc(r.cliente_nome || '-') + '</td>';
          html += '<td class="text-slate-300">' + esc(r.fornecedor_nome || '-') + '</td>';
          html += '<td class="font-medium text-white">' + esc(r.toner_modelo || '-') + '</td>';
          html += '<td class="text-orange-300">' + esc(r.defeito_nome || 'N/I') + '</td>';
          html += '<td class="text-slate-200 font-semibold">' + pct + '%</td>';
          html += '<td class="text-emerald-400">' + val + '</td>';
          html += '<td class="text-slate-400">' + dt + '</td>';
          html += '</tr>';
        });
        document.getElementById('reprovTabela').innerHTML = html;
      }
    } catch(err) {
      document.getElementById('reprovSubtitle').textContent = 'Erro ao carregar: ' + err.message;
    }
  }

  window.fecharReprovados = function() {
    document.getElementById('reprovOverlay').classList.remove('active');
    document.body.style.overflow = '';
  };

  // ===== Chart 5: Destino Donut =====
  function renderChartDestino(data) {
    resetChart('destino');
    const ctx = document.getElementById('chartDestino').getContext('2d');
    const total = data.reduce((s,d) => s + parseInt(d.total), 0);
    const bgColors = data.map(d => (DESTINO_COLORS[d.label] || COLORS.teal).bg);
    const bdColors = data.map(d => (DESTINO_COLORS[d.label] || COLORS.teal).border);
    chartInstances['destino'] = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: data.map(d => d.label),
        datasets: [{
          data: data.map(d => parseInt(d.total)),
          backgroundColor: bgColors,
          borderColor: bdColors,
          borderWidth: 2,
          hoverOffset: 6,
        }]
      },
      options: {
        cutout: '58%',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: ctx => {
                const pct = total > 0 ? ((ctx.parsed / total)*100).toFixed(1) : 0;
                return `  ${ctx.label}: ${ctx.parsed} (${pct}%)`;
              }
            }
          }
        }
      }
    });

    // Custom legend
    const legend = document.getElementById('destinoLegend');
    legend.innerHTML = data.map(d => {
      const color = (DESTINO_COLORS[d.label] || COLORS.teal).border;
      const pct = total > 0 ? ((parseInt(d.total)/total)*100).toFixed(1) : 0;
      return `<div class="flex items-center justify-between text-sm">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full" style="background:${color}"></span>
          <span class="text-slate-300">${d.label}</span>
        </div>
        <span class="font-semibold text-white">${d.total} <span class="text-xs text-slate-400">(${pct}%)</span></span>
      </div>`;
    }).join('');
  }

  // ===== Table =====
  function renderTable(rows) {
    const tbody = document.getElementById('tabelaUltimos');
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-slate-500 py-8">Nenhuma triagem encontrada para os filtros aplicados.</td></tr>';
      return;
    }
    tbody.innerHTML = rows.map(r => {
      const dc = DESTINO_COLORS[r.destino] || COLORS.teal;
      const dateStr = r.created_at ? new Date(r.created_at).toLocaleDateString('pt-BR') : '-';
      return `<tr>
        <td class="text-slate-200">${esc(r.cliente_nome || '-')}</td>
        <td class="text-slate-300">${esc(r.toner_modelo || '-')}</td>
        <td class="font-semibold text-white">${Number(r.percentual_calculado||0).toFixed(2).replace('.',',')}%</td>
        <td><span class="dash-destino-badge" style="background:${dc.bg};color:#fff;border:1px solid ${dc.border}">${esc(r.destino||'-')}</span></td>
        <td class="text-emerald-300 font-medium">${fmtBRL(r.valor_recuperado||0)}</td>
        <td class="text-slate-400">${dateStr}</td>
      </tr>`;
    }).join('');
  }
  function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

  // ===== Filter debounce + auto-fetch =====
  function onFilterChange() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchDashboard, 350);
  }
  window.limparFiltros = function() {
    ['filtroModelo','filtroCliente','filtroDefeito','filtroDestino','filtroDataInicio','filtroDataFim','filtroFilialHeader'].forEach(id => {
      document.getElementById(id).value = '';
    });
    document.getElementById('filtroModeloInput').value = '';
    document.getElementById('filtroClienteInput').value = '';
    Array.from(document.getElementById('filtroFaixas').options).forEach(o => { o.selected = false; });
    fetchDashboard();
  };

  // ===== Fullscreen =====
  let fullscreenChart = null;

  window.expandirGrafico = function(chartKey, title, subtitle) {
    const source = chartInstances[chartKey];
    if (!source) return;

    const overlay = document.getElementById('chartFullscreen');
    document.getElementById('fsTitle').textContent = title;
    document.getElementById('fsSubtitle').textContent = subtitle;

    // Destroy previous fullscreen chart
    if (fullscreenChart) { fullscreenChart.destroy(); fullscreenChart = null; }

    // Clone config from source chart
    const srcConfig = source.config;
    const clonedData = JSON.parse(JSON.stringify(srcConfig.data));
    const clonedOptions = JSON.parse(JSON.stringify(srcConfig.options || {}));

    // Bigger fonts for presentation
    clonedOptions.plugins = clonedOptions.plugins || {};
    clonedOptions.plugins.legend = clonedOptions.plugins.legend || {};
    clonedOptions.plugins.legend.labels = clonedOptions.plugins.legend.labels || {};
    clonedOptions.plugins.legend.labels.font = { size: 14 };
    clonedOptions.plugins.legend.labels.padding = 20;
    if (clonedOptions.scales) {
      Object.values(clonedOptions.scales).forEach(s => {
        s.ticks = s.ticks || {};
        s.ticks.font = { size: 13 };
        if (s.title) s.title.font = { size: 14, weight: '600' };
      });
    }
    clonedOptions.plugins.tooltip = clonedOptions.plugins.tooltip || {};
    clonedOptions.plugins.tooltip.titleFont = { size: 14 };
    clonedOptions.plugins.tooltip.bodyFont = { size: 13 };

    // Rebuild datasets with functions stripped (borderDash etc are plain arrays, safe)
    // For multi-type charts, preserve the type per dataset
    const datasets = clonedData.datasets.map((ds, i) => {
      const orig = srcConfig.data.datasets[i];
      // Copy backgroundColor/borderColor from original (may be arrays)
      if (orig.backgroundColor && typeof orig.backgroundColor !== 'string') {
        ds.backgroundColor = Array.isArray(orig.backgroundColor) ? [...orig.backgroundColor] : orig.backgroundColor;
      }
      if (orig.borderColor && typeof orig.borderColor !== 'string') {
        ds.borderColor = Array.isArray(orig.borderColor) ? [...orig.borderColor] : orig.borderColor;
      }
      return ds;
    });
    clonedData.datasets = datasets;

    const fsCanvas = document.getElementById('chartFullscreenCanvas');
    const ctx = fsCanvas.getContext('2d');

    // Preserve click behavior in fullscreen for evolução chart
    if (chartKey === 'evolucao') {
      clonedOptions.onClick = (evt, elements) => {
        if (elements.length > 0) {
          const idx = elements[0].index;
          const mesData = evolucaoRawData[idx];
          if (mesData && mesData.mes) {
            window.fecharFullscreen();
            abrirReprovados(mesData.mes);
          }
        }
      };
      clonedOptions.plugins = clonedOptions.plugins || {};
      clonedOptions.plugins.tooltip = clonedOptions.plugins.tooltip || {};
      clonedOptions.plugins.tooltip.footer = () => ['', 'Clique para ver detalhes'];
    }

    fullscreenChart = new Chart(ctx, {
      type: srcConfig.type,
      data: clonedData,
      options: { ...clonedOptions, responsive: true, maintainAspectRatio: false },
    });

    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  window.fecharFullscreen = function() {
    const overlay = document.getElementById('chartFullscreen');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
    if (fullscreenChart) { fullscreenChart.destroy(); fullscreenChart = null; }
  };

  // ESC key to close modals
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      if (document.getElementById('reprovOverlay').classList.contains('active')) {
        window.fecharReprovados();
      } else {
        window.fecharFullscreen();
      }
    }
  });

  // ===== Init =====
  document.addEventListener('DOMContentLoaded', () => {
    // Move overlays to <body> to escape transformed parent stacking context
    // (.page-transition uses transform in main layout, which breaks fixed centering)
    ['chartFullscreen', 'reprovOverlay'].forEach(id => {
      const el = document.getElementById(id);
      if (el && el.parentElement !== document.body) {
        document.body.appendChild(el);
      }
    });

    ['filtroDefeito','filtroDestino','filtroDataInicio','filtroDataFim','filtroFilialHeader'].forEach(id => {
      document.getElementById(id).addEventListener('change', onFilterChange);
    });
    document.getElementById('filtroFaixas').addEventListener('change', onFilterChange);

    ['filtroDataInicio','filtroDataFim'].forEach(id => {
      document.getElementById(id).addEventListener('input', onFilterChange);
    });
    fetchDashboard();
  });
})();
</script>
<?php endif; ?>
