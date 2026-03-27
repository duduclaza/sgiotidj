<?php
$pecas = $pecas ?? [];
$isAdmin = $_SESSION['user_role'] === 'admin';
?>

<section class="mb-8">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🔧 Cadastro de Peças</h1>
      <p class="text-gray-600 dark:text-gray-400 mt-1">Gerenciamento de peças cadastradas</p>
    </div>
    <div class="flex gap-3">
      <button onclick="openImportModal()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg flex items-center gap-2">
        <span>📊</span>
        Importar Peças
      </button>
      <button onclick="openFormModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
        + Nova Peça
      </button>
    </div>
  </div>

  <!-- Barra de Busca -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 p-4 mb-6 transition-colors">
    <div class="flex gap-4">
      <div class="flex-1 relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        <input type="text" 
               id="searchPecaInput"
               onkeyup="filterPecas()"
               class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all sm:text-sm" 
               placeholder="Pesquisar por código de referência ou descrição...">
      </div>
    </div>
  </div>
  <div id="formContainer" class="hidden bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-200 dark:border-slate-700/50 p-6 mb-6 transition-all">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white" id="formTitle">Nova Peça</h2>
      <button onclick="closeFormModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="pecaForm" class="space-y-4">
      <input type="hidden" name="id" id="pecaId">
      
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Código de Referência *</label>
        <input type="text" name="codigo_referencia" id="codigoReferencia" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição *</label>
        <textarea name="descricao" id="descricao" required rows="3" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors"></textarea>
      </div>

      <div class="flex justify-end space-x-3 pt-4 border-t dark:border-slate-700 mt-6">
        <button type="button" onclick="closeFormModal()" class="px-4 py-2 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors">
          Cancelar
        </button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md">
          💾 Salvar
        </button>
      </div>
    </form>
  </div>

  <!-- Grid -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 overflow-hidden transition-colors">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-slate-700">
        <thead class="bg-gray-50 dark:bg-slate-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Código Referência</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descrição</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Criado por</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700">
          <?php foreach ($pecas as $peca): ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300"><?= $peca['id'] ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"><?= e($peca['codigo_referencia']) ?></td>
            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"><?= e($peca['descricao']) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300"><?= e($peca['criador_nome'] ?? 'N/A') ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= date('d/m/Y', strtotime($peca['created_at'])) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <div class="flex items-center gap-2">
                <button onclick='editPeca(<?= json_encode($peca) ?>)' 
                        class="p-1.5 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/60 transition-colors"
                        title="Editar">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button onclick="deletePeca(<?= $peca['id'] ?>)" 
                        class="p-1.5 bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/60 transition-colors"
                        title="Excluir">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Modal de Importação -->
