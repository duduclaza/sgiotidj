<?php require __DIR__ . '/_subnav.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2 flex items-center gap-3">
        <i class="ph-fill ph-gear text-primary-500"></i> Gestão de Homologações 2.0
    </h1>
    <p class="text-slate-500 dark:text-slate-400">Configure os tipos de produtos e seus respectivos checklists técnicos.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Coluna 1: Tipos de Produto -->
    <div class="lg:col-span-4 space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20">
                <h2 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="ph ph-package text-lg"></i> Tipos de Produto
                </h2>
            </div>
            
            <form method="POST" class="p-5 space-y-4">
                <input type="hidden" name="acao_produto" value="adicionar">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Novo Tipo</label>
                    <input type="text" name="nome_produto" required placeholder="Ex: Servidor, Totem, Coletor..." 
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-all">
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                    <i class="ph-bold ph-plus"></i> Adicionar Tipo
                </button>
            </form>
            
            <div class="px-5 pb-5">
                <div class="text-xs font-semibold text-slate-500 uppercase mb-3 px-1">Cadastrados (<?= count($tipos) ?>)</div>
                <div class="space-y-1 max-h-[400px] overflow-y-auto pr-1">
                    <?php if (empty($tipos)): ?>
                        <div class="text-center py-8 text-slate-400 italic text-sm">Nenhum tipo cadastrado.</div>
                    <?php else: ?>
                        <?php foreach ($tipos as $tipo): ?>
                            <div class="group flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-600">
                                <span class="text-slate-700 dark:text-slate-200 font-medium text-sm"><?= htmlspecialchars($tipo['nome']) ?></span>
                                <form method="POST" onsubmit="return confirm('Excluir este tipo?')" class="m-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <input type="hidden" name="acao_produto" value="excluir">
                                    <input type="hidden" name="id_produto" value="<?= $tipo['id'] ?>">
                                    <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Coluna 2: Checklists -->
    <div class="lg:col-span-8 space-y-6">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20 flex justify-between items-center">
                <h2 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="ph ph-list-checks text-lg"></i> Checklists
                </h2>
                <button onclick="toggleNovoChecklist()" class="text-sm font-bold text-primary-600 hover:text-primary-700 flex items-center gap-1">
                    <i class="ph ph-plus-circle"></i> Criar Novo
                </button>
            </div>

            <!-- Form Novo Checklist (hidden) -->
            <div id="formChecklistArea" class="hidden p-6 border-b border-slate-100 dark:border-slate-700 bg-primary-50/10 dark:bg-primary-900/5">
                <form method="POST" class="space-y-5">
                    <input type="hidden" name="acao_checklist" value="adicionar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Título</label>
                            <input type="text" name="titulo" required placeholder="Ex: Checklist de Servidores" 
                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Produto</label>
                            <select name="tipo_produto_nome" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Genérico --</option>
                                <?php foreach ($tipos as $tp): ?>
                                    <option value="<?= htmlspecialchars($tp['nome']) ?>"><?= htmlspecialchars($tp['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase">Itens de Verificação</label>
                            <button type="button" onclick="addItemTo('itensContainerNew')" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                <i class="ph ph-plus-circle"></i> Item
                            </button>
                        </div>
                        <div id="itensContainerNew" class="space-y-2">
                            <input type="text" name="itens[]" required placeholder="Item 1" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2 text-sm text-slate-900 dark:text-white">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold px-6 py-2.5 rounded-xl flex items-center gap-2">
                            <i class="ph ph-floppy-disk"></i> Salvar
                        </button>
                        <button type="button" onclick="toggleNovoChecklist()" class="text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 px-6 py-2.5 rounded-xl font-medium">Cancelar</button>
                    </div>
                </form>
            </div>

            <!-- Lista de Checklists -->
            <div class="p-6">
                <?php
                    $todosChecklists = $checklistsPorTipo ?? [];
                    $temChecklist = false;
                    foreach ($todosChecklists as $tipoNome => $itens) {
                        if (!empty($itens)) $temChecklist = true;
                    }
                ?>
                <?php if (!$temChecklist && empty($checklists)): ?>
                    <div class="text-center py-12 text-slate-400">Nenhum checklist configurado.</div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Checklists Pré-existentes (mock_checklists_por_tipo) -->
                        <?php $existIdx = 0; foreach ($todosChecklists as $tipoNome => $itens): ?>
                            <?php if (empty($itens)) continue; $existIdx++; ?>
                            <div class="bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 flex flex-col justify-between hover:shadow-md transition-all">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-primary-500 uppercase tracking-wider"><?= htmlspecialchars($tipoNome) ?></span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" onclick="openEditExistente('<?= htmlspecialchars($tipoNome, ENT_QUOTES) ?>')" 
                                                class="text-slate-400 hover:text-primary-500 p-1" title="Editar">
                                                <i class="ph ph-pencil-simple"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <h3 class="font-bold text-slate-800 dark:text-white mb-2 text-sm">Checklist de <?= htmlspecialchars($tipoNome) ?></h3>
                                    <ul class="space-y-1" id="listaItens_exist_<?= $existIdx ?>">
                                        <?php foreach ($itens as $key => $label): ?>
                                            <li class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                                <i class="ph ph-check-square text-primary-500"></i>
                                                <?= htmlspecialchars($label) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center">
                                    <span class="text-[10px] text-slate-400"><?= count($itens) ?> itens</span>
                                    <span class="bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 text-[10px] font-bold px-2 py-0.5 rounded">PADRÃO</span>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Checklists Criados pelo Usuário -->
                        <?php foreach ($checklists as $ch): ?>
                            <div class="bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 flex flex-col justify-between hover:shadow-md transition-all">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-primary-500 uppercase tracking-wider">
                                            <?= htmlspecialchars($ch['tipo_produto_nome'] ?: 'Geral') ?>
                                        </span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" onclick='openEditCustom(<?= json_encode($ch, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                                                class="text-slate-400 hover:text-primary-500 p-1" title="Editar">
                                                <i class="ph ph-pencil-simple"></i>
                                            </button>
                                            <form method="POST" onsubmit="return confirm('Excluir?')" class="inline">
                                                <input type="hidden" name="acao_checklist" value="excluir">
                                                <input type="hidden" name="id_checklist" value="<?= $ch['id'] ?>">
                                                <button type="submit" class="text-slate-400 hover:text-rose-500 p-1"><i class="ph ph-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <h3 class="font-bold text-slate-800 dark:text-white mb-2 text-sm"><?= htmlspecialchars($ch['titulo']) ?></h3>
                                    <ul class="space-y-1">
                                        <?php foreach ($ch['itens'] as $item): ?>
                                            <li class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                                <i class="ph ph-check-square text-primary-500"></i>
                                                <?= htmlspecialchars($item) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="text-[10px] text-slate-400 mt-2">Criado: <?= date('d/m/Y', strtotime($ch['criado_em'])) ?></p>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center">
                                    <span class="text-[10px] text-slate-400"><?= count($ch['itens']) ?> itens</span>
                                    <span class="bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-[10px] font-bold px-2 py-0.5 rounded">CUSTOMIZADO</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE EDIÇÃO -->
<div id="modalEditar" class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4" onclick="if(event.target===this) fecharModalEditar()">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="ph ph-pencil-simple text-primary-500"></i> <span id="modalEditarTitulo">Editar Checklist</span>
            </h3>
            <button onclick="fecharModalEditar()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xl">&times;</button>
        </div>
        <form method="POST" id="formEditar" class="p-5 space-y-5">
            <!-- Estes hidden fields são preenchidos via JS -->
            <input type="hidden" name="acao_checklist" id="editAcao" value="">
            <input type="hidden" name="id_checklist" id="editId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div id="editTituloWrap">
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Título</label>
                    <input type="text" name="titulo" id="editTituloInput" required
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Produto</label>
                    <select name="tipo_produto_nome" id="editTipoProduto"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500">
                        <option value="">-- Genérico --</option>
                        <?php foreach ($tipos as $tp): ?>
                            <option value="<?= htmlspecialchars($tp['nome']) ?>"><?= htmlspecialchars($tp['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-semibold text-slate-500 uppercase">Itens de Verificação</label>
                    <button type="button" onclick="addItemTo('editItensContainer')" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                        <i class="ph ph-plus-circle"></i> Item
                    </button>
                </div>
                <div id="editItensContainer" class="space-y-2">
                    <!-- Preenchido via JS -->
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold px-6 py-2.5 rounded-xl flex items-center gap-2 transition-colors">
                    <i class="ph ph-floppy-disk"></i> Salvar Alterações
                </button>
                <button type="button" onclick="fecharModalEditar()" class="text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 px-6 py-2.5 rounded-xl font-medium transition-colors">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Dados dos checklists existentes para o JS -->
<script>
const checklistsPorTipo = <?= json_encode($checklistsPorTipo ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function toggleNovoChecklist() {
    document.getElementById('formChecklistArea').classList.toggle('hidden');
}

function addItemTo(containerId) {
    const container = document.getElementById(containerId);
    const count = container.querySelectorAll('input').length;
    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-center gap-2';
    wrapper.innerHTML = `
        <input type="text" name="itens[]" placeholder="Item ${count + 1}" required
            class="flex-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2 text-sm text-slate-900 dark:text-white">
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-600 p-1">
            <i class="ph ph-x-circle text-lg"></i>
        </button>
    `;
    container.appendChild(wrapper);
}

function fecharModalEditar() {
    document.getElementById('modalEditar').classList.add('hidden');
}

// Editar checklist pré-existente (do mock_checklists_por_tipo)
function openEditExistente(tipoNome) {
    const itens = checklistsPorTipo[tipoNome] || {};
    
    document.getElementById('editAcao').value = 'editar_existente';
    document.getElementById('editId').value = '';
    document.getElementById('editTituloInput').value = 'Checklist de ' + tipoNome;
    document.getElementById('editTituloWrap').style.display = 'none'; // Título fixo para existentes
    document.getElementById('editTipoProduto').value = tipoNome;
    document.getElementById('editTipoProduto').disabled = true; // Não pode mudar o tipo
    document.getElementById('modalEditarTitulo').textContent = 'Editar: ' + tipoNome;

    // Criar um hidden para garantir que tipo_produto_nome seja enviado mesmo disabled
    const existingHidden = document.getElementById('editTipoProdutoHidden');
    if (existingHidden) existingHidden.remove();
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'tipo_produto_nome';
    hiddenInput.value = tipoNome;
    hiddenInput.id = 'editTipoProdutoHidden';
    document.getElementById('formEditar').appendChild(hiddenInput);

    // Popular itens
    const container = document.getElementById('editItensContainer');
    container.innerHTML = '';
    const values = Object.values(itens);
    values.forEach((label, i) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-center gap-2';
        wrapper.innerHTML = `
            <input type="text" name="itens[]" value="${escapeHtml(label)}" required
                class="flex-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2 text-sm text-slate-900 dark:text-white">
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-600 p-1">
                <i class="ph ph-x-circle text-lg"></i>
            </button>
        `;
        container.appendChild(wrapper);
    });

    document.getElementById('modalEditar').classList.remove('hidden');
}

// Editar checklist criado pelo usuário
function openEditCustom(ch) {
    document.getElementById('editAcao').value = 'editar_custom';
    document.getElementById('editId').value = ch.id;
    document.getElementById('editTituloInput').value = ch.titulo;
    document.getElementById('editTituloWrap').style.display = '';
    document.getElementById('editTipoProduto').value = ch.tipo_produto_nome || '';
    document.getElementById('editTipoProduto').disabled = false;
    document.getElementById('modalEditarTitulo').textContent = 'Editar: ' + ch.titulo;

    // Remover hidden extra se existir
    const existingHidden = document.getElementById('editTipoProdutoHidden');
    if (existingHidden) existingHidden.remove();

    // Popular itens
    const container = document.getElementById('editItensContainer');
    container.innerHTML = '';
    (ch.itens || []).forEach((label, i) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex items-center gap-2';
        wrapper.innerHTML = `
            <input type="text" name="itens[]" value="${escapeHtml(label)}" required
                class="flex-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2 text-sm text-slate-900 dark:text-white">
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-600 p-1">
                <i class="ph ph-x-circle text-lg"></i>
            </button>
        `;
        container.appendChild(wrapper);
    });

    document.getElementById('modalEditar').classList.remove('hidden');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
