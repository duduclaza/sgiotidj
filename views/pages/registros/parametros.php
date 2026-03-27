<section class="space-y-6">
  <!-- Cabeçalho -->
  <div class="mb-8 p-6 bg-white/50 dark:bg-slate-800/50 backdrop-blur-xl border border-slate-200/50 dark:border-slate-700/50 rounded-2xl shadow-sm transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div class="flex items-center gap-4">
      <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 rounded-xl">
        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </div>
      <div>
        <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white tracking-tight">Parâmetros de Triagem</h1>
        <p class="mt-1 text-slate-500 dark:text-slate-400 font-medium">Defina as faixas de pesagem e orientações técnicas de retornados.</p>
      </div>
    </div>
    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-white/80 dark:bg-slate-900/80 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 shadow-sm transition-all">
      <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
      <?php echo count($parametros ?? []); ?> Regras Ativas
    </span>
  </div>

  <div class="grid grid-cols-1 gap-6">
    <!-- Formulário de Cadastro -->
    <div class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-md rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/20">
        <h2 class="text-base font-extrabold text-slate-800 dark:text-white tracking-tight">Configurar Novo Parâmetro de Pesagem</h2>
      </div>
      <form method="post" action="/registros/parametros/store" class="p-6 space-y-6" id="formParametros">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Descrição</label>
            <input type="text" name="nome" placeholder="Ex: Toner Vazio, Toner Cheio" 
                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all placeholder-slate-400 dark:placeholder-slate-600" required>
          </div>
          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Faixa Mínima (%)</label>
            <div class="relative">
              <input type="number" name="faixa_min" placeholder="0.0" step="0.1" min="0" max="100" 
                     class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all pr-10" required>
              <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-bold text-xs">%</span>
            </div>
          </div>
          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Faixa Máxima (%)</label>
            <div class="relative">
              <input type="number" name="faixa_max" placeholder="Opcional" step="0.1" min="0" max="100" 
                     class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all pr-10">
              <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 font-bold text-xs">%</span>
            </div>
          </div>
          <div class="flex items-end">
            <button type="submit" 
                    onclick="setButtonLoading(this)"
                    class="w-full h-[46px] rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition-all shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 group">
              <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
              Salvar Regra
            </button>
          </div>
        </div>
        <div class="space-y-2">
          <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Orientação Técnica / Procedimento de Triagem</label>
          <textarea name="orientacao" placeholder="Descreva as instruções que o técnico deve seguir ao identificar esta pesagem..." 
                    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all min-h-[100px] resize-none" required></textarea>
        </div>
      </form>
    </div>

    <!-- Lista de Parâmetros -->
    <div class="bg-white/50 dark:bg-slate-800/50 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/50 dark:border-slate-700/50 overflow-hidden transition-all">
      <div class="overflow-x-auto ring-1 ring-slate-100 dark:ring-slate-700/50 rounded-2xl">
        <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700/50">
          <thead class="bg-slate-50/80 dark:bg-slate-900/80 backdrop-blur-md">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Descrição do Parâmetro</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Range de Pesagem (%)</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Procedimento Operacional</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Gestão</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 dark:divide-slate-700/30 bg-white/30 dark:bg-transparent">
            <?php if (empty($parametros)): ?>
              <tr>
                <td colspan="4" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500 italic font-medium">Nenhum parâmetro de triagem configurado.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($parametros as $p): ?>
                <tr class="hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-colors group">
                  <!-- Nome -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                      </div>
                      <span class="edit-display-nome-<?= $p['id'] ?> text-sm font-bold text-slate-700 dark:text-slate-200"><?= e($p['nome']) ?></span>
                      <input type="text" class="edit-input-nome-<?= $p['id'] ?> bg-white dark:bg-slate-900 border border-emerald-400 dark:border-emerald-500 rounded-xl px-3 py-1.5 text-sm font-bold text-slate-800 dark:text-white focus:ring-4 focus:ring-emerald-500/10 outline-none hidden w-full shadow-inner" value="<?= e($p['nome']) ?>">
                    </div>
                  </td>
                  <!-- Faixa -->
                  <td class="px-6 py-4">
                    <span class="edit-display-faixa-<?= $p['id'] ?> inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 shadow-sm transition-all group-hover:border-emerald-300 dark:group-hover:border-emerald-800">
                      <?= number_format((float)$p['faixa_min'], 1, ',', '.') ?>%
                      <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                      <?= $p['faixa_max'] !== null ? number_format((float)$p['faixa_max'], 1, ',', '.').'%' : '∞' ?>
                    </span>
                    <div class="edit-input-faixa-<?= $p['id'] ?> hidden flex items-center gap-2">
                      <input type="number" step="0.1" min="0" max="100" class="edit-input-faixa-min-<?= $p['id'] ?> bg-white dark:bg-slate-900 border border-emerald-400 dark:border-emerald-500 rounded-xl px-2 py-1.5 w-20 text-xs font-bold text-slate-800 dark:text-white" value="<?= $p['faixa_min'] ?>">
                      <span class="text-slate-400 font-bold">-</span>
                      <input type="number" step="0.1" min="0" max="100" class="edit-input-faixa-max-<?= $p['id'] ?> bg-white dark:bg-slate-900 border border-emerald-400 dark:border-emerald-500 rounded-xl px-2 py-1.5 w-20 text-xs font-bold text-slate-800 dark:text-white" value="<?= $p['faixa_max'] ?>">
                    </div>
                  </td>
                  <!-- Orientação -->
                  <td class="px-6 py-4 max-w-sm">
                    <div class="edit-display-orientacao-<?= $p['id'] ?> text-xs font-medium text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed" title="<?= e($p['orientacao']) ?>">
                      <?= e($p['orientacao']) ?>
                    </div>
                    <textarea class="edit-input-orientacao-<?= $p['id'] ?> bg-white dark:bg-slate-900 border border-emerald-400 dark:border-emerald-500 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 dark:text-white focus:ring-4 focus:ring-emerald-500/10 outline-none hidden w-full min-h-[60px] shadow-inner"><?= e($p['orientacao']) ?></textarea>
                  </td>
                  <!-- Ações -->
                  <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <div class="flex gap-1.5 edit-controls-<?= $p['id'] ?>">
                        <button onclick="editRow(<?= $p['id'] ?>)" 
                                class="p-2 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 rounded-xl transition-all"
                                title="Editar Parâmetro">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button onclick="deleteRow(<?= $p['id'] ?>)" 
                                class="p-2 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40 rounded-xl transition-all"
                                title="Excluir Parâmetro">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      </div>
                      <div class="flex gap-1.5 edit-actions-<?= $p['id'] ?> hidden">
                        <button onclick="saveRow(<?= $p['id'] ?>, this)" 
                                class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/40 rounded-xl transition-all"
                                title="Confirmar Alteração">
                          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button onclick="cancelEdit(<?= $p['id'] ?>)" 
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
  ['nome', 'faixa', 'orientacao'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.add('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.remove('hidden');
  });
  document.querySelector('.edit-controls-' + id).classList.add('hidden');
  document.querySelector('.edit-actions-' + id).classList.remove('hidden');
  document.querySelector('.edit-input-nome-' + id).focus();
}

