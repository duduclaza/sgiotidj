<?php
if (!function_exists('hasPermission')) {
    function hasPermission($module, $action = 'view') {
        if (!isset($_SESSION['user_id'])) return false;
        $userRole = $_SESSION['user_role'] ?? '';
        if (in_array($userRole, ['admin', 'super_admin'])) return true;
        return \App\Services\PermissionService::hasPermission($_SESSION['user_id'], $module, $action);
    }
}
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

$canEdit   = hasPermission('triagem_toners', 'edit');
$canDelete = hasPermission('triagem_toners', 'delete');
$canImport = hasPermission('triagem_toners', 'import');
$userRole  = $_SESSION['user_role'] ?? '';
$isAdmin   = in_array($userRole, ['admin', 'super_admin']);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- Header -->
  <div class="mb-6">
    <div class="flex justify-between items-center flex-wrap gap-3">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Triagem de Toners</h1>
        <p class="mt-1 text-gray-600 dark:text-gray-400">Avalie a gramatura restante e defina o destino do toner retornado</p>
      </div>
      <div class="flex gap-2 flex-wrap">
        <?php if ($canImport): ?>
        <a href="/triagem-toners/template" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l-3-3m3 3l3-3m-9 4h12"/></svg>
          Baixar Modelo
        </a>
        <button onclick="abrirModalImportacao()" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
          Importar Planilha
        </button>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
        <button onclick="abrirModalParametros()" class="flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Parâmetros
        </button>
        <?php endif; ?>
        <?php if ($canEdit): ?>
        <button onclick="abrirModalNova()" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Nova Triagem
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 p-4 mb-6 transition-colors">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
      <div class="sm:col-span-2 lg:col-span-2">
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Busca Inteligente</label>
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input id="f-search" type="text" placeholder="Modelo, cliente, colaborador ou cód. requisição..." class="w-full border border-gray-300 dark:border-slate-600 rounded-xl pl-9 pr-3 py-2.5 text-sm bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:bg-white dark:focus:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Filial</label>
        <input id="f-filial" type="text" placeholder="Ex.: Matriz" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Modelo</label>
        <input id="f-modelo" type="text" placeholder="Ex.: HP CF280A" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
        <input id="f-cliente" type="text" placeholder="Nome ou código" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Colaborador</label>
        <input id="f-colaborador" type="text" placeholder="Nome" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Defeito</label>
        <input id="f-defeito" type="text" placeholder="Descrição" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Fornecedor</label>
        <input id="f-fornecedor" type="text" placeholder="Fornecedor" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cód. Requisição</label>
        <input id="f-codigo-req" type="text" placeholder="REQ-2026..." class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Destino</label>
        <select id="f-destino" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Todos</option>
          <option value="Descarte">Descarte</option>
          <option value="Garantia">Garantia</option>
          <option value="Uso Interno">Uso Interno</option>
          <option value="Estoque">Estoque</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Modo</label>
        <select id="f-modo" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Todos</option>
          <option value="peso">Peso</option>
          <option value="percentual">% Direto</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">% Mín.</label>
        <input id="f-percentual-min" type="number" min="0" max="100" step="0.01" placeholder="0" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">% Máx.</label>
        <input id="f-percentual-max" type="number" min="0" max="100" step="0.01" placeholder="100" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data Início</label>
        <input id="f-data-inicio" type="date" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Data Fim</label>
        <input id="f-data-fim" type="date" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
    </div>
    <div class="flex justify-end mt-3 gap-2">
      <button onclick="abrirModalColunas()" class="px-4 py-2 text-sm text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/40 hover:bg-indigo-200 dark:hover:bg-indigo-800/50 rounded-lg transition-colors">Colunas</button>
      <button onclick="limparFiltros()" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-lg transition-colors">Limpar</button>
      <button onclick="carregarRegistros(1)" class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">Filtrar</button>
    </div>
  </div>

  <!-- Grid -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 overflow-hidden transition-colors">
    <!-- Zoom and Barra de rolagem superior -->
    <div class="px-4 py-2 bg-gray-50 dark:bg-slate-900/30 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">🔍 Ajustar Zoom:</span>
        <input type="range" id="grid-zoom-slider" min="0.5" max="1.3" step="0.05" value="1.0" 
               class="w-32 h-1.5 bg-gray-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-600"
               oninput="updateGridZoom(this.value)">
        <span id="grid-zoom-val" class="text-xs font-bold text-gray-700 dark:text-gray-300 w-8">100%</span>
      </div>
      <div id="grid-top-scroll" class="flex-1 overflow-x-auto" style="overflow-y:hidden;height:12px;">
        <div id="grid-top-scroll-inner" style="height:1px;"></div>
      </div>
    </div>
    <div id="grid-scroll" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700 text-sm">
        <thead class="bg-gray-50 dark:bg-slate-900/50">
          <tr id="grid-head"></tr>
        </thead>
        <tbody id="grid-body" class="divide-y divide-gray-100 dark:divide-slate-700">
          <tr><td colspan="16" class="px-4 py-8 text-center text-gray-400">Carregando...</td></tr>
        </tbody>
      </table>
    </div>
    <!-- Paginação -->
    <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
      <span id="pag-info" class="text-sm text-gray-500"></span>
      <div class="flex gap-1" id="pag-buttons"></div>
    </div>
  </div>
</div>

<!-- ========== MODAL: PERSONALIZAR COLUNAS ========== -->
<div id="modal-colunas" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-xl transition-colors">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700">
      <div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Personalizar colunas</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Escolha quais colunas exibir e ajuste a ordem.</p>
      </div>
      <button onclick="fecharModalColunas()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="px-6 py-4">
      <div id="colunas-lista" class="space-y-2 max-h-80 overflow-y-auto"></div>
    </div>
    <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
      <button onclick="resetColunasPadrao()" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700">Padrão</button>
      <button onclick="salvarPreferenciasColunas()" class="px-4 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Salvar</button>
    </div>
  </div>
</div>

<!-- ========== MODAL: DUPLICAR COM OUTRO CLIENTE ========== -->
<div id="modal-duplicate-cliente" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 transition-colors">
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Duplicar com outro cliente</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Selecione o cliente para o novo registro duplicado.</p>

    <input type="hidden" id="duplicate-id">

    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar cliente</label>
    <input id="duplicate-cliente-search" type="text" placeholder="Digite nome/código do cliente..."
           oninput="autoSelecionarInteligente('duplicate-cliente-search','duplicate-cliente-id')"
           class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm mb-2 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
    <select id="duplicate-cliente-id" onchange="sincronizarInputComSelect('duplicate-cliente-id','duplicate-cliente-search')" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="">Selecione o cliente...</option>
      <?php foreach (($clientes ?? []) as $c): ?>
      <option value="<?= (int)$c['id'] ?>"><?= e(($c['codigo'] ?? '') . ' - ' . ($c['nome'] ?? '')) ?></option>
      <?php endforeach; ?>
    </select>

    <div class="flex justify-end gap-2 mt-5">
      <button type="button" onclick="fecharModalDuplicateCliente()" class="px-4 py-2 text-sm bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-600">Cancelar</button>
      <button type="button" onclick="confirmarDuplicacaoComCliente()" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Duplicar</button>
    </div>
  </div>
</div>

<!-- ========== MODAL: IMPORTAÇÃO ========== -->
<div id="modal-importacao" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg transition-colors">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700">
      <h2 class="text-lg font-bold text-gray-900 dark:text-white">Importar Triagens</h2>
      <button onclick="fecharModalImportacao()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="px-6 py-5 space-y-4">
      <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800/50 rounded-lg p-3 text-xs text-blue-800 dark:text-blue-200">
        1) Baixe o modelo em <strong>Baixar Modelo</strong>.<br>
        2) Preencha os dados da triagem.<br>
        3) Importe o arquivo CSV/XLS/XLSX.
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Arquivo</label>
        <input id="arquivo-importacao" type="file" accept=".csv,.xls,.xlsx" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white">
      </div>
      <div id="import-feedback" class="hidden text-sm rounded-lg p-3"></div>
    </div>
    <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
      <button onclick="fecharModalImportacao()" class="px-5 py-2 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">Cancelar</button>
      <button id="btn-importar" onclick="importarPlanilha()" class="px-5 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors font-medium">Importar</button>
    </div>
  </div>
</div>

