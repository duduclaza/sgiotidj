
<?php if (isset($error)): ?>
  <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
    <?= e($error) ?>
  </div>
<?php endif; ?>

<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Amostragens</h1>
    <div class="flex space-x-3">
      <button id="toggleAmostragemFormBtn" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Nova Amostragem</span>
      </button>
    </div>
  </div>

  <!-- Formulário Inline de Nova Amostragem -->
  <div id="amostragemFormContainer" class="hidden bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-lg p-6 shadow-sm transition-colors">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Nova Amostragem</h2>
      <button id="closeAmostragemFormBtn" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="amostragemForm" class="space-y-6" enctype="multipart/form-data">
      <input type="hidden" name="id" id="amostragemId">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Número da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número da NF *</label>
          <input type="text" name="numero_nf" required class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-white transition-colors">
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Status *</label>
          <div class="flex space-x-4">
            <label class="flex items-center">
              <input type="radio" name="status" value="pendente" class="mr-2" checked>
              <span class="text-sm font-medium text-yellow-700 dark:text-yellow-500">Pendente</span>
            </label>
            <label class="flex items-center">
              <input type="radio" name="status" value="aprovado" class="mr-2">
              <span class="text-sm font-medium text-green-700 dark:text-green-500">Aprovado</span>
            </label>
            <label class="flex items-center">
              <input type="radio" name="status" value="reprovado" class="mr-2">
              <span class="text-sm font-medium text-red-700 dark:text-red-500">Reprovado</span>
            </label>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Anexo da NF -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Anexo da NF (PDF) *</label>
          <input id="arquivo_nf" type="file" name="arquivo_nf" accept="application/pdf,.pdf" required onchange="validatePdfFile(this)" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-white transition-colors">
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Apenas PDF até 10MB</p>
          <div id="arquivo_nf_preview" class="mt-2"></div>
        </div>

        <!-- Evidências -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Evidências (Fotos)</label>
          <input id="evidencias" type="file" name="evidencias[]" accept="image/*" multiple onchange="validateEvidenceFiles(this)" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-white transition-colors">
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selecione uma ou mais fotos como evidência (opcional)</p>
          <div id="evidencias_preview" class="mt-2"></div>
        </div>
      </div>

      <!-- Responsáveis -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Responsáveis *</label>
        <div class="border border-gray-300 dark:border-slate-600 rounded-lg p-3 bg-white dark:bg-slate-900 transition-colors" style="min-height: 120px; max-height: 200px; overflow-y: auto;">
          <div id="responsaveisCheckboxes">
            <!-- Checkboxes will be loaded dynamically -->
          </div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selecione um ou mais responsáveis</p>
      </div>

      <!-- Observação -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observação</label>
        <textarea name="observacao" rows="3" placeholder="Observações sobre a amostragem (opcional)..." class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none bg-white dark:bg-slate-900 text-gray-900 dark:text-white transition-colors"></textarea>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Campo obrigatório apenas para status "Reprovado"</p>
      </div>

      <!-- Botões -->
      <div class="flex justify-end space-x-4 pt-4 border-t dark:border-slate-700 transition-colors">
        <button type="button" onclick="closeAmostragemForm()" class="px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
          Cancelar
        </button>
        <button type="button" onclick="submitAmostragem()" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
          Salvar Amostragem
        </button>
      </div>
    </form>
  </div>

  <!-- Filters and Search -->
  <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-lg p-4 shadow-sm transition-colors">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
        <input type="text" id="searchInput" placeholder="Número da NF, status..." class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-white transition-colors">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
        <select id="statusFilter" class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-white transition-colors">
          <option value="">Todos</option>
          <option value="pendente">Pendente</option>
          <option value="aprovado">Aprovado</option>
          <option value="reprovado">Reprovado</option>
        </select>
      </div>
      <div class="flex items-end space-x-2">
        <button onclick="limparFiltros()" class="flex-1 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
          <span>Limpar</span>
        </button>
        <button onclick="window.printAmostragens()" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center space-x-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          <span>Imprimir</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Amostragens Grid -->
  <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-lg overflow-hidden shadow-sm transition-colors">
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
      <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
        <thead class="bg-gray-50 dark:bg-slate-900/50" id="amostragemTableHead">
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número NF</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsáveis</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observação</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="amostragemTableBody">
          <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Carregando...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  </style>

  <style>
  /* Grid Zoom & Resizer Styles */
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
  .resizer {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 6px;
    cursor: col-resize;
    user-select: none;
    z-index: 10;
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
</section>

<!-- Modal removido - usando formulário inline -->

<script>
let selectedStatus = '';
let activityLog = [];
let filtrosDebounce;
let currentZoom = 1.0;
let isResizing = false;
let currentResizer = null;

// Configurações de Persistência
const STORAGE_KEYS = {
  filters: 'amostragens_filters_v1',
  zoom: 'amostragens_zoom_v1',
  columns: 'amostragens_grid_columns_v1'
};

// Activity logging
function logActivity(type, action, details = {}) {
  const timestamp = new Date().toISOString();
  activityLog.push({ timestamp, type, action, details });
  console.log(`[${type.toUpperCase()}] ${action}:`, details);
}

// ===== GRID & FILTROS =====

const carregarRegistros = async () => {
  const tbody = document.getElementById('amostragemTableBody');
  tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">🔍 Buscando registros...</td></tr>';

  const search = document.getElementById('searchInput').value;
  const status = document.getElementById('statusFilter').value;

  try {
    const response = await fetch(`/toners/amostragens/list?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`);
    const result = await response.json();

    if (result.success) {
      renderGrid(result.data);
    } else {
      tbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-red-500">${result.message}</td></tr>`;
    }
  } catch (error) {
    console.error('Erro ao carregar registros:', error);
    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-red-500">Erro de conexão ao carregar registros.</td></tr>';
  }
};

const renderGrid = (data) => {
  const tbody = document.getElementById('amostragemTableBody');
  tbody.innerHTML = '';

  if (data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">Nenhuma amostragem encontrada</td></tr>';
    return;
  }

  data.forEach(item => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors';
    
    // Responsáveis formatados
    let respHtml = '-';
    if (item.responsaveis_list && item.responsaveis_list.length > 0) {
      const names = item.responsaveis_list.map(r => r.name).filter(n => n);
      if (names.length > 0) {
        respHtml = names.slice(0, 2).join(', ');
        if (names.length > 2) respHtml += ` +${names.length - 2}`;
      }
    }

    const statusClasses = item.status === 'aprovado' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                         (item.status === 'pendente' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400');

    tr.innerHTML = `
      <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 font-medium">${item.numero_nf}</td>
      <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">${respHtml}</td>
      <td class="px-4 py-2">
        <select onchange="updateStatus(${item.id}, this.value, this)" class="text-xs font-semibold rounded-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 cursor-pointer ${statusClasses}">
          <option value="pendente" ${item.status === 'pendente' ? 'selected' : ''}>Pendente</option>
          <option value="aprovado" ${item.status === 'aprovado' ? 'selected' : ''}>Aprovado</option>
          <option value="reprovado" ${item.status === 'reprovado' ? 'selected' : ''}>Reprovado</option>
        </select>
      </td>
      <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">${new Date(item.data_registro).toLocaleDateString('pt-BR')}</td>
      <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
        <div class="flex items-center space-x-2">
          <span id="obs-text-${item.id}" class="flex-1 cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors" onclick="editObservacao(${item.id})" title="Clique para editar">
            ${item.observacao ? (item.observacao.substring(0, 50) + (item.observacao.length > 50 ? '...' : '')) : '<span class="text-gray-400 dark:text-gray-500 italic">Clique para adicionar</span>'}
          </span>
          <textarea id="obs-input-${item.id}" class="hidden flex-1 text-xs border border-gray-300 dark:border-slate-600 rounded px-2 py-1 resize-none bg-white dark:bg-slate-900 text-gray-900 dark:text-white" rows="2" placeholder="Digite a observação...">${item.observacao || ''}</textarea>
          <div id="obs-buttons-${item.id}" class="hidden flex space-x-1">
            <button onclick="saveObservacao(${item.id})" class="text-green-600 hover:text-green-800 text-xs">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
            </button>
            <button onclick="cancelEditObservacao(${item.id})" class="text-red-600 hover:text-red-800 text-xs">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </button>
          </div>
        </div>
      </td>
      <td class="px-4 py-2 text-sm">
        <div class="flex items-center space-x-2">
          ${item.has_pdf ? `
            <a href="/toners/amostragens/${item.id}/pdf" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs bg-blue-50 dark:bg-blue-900/30 px-2 py-1 rounded transition-colors">
              <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
              PDF
            </a>
          ` : ''}
          ${item.total_evidencias > 0 ? `
            <button onclick="viewEvidencias(${item.id})" class="text-green-600 dark:text-green-400 text-xs bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors border-none cursor-pointer">
              <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path></svg>
              ${item.total_evidencias} foto(s)
            </button>
          ` : ''}
          <button onclick="excluirAmostragem(${item.id}, '${item.numero_nf}')" class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded-md font-medium transition-colors duration-200 shadow-sm hover:shadow-md">
            Excluir
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
  
  updateGridTopScroll();
};

const filtrarComDebounce = () => {
  clearTimeout(filtrosDebounce);
  filtrosDebounce = setTimeout(() => {
    saveFilters();
    carregarRegistros();
  }, 350);
};

// ===== PERSISTÊNCIA =====

const saveFilters = () => {
  const filters = {
    search: document.getElementById('searchInput').value,
    status: document.getElementById('statusFilter').value
  };
  localStorage.setItem(STORAGE_KEYS.filters, JSON.stringify(filters));
};

const loadFilters = () => {
  const saved = localStorage.getItem(STORAGE_KEYS.filters);
  if (saved) {
    const filters = JSON.parse(saved);
    document.getElementById('searchInput').value = filters.search || '';
    document.getElementById('statusFilter').value = filters.status || '';
  }
};

const limparFiltros = () => {
  document.getElementById('searchInput').value = '';
  document.getElementById('statusFilter').value = '';
  saveFilters();
  carregarRegistros();
};

// ===== ZOOM =====

const updateGridZoom = (val) => {
  currentZoom = val;
  document.documentElement.style.setProperty('--grid-zoom', val);
  document.getElementById('grid-zoom-val').innerText = `${Math.round(val * 100)}%`;
  document.getElementById('grid-zoom-slider').value = val;
  localStorage.setItem(STORAGE_KEYS.zoom, val);
  updateGridTopScroll();
};

const loadZoomPreference = () => {
  const saved = localStorage.getItem(STORAGE_KEYS.zoom);
  if (saved) {
    updateGridZoom(parseFloat(saved));
  }
};

// ===== RESIZE COLUMNS =====

const renderGridHeader = () => {
  const savedWidths = JSON.parse(localStorage.getItem(STORAGE_KEYS.columns) || '{}');
  const headers = [
    { text: 'Número NF', id: 'numero_nf' },
    { text: 'Responsáveis', id: 'responsaveis' },
    { text: 'Status', id: 'status' },
    { text: 'Data', id: 'data' },
    { text: 'Observação', id: 'observacao' },
    { text: 'Ações', id: 'acoes' }
  ];

  const thead = document.getElementById('amostragemTableHead');
  thead.innerHTML = '';
  const tr = document.createElement('tr');

  headers.forEach((header, index) => {
    const th = document.createElement('th');
    th.className = 'px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50 dark:bg-slate-900/50';
    th.innerText = header.text;
    th.dataset.colId = header.id;

    if (savedWidths[header.id]) {
      th.style.width = savedWidths[header.id];
    }

    const resizer = document.createElement('div');
    resizer.className = 'resizer';
    resizer.addEventListener('mousedown', (e) => initResize(e, th, header.id));
    th.appendChild(resizer);
    tr.appendChild(th);
  });

  thead.appendChild(tr);
};

const initResize = (e, th, colId) => {
  isResizing = true;
  currentResizer = {
    th: th,
    colId: colId,
    startX: e.pageX,
    startWidth: th.offsetWidth
  };
  th.querySelector('.resizer').classList.add('resizing');
  document.body.style.cursor = 'col-resize';
  
  window.addEventListener('mousemove', onMouseMoveResize);
  window.addEventListener('mouseup', onMouseUpResize);
};

const onMouseMoveResize = (e) => {
  if (!isResizing || !currentResizer) return;
  const diff = e.pageX - currentResizer.startX;
  const newWidth = Math.max(50, currentResizer.startWidth + diff);
  currentResizer.th.style.width = `${newWidth}px`;
  updateGridTopScroll();
};

const onMouseUpResize = () => {
  if (!isResizing || !currentResizer) return;
  
  const savedWidths = JSON.parse(localStorage.getItem(STORAGE_KEYS.columns) || '{}');
  savedWidths[currentResizer.colId] = currentResizer.th.style.width;
  localStorage.setItem(STORAGE_KEYS.columns, JSON.stringify(savedWidths));
  
  currentResizer.th.querySelector('.resizer').classList.remove('resizing');
  isResizing = false;
  currentResizer = null;
  document.body.style.cursor = '';
  
  window.removeEventListener('mousemove', onMouseMoveResize);
  window.removeEventListener('mouseup', onMouseUpResize);
};

// ===== SCROLL SYNCHRONIZATION =====

const updateGridTopScroll = () => {
  const gridScroll = document.getElementById('grid-scroll');
  const topScroll = document.getElementById('grid-top-scroll');
  const topScrollInner = document.getElementById('grid-top-scroll-inner');
  
  if (gridScroll && topScroll && topScrollInner) {
    const table = gridScroll.querySelector('table');
    if (table) {
      topScrollInner.style.width = `${table.offsetWidth}px`;
      topScroll.scrollLeft = gridScroll.scrollLeft;
    }
  }
};

// Modal functions
function toggleAmostragemForm() {
  const container = document.getElementById('amostragemFormContainer');
  const btn = document.getElementById('toggleAmostragemFormBtn');
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    btn.innerHTML = `
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
      <span>Cancelar</span>
    `;
    // default status e usuários
    document.querySelector('input[name="status"][value="pendente"]').checked = true;
    selectedStatus = 'pendente';
    loadUsers();
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } else {
    closeAmostragemForm();
  }
}

function closeAmostragemForm() {
  const container = document.getElementById('amostragemFormContainer');
  const btn = document.getElementById('toggleAmostragemFormBtn');
  const form = document.getElementById('amostragemForm');
  container.classList.add('hidden');
  form.reset();
  btn.innerHTML = `
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
    </svg>
    <span>Nova Amostragem</span>
  `;
  // reset status e previews
  document.querySelector('input[name="status"][value="pendente"]').checked = true;
  clearFilePreview('arquivo_nf');
  clearFilePreview('evidencias');
}


function loadUsers() {
  console.log('Iniciando carregamento de usuários...');
  fetch('/api/users')
    .then(response => {
      console.log('Response status:', response.status);
      return response.json();
    })
    .then(users => {
      console.log('Dados recebidos da API:', users);
      const container = document.getElementById('responsaveisCheckboxes');
      console.log('Container element:', container);
      
      if (!container) {
        console.error('Container de checkboxes não encontrado!');
        return;
      }
      
      container.innerHTML = '';
      
      if (Array.isArray(users)) {
        users.forEach((user, index) => {
          const checkboxDiv = document.createElement('div');
          checkboxDiv.className = 'flex items-center p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg mb-1 transition-colors';
          
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = 'responsaveis[]';
          checkbox.value = JSON.stringify({ name: user.name, email: user.email });
          checkbox.id = `responsavel_${index}`;
          checkbox.className = 'mr-3 text-blue-600 focus:ring-blue-500 h-4 w-4';
          
          const label = document.createElement('label');
          label.htmlFor = `responsavel_${index}`;
          label.innerHTML = `<div class="flex flex-col"><span class="text-sm font-medium text-gray-900 dark:text-gray-100">${user.name}</span><span class="text-xs text-gray-500 dark:text-gray-400">${user.email}</span></div>`;
          label.className = 'cursor-pointer flex-1';
          
          checkboxDiv.appendChild(checkbox);
          checkboxDiv.appendChild(label);
          container.appendChild(checkboxDiv);
        });
        console.log('Usuários carregados:', users.length);
      } else {
        console.error('Resposta da API não é um array:', users);
        // Add test checkbox
        const checkboxDiv = document.createElement('div');
        checkboxDiv.className = 'flex items-center mb-2';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'responsaveis[]';
        checkbox.value = JSON.stringify({ name: 'Test User', email: 'test@example.com' });
        checkbox.id = 'responsavel_test';
        checkbox.className = 'mr-3 text-blue-600 focus:ring-blue-500 h-4 w-4';
        
        const label = document.createElement('label');
        label.htmlFor = 'responsavel_test';
        label.textContent = 'Test User (test@example.com)';
        label.className = 'text-sm cursor-pointer';
        
        checkboxDiv.appendChild(checkbox);
        checkboxDiv.appendChild(label);
        container.appendChild(checkboxDiv);
        console.log('Adicionado checkbox de teste');
      }
    })
    .catch(error => {
      console.error('Erro ao carregar usuários:', error);
      // Add test checkbox on error
      const container = document.getElementById('responsaveisCheckboxes');
      if (container) {
        const checkboxDiv = document.createElement('div');
        checkboxDiv.className = 'flex items-center mb-2';
        
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'responsaveis[]';
        checkbox.value = 'Test User';
        checkbox.id = 'responsavel_test';
        checkbox.className = 'mr-2';
        
        const label = document.createElement('label');
        label.htmlFor = 'responsavel_test';
        label.textContent = 'Test User (test@example.com)';
        label.className = 'text-sm cursor-pointer';
        
        checkboxDiv.appendChild(checkbox);
        checkboxDiv.appendChild(label);
        container.appendChild(checkboxDiv);
        console.log('Adicionado checkbox de teste devido ao erro');
      }
    });
}

function submitAmostragem() {
  logActivity('form', 'Submit Amostragem');
  
  // Get form values manually instead of using FormData
  const numeroNf = document.querySelector('input[name="numero_nf"]')?.value || '';
  const statusSelected = document.querySelector('input[name="status"]:checked')?.value || '';
  const observacao = document.querySelector('textarea[name="observacao"]')?.value || '';
  
  // Get selected responsaveis
  const responsaveisChecked = document.querySelectorAll('input[name="responsaveis[]"]:checked');
  const responsaveis = Array.from(responsaveisChecked).map(checkbox => checkbox.value);
  
  // Get files
  const arquivoNf = document.querySelector('input[name="arquivo_nf"]')?.files[0];
  const evidencias = document.querySelector('input[name="evidencias[]"]')?.files;
  
  console.log('Valores capturados:');
  console.log('numero_nf:', numeroNf);
  console.log('status:', statusSelected);
  console.log('observacao:', observacao);
  console.log('responsaveis:', responsaveis);
  console.log('arquivo_nf:', arquivoNf);
  console.log('evidencias:', evidencias);
  
  // Validate required fields
  if (!numeroNf) {
    alert('Por favor, preencha o número da NF.');
    return;
  }
  
  if (!statusSelected) {
    alert('Por favor, selecione um status.');
    return;
  }
  
  if (responsaveis.length === 0) {
    alert('Por favor, selecione pelo menos um responsável.');
    return;
  }
  
  if (!arquivoNf) {
    alert('Por favor, anexe o PDF da NF.');
    return;
  }
  
  // Validar observação para status reprovado
  if (statusSelected === 'reprovado' && !observacao.trim()) {
    alert('Por favor, preencha a observação para amostragens reprovadas.');
    return;
  }
  
  // Create FormData manually
  const formData = new FormData();
  formData.append('numero_nf', numeroNf);
  formData.append('status', statusSelected);
  formData.append('observacao', observacao);
  
  // Add responsaveis (JSON de name+email)
  responsaveis.forEach(responsavel => {
    formData.append('responsaveis[]', responsavel);
  });
  
  // Add files
  if (arquivoNf) {
    formData.append('arquivo_nf', arquivoNf);
  }
  
  if (evidencias && evidencias.length > 0) {
    for (let i = 0; i < evidencias.length; i++) {
      formData.append('evidencias[]', evidencias[i]);
    }
  }
  
  // Debug FormData
  console.log('FormData final:');
  for (let [key, value] of formData.entries()) {
    console.log(key, value);
  }
  
  fetch('/toners/amostragens', {
    method: 'POST',
    body: formData
  })
  .then(async (response) => {
    const contentType = response.headers.get('content-type') || '';
    if (!response.ok) {
      const text = await response.text().catch(() => '');
      throw new Error(`HTTP ${response.status} ${response.statusText}: ${text.slice(0, 200)}`);
    }
    if (contentType.includes('application/json')) {
      return response.json();
    }
    const text = await response.text();
    throw new Error(`Resposta não JSON do servidor: ${text.slice(0, 200)}`);
  })
  .then(result => {
    if (result && result.success) {
      alert(result.message || 'Amostragem registrada com sucesso!');
      closeAmostragemForm();
      carregarRegistros();
    } else {
      alert('Erro: ' + (result && result.message ? result.message : 'Falha desconhecida.'));
    }
  })
  .catch(error => {
    console.error('Erro no envio da amostragem:', error);
    alert('Erro de conexão: ' + error.message);
  });
}

function editAmostragem(id) {
  logActivity('user_action', 'Edit Amostragem', { id });
  // Implementar edição
}

function deleteAmostragem(id) {
  logActivity('user_action', 'Delete Amostragem', { id });
  if (confirm('Tem certeza que deseja excluir esta amostragem?')) {
    fetch(`/toners/amostragens/${id}`, { method: 'DELETE' })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        alert('Amostragem excluída com sucesso!');
        carregarRegistros();
      } else {
        alert('Erro: ' + result.message);
      }
    });
  }
}

function excluirAmostragem(id, numeroNf) {
  console.log('Excluir amostragem:', id, numeroNf);
  deleteAmostragem(id);
}

function printAmostragens() {
  logActivity('user_action', 'Print Amostragens');
  window.print();
}

function downloadAmostragemLog() {
  logActivity('user_action', 'Download Log');
  const report = {
    generated_at: new Date().toISOString(),
    activities: activityLog
  };
  const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `amostragens-log-${new Date().toISOString().slice(0,19)}.json`;
  a.click();
  URL.revokeObjectURL(url);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('toggleAmostragemFormBtn');
  if (toggleBtn) toggleBtn.addEventListener('click', toggleAmostragemForm);
  const closeBtn = document.getElementById('closeAmostragemFormBtn');
  if (closeBtn) closeBtn.addEventListener('click', closeAmostragemForm);
  
  // Listeners para filtros
  document.getElementById('searchInput').addEventListener('input', filtrarComDebounce);
  document.getElementById('statusFilter').addEventListener('change', filtrarComDebounce);

  // Sincronização de scroll
  const gridScroll = document.getElementById('grid-scroll');
  const topScroll = document.getElementById('grid-top-scroll');
  if (gridScroll && topScroll) {
    gridScroll.addEventListener('scroll', () => {
      topScroll.scrollLeft = gridScroll.scrollLeft;
    });
    topScroll.addEventListener('scroll', () => {
      gridScroll.scrollLeft = topScroll.scrollLeft;
    });
  }

  // Inicialização
  loadZoomPreference();
  loadFilters();
  renderGridHeader();
  carregarRegistros();
  
  logActivity('system', 'Page Loaded');
});

// Export functions globally
// Exports
window.toggleAmostragemForm = toggleAmostragemForm;
window.closeAmostragemForm = closeAmostragemForm;
window.loadUsers = loadUsers;
window.submitAmostragem = submitAmostragem;
window.editAmostragem = editAmostragem;
window.deleteAmostragem = deleteAmostragem;
window.excluirAmostragem = excluirAmostragem;
window.printAmostragens = printAmostragens;
window.downloadAmostragemLog = downloadAmostragemLog;

// ===== Utilidades de validação e preview =====
function validatePdfFile(input) {
  const file = input.files[0];
  const maxSize = 10 * 1024 * 1024; // 10MB
  if (!file) return true;
  const allowed = ['application/pdf'];
  if (!allowed.includes(file.type)) {
    alert('Apenas arquivos PDF são permitidos.');
    input.value = '';
    return false;
  }
  if (file.size > maxSize) {
    alert('O PDF deve ter no máximo 10MB.');
    input.value = '';
    return false;
  }
  showFilePreview(file, 'arquivo_nf_preview');
  return true;
}

function validateEvidenceFiles(input) {
  const files = Array.from(input.files || []);
  if (files.length === 0) return true;
  const maxEach = 5 * 1024 * 1024; // 5MB
  const allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
  if (files.length > 5) {
    alert('Máximo de 5 imagens.');
    input.value='';
    return false;
  }
  for (const f of files) {
    if (!allowed.includes(f.type)) { alert('Apenas imagens (JPG, PNG, GIF, WEBP).'); input.value=''; return false; }
    if (f.size > maxEach) { alert(`Imagem muito grande: ${f.name}`); input.value=''; return false; }
  }
  showImagePreviews(files, 'evidencias_preview');
  return true;
}

function showFilePreview(file, containerId) {
  const c = document.getElementById(containerId);
  if (!c) return;
  c.innerHTML = `
    <div class="flex items-center p-2 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg transition-colors">
      <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M8 6h8m5 5v7a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2h6"></path>
      </svg>
      <div class="flex-1">
        <p class="text-sm font-medium text-green-800 dark:text-green-200">${file.name}</p>
        <p class="text-xs text-green-600 dark:text-green-400">${(file.size/1048576).toFixed(2)} MB</p>
      </div>
      <button type="button" onclick="clearFilePreview('arquivo_nf')" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 bg-transparent border-none cursor-pointer text-xl">&times;</button>
    </div>`;
}

function showImagePreviews(files, containerId) {
  const c = document.getElementById(containerId);
  if (!c) return;
  c.innerHTML = '';
  files.forEach((file, idx) => {
    const r = new FileReader();
    r.onload = e => {
      const d = document.createElement('div');
      d.className = 'relative inline-block mr-2 mb-2';
      d.innerHTML = `
        <img src="${e.target.result}" alt="ev${idx}" class="w-20 h-20 object-cover rounded border" />
        <div class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs cursor-pointer" onclick="this.parentElement.remove()">×</div>`;
      c.appendChild(d);
    };
    r.readAsDataURL(file);
  });
}