<div id="importModal" class="modal-overlay fixed inset-0 bg-slate-900/80 backdrop-blur-sm items-center justify-center p-4 transition-all duration-300" style="z-index: 999999; display: none; visibility: hidden;" onclick="closeImportModal()">
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all" onclick="event.stopPropagation()">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/50">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center mr-3">
            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">📊 Importar Peças</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Upload de planilha Excel</p>
          </div>
        </div>
        <button onclick="closeImportModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Content -->
    <div class="px-6 py-6 space-y-6">
      <!-- File Input -->
      <div>
        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-3">
          📁 Selecione o arquivo Excel
        </label>
        <div class="relative group">
          <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" 
                 class="w-full border-2 border-dashed border-gray-200 dark:border-slate-600 rounded-2xl px-4 py-8 text-sm text-gray-500 dark:text-gray-400
                        hover:border-blue-400 dark:hover:border-blue-500 transition-all cursor-pointer bg-gray-50/50 dark:bg-slate-900/30
                        file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold 
                        file:bg-blue-600 file:text-white hover:file:bg-blue-700">
        </div>
        <div class="flex items-center mt-3 text-xs text-gray-500 dark:text-gray-400 bg-blue-50/50 dark:bg-blue-900/20 p-2 rounded-lg">
          <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Formatos: <span class="font-bold text-gray-700 dark:text-gray-200 ml-1">.xlsx, .xls, .csv</span>
        </div>
      </div>
      
      <!-- Progress Bar -->
      <div id="progressContainer" class="hidden">
        <div class="bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-700 rounded-2xl p-4">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
              <span class="text-xs font-bold text-gray-700 dark:text-gray-300 tracking-wider uppercase">⚡ Importando</span>
            </div>
            <span id="progressText" class="text-sm font-black text-blue-600">0%</span>
          </div>
          <div class="w-full bg-gray-200 dark:bg-slate-700 rounded-full h-2.5 overflow-hidden">
            <div id="progressBar" class="bg-blue-600 h-full transition-all duration-500 ease-out" style="width: 0%"></div>
          </div>
          <div id="importStatus" class="text-xs text-gray-500 dark:text-gray-400 mt-3 text-center italic">
            Preparando importação...
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-900 border-t border-gray-100 dark:border-slate-700 flex flex-col gap-3">
      <button onclick="importExcelPecas()" id="importBtn"
              class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all shadow-lg hover:shadow-blue-500/20">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        📤 Importar Agora
      </button>
      
      <button onclick="downloadTemplatePecas()" 
              class="w-full flex items-center justify-center px-4 py-2 text-xs font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors">
        📥 Baixar Planilha Exemplo
      </button>
    </div>
  </div>
</div>
  </div>
</div>

<!-- Adicionar biblioteca XLSX -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
let isEditing = false;

function openFormModal() {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Nova Peça';
  document.getElementById('pecaForm').reset();
  document.getElementById('pecaId').value = '';
  isEditing = false;
}

function closeFormModal() {
  document.getElementById('formContainer').classList.add('hidden');
  document.getElementById('pecaForm').reset();
}

function editPeca(peca) {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Editar Peça';
  document.getElementById('pecaId').value = peca.id;
  document.getElementById('codigoReferencia').value = peca.codigo_referencia;
  document.getElementById('descricao').value = peca.descricao;
  isEditing = true;
}

async function deletePeca(id) {
  showConfirm('Tem certeza que deseja excluir esta peça?', async () => {
    try {
      const response = await fetch('/cadastro-pecas/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}`
      });
      
      const result = await response.json();
      
      if (result.success) {
        showToast(result.message, 'success');
        setTimeout(() => window.location.reload(), 800);
      } else {
        showToast(result.message, 'error');
      }
    } catch (error) {
      showToast('Erro ao excluir peça', 'error');
    }
  }, { danger: true, title: 'Excluir Peça' });
}

document.getElementById('pecaForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const btn = this.querySelector('button[type="submit"]');
  const formData = new FormData(this);
  const url = isEditing ? '/cadastro-pecas/update' : '/cadastro-pecas/store';
  
  setButtonLoading(btn, true);
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success) {
      showToast(result.message, 'success');
      if (result.redirect) {
        setTimeout(() => window.location.href = result.redirect, 800);
      } else {
        window.location.reload();
      }
    } else {
      setButtonLoading(btn, false);
      showToast(result.message, 'error');
    }
  } catch (error) {
    setButtonLoading(btn, false);
    showToast('Erro ao salvar peça', 'error');
  }
});

// ===== FUNÇÕES DE IMPORTAÇÃO =====

function openImportModal() {
  const modal = document.getElementById('importModal');
  
  if (!modal) {
    showToast('Erro: Modal de importação não encontrado.', 'error');
    return;
  }
  
  // Mostrar modal
  modal.style.display = 'flex';
  modal.style.visibility = 'visible';
  modal.style.opacity = '1';
}

function closeImportModal() {
  const modal = document.getElementById('importModal');
  
  // Ocultar modal
  modal.style.display = 'none';
  modal.style.visibility = 'hidden';
  modal.style.opacity = '0';
  
  // Limpar inputs
  document.getElementById('excelFileInput').value = '';
  document.getElementById('progressContainer').classList.add('hidden');
}

