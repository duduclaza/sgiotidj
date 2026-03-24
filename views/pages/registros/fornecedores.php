<section class="space-y-6">
  <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Fornecedores</h1>
  
  <!-- Formulário de Cadastro -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 p-6 transition-colors">
    <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Cadastrar Novo Fornecedor</h2>
    <form method="post" action="/registros/fornecedores/store" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
      <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Nome</label>
        <input type="text" name="nome" placeholder="Nome do fornecedor" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl px-4 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all" required>
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5 ml-1">Contato</label>
        <input type="text" name="contato" placeholder="Link/Email/Tel" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl px-4 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
      </div>
      <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5 ml-1">RMA</label>
        <input type="text" name="rma" placeholder="Link/Email/Tel" class="w-full bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-xl px-4 py-2 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
      </div>
      <button class="px-6 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all shadow-lg hover:shadow-blue-500/20 flex items-center justify-center gap-2 h-10">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Salvar
      </button>
    </form>
  </div>

  <!-- Lista/Grid -->
  <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700/50 overflow-hidden transition-colors">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-900/50">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Fornecedores Cadastrados</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
        <thead class="bg-gray-50 dark:bg-slate-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nome</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contato</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">RMA</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-100 dark:divide-slate-700">
          <?php if (empty($fornecedores)): ?>
            <tr>
              <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 italic">Nenhum fornecedor cadastrado</td>
            </tr>
          <?php else: ?>
            <?php foreach ($fornecedores as $f): ?>
              <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="edit-display-nome-<?= $f['id'] ?> text-sm text-gray-900 dark:text-white font-medium"><?= e($f['nome']) ?></span>
                  <input type="text" class="edit-input-nome-<?= $f['id'] ?> bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-lg px-2 py-1 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none hidden w-full" value="<?= e($f['nome']) ?>">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="edit-display-contato-<?= $f['id'] ?> text-sm text-gray-600 dark:text-gray-400"><?= e($f['contato']) ?></span>
                  <input type="text" class="edit-input-contato-<?= $f['id'] ?> bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-lg px-2 py-1 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none hidden w-full" value="<?= e($f['contato']) ?>">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="edit-display-rma-<?= $f['id'] ?> text-sm text-gray-600 dark:text-gray-400"><?= e($f['rma']) ?></span>
                  <input type="text" class="edit-input-rma-<?= $f['id'] ?> bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-600 rounded-lg px-2 py-1 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none hidden w-full" value="<?= e($f['rma']) ?>">
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button onclick="editRow(<?= $f['id'] ?>)" class="edit-btn-<?= $f['id'] ?> p-1.5 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/60 transition-colors" title="Editar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="saveRow(<?= $f['id'] ?>)" class="save-btn-<?= $f['id'] ?> p-1.5 bg-green-50 dark:bg-green-900/40 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/60 transition-colors hidden" title="Salvar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <button onclick="cancelEdit(<?= $f['id'] ?>)" class="cancel-btn-<?= $f['id'] ?> p-1.5 bg-gray-50 dark:bg-slate-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-slate-600 transition-colors hidden" title="Cancelar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <button onclick="deleteRow(<?= $f['id'] ?>)" class="p-1.5 bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/60 transition-colors" title="Excluir">
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
function editRow(id) {
  ['nome', 'contato', 'rma'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.add('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.remove('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.add('hidden');
  document.querySelector('.save-btn-' + id).classList.remove('hidden');
  document.querySelector('.cancel-btn-' + id).classList.remove('hidden');
}

function cancelEdit(id) {
  ['nome', 'contato', 'rma'].forEach(field => {
    document.querySelector('.edit-display-' + field + '-' + id).classList.remove('hidden');
    document.querySelector('.edit-input-' + field + '-' + id).classList.add('hidden');
  });
  document.querySelector('.edit-btn-' + id).classList.remove('hidden');
  document.querySelector('.save-btn-' + id).classList.add('hidden');
  document.querySelector('.cancel-btn-' + id).classList.add('hidden');
}

function saveRow(id) {
  const nome = document.querySelector('.edit-input-nome-' + id).value.trim();
  const contato = document.querySelector('.edit-input-contato-' + id).value.trim();
  const rma = document.querySelector('.edit-input-rma-' + id).value.trim();
  
  if (!nome) { alert('Nome é obrigatório'); return; }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/registros/fornecedores/update';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">' +
                   '<input type="hidden" name="nome" value="' + nome + '">' +
                   '<input type="hidden" name="contato" value="' + contato + '">' +
                   '<input type="hidden" name="rma" value="' + rma + '">';
  document.body.appendChild(form);
  form.submit();
}

function deleteRow(id) {
  if (!confirm('Tem certeza que deseja excluir este fornecedor?')) return;
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/registros/fornecedores/delete';
  form.innerHTML = '<input type="hidden" name="id" value="' + id + '">';
  document.body.appendChild(form);
  form.submit();
}
</script>