function clearFilePreview(inputName) {
  const input = document.querySelector(`input[name="${inputName}"]`);
  const preview = document.getElementById(`${inputName}_preview`);
  if (input) input.value='';
  if (preview) preview.innerHTML='';
}

// Visualizar evidências de uma amostragem
async function viewEvidencias(amostragemId) {
  try {
    const response = await fetch(`/toners/amostragens/${amostragemId}/evidencias`);
    const data = await response.json();
    
    if (data.success && data.evidencias.length > 0) {
      let html = `
        <div class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4" onclick="this.remove()">
          <div class="bg-white dark:bg-slate-800 rounded-xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl transition-colors" onclick="event.stopPropagation()">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-0">Evidências - Amostragem #${amostragemId}</h3>
              <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 bg-gray-100 dark:bg-slate-700 rounded-full w-8 h-8 flex items-center justify-center transition-colors border-none cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      `;
      
      data.evidencias.forEach(evidencia => {
        html += `
          <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all group">
            <div class="relative overflow-hidden">
              <img src="/toners/amostragens/${amostragemId}/evidencia/${evidencia.id}" 
                   alt="${evidencia.name}" 
                   class="w-full h-40 object-cover cursor-pointer group-hover:scale-105 transition-transform duration-300"
                   onclick="window.open('/toners/amostragens/${amostragemId}/evidencia/${evidencia.id}', '_blank')">
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all pointer-events-none"></div>
            </div>
            <div class="p-3">
              <p class="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate mb-1" title="${evidencia.name}">${evidencia.name}</p>
              <p class="text-[10px] text-gray-500 dark:text-gray-400 m-0">${(evidencia.size/1024).toFixed(1)} KB</p>
            </div>
          </div>
        `;
      });
      
      html += `
            </div>
          </div>
        </div>
      `;
      
      document.body.insertAdjacentHTML('beforeend', html);
    } else {
      alert('Nenhuma evidência encontrada para esta amostragem.');
    }
  } catch (error) {
    console.error('Erro ao carregar evidências:', error);
    alert('Erro ao carregar evidências.');
  }
}