<style>
  /* Modal Triagem - visual modernizado */
  #modal-triagem {
    background: rgba(15, 23, 42, 0.52);
    backdrop-filter: blur(4px);
  }
  #modal-triagem .triagem-modal-panel {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border: 1px solid #e5edf8;
    box-shadow: 0 24px 70px rgba(2, 8, 23, 0.24);
  }
  .dark #modal-triagem .triagem-modal-panel {
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    border-color: #334155;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.6);
  }
  #modal-triagem .triagem-modal-header {
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    border-bottom-color: #e4eaf5;
  }
  .dark #modal-triagem .triagem-modal-header {
    background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
    border-bottom-color: #334155;
  }
  #modal-triagem .triagem-modal-body {
    background:
      radial-gradient(circle at top right, rgba(37, 99, 235, 0.05), transparent 45%),
      radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.05), transparent 45%);
  }
  .dark #modal-triagem .triagem-modal-body {
    background:
      radial-gradient(circle at top right, rgba(37, 99, 235, 0.1), transparent 45%),
      radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.1), transparent 45%);
  }
  #modal-triagem .triagem-modal-footer {
    border-top-color: #e4eaf5;
    background: #f8fafc;
  }
  .dark #modal-triagem .triagem-modal-footer {
    border-top-color: #334155;
    background: #1e293b;
  }
  #modal-triagem label {
    color: #1e3a5f;
    font-weight: 600;
    letter-spacing: 0.01em;
  }
  .dark #modal-triagem label {
    color: #cbd5e1;
  }
  #modal-triagem input:not([type="radio"]),
  #modal-triagem select,
  #modal-triagem textarea {
    background-color: #ffffff;
    border-color: #d7e0ee;
    border-radius: 0.75rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
    transition: all 0.18s ease;
  }
  .dark #modal-triagem input:not([type="radio"]),
  .dark #modal-triagem select,
  .dark #modal-triagem textarea {
    background-color: #1e293b;
    border-color: #334155;
    color: #f1f5f9;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
  }
  #modal-triagem input:not([type="radio"]):focus,
  #modal-triagem select:focus,
  #modal-triagem textarea:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
  }
  .dark #modal-triagem input:not([type="radio"]):focus,
  .dark #modal-triagem select:focus,
  .dark #modal-triagem textarea:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
  }
  #modal-triagem input[readonly] {
    background: #f1f5f9 !important;
    color: #475569 !important;
  }
  .dark #modal-triagem input[readonly] {
    background: #0f172a !important;
    color: #94a3b8 !important;
  }
  #modal-triagem .triagem-modal-body::-webkit-scrollbar {
    width: 9px;
  }
  #modal-triagem .triagem-modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
  }
  #modal-triagem .triagem-modal-body::-webkit-scrollbar-thumb {
    background: #475569;
  }

  /* Grid Zoom Styles */
  #grid-scroll table {
    font-size: calc(0.8125rem * var(--grid-zoom, 1));
    transition: font-size 0.1s ease;
  }
  #grid-scroll th, #grid-scroll td {
    padding-left: calc(1rem * var(--grid-zoom, 1));
    padding-right: calc(1rem * var(--grid-zoom, 1));
    padding-top: calc(0.75rem * var(--grid-zoom, 1));
    padding-bottom: calc(0.75rem * var(--grid-zoom, 1));
    transition: padding 0.1s ease;
  }
  #grid-scroll .w-16 {
    width: calc(4rem * var(--grid-zoom, 1));
  }

  /* Resizer Handle */
  .resizer {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 6px;
    cursor: col-resize;
    user-select: none;
    z-index: 10;
    transition: background-color 0.2s;
  }
  .resizer:hover, .resizer.resizing {
    background-color: rgba(59, 130, 246, 0.5);
  }
  th {
    position: relative;
    overflow: hidden;
    text-overflow: ellipsis;
  }
</style>

