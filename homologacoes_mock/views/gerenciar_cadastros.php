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
                    <i class="ph ph-package text-lg"></i> Novos Tipos de Produto
                </h2>
            </div>
            
            <form method="POST" class="p-5 space-y-4">
                <input type="hidden" name="acao_produto" value="adicionar">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Nome do Produto</label>
                    <input type="text" name="nome_produto" required placeholder="Ex: Servidor, Totem, Coletor..." 
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-all">
                </div>
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                    <i class="ph-bold ph-plus"></i> Adicionar Tipo
                </button>
            </form>
            
            <div class="px-5 pb-5">
                <div class="text-xs font-semibold text-slate-500 uppercase mb-3 px-1">Tipos Cadastrados</div>
                <div class="space-y-1 max-h-[400px] overflow-y-auto pr-1">
                    <?php if (empty($tipos)): ?>
                        <div class="text-center py-8 text-slate-400 italic text-sm">Nenhum tipo cadastrado.</div>
                    <?php else: ?>
                        <?php foreach ($tipos as $tipo): ?>
                            <div class="group flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-600">
                                <span class="text-slate-700 dark:text-slate-200 font-medium"><?= htmlspecialchars($tipo['nome']) ?></span>
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
                    <i class="ph ph-list-checks text-lg"></i> Configuração de Checklists
                </h2>
                <button onclick="toggleNovoChecklist()" class="text-sm font-bold text-primary-600 hover:text-primary-700 flex items-center gap-1">
                    <i class="ph ph-plus-circle"></i> Criar Novo Checklist
                </button>
            </div>

            <!-- Form Novo Checklist (Hidden by default) -->
            <div id="formChecklistArea" class="hidden p-6 border-b border-slate-100 dark:border-slate-700 bg-primary-50/10 dark:bg-primary-900/5">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="acao_checklist" value="adicionar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Título do Checklist</label>
                            <input type="text" name="titulo" required placeholder="Ex: Checklist de Servidores" 
                                class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Vincular ao Produto</label>
                            <select name="tipo_produto_nome" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 transition-all">
                                <option value="">-- Checklist Genérico --</option>
                                <?php foreach ($tipos as $tp): ?>
                                    <option value="<?= htmlspecialchars($tp['nome']) ?>"><?= htmlspecialchars($tp['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase">Itens de Verificação</label>
                            <button type="button" onclick="addItem()" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                <i class="ph ph-plus-circle"></i> Item
                            </button>
                        </div>
                        <div id="itensContainer" class="space-y-2">
                            <input type="text" name="itens[]" required placeholder="Item 1" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2 text-sm text-slate-900 dark:text-white">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold px-6 py-2.5 rounded-xl flex items-center gap-2">
                            <i class="ph ph-floppy-disk"></i> Salvar Checklist
                        </button>
                        <button type="button" onclick="toggleNovoChecklist()" class="text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 px-6 py-2.5 rounded-xl font-medium">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Checklists existentes (mock_checklists_por_tipo) -->
            <div class="p-6">
                <?php
                    // Mostrar os checklists do mock_checklists_por_tipo existente
                    $todosChecklists = $_SESSION['mock_checklists_por_tipo'] ?? [];
                    $temChecklist = false;
                    foreach ($todosChecklists as $tipoNome => $itens) {
                        if (!empty($itens)) $temChecklist = true;
                    }
                ?>
                <?php if (!$temChecklist && empty($checklists)): ?>
                    <div class="text-center py-12 text-slate-400">Nenhum checklist configurado.</div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($todosChecklists as $tipoNome => $itens): ?>
                            <?php if (empty($itens)) continue; ?>
                            <div class="bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 flex flex-col justify-between hover:shadow-md transition-all">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-primary-500 uppercase tracking-wider"><?= htmlspecialchars($tipoNome) ?></span>
                                    </div>
                                    <h3 class="font-bold text-slate-800 dark:text-white mb-2">Checklist de <?= htmlspecialchars($tipoNome) ?></h3>
                                    <ul class="space-y-1">
                                        <?php foreach ($itens as $key => $label): ?>
                                            <li class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                                <i class="ph ph-check-square text-primary-500"></i>
                                                <?= htmlspecialchars($label) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center">
                                    <span class="text-[10px] text-slate-400"><?= count($itens) ?> itens</span>
                                    <span class="bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-[10px] font-bold px-2 py-0.5 rounded">ATIVO</span>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($checklists as $ch): ?>
                            <div class="bg-slate-50/50 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 flex flex-col justify-between hover:shadow-md transition-all">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-primary-500 uppercase tracking-wider">
                                            <?= htmlspecialchars($ch['tipo_produto_nome'] ?: 'Geral') ?>
                                        </span>
                                        <form method="POST" onsubmit="return confirm('Excluir este checklist?')">
                                            <input type="hidden" name="acao_checklist" value="excluir">
                                            <input type="hidden" name="id_checklist" value="<?= $ch['id'] ?>">
                                            <button type="submit" class="text-slate-400 hover:text-rose-500"><i class="ph ph-trash"></i></button>
                                        </form>
                                    </div>
                                    <h3 class="font-bold text-slate-800 dark:text-white mb-2"><?= htmlspecialchars($ch['titulo']) ?></h3>
                                    <ul class="space-y-1">
                                        <?php foreach ($ch['itens'] as $item): ?>
                                            <li class="text-xs text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                                <i class="ph ph-check-square text-primary-500"></i>
                                                <?= htmlspecialchars($item) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="text-[10px] text-slate-400 mt-2">Criado em: <?= date('d/m/Y', strtotime($ch['criado_em'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleNovoChecklist() {
    const area = document.getElementById('formChecklistArea');
    area.classList.toggle('hidden');
}

function addItem() {
    const container = document.getElementById('itensContainer');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'itens[]';
    input.placeholder = `Item ${container.children.length + 1}`;
    input.required = true;
    input.className = 'w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl px-4 py-2 text-sm text-slate-900 dark:text-white mt-2';
    container.appendChild(input);
}
</script>
