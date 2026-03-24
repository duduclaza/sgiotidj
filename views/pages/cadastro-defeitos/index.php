<?php
$defeitos = $defeitos ?? [];
?>

<section class="mb-8">
  <div class="flex justify-between items-center mb-8 gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
        <span class="text-blue-600">🧩</span> Cadastro de Defeitos
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mt-1.5">Gerenciamento de catálogo visual de defeitos técnicos</p>
    </div>
    <button onclick="openFormModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-blue-500/20 flex items-center gap-2 shrink-0">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
      Novo Defeito
    </button>
  </div>

  <div id="formContainer" class="hidden bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-700/50 p-6 mb-8 transition-all animate-in fade-in slide-in-from-top-4 duration-300">
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <div class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="formTitle">Novo Defeito</h2>
      </div>
      <button onclick="closeFormModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-xl transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <form id="defeitoForm" class="grid grid-cols-1 md:grid-cols-2 gap-6" enctype="multipart/form-data">
      <input type="hidden" name="id" id="defeitoId">

      <div class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Nome do Defeito *</label>
          <input type="text" name="nome_defeito" id="nomeDefeito" required 
                 class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" 
                 placeholder="Ex: Risco na Película, Mancha no Cilindro...">
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Imagem do Defeito <span id="img-obrigatoria" class="text-red-500">*</span></label>
          <div class="relative group">
            <input type="file" name="imagem" id="imagemDefeito" accept="image/*" 
                   class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white file:mr-4 file:py-1 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 dark:file:bg-blue-900/30 file:text-blue-700 dark:file:text-blue-300 hover:file:bg-blue-100 transition-all cursor-pointer">
          </div>
          <p class="text-[10px] text-gray-500 dark:text-gray-500 mt-2 px-1">
            <span class="font-bold">Dica:</span> No modo edição, envie uma imagem apenas se desejar substituir a atual.
          </p>
        </div>
      </div>

      <div class="flex flex-col justify-end gap-3 md:border-l md:dark:border-slate-700 md:pl-6">
        <div class="flex gap-3">
          <button type="button" onclick="closeFormModal()" 
                  class="flex-1 px-6 py-2.5 bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-200 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-slate-600 transition-all">
            Cancelar
          </button>
          <button type="submit" 
                  class="flex-1 px-6 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-lg hover:shadow-blue-500/20">
            Salvar Registro
          </button>
        </div>
      </div>
    </form>
  </div>

  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700/50 overflow-hidden transition-colors">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
        <thead class="bg-gray-50 dark:bg-slate-900/50">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">ID</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Imagem</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nome do Defeito</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Criado por</th>
            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Data</th>
            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700">
          <?php if (empty($defeitos)): ?>
            <tr>
              <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic">Nenhum defeito cadastrado.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($defeitos as $d): ?>
              <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-500 font-mono"><?= str_pad((int)$d['id'], 3, '0', STR_PAD_LEFT) ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php if (!empty($d['imagem_nome'])): ?>
                    <a href="/cadastro-defeitos/<?= (int)$d['id'] ?>/imagem" target="_blank" class="group relative inline-block">
                      <img src="/cadastro-defeitos/<?= (int)$d['id'] ?>/imagem" alt="Imagem defeito" 
                           class="w-14 h-14 object-cover rounded-xl border-2 border-white dark:border-slate-700 shadow-sm group-hover:scale-110 transition-transform duration-300">
                      <div class="absolute inset-0 bg-black/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      </div>
                    </a>
                  <?php else: ?>
                    <div class="w-14 h-14 bg-gray-100 dark:bg-slate-900 rounded-xl flex items-center justify-center text-gray-400 border-2 border-dashed border-gray-200 dark:border-slate-700">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-tight"><?= htmlspecialchars($d['nome_defeito'] ?? '') ?></span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 rounded-full flex items-center justify-center text-[10px] font-bold">
                      <?= strtoupper(substr($d['criado_por_nome'] ?? 'NA', 0, 2)) ?>
                    </div>
                    <span class="text-xs text-gray-600 dark:text-gray-400"><?= htmlspecialchars($d['criado_por_nome'] ?? 'N/A') ?></span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-500">
                  <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <?= !empty($d['created_at']) ? date('d/m/Y', strtotime($d['created_at'])) : '—' ?>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button onclick='editDefeito(<?= json_encode($d) ?>)' class="p-2 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/60 transition-all shadow-sm" title="Editar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="deleteDefeito(<?= (int)$d['id'] ?>)" class="p-2 bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-400 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/60 transition-all shadow-sm" title="Excluir">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
let isEditingDefeito = false;

function openFormModal() {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Novo Defeito';
  document.getElementById('img-obrigatoria').classList.remove('hidden');
  document.getElementById('defeitoForm').reset();
  document.getElementById('defeitoId').value = '';
  isEditingDefeito = false;
}

function closeFormModal() {
  document.getElementById('formContainer').classList.add('hidden');
  document.getElementById('defeitoForm').reset();
}

function editDefeito(defeito) {
  document.getElementById('formContainer').classList.remove('hidden');
  document.getElementById('formTitle').textContent = 'Editar Defeito';
  document.getElementById('img-obrigatoria').classList.add('hidden');
  document.getElementById('defeitoId').value = defeito.id;
  document.getElementById('nomeDefeito').value = defeito.nome_defeito || '';
  isEditingDefeito = true;
}

async function deleteDefeito(id) {
  if (!confirm('Tem certeza que deseja excluir este defeito?')) return;

  try {
    const response = await fetch('/cadastro-defeitos/delete', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: `id=${id}`
    });

    const raw = await response.text();
    let result;
    try {
      result = JSON.parse(raw);
    } catch (_) {
      throw new Error(raw || `HTTP ${response.status}`);
    }

    alert(result.message);
    if (result.success) window.location.reload();
  } catch (error) {
    alert('Erro ao excluir defeito: ' + (error.message || 'erro desconhecido'));
  }
}

document.getElementById('defeitoForm').addEventListener('submit', async function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const url = isEditingDefeito ? '/cadastro-defeitos/update' : '/cadastro-defeitos/store';

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    });

    const raw = await response.text();
    let result;
    try {
      result = JSON.parse(raw);
    } catch (_) {
      throw new Error(raw || `HTTP ${response.status}`);
    }

    alert(result.message);
    if (result.success) window.location.reload();
  } catch (error) {
    alert('Erro ao salvar defeito: ' + (error.message || 'erro desconhecido'));
  }
});
</script>