<!-- ========== MODAL: NOVA / EDITAR TRIAGEM ========== -->
<div id="modal-triagem" class="fixed inset-0 flex items-center justify-center z-50 hidden p-4">
  <div class="triagem-modal-panel rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="triagem-modal-header flex items-center justify-between px-6 py-4 border-b">
      <h2 id="modal-titulo" class="text-lg font-bold text-gray-900 dark:text-white">Nova Triagem</h2>
      <button onclick="fecharModalTriagem()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="triagem-modal-body px-6 py-5 space-y-5">
      <input type="hidden" id="t-id">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filial (automático)</label>
          <input id="t-filial" type="text" value="<?= e($_SESSION['user_filial'] ?? 'Não informado') ?>" readonly
                 class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-gray-100 dark:bg-slate-900 text-gray-600 dark:text-gray-400">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Colaborador que registrou</label>
          <input id="t-colaborador" type="text" value="<?= e($_SESSION['user_name'] ?? 'Usuário') ?>" readonly
                 class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-gray-100 dark:bg-slate-900 text-gray-600 dark:text-gray-400">
        </div>
      </div>

      <!-- Código de Requisição (Opcional) - PRIMEIRO CAMPO -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Código de Requisição <span class="text-gray-400 dark:text-gray-500 text-xs">(opcional mas recomendado)</span></label>
        <div class="flex gap-2">
          <input id="t-codigo-req" type="text" maxlength="100" placeholder="Ex: REQ-2026-0001"
                 oninput="debouceBuscarDefeitos()" onchange="buscarDefeitosPorCodigo()"
                 class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <button type="button" onclick="buscarDefeitosPorCodigo()" class="bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-gray-600 dark:text-gray-300 transition-colors" title="Buscar Defeitos">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </button>
        </div>
        
        <!-- Lista dinâmica de defeitos localizados -->
        <div id="defeitos-lista-container" class="hidden mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-lg">
          <div class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-2">Selecione o toner com defeito que corresponde a esta triagem:</div>
          <div id="defeitos-opcoes" class="space-y-2 max-h-40 overflow-y-auto">
            <!-- Radio options will be rendered here -->
          </div>
        </div>
      </div>

      <!-- Seleção do Cliente -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente <span class="text-red-500">*</span></label>
        <input id="t-cliente-search" type="text" placeholder="Digite nome/código do cliente (seleção automática)..." oninput="autoSelecionarInteligente('t-cliente-search','t-cliente-id')"
               class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm mb-2 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select id="t-cliente-id" onchange="sincronizarInputComSelect('t-cliente-id','t-cliente-search')" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Selecione o cliente...</option>
          <?php foreach (($clientes ?? []) as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= e(($c['codigo'] ?? '') . ' - ' . ($c['nome'] ?? '')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Seleção do Toner -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Modelo do Toner <span class="text-red-500">*</span></label>
        <input id="t-toner-search" type="text" placeholder="Digite o modelo do toner (seleção automática)..." oninput="autoSelecionarInteligente('t-toner-search','t-toner-id', 'onTonerChange')"
               class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm mb-2 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select id="t-toner-id" onchange="onTonerChange(); sincronizarInputComSelect('t-toner-id','t-toner-search')" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Selecione o modelo...</option>
          <?php foreach ($toners as $t): ?>
          <option value="<?= $t['id'] ?>"
            data-peso-cheio="<?= $t['peso_cheio'] ?>"
            data-peso-vazio="<?= $t['peso_vazio'] ?>"
            data-gramatura="<?= $t['gramatura'] ?: (($t['peso_cheio'] ?? 0) - ($t['peso_vazio'] ?? 0)) ?>"
            data-capacidade="<?= $t['capacidade_folhas'] ?>"
            data-custo-folha="<?= $t['custo_por_folha'] ?>"
            data-preco="<?= $t['preco_toner'] ?>">
            <?= e($t['modelo']) ?>
            <?php if ($t['peso_cheio']): ?>
              (Cheio: <?= number_format($t['peso_cheio'],1) ?>g / Vazio: <?= number_format($t['peso_vazio'],1) ?>g)
            <?php endif; ?>
          </option>
          <?php endforeach; ?>
        </select>
        <div id="info-toner" class="mt-2 hidden bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-lg p-3 text-xs text-blue-800 dark:text-blue-300 space-y-1">
          <div>📊 Gramatura total: <strong id="info-gram"></strong>g</div>
          <div>⚖️ Peso cheio: <strong id="info-cheio"></strong>g &nbsp;|&nbsp; Peso vazio: <strong id="info-vazio"></strong>g</div>
        </div>
      </div>

      <!-- Modo de entrada -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Modo de Entrada <span class="text-red-500">*</span></label>
        <div class="flex gap-4">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="modo" value="peso" id="modo-peso" checked onchange="onModoChange()" class="accent-blue-600">
            <span class="text-sm dark:text-gray-300">Informar Peso (g)</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="modo" value="percentual" id="modo-pct" onchange="onModoChange()" class="accent-blue-600">
            <span class="text-sm dark:text-gray-300">Informar % direto</span>
          </label>
        </div>
      </div>

      <!-- Campo Peso -->
      <div id="campo-peso">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Peso do Toner Retornado (g) <span class="text-red-500">*</span></label>
        <input type="number" id="t-peso" step="0.01" min="0" placeholder="Ex: 320.50"
               oninput="recalcular()" onchange="recalcular()"
               class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Campo Percentual -->
      <div id="campo-pct" class="hidden">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Percentual de Gramatura Restante (%) <span class="text-red-500">*</span></label>
        <input type="number" id="t-pct" step="0.01" min="0" max="100" placeholder="Ex: 65.00"
               oninput="recalcular()" onchange="recalcular()"
               class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Resultado do cálculo -->
      <div id="resultado-calc" class="hidden bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-700 rounded-xl p-4 space-y-3 transition-colors">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">% de Gramatura Restante</span>
          <span id="res-pct" class="text-2xl font-bold text-blue-700 dark:text-blue-400">—</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-3">
          <div id="res-barra" class="h-3 rounded-full transition-all duration-500 bg-green-500" style="width:0%"></div>
        </div>
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
          <span>Gramatura restante: <strong id="res-gram" class="dark:text-gray-300">—</strong>g</span>
          <span id="res-folhas-wrap" class="hidden">📄 Folhas equivalentes: <strong id="res-folhas" class="dark:text-gray-300">—</strong></span>
          <span id="res-valor-wrap" class="hidden"><span id="res-valor-label">💰 Impacto:</span> <strong id="res-valor" class="text-green-700 dark:text-green-400">—</strong></span>
        </div>
        <!-- Parecer -->
        <div id="res-parecer-box" class="hidden rounded-lg p-3 border dark:border-slate-600">
          <div class="text-xs font-semibold uppercase tracking-wide mb-1 text-gray-500 dark:text-gray-400">📋 Parecer do Sistema</div>
          <div id="res-parecer" class="text-sm font-medium dark:text-white"></div>
        </div>
      </div>

      <!-- Destino Final -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destino Final <span class="text-red-500">*</span></label>
        <select id="t-destino" onchange="onDestinoChange()" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Selecione o destino...</option>
          <option value="Descarte">♻️ Descarte</option>
          <option value="Garantia">🛡️ Garantia</option>
          <option value="Uso Interno">🏢 Uso Interno</option>
          <option value="Estoque">📦 Estoque</option>
        </select>
        <div id="info-estoque" class="hidden mt-2 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/50 rounded-lg p-3 text-xs text-green-800 dark:text-green-400">
          💰 Ao selecionar <strong>Estoque</strong>, o sistema calculará automaticamente o <strong>valor em R$ recuperado</strong> com base na capacidade de folhas e custo por folha do toner.
        </div>
        <div id="info-descarte" class="hidden mt-2 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-lg p-3 text-xs text-red-800 dark:text-red-400">
          🗑️ Ao selecionar <strong>Descarte</strong>, o sistema calculará automaticamente as <strong>folhas descartadas</strong> e o <strong>valor em R$ perdido</strong> (negativo).
        </div>
      </div>

      <!-- Fornecedor (obrigatório para Garantia) -->
      <div id="wrap-fornecedor" class="hidden">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fornecedor <span id="fornecedor-required" class="text-red-500">*</span></label>
        <select id="t-fornecedor-id" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Selecione o fornecedor...</option>
          <?php foreach (($fornecedores ?? []) as $f): ?>
            <option value="<?= (int)$f['id'] ?>"><?= e($f['nome'] ?? '') ?></option>
          <?php endforeach; ?>
        </select>
        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Obrigatório quando o destino for <strong>Garantia</strong>.</div>
      </div>

      <!-- Defeito (Opcional) -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Defeito <span class="text-gray-400 dark:text-gray-500 text-xs">(opcional)</span></label>
        <select id="t-defeito-id" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Sem defeito</option>
          <?php foreach (($defeitos ?? []) as $d): ?>
            <option value="<?= (int)$d['id'] ?>"><?= e($d['nome_defeito'] ?? '') ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Observações -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observações</label>
        <textarea id="t-obs" rows="2" placeholder="Observações adicionais..." class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
      </div>
    </div>
    <div class="triagem-modal-footer flex justify-end gap-3 px-6 py-4 border-t">
      <button onclick="fecharModalTriagem()" class="px-5 py-2 text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">Cancelar</button>
      <button onclick="salvarTriagem()" id="btn-salvar" class="px-5 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors font-medium">Salvar</button>
    </div>
  </div>
</div>

<!-- ========== MODAL: PARÂMETROS ========== -->
<div id="modal-params" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transition-colors">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700">
      <div>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">⚙️ Parâmetros de Triagem</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Configure os pareceres por faixa de percentual</p>
      </div>
      <button onclick="fecharModalParams()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="px-6 py-5">
      <div id="params-lista" class="space-y-3"></div>
      <button onclick="addParam()" class="mt-4 flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Adicionar Faixa
      </button>
    </div>
    <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
      <button onclick="fecharModalParams()" class="px-5 py-2 text-sm text-gray-600 dark:text-gray-400 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700">Cancelar</button>
      <button onclick="salvarParametros()" class="px-5 py-2 text-sm text-white bg-blue-600 hover:bg-blue-700 rounded-lg font-medium">Salvar Parâmetros</button>
    </div>
  </div>
</div>

<!-- ========== MODAL: CONFIRMAR EXCLUSÃO ========== -->
<div id="modal-delete" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center transition-colors">
    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
      <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    </div>
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Excluir Registro</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Tem certeza que deseja excluir este registro de triagem? Esta ação não pode ser desfeita.</p>
    <input type="hidden" id="delete-id">
    <div class="flex gap-3 justify-center">
      <button onclick="fecharModalDelete()" class="px-5 py-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-lg transition-colors">Cancelar</button>
      <button onclick="confirmarDelete()" class="px-5 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg font-medium transition-colors">Excluir</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed top-5 right-5 z-[9999] hidden">
  <div id="toast-inner" class="px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white max-w-sm"></div>
</div>

<script>
// ===== DADOS PHP → JS =====
const TONERS_LIST = <?= json_encode($toners) ?>;
const PARAMETROS_INIT = <?= json_encode($parametros) ?>;
const CAN_EDIT   = <?= $canEdit   ? 'true' : 'false' ?>;
const CAN_DELETE = <?= $canDelete ? 'true' : 'false' ?>;
const IS_ADMIN   = <?= $isAdmin   ? 'true' : 'false' ?>;
const USER_FILIAL = <?= json_encode($_SESSION['user_filial'] ?? 'Não informado') ?>;
const USER_NAME = <?= json_encode($_SESSION['user_name'] ?? 'Usuário') ?>;

// ===== ESTADO =====
let currentPage = 1;
let calcDebounce = null;
let lastCalcResult = null;
let filtrosDebounce = null;
let lastGridData = [];

const GRID_COLUMNS_STORAGE_KEY = 'triagem_toners_grid_columns_v1';
const FILTER_STORAGE_KEY = 'triagem_toners_filters_v1';
const ZOOM_STORAGE_KEY = 'triagem_toners_zoom_v1';
const GRID_COLUMNS_DEFAULT = [
  { key: 'id', label: '#', visible: true, locked: true, align: 'left' },
  { key: 'cliente', label: 'Cliente', visible: true },
  { key: 'codigo_requisicao', label: 'Cód. Requisição', visible: true },
  { key: 'filial', label: 'Filial', visible: true },
  { key: 'colaborador', label: 'Colaborador', visible: true },
  { key: 'defeito', label: 'Defeito', visible: true },
  { key: 'fornecedor', label: 'Fornecedor', visible: true },
  { key: 'modelo', label: 'Modelo', visible: true },
  { key: 'modo', label: 'Modo', visible: true },
  { key: 'peso', label: 'Peso Ret. (g)', visible: true },
  { key: 'percentual', label: '% Toner', visible: true },
  { key: 'parecer', label: 'Parecer', visible: true },
  { key: 'destino', label: 'Destino', visible: true },
  { key: 'valor', label: 'Impacto (R$)', visible: true },
  { key: 'datahora', label: 'Data/Hora', visible: true },
  { key: 'acoes', label: 'Ações', visible: true, locked: true, align: 'center' },
];
let gridColumns = [];

function updateGridZoom(val, save = true) {
  const zoom = parseFloat(val);
  document.documentElement.style.setProperty('--grid-zoom', zoom);
  const valDisplay = document.getElementById('grid-zoom-val');
  if (valDisplay) valDisplay.textContent = Math.round(zoom * 100) + '%';
  const slider = document.getElementById('grid-zoom-slider');
  if (slider) slider.value = zoom;
  if (save) localStorage.setItem(ZOOM_STORAGE_KEY, zoom);
  setTimeout(syncTopScrollWidth, 100);
}

function loadZoomPreference() {
  const saved = localStorage.getItem(ZOOM_STORAGE_KEY);
  if (saved) updateGridZoom(saved, false);
}

// Resizable Columns Logic
let resizerState = {
  active: false,
  key: null,
  startX: 0,
  startWidth: 0,
  th: null
};

function initResize(e, key) {
  e.preventDefault();
  const th = e.target.parentElement;
  resizerState = {
    active: true,
    key: key,
    startX: e.pageX,
    startWidth: th.offsetWidth,
    th: th
  };
  e.target.classList.add('resizing');
  window.addEventListener('mousemove', onMouseMoveResize);
  window.addEventListener('mouseup', onMouseUpResize);
}

function onMouseMoveResize(e) {
  if (!resizerState.active) return;
  const delta = e.pageX - resizerState.startX;
  const newWidth = Math.max(50, resizerState.startWidth + delta);
  resizerState.th.style.width = newWidth + 'px';
  resizerState.th.style.minWidth = newWidth + 'px';
  
  // Update gridColumns state
  const col = gridColumns.find(c => c.key === resizerState.key);
  if (col) col.width = newWidth;
  
  syncTopScrollWidth();
}

function onMouseUpResize() {
  if (!resizerState.active) return;
  resizerState.active = false;
  const resizer = resizerState.th.querySelector('.resizer');
  if (resizer) resizer.classList.remove('resizing');
  
  window.removeEventListener('mousemove', onMouseMoveResize);
  window.removeEventListener('mouseup', onMouseUpResize);
  
  saveColumnPreferences();
}

function saveFilters() {
  const filters = {
    search: document.getElementById('f-search').value,
    filial: document.getElementById('f-filial').value,
    modelo: document.getElementById('f-modelo').value,
    cliente: document.getElementById('f-cliente').value,
    colaborador: document.getElementById('f-colaborador').value,
    defeito: document.getElementById('f-defeito').value,
    fornecedor: document.getElementById('f-fornecedor').value,
    'codigo-req': document.getElementById('f-codigo-req').value,
    destino: document.getElementById('f-destino').value,
    modo: document.getElementById('f-modo').value,
    'percentual-min': document.getElementById('f-percentual-min').value,
    'percentual-max': document.getElementById('f-percentual-max').value,
    'data-inicio': document.getElementById('f-data-inicio').value,
    'data-fim': document.getElementById('f-data-fim').value,
  };
  localStorage.setItem(FILTER_STORAGE_KEY, JSON.stringify(filters));
}

function loadFilters() {
  try {
    const saved = localStorage.getItem(FILTER_STORAGE_KEY);
    if (!saved) return;
    const filters = JSON.parse(saved);
    Object.keys(filters).forEach(id => {
      const el = document.getElementById('f-' + id);
      if (el) el.value = filters[id];
    });
  } catch (e) { console.error('Error loading filters', e); }
}

function cloneDefaultColumns() {
  return GRID_COLUMNS_DEFAULT.map(c => ({ ...c }));
}

function loadColumnPreferences() {
  const defaults = cloneDefaultColumns();
  try {
    const savedRaw = localStorage.getItem(GRID_COLUMNS_STORAGE_KEY);
    if (!savedRaw) {
      gridColumns = defaults;
      return;
    }

    const saved = JSON.parse(savedRaw);
    if (!Array.isArray(saved) || saved.length === 0) {
      gridColumns = defaults;
      return;
    }

    const defaultsByKey = new Map(defaults.map(c => [c.key, c]));
    const merged = [];

    saved.forEach((item) => {
      const base = defaultsByKey.get(item.key);
      if (!base) return;
      merged.push({ 
        ...base, 
        visible: base.locked ? true : !!item.visible,
        width: item.width || null
      });
      defaultsByKey.delete(item.key);
    });

    defaultsByKey.forEach((value) => merged.push({ ...value }));
    gridColumns = merged;
  } catch (_) {
    gridColumns = defaults;
  }
}

function getActiveColumns() {
  return gridColumns.filter(c => c.locked || c.visible);
}

function renderGridHeader() {
  const head = document.getElementById('grid-head');
  if (!head) return;

  head.innerHTML = getActiveColumns().map((col) => {
    const alignClass = col.align === 'center' ? 'text-center' : 'text-left';
    const widthStyle = col.width ? `style="width: ${col.width}px; min-width: ${col.width}px;"` : '';
    const resizer = col.locked ? '' : `<div class="resizer" onmousedown="initResize(event, '${col.key}')"></div>`;
    
    return `<th class="px-4 py-3 ${alignClass} font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap" ${widthStyle}>
      ${col.label}
      ${resizer}
    </th>`;
  }).join('');
}

function saveColumnPreferences() {
  const data = gridColumns.map(({ key, visible, width }) => ({ 
    key, 
    visible: !!visible,
    width: width || null
  }));
  localStorage.setItem(GRID_COLUMNS_STORAGE_KEY, JSON.stringify(data));
}

function moveColumn(key, direction) {
  const index = gridColumns.findIndex(c => c.key === key);
  if (index < 0) return;
  const targetIndex = index + direction;
  if (targetIndex < 0 || targetIndex >= gridColumns.length) return;
  const current = gridColumns[index];
  const target = gridColumns[targetIndex];
  if (current.locked || target.locked) return;
  [gridColumns[index], gridColumns[targetIndex]] = [gridColumns[targetIndex], gridColumns[index]];
  renderColunasLista();
}

function renderColunasLista() {
  const lista = document.getElementById('colunas-lista');
  if (!lista) return;

  lista.innerHTML = gridColumns.map((col, index) => {
    const canMoveUp = !col.locked && index > 0 && !gridColumns[index - 1]?.locked;
    const canMoveDown = !col.locked && index < gridColumns.length - 1 && !gridColumns[index + 1]?.locked;

    return `<div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-slate-600 px-3 py-2">
      <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-200">
        <input
          type="checkbox"
          ${col.visible || col.locked ? 'checked' : ''}
          ${col.locked ? 'disabled' : ''}
          onchange="toggleColunaVisivel('${col.key}', this.checked)"
          class="rounded border-gray-300 dark:border-slate-500 text-blue-600 focus:ring-blue-500"
        >
        <span>${col.label}${col.locked ? ' <span class="text-xs text-gray-400 dark:text-gray-500">(fixa)</span>' : ''}</span>
      </label>
      <div class="flex items-center gap-1">
        <button
          type="button"
          onclick="moveColumn('${col.key}', -1)"
          ${canMoveUp ? '' : 'disabled'}
          class="px-2 py-1 text-xs rounded border ${canMoveUp ? 'border-gray-300 dark:border-slate-500 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600' : 'border-gray-200 dark:border-slate-700 text-gray-300 dark:text-slate-600 cursor-not-allowed'}"
          title="Mover para cima"
        >↑</button>
        <button
          type="button"
          onclick="moveColumn('${col.key}', 1)"
          ${canMoveDown ? '' : 'disabled'}
          class="px-2 py-1 text-xs rounded border ${canMoveDown ? 'border-gray-300 dark:border-slate-500 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-600' : 'border-gray-200 dark:border-slate-700 text-gray-300 dark:text-slate-600 cursor-not-allowed'}"
          title="Mover para baixo"
        >↓</button>
      </div>
    </div>`;
  }).join('');
}

function toggleColunaVisivel(key, checked) {
  gridColumns = gridColumns.map((col) => {
    if (col.key !== key || col.locked) return col;
    return { ...col, visible: checked };
  });
}

function abrirModalColunas() {
  renderColunasLista();
  document.getElementById('modal-colunas').classList.remove('hidden');
}

function fecharModalColunas() {
  document.getElementById('modal-colunas').classList.add('hidden');
}

function resetColunasPadrao() {
  gridColumns = cloneDefaultColumns();
  renderColunasLista();
}

function salvarPreferenciasColunas() {
  saveColumnPreferences();
  renderGridHeader();
  renderGrid(lastGridData);
  fecharModalColunas();
}

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', () => {
  // Move modals to <body> to avoid fixed-position issues inside transformed containers
  ['modal-triagem', 'modal-delete', 'modal-importacao', 'modal-params', 'modal-duplicate-cliente', 'modal-colunas'].forEach(id => {
    const el = document.getElementById(id);
    if (el && el.parentElement !== document.body) {
      document.body.appendChild(el);
    }
  });

  const searchInput = document.getElementById('f-search');
  const filialInput = document.getElementById('f-filial');
  const modeloInput = document.getElementById('f-modelo');
  const clienteInput = document.getElementById('f-cliente');
  const colaboradorInput = document.getElementById('f-colaborador');
  const defeitoInput = document.getElementById('f-defeito');
  const fornecedorInput = document.getElementById('f-fornecedor');
  const codigoReqInput = document.getElementById('f-codigo-req');
  const destinoSelect = document.getElementById('f-destino');
  const modoSelect = document.getElementById('f-modo');
  const percentualMinInput = document.getElementById('f-percentual-min');
  const percentualMaxInput = document.getElementById('f-percentual-max');
  const dataInicioInput = document.getElementById('f-data-inicio');
  const dataFimInput = document.getElementById('f-data-fim');

  const filtrarComDebounce = () => {
    clearTimeout(filtrosDebounce);
    filtrosDebounce = setTimeout(() => {
      saveFilters();
      carregarRegistros(1);
    }, 350);
  };

  [searchInput, filialInput, modeloInput, clienteInput, colaboradorInput, defeitoInput, fornecedorInput, codigoReqInput].forEach((el) => {
    el.addEventListener('input', filtrarComDebounce);
    el.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(filtrosDebounce);
        carregarRegistros(1);
      }
    });
  });

  [destinoSelect, modoSelect, percentualMinInput, percentualMaxInput, dataInicioInput, dataFimInput].forEach((el) => {
    el.addEventListener('change', filtrarComDebounce);
  });

  loadColumnPreferences();
  loadFilters();
  loadZoomPreference();
  renderGridHeader();

  // Sync top scrollbar with the main grid scroll
  const topScroll = document.getElementById('grid-top-scroll');
  const gridScroll = document.getElementById('grid-scroll');
  let syncing = false;
  topScroll.addEventListener('scroll', () => {
    if (syncing) return;
    syncing = true;
    gridScroll.scrollLeft = topScroll.scrollLeft;
    syncing = false;
  });
  gridScroll.addEventListener('scroll', () => {
    if (syncing) return;
    syncing = true;
    topScroll.scrollLeft = gridScroll.scrollLeft;
    syncing = false;
  });

  carregarRegistros(1);
});

