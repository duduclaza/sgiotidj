<section class="space-y-6">
  <!-- Cabeçalho -->
  <div class="mb-8 p-6 bg-white/50 dark:bg-slate-800/50 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-sm transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div class="flex items-center gap-4">
      <div class="p-3 bg-amber-100 dark:bg-amber-900/40 rounded-xl">
        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      </div>
      <div>
        <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Gestão de Fornecedores</h1>
        <p class="mt-1 text-slate-500 dark:text-slate-400 font-medium">Administre sua rede de parceiros e contatos de suporte.</p>
      </div>
    </div>
    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-white/80 dark:bg-slate-900/80 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30 shadow-sm transition-all">
      <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
      <?php echo count($fornecedores ?? []); ?> Parceiros Ativos
    </span>
  </div>

  <div class="grid grid-cols-1 gap-6">
    <!-- Formulário de Cadastro -->
    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/20">
        <h2 class="text-base font-extrabold text-slate-800 dark:text-white tracking-tight text-center sm:text-left">Novo Registro de Fornecedor</h2>
      </div>
      <form method="post" action="/registros/fornecedores/store" class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end" id="formFornecedor">
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Razão Social / Nome</label>
          <input type="text" name="nome" placeholder="Ex: Tech Solutions Ltda" 
                 class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all" required>
        </div>
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Canal de Contato</label>
          <input type="text" name="contato" placeholder="Email, WhatsApp ou Link" 
                 class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all">
        </div>
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Suporte / RMA</label>
          <input type="text" name="rma" placeholder="Portal de Chamados ou Tel" 
                 class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 outline-none transition-all">
        </div>
        <button type="submit" 
                onclick="setButtonLoading(this)"
                class="w-full lg:w-auto h-12 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold transition-all shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 group">
          <svg class="w-5 h-5 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Salvar Parceiro
        </button>
      </form>
    </div>

    <!-- Lista de Fornecedores -->
    <div class="bg-white/50 dark:bg-slate-800/50 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="overflow-x-auto ring-1 ring-slate-100 dark:ring-slate-700/50 rounded-2xl">
        <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/50">
          <thead class="bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Identificação</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contato Comercial</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Suporte Técnico (RMA)</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Gestão</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 dark:divide-slate-700/30 bg-white/30 dark:bg-transparent">
            <?php if (empty($fornecedores)): ?>
              <tr>
                <td colspan="4" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 italic font-medium">Nenhum parceiro comercial registrado.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($fornecedores as $f): ?>
                <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-900/10 transition-colors group">
                  <!-- Nome -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-amber-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                      </div>
                      <span class="edit-display-nome-<?= $f['id'] ?> text-sm font-bold text-slate-700 dark:text-slate-200"><?= e($f['nome']) ?></span>
                      <input type="text" class="edit-input-nome-<?= $f['id'] ?> bg-white dark:bg-slate-900 border border-amber-400 dark:border-amber-500 rounded-xl px-3 py-1.5 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-amber-500/10 outline-none hidden w-full shadow-inner" value="<?= e($f['nome']) ?>">
                    </div>
                  </td>
                  <!-- Contato -->
                  <td class="px-6 py-4">
                    <span class="edit-display-contato-<?= $f['id'] ?> text-sm font-medium text-slate-500 dark:text-slate-400 break-all"><?= e($f['contato']) ?: '---' ?></span>
                    <input type="text" class="edit-input-contato-<?= $f['id'] ?> bg-white dark:bg-slate-900 border border-amber-400 dark:border-amber-500 rounded-xl px-3 py-1.5 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-amber-500/10 outline-none hidden w-full shadow-inner" value="<?= e($f['contato']) ?>">
                  </td>
                  <!-- RMA -->
                  <td class="px-6 py-4">
                    <span class="edit-display-rma-<?= $f['id'] ?> text-sm font-medium text-slate-500 dark:text-slate-400 break-all"><?= e($f['rma']) ?: '---' ?></span>
                    <input type="text" class="edit-input-rma-<?= $f['id'] ?> bg-white dark:bg-slate-900 border border-amber-400 dark:border-amber-500 rounded-xl px-3 py-1.5 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-amber-500/10 outline-none hidden w-full shadow-inner" value="<?= e($f['rma']) ?>">
                  </td>
                  <!-- Ações -->
                  <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <div class="flex gap-1.5 edit-controls-<?= $f['id'] ?>">
                        <button onclick="editRow(<?= $f['id'] ?>)" 
                                class="p-2 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/40 rounded-xl transition-all"
                                title="Editar Fornecedor">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="deleteRow(<?= $f['id'] ?>)" 
                                class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-xl transition-all"
                                title="Excluir Fornecedor">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      </div>
                      <div class="flex gap-1.5 edit-actions-<?= $f['id'] ?> hidden">
                        <button onclick="saveRow(<?= $f['id'] ?>, this)" 
                                class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 rounded-xl transition-all"
                                title="Confirmar Alteração">
                          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button onclick="cancelEdit(<?= $f['id'] ?>)" 
                                class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-xl transition-all"
                                title="Cancelar">
                          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<script>
function editRow(id) {
  ['nome', 'contato', 'rma'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.add('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.remove('hidden');
  });
  document.querySelector('.edit-controls-' + id).classList.add('hidden');
  document.querySelector('.edit-actions-' + id).classList.remove('hidden');
  document.querySelector('.edit-input-nome-' + id).focus();
}

function cancelEdit(id) {
  ['nome', 'contato', 'rma'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.remove('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.add('hidden');
  });
  document.querySelector('.edit-controls-' + id).classList.remove('hidden');
  document.querySelector('.edit-actions-' + id).classList.add('hidden');
}

async function saveRow(id, btn) {
  const nome = document.querySelector('.edit-input-nome-' + id).value.trim();
  const contato = document.querySelector('.edit-input-contato-' + id).value.trim();
  const rma = document.querySelector('.edit-input-rma-' + id).value.trim();
  
  if (!nome) { showToast('Nome é obrigatório', 'error'); return; }
  
  setButtonLoading(btn);
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('nome', nome);
    formData.append('contato', contato);
    formData.append('rma', rma);
    
    const response = await fetch('/registros/fornecedores/update', {
      method: 'POST',
      body: formData
    });
    
    if (response.ok) {
      window.location.reload();
    } else {
      showToast('Erro ao salvar alteração', 'error');
      setButtonLoading(btn, false);
    }
  } catch (err) {
    showToast('Erro de conexão', 'error');
    setButtonLoading(btn, false);
  }
}

async function deleteRow(id) {
  const confirm = await showConfirm('Excluir Fornecedor', 'Tem certeza que deseja remover este parceiro comercial? Esta ação não pode ser desfeita.');
  if (!confirm) return;
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('/registros/fornecedores/delete', {
      method: 'POST',
      body: formData
    });
    
    if (response.ok) {
      window.location.reload();
    } else {
      showToast('Erro ao excluir fornecedor', 'error');
    }
  } catch (err) {
    showToast('Erro de conexão', 'error');
  }
}
</script>
