<?php require __DIR__ . '/_subnav.php'; 

if ($u['perfil'] !== 'compras' && $u['perfil'] !== 'admin' && $u['perfil'] !== 'super_admin') {
    echo "<div class='bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 mb-6 shadow-sm dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300 flex items-center gap-3'><i class='ph-fill ph-warning-circle text-xl'></i> Acesso restrito ao Setor de Compras. Somente usuários de compras visualizam este painel de monitoramento. Selecione o perfil correspondente na barra superior.</div>";
    return;
}
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">Painel de Monitoramento</h2>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Visão gerencial e SLA para a área de Compras.</p>
</div>

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
    <div class="p-5 border-b border-slate-200 dark:border-slate-700/50 flex flex-col md:flex-row items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-800/80">
        <h6 class="mb-0 text-slate-800 dark:text-white font-bold flex items-center gap-2">
            <i class="ph-fill ph-list-magnifying-glass text-primary-500"></i> Acompanhamento Logístico e Técnico
        </h6>
        <button class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 transition-colors shadow-sm" onclick="alert('Exportação CSV simulada!')">
            <i class="ph-fill ph-file-csv"></i> Exportar
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700/50">
                <tr>
                    <th scope="col" class="px-6 py-4">Código</th>
                    <th scope="col" class="px-6 py-4">Equipamento</th>
                    <th scope="col" class="px-6 py-4">Qtd / Tipo</th>
                    <th scope="col" class="px-6 py-4">Status Atual</th>
                    <th scope="col" class="px-6 py-4">Fase da Esteira</th>
                    <th scope="col" class="px-6 py-4">Vencimento / SLA</th>
                    <th scope="col" class="px-6 py-4">Parecer Final</th>
                    <th scope="col" class="px-6 py-4 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                <?php foreach ($homologacoes as $h): ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap"><?= $h['codigo'] ?></td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <i class="ph <?= getIconForTipo($h['tipo_equipamento']) ?> text-slate-400"></i> <?= $h['tipo_equipamento'] ?>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5"><?= $h['fornecedor'] ?> | <?= $h['modelo'] ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-200"><?= $h['quantidade'] ?? 1 ?> un.</div>
                        <div class="text-[10px] <?= ($h['tipo_aquisicao'] ?? 'comprado') === 'comprado' ? 'text-emerald-500 font-bold' : 'text-amber-500 font-bold' ?>">
                            <?= ($h['tipo_aquisicao'] ?? 'comprado') === 'comprado' ? '<i class="ph-bold ph-money"></i> Comprado' : '<i class="ph-bold ph-handshake"></i> Emprestado' ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= getBadgeClass($h['status']) ?>">
                            <?= getStatusLabel($h['status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($h['status'] === 'aguardando_chegada'): ?>
                            <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1.5 font-medium"><i class="ph-fill ph-truck"></i> Físico pendente na Logística</span>
                        <?php elseif ($h['status'] === 'item_recebido'): ?>
                            <span class="text-cyan-600 dark:text-cyan-400 flex items-center gap-1.5 font-medium"><i class="ph-fill ph-package"></i> Pendente retirada da TI</span>
                        <?php elseif ($h['status'] === 'em_homologacao'): ?>
                            <span class="text-primary-600 dark:text-primary-400 flex items-center gap-1.5 font-medium"><i class="ph-fill ph-flask"></i> TI Computando Testes</span>
                        <?php else: ?>
                            <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 font-medium"><i class="ph-fill ph-check-circle"></i> Homologação Concluída</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php 
                            if ($h['status'] === 'aguardando_chegada' && $h['data_prevista_chegada']) {
                                $dias = calcularDiasRestantes($h['data_prevista_chegada']);
                                if ($dias < 0) echo "<span class='text-rose-600 dark:text-rose-400 font-bold flex items-center gap-1'><i class='ph-fill ph-warning-circle'></i> Atrasado " . abs($dias) . " dias</span>";
                                elseif ($dias <= $h['dias_antecedencia_notif']) echo "<span class='text-amber-600 dark:text-amber-400 font-bold'>Chega logo em $dias dia(s)</span>";
                                else echo "<span class='text-slate-500 dark:text-slate-400'>$dias dias para o prazo limit</span>";
                            } elseif ($h['status'] === 'em_homologacao' && $h['data_inicio_homologacao']) {
                                $dias_homol = calcularDiasRestantes($h['data_inicio_homologacao']);
                                echo "<span class='text-primary-600 dark:text-primary-400 font-medium'>Em testes por " . abs($dias_homol) . " dias</span>";
                            } else {
                                echo "<span class='text-slate-400'>-</span>";
                            }
                        ?>
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
                            <a href="detalhe_homologacao.php?id=<?= $h['id'] ?>" class="text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-800/50 rounded-lg p-2 transition-colors" title="Ver Relatório">
                                <i class="ph-bold ph-eye text-lg"></i>
                            </a>
                            
                            <?php if ($h['status'] !== 'concluida' && $h['status'] !== 'cancelada'): ?>
                                <button type="button" onclick="openModal('cancelModal<?= $h['id'] ?>')" class="text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-800/50 rounded-lg p-2 transition-colors" title="Cancelar Procresso">
                                    <i class="ph-bold ph-x text-lg"></i>
                                </button>
    
                                <!-- Modal Cancelamento Oculto pelo Tailwind customizado do SGQ (temos a func openModal do sgq) -->
                                <div id="cancelModal<?= $h['id'] ?>" class="modal-backend hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 text-left">
                                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 w-full max-w-md overflow-hidden animate-modal">
                                        <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-rose-50 dark:bg-rose-900/20">
                                            <h5 class="text-lg font-bold text-rose-800 dark:text-rose-400 flex items-center gap-2">
                                                <i class="ph-fill ph-warning-octagon"></i> Cancelar Processo
                                            </h5>
                                            <button type="button" onclick="closeModal('cancelModal<?= $h['id'] ?>')" class="text-rose-500 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-200 transition-colors"><i class="ph flex ph-x text-xl"></i></button>
                                        </div>
                                        <form method="POST" action="" class="p-6">
                                            <input type="hidden" name="cancelar_id" value="<?= $h['id'] ?>">
                                            <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">Tem certeza que deseja inutilizar permanentemente a homologação <strong><?= $h['codigo'] ?></strong>? O envio das amostras será pausado.</p>
                                            
                                            <div class="mb-5">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Motivo do Cancelamento</label>
                                                <textarea name="motivo_cancelamento" rows="2" required placeholder="A compra foi suspensa?" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-rose-500 focus:border-rose-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white"></textarea>
                                            </div>
                                            
                                            <div class="flex justify-end gap-3">
                                                <button type="button" onclick="closeModal('cancelModal<?= $h['id'] ?>')" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:ring-4 focus:outline-none focus:ring-slate-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:text-white dark:hover:bg-slate-700">Manter Ativa</button>
                                                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:ring-4 focus:outline-none focus:ring-rose-300 dark:bg-rose-600 dark:hover:bg-rose-700 shadow-sm">Confirmar Baixa</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