function syncTopScrollWidth() {
  const gridScroll = document.getElementById('grid-scroll');
  const inner = document.getElementById('grid-top-scroll-inner');
  if (gridScroll && inner) {
    inner.style.width = gridScroll.scrollWidth + 'px';
  }
}

// ===== GRID =====
function carregarRegistros(page) {
  currentPage = page;
  const params = new URLSearchParams({
    page,
    per_page: 15,
    search:       document.getElementById('f-search').value,
    filial:       document.getElementById('f-filial').value,
    modelo:       document.getElementById('f-modelo').value,
    cliente:      document.getElementById('f-cliente').value,
    colaborador:  document.getElementById('f-colaborador').value,
    defeito:      document.getElementById('f-defeito').value,
    fornecedor:   document.getElementById('f-fornecedor').value,
    codigo_requisicao: document.getElementById('f-codigo-req').value,
    destino:      document.getElementById('f-destino').value,
    modo:         document.getElementById('f-modo').value,
    percentual_min: document.getElementById('f-percentual-min').value,
    percentual_max: document.getElementById('f-percentual-max').value,
    data_inicio:  document.getElementById('f-data-inicio').value,
    data_fim:     document.getElementById('f-data-fim').value,
  });

  fetch('/triagem-toners/list?' + params)
    .then(r => r.json())
    .then(res => {
      if (!res.success) { showToast(res.message, 'error'); return; }
      lastGridData = Array.isArray(res.data) ? res.data : [];
      renderGrid(res.data);
      renderPaginacao(res.pagination);
    })
    .catch(() => showToast('Erro ao carregar registros.', 'error'));
}