function downloadTemplatePecas() {
  
  // Criar dados da planilha
  const data = [
    ['TEMPLATE DE IMPORTAÇÃO DE PEÇAS - SGQ OTI DJ'],
    [],
    ['📋 INSTRUÇÕES DE PREENCHIMENTO:'],
    ['1. Preencha os dados a partir da linha 8 (abaixo dos cabeçalhos)'],
    ['2. CAMPOS OBRIGATÓRIOS: Código de Referência e Descrição'],
    ['3. Código de Referência: identificador único da peça'],
    ['4. Descrição: descrição completa e detalhada da peça'],
    [],
    ['Código de Referência *', 'Descrição *'],
    ['P-001', 'Parafuso M6 x 20mm - Aço Inox'],
    ['P-002', 'Rolamento 6200 - Alta Velocidade'],
    ['P-003', 'Correia Dentada GT2 - 6mm x 2m'],
    ['P-004', 'Engrenagem Helicoidal Z40 - Módulo 2'],
    ['P-005', 'Sensor Indutivo PNP - 8mm - 12-24V']
  ];
  
  // Criar workbook
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(data);
  
  // Larguras das colunas
  ws['!cols'] = [
    {wch: 25}, // Código
    {wch: 50}  // Descrição
  ];
  
  // Mesclar título
  ws['!merges'] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }
  ];
  
  // Estilizar título
  if (!ws['A1'].s) ws['A1'].s = {};
  ws['A1'].s = {
    font: { bold: true, sz: 14, color: { rgb: "FFFFFF" } },
    fill: { fgColor: { rgb: "1E40AF" } },
    alignment: { horizontal: "center", vertical: "center" }
  };
  
  // Estilizar instruções
  for (let row = 2; row <= 6; row++) {
    const cellRef = XLSX.utils.encode_cell({ r: row, c: 0 });
    if (!ws[cellRef]) ws[cellRef] = { v: '', t: 's' };
    ws[cellRef].s = {
      font: { italic: true, sz: 10, color: { rgb: "374151" } },
      fill: { fgColor: { rgb: "F3F4F6" } },
      alignment: { horizontal: "left", vertical: "center" }
    };
  }
  
  // Estilizar cabeçalhos
  for (let col = 0; col < 2; col++) {
    const cellRef = XLSX.utils.encode_cell({ r: 8, c: col });
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
  
  // Estilizar exemplos
  for (let row = 9; row <= 13; row++) {
    for (let col = 0; col < 2; col++) {
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
  
  // Adicionar aba
  XLSX.utils.book_append_sheet(wb, ws, "Cadastro de Peças");
  
  // Aba de instruções
  const instrData = [
    ['INSTRUÇÕES DETALHADAS - IMPORTAÇÃO DE PEÇAS'],
    [],
    ['CAMPOS OBRIGATÓRIOS (*)'],
    ['Campo', 'Descrição', 'Exemplo'],
    ['Código de Referência', 'Identificador único da peça', 'P-001'],
    ['Descrição', 'Descrição completa e detalhada', 'Parafuso M6 x 20mm - Aço Inox'],
    [],
    ['OBSERVAÇÕES IMPORTANTES:'],
    ['• Ambos os campos são obrigatórios'],
    ['• Código de Referência deve ser único (máx. 100 caracteres)'],
    ['• Descrição pode ser detalhada (campo de texto longo)'],
    ['• A primeira linha com dados é a linha 9 (após os cabeçalhos)'],
    ['• Linhas em branco são ignoradas automaticamente'],
    ['• Use códigos claros e padronizados para facilitar busca'],
    [],
    ['EXEMPLOS DE PREENCHIMENTO:'],
    ['P-001 | Parafuso M6 x 20mm - Aço Inox'],
    ['ENG-040 | Engrenagem Helicoidal Z40 - Módulo 2'],
    ['SENS-IND-8 | Sensor Indutivo PNP - 8mm - 12-24V']
  ];
  
  const wsInstr = XLSX.utils.aoa_to_sheet(instrData);
  wsInstr['!cols'] = [{wch: 25}, {wch: 50}, {wch: 30}];
  XLSX.utils.book_append_sheet(wb, wsInstr, "Instruções");
  
  // Download
  const fileName = `template_pecas_${new Date().toISOString().split('T')[0]}.xlsx`;
  XLSX.writeFile(wb, fileName);
  
  // Feedback
  const btn = event.target;
  const originalText = btn.innerHTML;
  btn.innerHTML = '✅ Template baixado!';
  btn.disabled = true;
  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
  }, 2000);
}

