<?php require __DIR__ . '/_subnav.php'; 

if (empty($canCancelOrDelete)) {
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
                    <th scope="col" class="px-6 py-4">Versão</th>
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
                    <td class="px-6 py-4">
                        <div class="text-xs text-slate-500 dark:text-slate-400 mb-1"><?= getRotuloVersao(getVersaoHomologacao($h['id'])) ?></div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap"><?= $h['codigo'] ?></div>
                    </td>
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
                            <a href="/homologacoes-2/<?= $h['id'] ?>" class="text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-800/50 rounded-lg p-2 transition-colors" title="Ver Relatório">
                                <i class="ph-bold ph-eye text-lg"></i>
                            </a>
                            
                            <?php if (!empty($canCancelOrDelete)): ?>
                                <button type="button" onclick="window.openCancelModal(<?= $h['id'] ?>, '<?= $h['codigo'] ?>')" class="text-rose-600 bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-800/50 rounded-lg p-2 transition-colors group" title="Excluir/Cancelar Processo">
                                    <i class="ph-fill ph-trash text-lg group-hover:scale-110 transition-transform"></i>
                                </button>
                            <?php endif; ?>
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

<!-- Overlay de Notificação (Simulação) -->
<div id="notifOverlay" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md">
    <div class="text-center">
        <div class="relative w-24 h-24 mb-6 mx-auto">
            <div class="absolute inset-0 bg-rose-500/20 rounded-full animate-ping"></div>
            <div class="relative w-24 h-24 bg-rose-600 rounded-full flex items-center justify-center text-white shadow-2xl">
                <i class="ph-fill ph-paper-plane-tilt text-4xl"></i>
            </div>
        </div>
        <h2 class="text-2xl font-black text-white mb-2 tracking-tight uppercase">Enviando Alertas...</h2>
        <p class="text-rose-200 text-sm font-medium">Notificando todos os setores envolvidos sobre o cancelamento.</p>
    </div>
</div>

<script>
window.openCancelModal = function(id, code) {
    console.log('Abrindo modal para:', id, code);
    const modal = document.getElementById('cancelModal');
    if (modal) {
        document.getElementById('cancelId').value = id;
        document.getElementById('cancelCode').innerText = code;
        modal.classList.remove('hidden');
    } else {
        console.error('Modal de cancelamento não encontrado!');
    }
};

window.closeCancelModal = function() {
    document.getElementById('cancelModal').classList.add('hidden');
};

window.processCancellation = function() {
    closeCancelModal();
    const overlay = document.getElementById('notifOverlay');
    if (overlay) overlay.classList.remove('hidden');
    
    setTimeout(() => {
        document.getElementById('cancelForm').submit();
    }, 800);
};

document.querySelectorAll('button[title="Excluir/Cancelar Processo"]').forEach((button) => {
    if ((button.getAttribute('onclick') || '').includes('openCancelModal')) {
        button.removeAttribute('onclick');
    }

    button.addEventListener('click', (event) => {
        event.preventDefault();

        const row = button.closest('tr');
        const detailLink = row?.querySelector('a[href^="/homologacoes-2/"]');
        const idMatch = detailLink?.getAttribute('href')?.match(/\/homologacoes-2\/(\d+)/);
        const code = row?.querySelector('td .font-semibold')?.textContent?.trim() || '';

        if (idMatch) {
            window.openCancelModal(Number(idMatch[1]), code);
        }
    });
});
</script>
