<?php
$maquinas = $maquinas ?? [];
$isAdmin = $_SESSION['user_role'] === 'admin';
?>

<section class="mb-8">
  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🖨️ Cadastro de Máquinas</h1>
      <p class="text-gray-600 dark:text-gray-400 mt-1">Gerenciamento de máquinas cadastradas</p>
    </div>
    <div class="flex gap-2">
      <button onclick="openFormModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
        + Nova Máquina
      </button>
      <button onclick="exportMaquinas(event)" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold transition-colors shadow-lg flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Exportar Excel
      </button>
    </div>
  </div>

  <!-- Formulário Inline -->
  <div id="formContainer" class="hidden bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-200 dark:border-slate-700/50 p-6 mb-6 transition-all">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white" id="formTitle">Nova Máquina</h2>
      <button onclick="closeFormModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="maquinaForm" class="space-y-4">
      <input type="hidden" name="id" id="maquinaId">
      
      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Modelo *</label>
        <input type="text" name="modelo" id="modelo" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Código de Referência *</label>
        <input type="text" name="cod_referencia" id="codReferencia" required class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-colors">
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Modelo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Código Referência</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Criado por</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700">
          <?php foreach ($maquinas as $maquina): ?>
          <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300"><?= $maquina['id'] ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"><?= e($maquina['modelo']) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300"><?= e($maquina['cod_referencia']) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300"><?= e($maquina['criador_nome'] ?? 'N/A') ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><?= date('d/m/Y', strtotime($maquina['created_at'])) ?></td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <div class="flex items-center gap-2">
                <button onclick='editMaquina(<?= json_encode($maquina) ?>)' 
                        class="p-1.5 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/60 transition-colors"
                        title="Editar">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button onclick="deleteMaquina(<?= $maquina['id'] ?>)" 
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

<script>
let isEditing = false;

function openFormModal() {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Nova Máquina';
  document.getElementById('maquinaForm').reset();
  document.getElementById('maquinaId').value = '';
  isEditing = false;
}

function closeFormModal() {
  document.getElementById('formContainer').classList.add('hidden');
  document.getElementById('maquinaForm').reset();
}

function editMaquina(maquina) {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Editar Máquina';
  document.getElementById('maquinaId').value = maquina.id;
  document.getElementById('modelo').value = maquina.modelo;
  document.getElementById('codReferencia').value = maquina.cod_referencia;
  isEditing = true;
}

async function deleteMaquina(id) {
  if (!confirm('Tem certeza que deseja excluir esta máquina?')) return;
  
  try {
    const response = await fetch('/cadastro-maquinas/delete', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `id=${id}`
    });
    
    const result = await response.json();
    alert(result.message);
    
    if (result.success) {
      window.location.reload();
    }
  } catch (error) {
    alert('Erro ao excluir máquina');
  }
}

function exportMaquinas(e) {
  const button = e ? e.target.closest('button') : document.querySelector('button[onclick*="exportMaquinas"]');
  const originalContent = button.innerHTML;
  button.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Exportando...';
  button.disabled = true;

  const link = document.createElement('a');
  link.href = '/cadastro-maquinas/export';
  link.download = 'maquinas_' + new Date().toISOString().slice(0, 10) + '.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);

  setTimeout(() => {
    button.innerHTML = originalContent;
    button.disabled = false;
    alert('Exportação concluída com sucesso!');
  }, 2000);
}

document.getElementById('maquinaForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const url = isEditing ? '/cadastro-maquinas/update' : '/cadastro-maquinas/store';
  
  try {
    const response = await fetch(url, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    alert(result.message);
    
    if (result.success && result.redirect) {
      window.location.href = result.redirect;
    }
  } catch (error) {
    alert('Erro ao salvar máquina');
  }
});
</script>