function renderGrid(data) {
  const tbody = document.getElementById('grid-body');
  const activeCols = getActiveColumns();
  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="${Math.max(activeCols.length, 1)}" class="px-4 py-10 text-center text-gray-400 text-sm">Nenhum registro encontrado.</td></tr>`;
    return;
  }

  tbody.innerHTML = data.map(r => {
    const pct = parseFloat(r.percentual_calculado || 0);
    const pctSalvo = parseFloat((r.percentual_informado ?? r.percentual_calculado) || 0);
    const barColor = pct <= 5 ? 'bg-red-500' : pct <= 40 ? 'bg-orange-400' : pct <= 80 ? 'bg-yellow-400' : 'bg-green-500';
    const destBadge = {
      'Descarte':    'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
      'Garantia':    'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
      'Uso Interno': 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
      'Estoque':     'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    }[r.destino] || 'bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-gray-300';

    const modoBadge = r.modo === 'peso'
      ? '<span class="px-1.5 py-0.5 bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300 rounded text-xs">⚖️ Peso</span>'
      : '<span class="px-1.5 py-0.5 bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300 rounded text-xs">📊 % Direto</span>';

    const peso = r.modo === 'peso' && r.peso_retornado ? `${parseFloat(r.peso_retornado).toFixed(1)}g` : '—';
    const valorRecuperado = parseFloat(r.valor_recuperado || 0);
    const folhasEquivalentes = parseInt(r.folhas_equivalentes || 0, 10);
    let valor = '—';
    if (r.destino === 'Estoque' && valorRecuperado > 0) {
      valor = `<div class="leading-tight"><span class="font-semibold text-green-700 dark:text-green-400">R$ ${Math.abs(valorRecuperado).toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>${folhasEquivalentes > 0 ? `<div class="text-[10px] text-green-700/80 dark:text-green-400/80 mt-0.5">+ ${folhasEquivalentes.toLocaleString('pt-BR')} folhas</div>` : ''}</div>`;
    } else if (r.destino === 'Descarte' && valorRecuperado < 0) {
      valor = `<div class="leading-tight"><span class="font-semibold text-red-700 dark:text-red-400">-R$ ${Math.abs(valorRecuperado).toLocaleString('pt-BR', {minimumFractionDigits:2})}</span>${folhasEquivalentes > 0 ? `<div class="text-[10px] text-red-700/80 dark:text-red-400/80 mt-0.5">- ${folhasEquivalentes.toLocaleString('pt-BR')} folhas</div>` : ''}</div>`;
    }

    const dt = new Date(r.created_at);
    const dtStr = dt.toLocaleDateString('pt-BR') + ' ' + dt.toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});

    const parecerShort = r.parecer ? (r.parecer.length > 60 ? r.parecer.substring(0,60) + '...' : r.parecer) : '—';

    const editBtn  = CAN_EDIT   ? `<button onclick='abrirModalEditar(${JSON.stringify(r)})' class="p-1.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors" title="Editar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>` : '';
    const dupBtn   = CAN_EDIT   ? `<button onclick="duplicarRegistro(${r.id}, ${r.cliente_id || 0})" class="p-1.5 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors" title="Duplicar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V4a1 1 0 011-1h11a1 1 0 011 1v11a1 1 0 01-1 1h-3m-9 4H4a1 1 0 01-1-1V8a1 1 0 011-1h11a1 1 0 011 1v11a1 1 0 01-1 1z"/></svg></button>` : '';
    const delBtn   = CAN_DELETE ? `<button onclick="abrirModalDelete(${r.id})" class="p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors" title="Excluir"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>` : '';

    const cellByColumn = {
      id: `<td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">${r.id}</td>`,
      cliente: `<td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">${escapeHtml(r.cliente_nome || '—')}</td>`,
      codigo_requisicao: `<td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">${escapeHtml(r.codigo_requisicao || '—')}</td>`,
      filial: `<td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">${escapeHtml(r.filial_registro_nome || r.filial_registro || '—')}</td>`,
      colaborador: `<td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">${escapeHtml(r.colaborador_registro_nome || r.colaborador_registro || r.criado_por_nome || '—')}</td>`,
      defeito: `<td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">${escapeHtml(r.defeito_nome || '—')}</td>`,
      fornecedor: `<td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">${escapeHtml(r.fornecedor_nome || '—')}</td>`,
      modelo: `<td class="px-4 py-3 font-medium text-gray-900 dark:text-white text-xs">${escapeHtml(r.toner_modelo || '—')}</td>`,
      modo: `<td class="px-4 py-3">${modoBadge}</td>`,
      peso: `<td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">${peso}</td>`,
      percentual: `<td class="px-4 py-3"><div class="flex items-center gap-2"><div class="w-16 bg-gray-200 dark:bg-slate-600 rounded-full h-1.5"><div class="${barColor} h-1.5 rounded-full" style="width:${pct}%"></div></div><span class="text-xs font-bold text-gray-800 dark:text-gray-200">${pctSalvo.toFixed(1)}%</span></div></td>`,
      parecer: `<td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400 max-w-xs" title="${escapeHtml(r.parecer || '')}">${escapeHtml(parecerShort)}</td>`,
      destino: `<td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium ${destBadge}">${escapeHtml(r.destino || '—')}</span></td>`,
      valor: `<td class="px-4 py-3 text-xs">${valor}</td>`,
      datahora: `<td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">${dtStr}</td>`,
      acoes: `<td class="px-4 py-3 text-center"><div class="flex justify-center gap-1">${editBtn}${dupBtn}${delBtn}</div></td>`,
    };

    const rowCells = activeCols.map((col) => cellByColumn[col.key] || '<td class="px-4 py-3 text-xs text-gray-400 dark:text-gray-500">—</td>').join('');
    return `<tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">${rowCells}</tr>`;
  }).join('');
  // Update top scrollbar width after rendering
  setTimeout(syncTopScrollWidth, 0);
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function renderPaginacao(pag) {
  const pagInfo = document.getElementById('pag-info');
  pagInfo.textContent = `Exibindo ${Math.min((pag.page-1)*pag.per_page+1, pag.total)}–${Math.min(pag.page*pag.per_page, pag.total)} de ${pag.total} registros`;
  pagInfo.className = 'text-sm text-gray-500 dark:text-gray-400';

  const btns = document.getElementById('pag-buttons');
  btns.innerHTML = '';
  for (let i = 1; i <= pag.total_pages; i++) {
    const active = i === pag.page;
    const btn = document.createElement('button');
    btn.textContent = i;
    btn.className = `px-3 py-1 rounded text-sm ${active ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700'}`;
    btn.onclick = () => carregarRegistros(i);
    btns.appendChild(btn);
  }
}