function cancelEdit(id) {
  ['nome', 'faixa', 'orientacao'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.remove('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.add('hidden');
  });
  document.querySelector('.edit-controls-' + id).classList.remove('hidden');
  document.querySelector('.edit-actions-' + id).classList.add('hidden');
}

async function saveRow(id, btn) {
  const nome = document.querySelector('.edit-input-nome-' + id).value.trim();
  const faixa_min = parseFloat(document.querySelector('.edit-input-faixa-min-' + id).value);
  const faixa_max = document.querySelector('.edit-input-faixa-max-' + id).value;
  const orientacao = document.querySelector('.edit-input-orientacao-' + id).value.trim();
  
  if (!nome || !orientacao) { showToast('Nome e orientação são obrigatórios', 'error'); return; }
  
  // Validar faixas
  if (isNaN(faixa_min) || faixa_min < 0 || faixa_min > 100) {
    showToast('Faixa mínima deve ser um número entre 0 e 100', 'warning');
    return;
  }
  
  if (faixa_max !== '' && faixa_max !== null) {
    const faixa_max_num = parseFloat(faixa_max);
    if (isNaN(faixa_max_num) || faixa_max_num < 0 || faixa_max_num > 100) {
      showToast('Faixa máxima deve ser um número entre 0 e 100', 'warning');
      return;
    }
    if (faixa_max_num <= faixa_min) {
      showToast('Faixa máxima deve ser maior que a faixa mínima', 'warning');
      return;
    }
  }
  
  setButtonLoading(btn);
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('nome', nome);
    formData.append('faixa_min', faixa_min);
    formData.append('faixa_max', faixa_max);
    formData.append('orientacao', orientacao);
    
    const response = await fetch('/registros/parametros/update', {
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
  const confirm = await showConfirm('Excluir Parâmetro', 'Tem certeza que deseja remover esta regra de pesagem? Esta ação pode afetar a triagem de novos toners.');
  if (!confirm) return;
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('/registros/parametros/delete', {
      method: 'POST',
      body: formData
    });
    
    if (response.ok) {
      window.location.reload();
    } else {
      showToast('Erro ao excluir parâmetro', 'error');
    }
  } catch (err) {
    showToast('Erro de conexão', 'error');
  }
}
</script>
