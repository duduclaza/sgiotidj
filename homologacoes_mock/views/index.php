<?php require __DIR__ . '/_subnav.php'; ?>

<!-- Header -->
<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">Painel Geral</h2>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Resumo e acompanhamento de todas as máquinas</p>
</div>

<!-- Alertas -->
<?php foreach ($alertas as $alerta): ?>
    <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 p-4 rounded-r-xl shadow-sm mb-6 flex items-start gap-3">
        <i class="ph-fill ph-warning-circle text-xl mt-0.5"></i>
        <div class="text-sm"><?= $alerta ?></div>
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
                    <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap"><?= $h['codigo'] ?></td>
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
                        <a href="detalhe_homologacao.php?id=<?= $h['id'] ?>" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-colors">
                            Abrir
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