// ===== Edição Inline =====

// Atualizar status
async function updateStatus(id, newStatus, selectElement) {
  try {
    console.log('🔄 Atualizando status:', { id, newStatus });
    
    const formData = new FormData();
    formData.append('status', newStatus);
    
    // Buscar observação atual para validação
    const obsInput = document.getElementById(`obs-input-${id}`);
    const currentObs = obsInput ? obsInput.value.trim() : '';
    formData.append('observacao', currentObs);
    
    const response = await fetch(`/toners/amostragens/${id}/update`, {
      method: 'POST',
      body: formData
    });
    
    console.log('📡 Response status:', response.status);
    console.log('📡 Response headers:', response.headers.get('content-type'));
    
    // Verificar se a resposta é OK
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    // Tentar ler como texto primeiro para debug
    const responseText = await response.text();
    console.log('📋 Response text:', responseText);
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error('❌ Erro ao fazer parse do JSON:', parseError);
      console.error('📄 Resposta recebida:', responseText);
      throw new Error('Resposta inválida do servidor');
    }
    
    console.log('✅ Resultado parseado:', result);
    
    if (result && result.success) {
      // Atualizar cor do select usando o elemento passado como parâmetro
      if (selectElement) {
        selectElement.className = `text-xs font-semibold rounded-full px-2 py-1 border-0 focus:ring-2 focus:ring-blue-500 cursor-pointer ${
          newStatus === 'aprovado' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
          newStatus === 'pendente' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
        }`;
      }
      
      // Se mudou para reprovado e não tem observação, abrir edição
      if (newStatus === 'reprovado' && !currentObs) {
        editObservacao(id);
        // Não mostrar alert, apenas abrir editor
      }
      
      console.log('✅ Status atualizado com sucesso!');
      // Status atualizado silenciosamente - sem alert
    } else {
      console.error('❌ Erro retornado pela API:', result);
      alert('Erro: ' + (result ? result.message : 'Resposta inválida'));
      // Reverter select
      location.reload();
    }
  } catch (error) {
    console.error('❌ Erro ao atualizar status:', error);
    alert('Erro ao atualizar status: ' + error.message);
    location.reload();
  }
}