function limparFiltros() {
  ['f-search', 'f-filial', 'f-modelo', 'f-cliente', 'f-colaborador', 'f-defeito', 'f-fornecedor', 'f-codigo-req', 'f-percentual-min', 'f-percentual-max', 'f-data-inicio', 'f-data-fim'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  document.getElementById('f-destino').value = '';
  document.getElementById('f-modo').value = '';
  localStorage.removeItem(FILTER_STORAGE_KEY);
  carregarRegistros(1);
}

// ===== IMPORTAÇÃO =====
function abrirModalImportacao() {
  document.getElementById('modal-importacao').classList.remove('hidden');
  const feedback = document.getElementById('import-feedback');
  feedback.classList.add('hidden');
  feedback.textContent = '';
}

function fecharModalImportacao() {
  document.getElementById('modal-importacao').classList.add('hidden');
  document.getElementById('arquivo-importacao').value = '';
}

function importarPlanilha() {
  const input = document.getElementById('arquivo-importacao');
  const btn = document.getElementById('btn-importar');
  const feedback = document.getElementById('import-feedback');

  if (!input.files || !input.files[0]) {
    feedback.className = 'text-sm rounded-lg p-3 bg-red-50 border border-red-200 text-red-700';
    feedback.textContent = 'Selecione um arquivo para importar.';
    feedback.classList.remove('hidden');
    return;
  }

  const fd = new FormData();
  fd.append('arquivo', input.files[0]);

  btn.disabled = true;
  btn.textContent = 'Importando...';

  fetch('/triagem-toners/importar', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false;
      btn.textContent = 'Importar';

      if (res.success) {
        const detalhesImportados = (res.imported_details && res.imported_details.length)
          ? `\n\nImportados:\n- ${res.imported_details.slice(0, 10).join('\n- ')}`
          : '';
        const erros = (res.errors && res.errors.length)
          ? `\n\nErros:\n- ${res.errors.slice(0, 5).join('\n- ')}`
          : '';
        feedback.className = 'text-sm rounded-lg p-3 bg-green-50 border border-green-200 text-green-700 whitespace-pre-line';
        feedback.textContent = `${res.message}${detalhesImportados}${erros}`;
        feedback.classList.remove('hidden');
        showToast(res.message, 'success');
        carregarRegistros(1);
      } else {
        const detalhesImportados = (res.imported_details && res.imported_details.length)
          ? `\n\nImportados:\n- ${res.imported_details.slice(0, 10).join('\n- ')}`
          : '';
        const erros = (res.errors && res.errors.length)
          ? `\n\nErros:\n- ${res.errors.slice(0, 8).join('\n- ')}`
          : '';
        feedback.className = 'text-sm rounded-lg p-3 bg-red-50 border border-red-200 text-red-700';
        feedback.classList.add('whitespace-pre-line');
        feedback.textContent = `${res.message || 'Erro ao importar arquivo.'}${detalhesImportados}${erros}`;
        feedback.classList.remove('hidden');
        showToast(res.message || 'Erro ao importar arquivo.', 'error');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = 'Importar';
      feedback.className = 'text-sm rounded-lg p-3 bg-red-50 border border-red-200 text-red-700';
      feedback.textContent = 'Erro ao importar arquivo.';
      feedback.classList.remove('hidden');
      showToast('Erro ao importar arquivo.', 'error');
    });
}

// ===== MODAL TRIAGEM =====
function abrirModalNova() {
  resetModalTriagem();
  document.getElementById('modal-titulo').textContent = 'Nova Triagem';
  document.getElementById('modal-triagem').classList.remove('hidden');
}

function abrirModalEditar(r) {
  resetModalTriagem();
  document.getElementById('modal-titulo').textContent = 'Editar Triagem';
  document.getElementById('t-id').value = r.id;
  document.getElementById('t-filial').value = r.filial_registro_nome || r.filial_registro || USER_FILIAL;
  document.getElementById('t-colaborador').value = r.colaborador_registro_nome || r.colaborador_registro || r.criado_por_nome || USER_NAME;
  document.getElementById('t-cliente-id').value = r.cliente_id || '';
  document.getElementById('t-toner-id').value = r.toner_id;
  document.getElementById('t-defeito-id').value = r.defeito_id || '';
  document.getElementById('t-fornecedor-id').value = r.fornecedor_id || '';
  document.getElementById('t-codigo-req').value = r.codigo_requisicao || '';
  sincronizarInputComSelect('t-cliente-id','t-cliente-search');
  sincronizarInputComSelect('t-toner-id','t-toner-search');
  onTonerChange();

  const modo = r.modo;
  document.getElementById('modo-peso').checked = modo === 'peso';
  document.getElementById('modo-pct').checked  = modo === 'percentual';
  onModoChange();

  if (modo === 'peso' && r.peso_retornado) {
    document.getElementById('t-peso').value = r.peso_retornado;
  }
  if (modo === 'percentual' && r.percentual_informado) {
    document.getElementById('t-pct').value = r.percentual_informado;
  }
  document.getElementById('t-destino').value = r.destino;
  document.getElementById('t-obs').value = r.observacoes || '';
  onDestinoChange();
  exibirResultado(
    parseFloat(r.percentual_calculado),
    r.gramatura_restante,
    r.parecer,
    Number(r.valor_recuperado || 0),
    r.destino,
    Number(r.folhas_equivalentes || 0)
  );
  
  if (r.codigo_requisicao) {
    buscarDefeitosPorCodigo(); // Attempt to load if there's a requirement code
  }
  
  document.getElementById('modal-triagem').classList.remove('hidden');
}

function fecharModalTriagem() {
  document.getElementById('modal-triagem').classList.add('hidden');
}

function resetModalTriagem() {
  document.getElementById('t-id').value = '';
  document.getElementById('t-filial').value = USER_FILIAL;
  document.getElementById('t-colaborador').value = USER_NAME;
  document.getElementById('t-cliente-id').value = '';
  document.getElementById('t-cliente-search').value = '';
  document.getElementById('t-toner-id').value = '';
  document.getElementById('t-toner-search').value = '';
  document.getElementById('t-peso').value = '';
  document.getElementById('t-pct').value = '';
  document.getElementById('t-destino').value = '';
  document.getElementById('t-fornecedor-id').value = '';
  document.getElementById('t-defeito-id').value = '';
  document.getElementById('t-codigo-req').value = '';
  document.getElementById('t-obs').value = '';
  document.getElementById('modo-peso').checked = true;
  document.getElementById('info-toner').classList.add('hidden');
  document.getElementById('resultado-calc').classList.add('hidden');
  document.getElementById('info-estoque').classList.add('hidden');
  document.getElementById('info-descarte').classList.add('hidden');
  
  const defContainer = document.getElementById('defeitos-lista-container');
  if (defContainer) defContainer.classList.add('hidden');
  const defLista = document.getElementById('defeitos-opcoes');
  if (defLista) defLista.innerHTML = '';
  
  onModoChange();
  lastCalcResult = null;
}

function onTonerChange() {
  const sel = document.getElementById('t-toner-id');
  const opt = sel.options[sel.selectedIndex];
  const infoBox = document.getElementById('info-toner');
  if (!sel.value) { infoBox.classList.add('hidden'); return; }
  document.getElementById('info-gram').textContent  = parseFloat(opt.dataset.gramatura || 0).toFixed(2);
  document.getElementById('info-cheio').textContent = parseFloat(opt.dataset.pesoCheio || 0).toFixed(2);
  document.getElementById('info-vazio').textContent = parseFloat(opt.dataset.pesoVazio || 0).toFixed(2);
  infoBox.classList.remove('hidden');
  recalcular();
  
  // We no longer trigger search on toner change because order code is filled first
}

let buscarDefeitosDebounce = null;
function debouceBuscarDefeitos() {
  clearTimeout(buscarDefeitosDebounce);
  buscarDefeitosDebounce = setTimeout(buscarDefeitosPorCodigo, 500);
}

function buscarDefeitosPorCodigo() {
  const codigo = document.getElementById('t-codigo-req').value;
  const container = document.getElementById('defeitos-lista-container');
  const lista = document.getElementById('defeitos-opcoes');
  
  if (!codigo) {
    container.classList.add('hidden');
    lista.innerHTML = '';
    // Unlock fields
    document.getElementById('t-cliente-id').disabled = false;
    document.getElementById('t-cliente-search').disabled = false;
    document.getElementById('t-toner-id').disabled = false;
    document.getElementById('t-toner-search').disabled = false;
    return;
  }
  
  lista.innerHTML = '<div class="text-xs text-blue-600">Buscando...</div>';
  container.classList.remove('hidden');
  
  fetch('/triagem-toners/defeitos-codigo?codigo_requisicao=' + encodeURIComponent(codigo))
    .then(async r => {
      if (!r.ok) {
        const text = await r.text();
        throw new Error('HTTP ' + r.status + ' - ' + text.substring(0, 50));
      }
      return r.json();
    })
    .then(res => {
      if (!res.success || !res.data || res.data.length === 0) {
        lista.innerHTML = '<div class="text-xs text-red-600">Nenhum toner com defeito encontrado para este código de requisição. A triagem exigirá que você informe o cliente e modelo manualmente, e as automações de defeito não serão aplicadas.</div>';
        return;
      }
      
      const defeitos = res.data;
      lista.innerHTML = defeitos.map((d, index) => `
        <label class="flex items-start gap-2 p-3 bg-white rounded border ${index === 0 ? 'border-blue-400 bg-blue-50' : 'border-gray-200'} cursor-pointer hover:bg-blue-50 transition-colors">
          <input type="radio" name="rd-toner-defeito" class="rd-toner-defeito mt-0.5" value="${d.id}"
                 data-toner-id="${d.toner_id}" data-cliente-id="${d.cliente_id}" ${index === 0 ? 'checked' : ''} onchange="onSelectDefectiveToner(this)">
          <div class="text-xs text-gray-700 w-full">
            <div class="flex justify-between font-semibold">
              <span class="text-blue-900">${d.toner_modelo}</span>
              <span class="text-red-700">${d.defeito_relatado || 'Defeito não informado'}</span>
            </div>
            <div class="text-gray-500 mt-1">Cliente: ${d.cliente_nome || 'N/A'}</div>
          </div>
        </label>
      `).join('');
      
      // Select the first one automatically
      const firstRadio = lista.querySelector('.rd-toner-defeito');
      if (firstRadio) {
        onSelectDefectiveToner(firstRadio);
      }
    })
    .catch((e) => {
      lista.innerHTML = '<div class="text-xs text-red-600">Erro ao buscar toners: ' + escapeHtml(e.message) + '</div>';
    });
}

function escapeHtml(unsafe) {
    return (unsafe || '').toString()
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

function onSelectDefectiveToner(radioInput) {
  if (!radioInput.checked) return;
  
  // Highlight selected container
  document.querySelectorAll('.rd-toner-defeito').forEach(r => {
    r.closest('label').classList.remove('border-blue-400', 'bg-blue-50');
    r.closest('label').classList.add('border-gray-200');
  });
  radioInput.closest('label').classList.add('border-blue-400', 'bg-blue-50');
  radioInput.closest('label').classList.remove('border-gray-200');

  // Auto-fill form
  const clienteId = radioInput.dataset.clienteId;
  const tonerId = radioInput.dataset.tonerId;
  
  const clienteDropdown = document.getElementById('t-cliente-id');
  const tonerDropdown = document.getElementById('t-toner-id');
  
  if (clienteId && clienteId !== 'null') {
    clienteDropdown.value = clienteId;
    sincronizarSelectComInput('t-cliente-id', 't-cliente-search');
  }
  
  if (tonerId && tonerId !== 'null') {
    tonerDropdown.value = tonerId;
    sincronizarSelectComInput('t-toner-id', 't-toner-search');
    onTonerChange();
  }

  // Lock fields to prevent errors as requested
  clienteDropdown.disabled = true;
  document.getElementById('t-cliente-search').disabled = true;
  tonerDropdown.disabled = true;
  document.getElementById('t-toner-search').disabled = true;
}

function sincronizarSelectComInput(selectId, inputId) {
    const sel = document.getElementById(selectId);
    const inp = document.getElementById(inputId);
    if (!sel || !inp) return;
    if (sel.selectedIndex >= 0) {
        let text = sel.options[sel.selectedIndex].text;
        if (sel.value === "") text = "";
        inp.value = text;
    }
}

function onModoChange() {
  const isPeso = document.getElementById('modo-peso').checked;
  document.getElementById('campo-peso').classList.toggle('hidden', !isPeso);
  document.getElementById('campo-pct').classList.toggle('hidden', isPeso);
  recalcular();
}

function onDestinoChange() {
  const dest = document.getElementById('t-destino').value;
  document.getElementById('info-estoque').classList.toggle('hidden', dest !== 'Estoque');
  document.getElementById('info-descarte').classList.toggle('hidden', dest !== 'Descarte');
  const wrapFornecedor = document.getElementById('wrap-fornecedor');
  const selectFornecedor = document.getElementById('t-fornecedor-id');
  const isGarantia = dest === 'Garantia';
  wrapFornecedor.classList.toggle('hidden', !isGarantia);
  if (!isGarantia) {
    selectFornecedor.value = '';
  }
  if (lastCalcResult) {
    exibirResultado(
      lastCalcResult.pct,
      lastCalcResult.gram,
      lastCalcResult.parecer,
      dest === 'Estoque' ? lastCalcResult.valorEstoque : (dest === 'Descarte' ? lastCalcResult.valorDescarte : 0),
      dest,
      lastCalcResult.folhasEquivalentes
    );
  }
}

function recalcular() {
  clearTimeout(calcDebounce);
  calcDebounce = setTimeout(() => {
    const tonerId = document.getElementById('t-toner-id').value;
    if (!tonerId) return;

    const modo = document.getElementById('modo-peso').checked ? 'peso' : 'percentual';
    const peso = document.getElementById('t-peso').value;
    const pct  = document.getElementById('t-pct').value;

    if (modo === 'peso' && !peso) return;
    if (modo === 'percentual' && !pct) return;

    const fd = new FormData();
    fd.append('toner_id', tonerId);
    fd.append('modo', modo);
    if (modo === 'peso') fd.append('peso_retornado', peso);
    else                 fd.append('percentual', pct);

    fetch('/triagem-toners/calcular', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(r => r.json())
      .then(res => {
        if (!res.success) { showToast(res.message, 'error'); return; }
        const dest = document.getElementById('t-destino').value;
        lastCalcResult = {
          pct: res.percentual_calculado,
          gram: res.gramatura_restante,
          parecer: res.parecer,
          valorEstoque: Number(res.valor_estoque || 0),
          valorDescarte: Number(res.valor_descarte || 0),
          folhasEquivalentes: Number(res.folhas_equivalentes || 0),
        };
        exibirResultado(res.percentual_calculado, res.gramatura_restante, res.parecer,
          dest === 'Estoque' ? Number(res.valor_estoque || 0) : (dest === 'Descarte' ? Number(res.valor_descarte || 0) : 0),
          dest,
          Number(res.folhas_equivalentes || 0));
      });
  }, 400);
}

function exibirResultado(pct, gram, parecer, valor, destino, folhasEquivalentes = 0) {
  document.getElementById('resultado-calc').classList.remove('hidden');
  document.getElementById('res-pct').textContent  = pct.toFixed(2) + '%';
  document.getElementById('res-gram').textContent = gram ? parseFloat(gram).toFixed(2) : '—';

  const barra = document.getElementById('res-barra');
  barra.style.width = pct + '%';
  barra.className = 'h-3 rounded-full transition-all duration-500 ' +
    (pct <= 5 ? 'bg-red-500' : pct <= 40 ? 'bg-orange-400' : pct <= 80 ? 'bg-yellow-400' : 'bg-green-500');

  const valorWrap = document.getElementById('res-valor-wrap');
  const valorLabel = document.getElementById('res-valor-label');
  const valorEl = document.getElementById('res-valor');
  const folhasWrap = document.getElementById('res-folhas-wrap');
  const folhasEl = document.getElementById('res-folhas');

  if ((destino === 'Estoque' || destino === 'Descarte') && folhasEquivalentes > 0) {
    folhasEl.textContent = Number(folhasEquivalentes).toLocaleString('pt-BR');
    folhasWrap.classList.remove('hidden');
  } else {
    folhasWrap.classList.add('hidden');
  }

  if (destino === 'Estoque' && valor > 0) {
    valorLabel.textContent = '💰 Valor recuperado:';
    valorEl.className = 'text-green-700';
    valorEl.textContent = 'R$ ' + Math.abs(parseFloat(valor)).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    valorWrap.classList.remove('hidden');
  } else if (destino === 'Descarte' && valor < 0) {
    valorLabel.textContent = '💸 Valor descartado:';
    valorEl.className = 'text-red-700';
    valorEl.textContent = '-R$ ' + Math.abs(parseFloat(valor)).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    valorWrap.classList.remove('hidden');
  } else {
    valorWrap.classList.add('hidden');
  }

  if (parecer) {
    const box = document.getElementById('res-parecer-box');
    box.classList.remove('hidden');
    // Cor do box conforme percentual
    box.className = 'rounded-lg p-3 border ' + (pct <= 5 ? 'bg-red-50 border-red-300' : pct <= 40 ? 'bg-orange-50 border-orange-300' : pct <= 80 ? 'bg-yellow-50 border-yellow-300' : 'bg-green-50 border-green-300');
    document.getElementById('res-parecer').textContent = parecer;
  }
}

function salvarTriagem() {
  const id       = document.getElementById('t-id').value;
  const clienteId = document.getElementById('t-cliente-id').value;
  const tonerId  = document.getElementById('t-toner-id').value;
  const modo     = document.getElementById('modo-peso').checked ? 'peso' : 'percentual';
  const peso     = document.getElementById('t-peso').value;
  const pct      = document.getElementById('t-pct').value;
  const destino  = document.getElementById('t-destino').value;
  const fornecedorId = document.getElementById('t-fornecedor-id').value;
  const defeitoId = document.getElementById('t-defeito-id').value;
  const codigoReq = document.getElementById('t-codigo-req').value;
  const obs      = document.getElementById('t-obs').value;

  if (!clienteId || !tonerId || !destino) {
    showToast('Preencha cliente, toner e destino.', 'error'); return;
  }
  if (modo === 'peso' && !peso) {
    showToast('Informe o peso retornado.', 'error'); return;
  }
  if (modo === 'percentual' && !pct) {
    showToast('Informe o percentual.', 'error'); return;
  }
  if (destino === 'Garantia' && !fornecedorId) {
    showToast('Selecione o fornecedor para destino Garantia.', 'error'); return;
  }

  const fd = new FormData();
  if (id) fd.append('id', id);
  fd.append('cliente_id', clienteId);
  fd.append('toner_id', tonerId);
  fd.append('modo', modo);
  if (modo === 'peso') fd.append('peso_retornado', peso);
  else                 fd.append('percentual', pct);
  fd.append('destino', destino);
  fd.append('fornecedor_id', fornecedorId);
  fd.append('defeito_id', defeitoId);
  fd.append('codigo_requisicao', codigoReq);
  fd.append('observacoes', obs);
  
  const selectedRadio = document.querySelector('.rd-toner-defeito:checked');
  if (selectedRadio) {
    fd.append('toners_defeitos_ids[]', selectedRadio.value);
  }

  const url = id ? '/triagem-toners/update' : '/triagem-toners/store';
  const btn = document.getElementById('btn-salvar');
  btn.disabled = true;
  btn.textContent = 'Salvando...';

  fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      btn.disabled = false;
      btn.textContent = 'Salvar';
      if (res.success) {
        showToast(res.message, 'success');
        fecharModalTriagem();
        carregarRegistros(currentPage);
      } else {
        showToast(res.message, 'error');
      }
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Salvar'; showToast('Erro ao salvar.', 'error'); });
}

// ===== MODAL DELETE =====
function abrirModalDelete(id) {
  document.getElementById('delete-id').value = id;
  document.getElementById('modal-delete').classList.remove('hidden');
}
function fecharModalDelete() {
  document.getElementById('modal-delete').classList.add('hidden');
}
function confirmarDelete() {
  const id = document.getElementById('delete-id').value;
  const fd = new FormData();
  fd.append('id', id);
  fetch('/triagem-toners/delete', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      fecharModalDelete();
      if (res.success) { showToast(res.message, 'success'); carregarRegistros(currentPage); }
      else             { showToast(res.message, 'error'); }
    });
}

function duplicarRegistro(id, clienteIdAtual = 0) {
  if (confirm('Deseja trocar o cliente na duplicação?\n\nOK = Sim | Cancelar = Não')) {
    abrirModalDuplicateCliente(id, clienteIdAtual);
    return;
  }

  executarDuplicacao(id, null);
}

function abrirModalDuplicateCliente(id, clienteIdAtual = 0) {
  document.getElementById('duplicate-id').value = id;
  document.getElementById('duplicate-cliente-id').value = clienteIdAtual || '';
  sincronizarInputComSelect('duplicate-cliente-id', 'duplicate-cliente-search');
  document.getElementById('modal-duplicate-cliente').classList.remove('hidden');
}

function fecharModalDuplicateCliente() {
  document.getElementById('duplicate-cliente-search').value = '';
  document.getElementById('duplicate-cliente-id').value = '';
  document.getElementById('modal-duplicate-cliente').classList.add('hidden');
}

function confirmarDuplicacaoComCliente() {
  const id = parseInt(document.getElementById('duplicate-id').value || '0', 10);
  const clienteId = parseInt(document.getElementById('duplicate-cliente-id').value || '0', 10);
  if (!id) {
    showToast('Registro inválido para duplicação.', 'error');
    return;
  }
  if (!clienteId) {
    showToast('Selecione o cliente para duplicar.', 'error');
    return;
  }
  fecharModalDuplicateCliente();
  executarDuplicacao(id, clienteId);
}

function executarDuplicacao(id, clienteId = null) {
  const fd = new FormData();
  fd.append('id', id);
  if (clienteId) {
    fd.append('cliente_id', clienteId);
  }

  fetch('/triagem-toners/duplicate', { method: 'POST', body: fd, credentials: 'same-origin' })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast(res.message, 'success');
        carregarRegistros(1);
      } else {
        showToast(res.message, 'error');
      }
    })
    .catch(() => showToast('Erro ao duplicar registro.', 'error'));
}

