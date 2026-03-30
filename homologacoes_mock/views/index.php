<?php require __DIR__ . '/_subnav.php'; ?>

<!-- Header -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">Painel Geral</h2>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Resumo e acompanhamento de todas as máquinas</p>
</div>

<!-- Alertas -->
<?php foreach ($alertas as $alerta): ?>
    <?php 
        $alertColor = $alerta['tipo'] === 'logistica' ? 'amber' : 'rose';
        $alertIcon = $alerta['tipo'] === 'logistica' ? 'ph-truck' : 'ph-warning-octagon';
    ?>
    <div class="bg-<?= $alertColor ?>-50 border-l-4 border-<?= $alertColor ?>-500 text-<?= $alertColor ?>-800 dark:bg-<?= $alertColor ?>-900/30 dark:text-<?= $alertColor ?>-300 p-4 rounded-r-xl shadow-sm mb-4 last:mb-6 flex items-start gap-3">
        <i class="ph-fill <?= $alertIcon ?> text-xl mt-0.5"></i>
        <div class="text-sm"><?= $alerta['msg'] ?></div>
    </div>
<?php endforeach; ?>

<!-- Resumo em Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-5 border border-slate-100 dark:border-slate-700">
        <h6 class="text-slate-500 flex items-center gap-1.5 dark:text-slate-400 text-xs uppercase font-bold tracking-wider mb-2">Total</h6>
        <h3 class="text-3xl font-black text-slate-800 dark:text-white"><?= $totais['total'] ?></h3>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-5 border-l-4 border-amber-400 border-y border-r border-slate-100 dark:border-slate-700">
        <h6 class="text-slate-500 flex items-center gap-1.5 dark:text-slate-400 text-xs uppercase font-bold tracking-wider mb-2">Aguardando</h6>
        <h3 class="text-3xl font-black text-amber-500 dark:text-amber-400"><?= $totais['aguardando'] ?></h3>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-5 border-l-4 border-primary-500 border-y border-r border-slate-100 dark:border-slate-700">
        <h6 class="text-slate-500 flex items-center gap-1.5 dark:text-slate-400 text-xs uppercase font-bold tracking-wider mb-2">Em Testes</h6>
        <h3 class="text-3xl font-black text-primary-500 dark:text-primary-400"><?= $totais['em_andamento'] ?></h3>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-5 border-l-4 border-emerald-500 border-y border-r border-slate-100 dark:border-slate-700">
        <h6 class="text-slate-500 flex items-center gap-1.5 dark:text-slate-400 text-xs uppercase font-bold tracking-wider mb-2">Concluídas</h6>
        <h3 class="text-3xl font-black text-emerald-500 dark:text-emerald-400"><?= $totais['concluidas'] ?></h3>
    </div>
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-5 border-l-4 border-rose-500 border-y border-r border-slate-100 dark:border-slate-700">
        <h6 class="text-slate-500 flex items-center gap-1.5 dark:text-slate-400 text-xs uppercase font-bold tracking-wider mb-2">Canceladas</h6>
        <h3 class="text-3xl font-black text-rose-500 dark:text-rose-400"><?= $totais['canceladas'] ?></h3>
    </div>
</div>

