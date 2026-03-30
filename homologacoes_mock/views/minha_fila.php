<?php require __DIR__ . '/_subnav.php'; 

if ($u['perfil'] !== 'responsavel' && $u['perfil'] !== 'qualidade' && $u['perfil'] !== 'tecnico' && $u['perfil'] !== 'admin' && $u['perfil'] !== 'super_admin') {
    echo "<div class='bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 mb-6 shadow-sm dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300 flex items-center gap-3'><i class='ph-fill ph-warning-circle text-xl'></i> Acesso restrito. Sua conta não está na pool técnica. Use o switch de simulador de usuário se desejar.</div>";
    return;
}
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">Minhas Homologações</h2>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Gerencie as homologações atribuídas a você. Clique em "Entrar" para preencher o checklist e atualizar o status.</p>
</div>

<!-- Grid Minhas Homologações -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden mb-10">
    <div class="p-5 border-b border-slate-200 dark:border-slate-700/50 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="ph-fill ph-list-checks text-primary-500"></i> Fila Ativa
        </h3>
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-full"><?= count($minha_fila) ?> item(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700/50">
                <tr>
                    <th class="px-5 py-3">Versão</th>
                    <th class="px-5 py-3">Código</th>
                    <th class="px-5 py-3">Equipamento</th>
                    <th class="px-5 py-3 text-center">Qtd / Tipo</th>
                    <th class="px-5 py-3">Fornecedor</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Progresso</th>
                    <th class="px-5 py-3">Parecer Final</th>
                    <th class="px-5 py-3 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                <?php if (empty($minha_fila)): ?>
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">
                        <i class="ph-fill ph-check-square-offset text-3xl mb-2 block opacity-50"></i>
                        Nenhuma homologação pendente na sua fila.
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($minha_fila as $h): ?>
                <?php
                    $total_items = count($data['checklists'][$h['tipo_equipamento']] ?? []);
                    $respondidos = count(array_filter($h['checklist_respostas'] ?? [], fn($r) => $r !== null && $r !== 'pendente' && $r !== ''));
                    $perc = $total_items > 0 ? round(($respondidos / $total_items) * 100) : 0;
                ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-5 py-3 font-bold text-center">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                            <?= getRotuloVersao(getVersaoHomologacao($h['id'])) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap"><?= $h['codigo'] ?></td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 shrink-0">
                                <i class="ph <?= getIconForTipo($h['tipo_equipamento']) ?> text-lg"></i>
                            </div>
                            <div>
                                <div class="font-medium text-slate-800 dark:text-slate-200"><?= $h['modelo'] ?></div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1"><?= $h['titulo'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap text-center">
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-200"><?= $h['quantidade'] ?? 1 ?> un.</div>
                        <div class="text-[10px] <?= ($h['tipo_aquisicao'] ?? 'comprado') === 'comprado' ? 'text-emerald-600' : 'text-amber-600' ?> font-semibold">
                            <?= ($h['tipo_aquisicao'] ?? 'comprado') === 'comprado' ? 'Comprado' : 'Emprestado' ?>
                        </div>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap"><?= $h['fornecedor'] ?></td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= getBadgeClass($h['status']) ?>">
                            <?= getStatusLabel($h['status']) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <?php if ($h['status'] === 'em_homologacao'): ?>
                            <div class="flex items-center gap-2 min-w-[120px]">
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                    <div class="bg-primary-500 h-2 transition-all duration-500" style="width: <?= $perc ?>%"></div>
                                </div>
                                <span class="text-xs font-bold text-primary-600 dark:text-primary-400 whitespace-nowrap"><?= $perc ?>%</span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3">
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
                    <td class="px-5 py-3 text-right">
                        <a href="detalhe_homologacao.php?id=<?= $h['id'] ?>" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white <?= $h['status'] === 'em_homologacao' ? 'bg-primary-600 hover:bg-primary-700' : 'bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600' ?> rounded-lg transition-colors shadow-sm">
                            <i class="ph-bold ph-sign-in"></i> Entrar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Histórico -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
    <div class="p-5 border-b border-slate-200 dark:border-slate-700/50">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="ph-fill ph-clock-counter-clockwise text-slate-400"></i> Histórico
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700/50">
                <tr>
                    <th class="px-5 py-3">Código</th>
                    <th class="px-5 py-3">Equipamento</th>
                    <th class="px-5 py-3">Data Conclusão</th>
                    <th class="px-5 py-3 text-right">Resultado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                <?php if (empty($historico)): ?>
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">Nenhuma homologação concluída ainda.</td></tr>
                <?php endif; ?>
                <?php foreach ($historico as $hist): ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= $hist['codigo'] ?></td>
                    <td class="px-5 py-3"><?= $hist['titulo'] ?></td>
                    <td class="px-5 py-3 text-slate-500"><?= isset($hist['data_fim_homologacao']) ? date('d/m/Y', strtotime($hist['data_fim_homologacao'])) : '-' ?></td>
                    <td class="px-5 py-3 text-right">
                        <?php if ($hist['status'] === 'cancelada'): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                <i class="ph-bold ph-prohibit"></i> Cancelada
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= $hist['resultado'] === 'aprovado' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400' ?>">
                                <i class="ph-bold <?= $hist['resultado'] === 'aprovado' ? 'ph-check' : 'ph-x' ?>"></i> <?= ucfirst($hist['resultado'] ?? '') ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