function importExcelPecas() {
  const fileInput = document.getElementById('excelFileInput');
  const file = fileInput.files[0];
  
  if (!file) {
    alert('Por favor, selecione um arquivo Excel.');
    return;
  }
  
  document.getElementById('progressContainer').classList.remove('hidden');
  document.getElementById('importBtn').disabled = true;
  
  const reader = new FileReader();
  reader.onload = function(e) {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
      const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });
      
      if (jsonData.length <= 9) {
        throw new Error('Arquivo vazio ou sem dados');
      }
      
      processImportPecas(jsonData);
      
    } catch (error) {
      showImportError('Erro ao ler arquivo: ' + error.message);
    }
  };
  
  reader.onerror = function() {
    showImportError('Erro ao ler o arquivo');
  };
  
  reader.readAsArrayBuffer(file);
}

function processImportPecas(data) {
  updateProgressPecas(20, 'Processando dados...');
  const dataRows = data.slice(9).filter(row => row && row.length >= 2 && row[0] && row[1]);
  
  if (dataRows.length === 0) {
    showImportError('Nenhum dado válido encontrado no arquivo');
    return;
  }
  
  updateProgressPecas(40, `Localizadas ${dataRows.length} peças...`);
  const formData = new FormData();
  formData.append('pecas_data', JSON.stringify(dataRows));
  
  updateProgressPecas(60, 'Enviando para o servidor...');
  
  fetch('/cadastro-pecas/import', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      updateProgressPecas(100, `Concluído! ${result.imported} peças importadas`);
      setTimeout(() => {
        closeImportModal();
        showToast(`Importação concluída: ${result.imported} registros.`, 'success');
        setTimeout(() => window.location.reload(), 1000);
      }, 1000);
    } else {
      showImportError(result.message || 'Erro ao importar peças');
    }
  })
  .catch(error => {
    showImportError('Erro de conexão: ' + error.message);
  });
}

function updateProgressPecas(percentage, status) {
  const bar = document.getElementById('progressBar');
  const text = document.getElementById('progressText');
  const statusEl = document.getElementById('importStatus');
  if (bar) bar.style.width = percentage + '%';
  if (text) text.textContent = percentage + '%';
  if (statusEl) statusEl.textContent = status;
}

function showImportError(message) {
  document.getElementById('progressContainer').classList.add('hidden');
  document.getElementById('importBtn').disabled = false;
  showToast('Erro na importação: ' + message, 'error');
}

function filterPecas() {
  const input = document.getElementById('searchPecaInput');
  const filter = input.value.toLowerCase();
  const table = document.querySelector('table tbody');
  const rows = table.getElementsByTagName('tr');

  for (let i = 0; i < rows.length; i++) {
    const codigoCell = rows[i].getElementsByTagName('td')[1];
    const descCell = rows[i].getElementsByTagName('td')[2];
    
    if (codigoCell && descCell) {
      const codigoText = codigoCell.textContent || codigoCell.innerText;
      const descText = descCell.textContent || descCell.innerText;
      
      if (codigoText.toLowerCase().indexOf(filter) > -1 || descText.toLowerCase().indexOf(filter) > -1) {
        rows[i].style.display = "";
      } else {
        rows[i].style.display = "none";
      }
    }
  }
}

</script>