// ===== MODAL PARÂMETROS =====
function abrirModalParametros() {
  fetch('/triagem-toners/parametros')
    .then(r => r.json())
    .then(res => {
      renderParametros(res.data || PARAMETROS_INIT);
      document.getElementById('modal-params').classList.remove('hidden');
    });
}
function fecharModalParams() {
  document.getElementById('modal-params').classList.add('hidden');
}
function renderParametros(params) {
  const lista = document.getElementById('params-lista');
  lista.innerHTML = '';
  params.forEach((p, i) => {
    const div = document.createElement('div');
    div.className = 'flex gap-3 items-start bg-gray-50 rounded-xl p-3 border border-gray-200';
    div.dataset.index = i;
    div.innerHTML = `
      <div class="flex flex-col items-center gap-1">
        <label class="text-xs text-gray-500">Min %</label>
        <input type="number" step="0.01" min="0" max="100" value="${p.percentual_min}" class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 param-min">
      </div>
      <div class="flex flex-col items-center gap-1">
        <label class="text-xs text-gray-500">Max %</label>
        <input type="number" step="0.01" min="0" max="100" value="${p.percentual_max}" class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 param-max">
      </div>
      <div class="flex-1 flex flex-col gap-1">
        <label class="text-xs text-gray-500">Parecer</label>
        <textarea rows="2" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none param-parecer">${p.parecer}</textarea>
      </div>
      <button onclick="removeParam(this)" class="mt-5 text-red-400 hover:text-red-600 transition-colors" title="Remover">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>`;
    lista.appendChild(div);
  });
}
function addParam() {
  const lista = document.getElementById('params-lista');
  const count = lista.children.length;
  const div = document.createElement('div');
  div.className = 'flex gap-3 items-start bg-gray-50 rounded-xl p-3 border border-gray-200';
  div.dataset.index = count;
  div.innerHTML = `
    <div class="flex flex-col items-center gap-1">
      <label class="text-xs text-gray-500">Min %</label>
      <input type="number" step="0.01" min="0" max="100" value="0" class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 param-min">
    </div>
    <div class="flex flex-col items-center gap-1">
      <label class="text-xs text-gray-500">Max %</label>
      <input type="number" step="0.01" min="0" max="100" value="100" class="w-20 border border-gray-300 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500 param-max">
    </div>
    <div class="flex-1 flex flex-col gap-1">
      <label class="text-xs text-gray-500">Parecer</label>
      <textarea rows="2" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none param-parecer"></textarea>
    </div>
    <button onclick="removeParam(this)" class="mt-5 text-red-400 hover:text-red-600 transition-colors" title="Remover">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>`;
  lista.appendChild(div);
}
function removeParam(btn) {
  btn.closest('[data-index]').remove();
}
function salvarParametros() {
  const lista = document.getElementById('params-lista');
  const items = lista.querySelectorAll('[data-index]');
  const params = [];
  let valid = true;
  items.forEach(item => {
    const min    = parseFloat(item.querySelector('.param-min').value);
    const max    = parseFloat(item.querySelector('.param-max').value);
    const parecer = item.querySelector('.param-parecer').value.trim();
    if (isNaN(min) || isNaN(max) || !parecer) { valid = false; return; }
    params.push({ percentual_min: min, percentual_max: max, parecer });
  });
  if (!valid) { showToast('Preencha todos os campos dos parâmetros.', 'error'); return; }

  fetch('/triagem-toners/parametros/save', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(params),
    credentials: 'same-origin'
  })
    .then(r => r.json())
    .then(res => {
      if (res.success) { showToast(res.message, 'success'); fecharModalParams(); }
      else             { showToast(res.message, 'error'); }
    });
}

