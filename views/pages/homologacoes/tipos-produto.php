<section class="space-y-6">
  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-slate-800">Tipos de Produto - Homologações</h1>
    <a href="/homologacoes" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 text-sm font-medium transition">
      Voltar para Kanban
    </a>
  </div>
  
  <!-- Formulário de Cadastro -->
  <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-slate-700 mb-4">Novo Tipo de Produto</h2>
    <form method="post" action="/homologacoes/tipos/store" class="flex flex-col sm:flex-row gap-4 items-end">
      <div class="flex-1 w-full">
        <label class="block text-sm font-medium text-slate-600 mb-1">Nome do Equipamento/Produto</label>
        <input type="text" name="nome" placeholder="Ex: Servidor, Coletor, TOTEM..." 
               class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" required>
      </div>
      <button class="px-6 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition shadow-sm">
        Cadastrar
      </button>
    </form>
  </div>

  <!-- Lista de Tipos -->
  <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
      <h2 class="text-lg font-semibold text-slate-700">Tipos Cadastrados</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nome</th>
            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($tipos)): ?>
            <tr>
              <td colspan="3" class="px-6 py-10 text-center text-slate-400 italic">
                Nenhum tipo de produto cadastrado.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($tipos as $t): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span id="display-nome-<?= $t['id'] ?>" class="text-sm font-medium text-slate-900"><?= htmlspecialchars($t['nome']) ?></span>
                  <input type="text" id="input-nome-<?= $t['id'] ?>" 
                         class="hidden border border-slate-300 rounded px-2 py-1 text-sm w-full max-w-xs" 
                         value="<?= htmlspecialchars($t['nome']) ?>">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php if ($t['ativo']): ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                  <?php else: ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800">Inativo</span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                  <button onclick="editMode(<?= $t['id'] ?>)" id="btn-edit-<?= $t['id'] ?>" 
                          class="text-blue-600 hover:text-blue-900">Editar</button>
                  
                  <button onclick="saveEdit(<?= $t['id'] ?>)" id="btn-save-<?= $t['id'] ?>" 
                          class="hidden text-green-600 hover:text-green-900">Salvar</button>
                  
                  <button onclick="cancelEdit(<?= $t['id'] ?>)" id="btn-cancel-<?= $t['id'] ?>" 
                          class="hidden text-slate-500 hover:text-slate-800">Cancelar</button>
                  
                  <form method="post" action="/homologacoes/tipos/delete" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esse tipo?')">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                  </form>
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
function editMode(id) {
    document.getElementById('display-nome-' + id).classList.add('hidden');
    document.getElementById('input-nome-' + id).classList.remove('hidden');
    document.getElementById('btn-edit-' + id).classList.add('hidden');
    document.getElementById('btn-save-' + id).classList.remove('hidden');
    document.getElementById('btn-cancel-' + id).classList.remove('hidden');
}

function cancelEdit(id) {
    document.getElementById('display-nome-' + id).classList.remove('hidden');
    document.getElementById('input-nome-' + id).classList.add('hidden');
    document.getElementById('btn-edit-' + id).classList.remove('hidden');
    document.getElementById('btn-save-' + id).classList.add('hidden');
    document.getElementById('btn-cancel-' + id).classList.add('hidden');
}

function saveEdit(id) {
    const nome = document.getElementById('input-nome-' + id).value.trim();
    if (!nome) {
        alert('Nome não pode estar vazio!');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/homologacoes/tipos/update';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = id;
    
    const nomeInput = document.createElement('input');
    nomeInput.type = 'hidden';
    nomeInput.name = 'nome';
    nomeInput.value = nome;
    
    const ativoInput = document.createElement('input');
    ativoInput.type = 'hidden';
    ativoInput.name = 'ativo';
    ativoInput.value = '1'; // Mantendo ativo por padrão na edição simples

    form.appendChild(idInput);
    form.appendChild(nomeInput);
    form.appendChild(ativoInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