// Editar observação
function editObservacao(id) {
  const textSpan = document.getElementById(`obs-text-${id}`);
  const input = document.getElementById(`obs-input-${id}`);
  const buttons = document.getElementById(`obs-buttons-${id}`);
  
  textSpan.classList.add('hidden');
  input.classList.remove('hidden');
  buttons.classList.remove('hidden');
  input.focus();
}

// Cancelar edição de observação
function cancelEditObservacao(id) {
  const textSpan = document.getElementById(`obs-text-${id}`);
  const input = document.getElementById(`obs-input-${id}`);
  const buttons = document.getElementById(`obs-buttons-${id}`);
  
  textSpan.classList.remove('hidden');
  input.classList.add('hidden');
  buttons.classList.add('hidden');
}

// Salvar observação
async function saveObservacao(id) {
  try {
    console.log('💾 Salvando observação para ID:', id);
    
    const input = document.getElementById(`obs-input-${id}`);
    const observacao = input.value.trim();
    
    // Buscar status atual
    const statusSelect = document.querySelector(`select[onchange*="${id}"]`);
    const currentStatus = statusSelect.value;
    
    console.log('📝 Dados:', { observacao, currentStatus });
    
    const formData = new FormData();
    formData.append('status', currentStatus);
    formData.append('observacao', observacao);
    
    const response = await fetch(`/toners/amostragens/${id}/update`, {
      method: 'POST',
      body: formData
    });
    
    console.log('📡 Response status:', response.status);
    
    // Verificar se a resposta é OK
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    // Tentar ler como texto primeiro para debug
    const responseText = await response.text();
    console.log('📋 Response text:', responseText);
    
    let result;
    try {
      result = JSON.parse(responseText);
    } catch (parseError) {
      console.error('❌ Erro ao fazer parse do JSON:', parseError);
      throw new Error('Resposta inválida do servidor');
    }
    
    console.log('✅ Resultado parseado:', result);
    
    if (result && result.success) {
      // Atualizar texto
      const textSpan = document.getElementById(`obs-text-${id}`);
      if (observacao) {
        const displayText = observacao.length > 50 ? observacao.substring(0, 50) + '...' : observacao;
        textSpan.innerHTML = displayText;
        textSpan.title = observacao;
      } else {
        textSpan.innerHTML = '<span class="text-gray-400 italic">Clique para adicionar</span>';
        textSpan.title = 'Clique para editar';
      }
      
      cancelEditObservacao(id);
      console.log('✅ Observação salva com sucesso!');
      // Observação salva silenciosamente - sem alert
    } else {
      console.error('❌ Erro retornado pela API:', result);
      alert('Erro: ' + (result ? result.message : 'Resposta inválida'));
    }
  } catch (error) {
    console.error('❌ Erro ao salvar observação:', error);
    alert('Erro ao salvar observação: ' + error.message);
  }
}

</script>