// ===== TOAST =====
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  const ti = document.getElementById('toast-inner');
  ti.className = 'px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white max-w-sm ' +
    (type === 'success' ? 'bg-green-600' : 'bg-red-600');
  ti.textContent = msg;
  t.classList.remove('hidden');
  setTimeout(() => t.classList.add('hidden'), 3500);
}

function normalizarTexto(texto) {
  return (texto || '')
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

function pontuarCorrespondencia(termo, candidato) {
  if (!termo) return 0;
  if (candidato === termo) return 1000;
  if (candidato.startsWith(termo)) return 700 - (candidato.length - termo.length);

  const idx = candidato.indexOf(termo);
  if (idx >= 0) return 500 - idx;

  const partes = termo.split(/\s+/).filter(Boolean);
  if (partes.length > 1 && partes.every(p => candidato.includes(p))) {
    return 350;
  }

  return -1;
}

function autoSelecionarInteligente(inputId, selectId, onChangeFnName = null) {
  const input = document.getElementById(inputId);
  const select = document.getElementById(selectId);
  const termo = normalizarTexto(input.value);
  const options = Array.from(select.options);

  const matches = [];
  options.forEach((opt, idx) => {
    if (idx === 0) {
      opt.hidden = false;
      return;
    }
    const candidato = normalizarTexto(opt.text);
    const score = pontuarCorrespondencia(termo, candidato);
    const isMatch = termo === '' || score >= 0;
    opt.hidden = !isMatch;
    if (isMatch && termo !== '') {
      matches.push({ value: opt.value, score, text: opt.text });
    }
  });

  if (termo === '') {
    return;
  }

  matches.sort((a, b) => b.score - a.score || a.text.localeCompare(b.text));
  if (matches.length > 0) {
    const melhor = matches[0];
    const mudou = select.value !== melhor.value;
    select.value = melhor.value;
    if (mudou && onChangeFnName && typeof window[onChangeFnName] === 'function') {
      window[onChangeFnName]();
    }
  }
}

function sincronizarInputComSelect(selectId, inputId) {
  const select = document.getElementById(selectId);
  const input = document.getElementById(inputId);
  const opt = select.options[select.selectedIndex];
  if (!opt || !opt.value) return;
  input.value = opt.text;
}
</script>
