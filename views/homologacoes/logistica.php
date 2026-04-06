<?php require __DIR__ . '/_subnav.php'; 

if ($u['perfil'] !== 'logistica' && $u['perfil'] !== 'admin' && $u['perfil'] !== 'super_admin') {
    echo "<div class='bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 mb-6 shadow-sm dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300 flex items-center gap-3'><i class='ph-fill ph-warning-circle text-xl'></i> Acesso restrito. Mude o simulador no subnavbar para o perfil Logística.</div>";
    return;
}
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-1">Recebimento (Logística)</h2>
    <p class="text-slate-500 dark:text-slate-400 text-sm">Registre a chegada dos itens de homologações alertando automaticamente a TI responsável.</p>
</div>

<div class="mb-10">
    <h3 class="text-lg font-bold flex items-center gap-2 text-slate-800 dark:text-white mb-4">
        <i class="ph-fill ph-truck text-amber-500 text-2xl"></i> Caminhões a caminho / Aguardando Entrega Físisca
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($aguardando)): ?>
            <div class="col-span-full bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/50 border-dashed rounded-xl p-8 text-center text-slate-500 dark:text-slate-400">
                Nenhuma carga pendente sob seu farol no momento.
            </div>
        <?php endif; ?>
        
        <?php foreach ($aguardando as $h): ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 border-t-4 border-t-amber-500 flex flex-col hover:shadow-md transition-shadow">
            <div class="p-5 flex-1">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-2 flex-col items-start">
                        <span class="text-xs text-slate-500 dark:text-slate-400 italic mb-1"><?= getRotuloVersao(getVersaoHomologacao($h['id'])) ?></span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                            <i class="ph-fill ph-clock"></i> Pendente Doca
                        </span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 text-sm font-mono font-medium"><?= $h['codigo'] ?></span>
                </div>
                
                <h4 class="text-lg font-bold text-slate-800 dark:text-white mb-1 line-clamp-2"><?= $h['titulo'] ?></h4>
                <div class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                    <div class="flex flex-col gap-1 mt-3">
                        <span class="flex items-center gap-2"><i class="ph-fill ph-factory text-slate-400"></i> <strong class="text-slate-700 dark:text-slate-300">Fornecedor:</strong> <?= $h['fornecedor'] ?></span>
                        <span class="flex items-center gap-2"><i class="ph-fill ph-calendar text-slate-400"></i> <strong class="text-slate-700 dark:text-slate-300">Previsão:</strong> <?= $h['data_prevista_chegada'] ? date('d/m/Y', strtotime($h['data_prevista_chegada'])) : '-' ?></span>
                        <span class="flex items-center gap-2 mt-1">
                            <i class="ph-fill ph-stack text-slate-400"></i> 
                            <strong class="text-slate-700 dark:text-slate-300">Qtd:</strong> <?= $h['quantidade'] ?? 1 ?>
                            <span class="mx-1 text-slate-300">|</span>
                            <?php if (($h['tipo_aquisicao'] ?? 'comprado') === 'comprado'): ?>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1"><i class="ph-bold ph-money"></i> Comprado</span>
                            <?php else: ?>
                                <span class="text-amber-600 dark:text-amber-400 font-bold flex items-center gap-1"><i class="ph-bold ph-handshake"></i> Emprestado</span>
                            <?php endif; ?>
                        </span>
                        <?php if(!empty($h['numero_serie'])): ?>
                            <span class="flex items-center gap-2 mt-1 text-[11px] bg-slate-100 dark:bg-slate-700/50 px-2 py-1 rounded w-fit">
                                <i class="ph ph-barcode text-slate-500"></i> <strong class="text-slate-600 dark:text-slate-400">S/N:</strong> <?= $h['numero_serie'] ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/80 rounded-b-xl">
                <button type="button" onclick="openModal('receiveModal<?= $h['id'] ?>')" class="w-full flex justify-center items-center gap-2 text-amber-900 bg-amber-400 hover:bg-amber-500 focus:ring-4 focus:ring-amber-200 font-bold rounded-lg text-sm px-5 py-2.5 dark:focus:ring-amber-900 transition-colors shadow-sm">
                    <i class="ph-bold ph-package"></i> Registrar Chegada da Carga
                </button>
            </div>
        </div>

        <!-- Modal Confirmação -->
        <div id="receiveModal<?= $h['id'] ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('receiveModal<?= $h['id'] ?>')"></div>
            <div class="relative w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden transform scale-100">
                <div class="bg-amber-400 px-6 py-4 flex justify-between items-center">
                    <h5 class="text-lg font-bold text-amber-950 flex items-center gap-2"><i class="ph-fill ph-clipboard-text"></i> Termo de Recebimento Físico</h5>
                    <button type="button" onclick="closeModal('receiveModal<?= $h['id'] ?>')" class="text-amber-800 hover:text-amber-950 transition-colors"><i class="ph-bold ph-x text-xl"></i></button>
                </div>
                <form method="POST" action="" class="p-6" enctype="multipart/form-data">
                    <input type="hidden" name="confirmar_recebimento_id" value="<?= $h['id'] ?>">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Data do Recebimento</label>
                        <input type="date" name="data_recebimento" value="<?= date('Y-m-d') ?>" required class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Anotações da Carga (Doca)</label>
                        <textarea name="observacoes_logistica" rows="3" placeholder="Caixa amassada? Lacre rompido? Volume extra?" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white"></textarea>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Fotos da Carga (Max. 5 imagens PNG/JPEG)</label>
                        <input type="file" name="logistica_anexos[]" multiple accept=".png,.jpg,.jpeg" onchange="validarArquivosImagem(this, 'preview_logistica_<?= $h['id'] ?>', 5)"
                               class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 cursor-pointer border border-dashed border-slate-300 rounded-xl p-4">
                        <div id="preview_logistica_<?= $h['id'] ?>" class="mt-2 text-[10px] flex flex-wrap gap-2"></div>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal('receiveModal<?= $h['id'] ?>')" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-amber-950 bg-amber-400 rounded-lg hover:bg-amber-500 shadow-sm">Carimbar Entrada</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Grid de Itens -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
    <div class="p-5 border-b border-slate-200 dark:border-slate-700/50">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="ph-fill ph-list-dashes text-primary-500"></i> Histórico de Recebimentos
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
            <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700/50 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700/50">
                <tr>
                    <th class="px-5 py-3">Código</th>
                    <th class="px-5 py-3">Item</th>
                    <th class="px-5 py-3 text-center">Evidências</th>
                    <th class="px-5 py-3">Fornecedor</th>
                    <th class="px-5 py-3">Prev. Chegada</th>
                    <th class="px-5 py-3 text-center">Data Receb.</th>
                    <th class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/50">
                <?php
                $todosItens = array_merge(array_values($aguardando), array_values($recebidos));
                // Incluir também itens já em homologação ou concluídos que passaram pela logística
                foreach ($homologacoes as $hh) {
                    if (!in_array($hh['status'], ['aguardando_chegada', 'item_recebido']) && $hh['data_recebimento']) {
                        $todosItens[] = $hh;
                    }
                }
                ?>
                <?php if (empty($todosItens)): ?>
                    <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">Nenhum item registrado.</td></tr>
                <?php endif; ?>
                <?php foreach ($todosItens as $h): ?>
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200 whitespace-nowrap"><?= $h['codigo'] ?></td>
                    <td class="px-5 py-3">
                        <div class="font-medium text-slate-800 dark:text-slate-200"><?= $h['modelo'] ?></div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1"><?= $h['titulo'] ?></div>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <?php if (($h['logistica_anexos_count'] ?? 0) > 0 || !empty($h['foto_carga'])): ?>
                                <i class="ph-fill ph-camera text-amber-500 text-lg" title="Foto disponível"></i>
                            <?php endif; ?>
                            <?php if (!empty($h['observacoes_logistica'])): ?>
                                <i class="ph-fill ph-chat-centered-text text-blue-500 text-lg" title="<?= htmlspecialchars($h['observacoes_logistica']) ?>"></i>
                            <?php endif; ?>
                            <?php if (($h['logistica_anexos_count'] ?? 0) === 0 && empty($h['foto_carga']) && empty($h['observacoes_logistica']) && $h['status'] !== 'aguardando_chegada'): ?>
                                <span class="text-slate-300 dark:text-slate-600">-</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap"><?= $h['fornecedor'] ?></td>
                    <td class="px-5 py-3 whitespace-nowrap"><?= $h['data_prevista_chegada'] ? date('d/m/Y', strtotime($h['data_prevista_chegada'])) : '-' ?></td>
                    <td class="px-5 py-3 text-center">
                        <div class="text-slate-800 dark:text-slate-200 font-medium"><?= $h['data_recebimento'] ? date('d/m/Y', strtotime($h['data_recebimento'])) : '-' ?></div>
                        <div class="text-[10px] text-slate-500"><?= $h['recebido_por'] ? getUserById($h['recebido_por'])['nome'] : '' ?></div>
                    </td>
                    <td class="px-5 py-3">
                        <?php if ($h['status'] === 'aguardando_chegada'): ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Pendente</span>
                        <?php else: ?>
                            <a href="/homologacoes-2/<?= $h['id'] ?>" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 hover:bg-emerald-200 transition-colors">Recebido <i class="ph ph-arrow-square-out ml-1"></i></a>
                        <?php endif; ?>
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
function validarArquivosImagem(input, previewId, limite) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';

    if (input.files.length > limite) {
        alert(`Voce so pode selecionar no maximo ${limite} imagens.`);
        input.value = '';
        return;
    }

    for (const file of Array.from(input.files)) {
        if (!['image/png', 'image/jpeg'].includes(file.type)) {
            alert('Apenas imagens PNG ou JPEG sao permitidas.');
            input.value = '';
            preview.innerHTML = '';
            return;
        }

        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200';
        tag.innerHTML = `<i class="ph ph-image"></i> ${file.name.substring(0, 20)}${file.name.length > 20 ? '...' : ''}`;
        preview.appendChild(tag);
    }
}
</script>