<!-- Lista de Homologações -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
    <div class="p-5 border-b border-slate-200 dark:border-slate-700/50 flex flex-col lg:flex-row items-center justify-between gap-4">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="ph-fill ph-list-dashes text-primary-500"></i> Fila Geral
        </h3>
        
        <form method="GET" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <select name="status" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5" onchange="this.form.submit()">
                <option value="">Status (Todos)</option>
                <option value="aguardando_chegada" <?= $filtroStatus == 'aguardando_chegada' ? 'selected' : '' ?>>Aguardando Chegada</option>
                <option value="item_recebido" <?= $filtroStatus == 'item_recebido' ? 'selected' : '' ?>>Item Recebido</option>
                <option value="em_homologacao" <?= $filtroStatus == 'em_homologacao' ? 'selected' : '' ?>>Em Homologação</option>
                <option value="concluida" <?= $filtroStatus == 'concluida' ? 'selected' : '' ?>>Concluída</option>
                <option value="cancelada" <?= $filtroStatus == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
            </select>
            <select name="tipo" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5" onchange="this.form.submit()">
                <option value="">Tipo (Todos)</option>
                <option value="Impressora" <?= $filtroTipo == 'Impressora' ? 'selected' : '' ?>>Impressora</option>
                <option value="Notebook" <?= $filtroTipo == 'Notebook' ? 'selected' : '' ?>>Notebook</option>
                <option value="Suprimento de Impressora" <?= $filtroTipo == 'Suprimento de Impressora' ? 'selected' : '' ?>>Suprimento</option>
                <option value="Peça de Impressora" <?= $filtroTipo == 'Peça de Impressora' ? 'selected' : '' ?>>Peça</option>
            </select>
            <a href="index.php" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white p-2">Limpar</a>
        </form>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700/50">
                <tr>
                    <th scope="col" class="px-6 py-4">Versão</th>
                    <th scope="col" class="px-6 py-4">Código</th>
                    <th scope="col" class="px-6 py-4">Equipamento</th>
                    <th scope="col" class="px-6 py-4">Qtd / Tipo</th>
                    <th scope="col" class="px-6 py-4">Status</th>
                    <th scope="col" class="px-6 py-4">Prev. Chegada</th>
                    <th scope="col" class="px-6 py-4">Responsáveis</th>
                    <th scope="col" class="px-6 py-4">Parecer Final</th>
                    <th scope="col" class="px-6 py-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                <?php if (empty($lista)): ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">A fila de homologações está vazia.</td></tr>
                <?php endif; ?>
                <?php foreach ($lista as $h): ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1"><?= getRotuloVersao(getVersaoHomologacao($h['id'])) ?></div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap"><?= $h['codigo'] ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center mr-3 text-slate-500 dark:text-slate-400">
                                <i class="ph <?= getIconForTipo($h['tipo_equipamento']) ?> text-xl"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 dark:text-slate-200"><?= $h['titulo'] ?></div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= $h['fornecedor'] ?> | <?= $h['modelo'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-200"><?= $h['quantidade'] ?? 1 ?> un.</div>
                        <div class="text-[11px] <?= ($h['tipo_aquisicao'] ?? 'comprado') === 'comprado' ? 'text-emerald-600' : 'text-amber-600' ?> font-semibold">
                            <?= ($h['tipo_aquisicao'] ?? 'comprado') === 'comprado' ? 'Comprado' : 'Emprestado' ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= getBadgeClass($h['status']) ?>">
                            <?= getStatusLabel($h['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?= $h['data_prevista_chegada'] ? date('d/m/Y', strtotime($h['data_prevista_chegada'])) : '-' ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            <?php foreach ($h['responsaveis'] as $resp_id): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                    <?= getUserById($resp_id)['nome'] ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($h['resultado'] === 'aprovado'): ?>
                            <span class="inline-flex text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 px-2 py-1 rounded-md text-xs font-bold">Aprovado</span>
                        <?php elseif ($h['resultado'] === 'reprovado'): ?>
                            <span class="inline-flex text-rose-600 bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 px-2 py-1 rounded-md text-xs font-bold">Reprovado</span>
                        <?php elseif ($h['resultado'] === 'aprovado_restricoes'): ?>
                            <span class="inline-flex text-amber-600 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 px-2 py-1 rounded-md text-xs font-bold">Aprov. c/ Restrições</span>
                        <?php else: ?>
                            <span class="text-slate-400 italic text-xs">Aguardando</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            <?php if (($u['perfil'] === 'compras' || $u['perfil'] === 'admin') && $h['status'] !== 'cancelada'): ?>
                                <button type="button" onclick="openCancelModal(<?= $h['id'] ?>, '<?= $h['codigo'] ?>')" class="p-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-colors group" title="Cancelar Homologação">
                                    <i class="ph-fill ph-x-circle text-xl group-hover:scale-110 transition-transform"></i>
                                </button>
                            <?php endif; ?>
                            <a href="detalhe_homologacao.php?id=<?= $h['id'] ?>" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-colors">
                                Abrir
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal de Cancelamento -->
<div id="cancelModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeCancelModal()"></div>
    <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform transition-all">
        <div class="p-6">
            <div class="w-16 h-16 bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center mb-4 mx-auto">
                <i class="ph-fill ph-warning text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 dark:text-white text-center mb-2">Cancelar Homologação</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm text-center mb-6">Você está prestes a cancelar a <strong id="cancelCode"></strong>. Como deseja proceder?</p>
            
            <form id="cancelForm" method="POST" action="">
                <input type="hidden" name="acao" value="cancelar_homologacao">
                <input type="hidden" name="id" id="cancelId">
                
                <div class="space-y-3 mb-8">
                    <label class="flex items-center gap-3 p-4 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <input type="radio" name="excluir_definitivo" value="0" checked class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                        <div class="flex-1">
                            <span class="block text-sm font-bold text-slate-700 dark:text-slate-200">Apenas Cancelar</span>
                            <span class="block text-[10px] text-slate-500">Mantém o registro na fila com status "Cancelado".</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 border border-slate-200 dark:border-slate-700 rounded-xl cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <input type="radio" name="excluir_definitivo" value="1" class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                        <div class="flex-1">
                            <span class="block text-sm font-bold text-rose-600">Excluir da Fila Geral</span>
                            <span class="block text-[10px] text-slate-500">Remove permanentemente esta homologação do sistema.</span>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeCancelModal()" class="flex-1 px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">Manter Ativa</button>
                    <button type="button" onclick="processCancellation()" class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 shadow-lg shadow-rose-200 dark:shadow-none">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Overlay de Notificação (Simulação de Animação) -->
<div id="notifOverlay" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md">
    <div class="text-center animate-bounce-slow">
        <div class="relative w-24 h-24 mb-6 mx-auto">
            <div class="absolute inset-0 bg-rose-500/20 rounded-full animate-ping"></div>
            <div class="relative w-24 h-24 bg-rose-600 rounded-full flex items-center justify-center text-white shadow-2xl">
                <i class="ph-fill ph-paper-plane-tilt text-4xl"></i>
            </div>
        </div>
        <h2 class="text-2xl font-black text-white mb-2 tracking-tight">ENVIANDO ALERTAS...</h2>
        <p class="text-rose-200 text-sm font-medium">Notificando todos os setores envolvidos sobre o cancelamento.</p>
    </div>
</div>

<script>
function openCancelModal(id, code) {
    document.getElementById('cancelId').value = id;
    document.getElementById('cancelCode').innerText = code;
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}

function processCancellation() {
    // Esconder modal
    closeCancelModal();
    // Mostrar animação de notificação
    document.getElementById('notifOverlay').classList.remove('hidden');
    
    // Pequeno delay para simular o envio antes do submit
    setTimeout(() => {
        document.getElementById('cancelForm').submit();
    }, 800);
}
</script>

<style>
@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.animate-bounce-slow {
    animation: bounce-slow 2s infinite ease-in-out;
}
</style>
