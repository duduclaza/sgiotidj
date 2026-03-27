<?php require __DIR__ . '/_subnav.php'; ?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <h2 class="text-3xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <?= $h['codigo'] ?>
        </h2>
        <h5 class="text-slate-500 dark:text-slate-400 font-medium text-lg mt-1"><?= $h['titulo'] ?></h5>
    </div>
    <div class="flex flex-col items-end gap-2">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold shadow-sm <?= getBadgeClass($h['status']) ?>">
            <i class="ph-fill ph-flag"></i> Status: <?= getStatusLabel($h['status']) ?>
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Left Column: Specs and Timeline -->
    <div class="col-span-1 lg:col-span-4 space-y-6">
        
        <!-- Geral Info Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
            <div class="bg-slate-50 dark:bg-slate-800/80 p-4 border-b border-slate-200 dark:border-slate-700/50">
                <h6 class="text-slate-800 dark:text-white font-bold flex items-center gap-2">
                    <i class="ph-fill ph-info text-primary-500 text-lg"></i> Ficha Técnica
                </h6>
            </div>
            <div class="p-5">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        <tr class="flex justify-between py-2"><td class="text-slate-500 dark:text-slate-400 font-medium">Tipo</td><td class="font-bold text-slate-800 dark:text-white text-right"><?= $h['tipo_equipamento'] ?></td></tr>
                        <tr class="flex justify-between py-2"><td class="text-slate-500 dark:text-slate-400 font-medium">Fornecedor</td><td class="text-slate-800 dark:text-slate-200 text-right"><?= $h['fornecedor'] ?></td></tr>
                        <tr class="flex justify-between py-2"><td class="text-slate-500 dark:text-slate-400 font-medium">Modelo</td><td class="text-slate-800 dark:text-slate-200 text-right"><?= $h['modelo'] ?></td></tr>
                        <tr class="flex justify-between py-2"><td class="text-slate-500 dark:text-slate-400 font-medium">Quantidade</td><td class="text-slate-800 dark:text-white font-bold text-right"><?= $h['quantidade'] ?? 1 ?> un.</td></tr>
                        <tr class="flex justify-between py-2"><td class="text-slate-500 dark:text-slate-400 font-medium">Aquisição</td><td class="text-right">
                            <?php if (($h['tipo_aquisicao'] ?? 'comprado') === 'comprado'): ?>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">💰 Comprado</span>
                            <?php else: ?>
                                <span class="text-amber-600 dark:text-amber-400 font-bold">🤝 Emprestado</span>
                            <?php endif; ?>
                        </td></tr>
                        <tr class="flex justify-between py-2"><td class="text-slate-500 dark:text-slate-400 font-medium">N/S Lote</td><td class="text-slate-800 dark:text-slate-200 text-right"><?= $h['numero_serie'] ?: '<span class="italic text-slate-400">Não informado</span>' ?></td></tr>
                        <tr class="flex justify-between py-2 border-t border-slate-100 dark:border-slate-700 mt-1 pt-3"><td class="text-rose-500 dark:text-rose-400 font-bold uppercase text-[10px]">Vencimento Técnico</td><td class="text-rose-600 dark:text-rose-400 font-black text-right"><?= $h['data_vencimento'] ? date('d/m/Y', strtotime($h['data_vencimento'])) : '-' ?></td></tr>
                    </tbody>
                </table>
                <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-100 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-400">
                    <strong class="font-semibold block mb-1 text-slate-700 dark:text-slate-300">Resumo da Demanda:</strong>
                    <?= $h['descricao'] ?>
                </div>
                
                <h6 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mt-6 mb-3">Auditoria / SLAs</h6>
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        <tr class="flex justify-between py-2"><td class="text-slate-500 dark:text-slate-400">Criador</td><td class="text-slate-700 dark:text-slate-300 text-right"><?= getUserById($h['criado_por'])['nome'] ?></td></tr>
                        <tr class="flex justify-between py-2"><td class="text-slate-500 dark:text-slate-400 whitespace-nowrap">Logística (Chegada)</td><td class="text-slate-700 dark:text-slate-300 text-right"><?= $h['data_prevista_chegada'] ? date('d/m/Y', strtotime($h['data_prevista_chegada'])) : '-' ?></td></tr>
                        <tr class="flex justify-between py-2"><td class="text-rose-500 dark:text-rose-400 font-semibold italic">Deadline Final (SLA)</td><td class="text-rose-600 dark:text-rose-400 font-bold text-right"><?= $h['data_vencimento'] ? date('d/m/Y', strtotime($h['data_vencimento'])) : '-' ?></td></tr>
                        <tr class="flex flex-col gap-2 py-3 border-t border-slate-100 dark:border-slate-700/50 mt-2">
                            <td class="text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-widest">Setor Responsável</td>
                            <td class="flex items-center gap-2">
                                <?php 
                                    $setor = $h['setor_responsavel'] ?? 'tecnico';
                                    $icon = $setor === 'tecnico' ? 'ph-wrench' : ($setor === 'qualidade' ? 'ph-seal-check' : 'ph-briefcase');
                                    $color = $setor === 'tecnico' ? 'cyan' : ($setor === 'qualidade' ? 'emerald' : 'indigo');
                                ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-<?= $color ?>-100 text-<?= $color ?>-800 dark:bg-<?= $color ?>-900/30 dark:text-<?= $color ?>-400 text-xs font-bold border border-<?= $color ?>-200 dark:border-<?= $color ?>-800">
                                    <i class="ph-bold <?= $icon ?>"></i> <?= strtoupper($setor) ?>
                                </span>
                            </td>
                        </tr>
                        <?php if ($setor === 'comercial' && !empty($h['dados_comercial'])): ?>
                        <tr class="flex flex-col gap-1 py-2 bg-indigo-50/50 dark:bg-indigo-900/10 p-3 rounded-lg border border-indigo-100 dark:border-indigo-900/30 mt-2">
                            <td class="text-indigo-700 dark:text-indigo-400 text-[11px] font-bold">Vendedor: <span class="font-normal text-slate-700 dark:text-slate-300"><?= $h['dados_comercial']['vendedor_nome'] ?></span></td>
                            <td class="text-indigo-700 dark:text-indigo-400 text-[11px] font-bold">E-mail: <span class="font-normal text-slate-700 dark:text-slate-300"><?= $h['dados_comercial']['vendedor_email'] ?></span></td>
                            <td class="text-indigo-700 dark:text-indigo-400 text-[11px] font-bold">Supervisor: <span class="font-normal text-slate-700 dark:text-slate-300"><?= $h['dados_comercial']['supervisor_email'] ?></span></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
            <div class="bg-slate-50 dark:bg-slate-800/80 p-4 border-b border-slate-200 dark:border-slate-700/50">
                <h6 class="text-slate-800 dark:text-white font-bold flex items-center gap-2">
                    <i class="ph-fill ph-git-commit text-primary-500 text-lg"></i> Linha do Tempo
                </h6>
            </div>
            
            <div class="p-6">
                <!-- Passo 1 -->
                <div class="relative pl-6 pb-6 border-l-2 border-slate-200 dark:border-slate-600 last:border-0 last:pb-0">
                    <div class="absolute w-4 h-4 bg-primary-500 rounded-full border-4 border-white dark:border-slate-800 -left-[9px] top-1"></div>
                    <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Processo Licitado/Criado</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Aberta por <?= getUserById($h['criado_por'])['nome'] ?> em <?= date('d/m/Y', strtotime($h['data_criacao'])) ?></p>
                </div>
                
                <!-- Passo 2 -->
                <div class="relative pl-6 pb-6 border-l-2 border-slate-200 dark:border-slate-600 last:border-0 last:pb-0">
                    <?php if ($h['data_recebimento']): ?>
                        <div class="absolute w-4 h-4 bg-primary-500 rounded-full border-4 border-white dark:border-slate-800 -left-[9px] top-1"></div>
                        <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Doca Logística</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Confirmado por <?= getUserById($h['recebido_por'])['nome'] ?> em <?= date('d/m/Y', strtotime($h['data_recebimento'])) ?></p>
                    <?php else: ?>
                        <div class="absolute w-4 h-4 bg-white dark:bg-slate-700 rounded-full border-4 border-slate-200 dark:border-slate-500 -left-[9px] top-1"></div>
                        <h4 class="font-bold text-slate-400 dark:text-slate-500 text-sm mb-1">Aguardando Doca</h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 italic">Na espera da transportadora.</p>
                    <?php endif; ?>
                </div>

                <!-- Passo 3 -->
                <div class="relative pl-6 pb-6 border-l-2 border-slate-200 dark:border-slate-600 last:border-0 last:pb-0">
                    <?php if ($h['data_inicio_homologacao']): ?>
                        <div class="absolute w-4 h-4 bg-primary-500 rounded-full border-4 border-white dark:border-slate-800 -left-[9px] top-1"></div>
                        <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Testes em Curso da TI</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Iniciado em <?= date('d/m/Y', strtotime($h['data_inicio_homologacao'])) ?> (Local: <?= ucfirst($h['local_homologacao']) ?>)</p>
                    <?php else: ?>
                        <div class="absolute w-4 h-4 bg-amber-400 dark:bg-amber-500 rounded-full border-4 border-white dark:border-slate-800 -left-[9px] top-1 animate-pulse"></div>
                        <h4 class="font-bold text-amber-600 dark:text-amber-400 text-sm mb-1">Aguardando Avaliação dos Técnicos</h4>
                    <?php endif; ?>
                </div>
                
                <!-- Passo 4 -->
                <div class="relative pl-6 last:border-0 last:pb-0">
                    <?php if ($h['data_fim_homologacao']): ?>
                        <div class="absolute w-4 h-4 <?= $h['resultado'] === 'aprovado' ? 'bg-emerald-500' : 'bg-rose-500' ?> rounded-full border-4 border-white dark:border-slate-800 -left-[9px] top-1"></div>
                        <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-1">Emissão de Laudo Final</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Encerrado em <?= date('d/m/Y', strtotime($h['data_fim_homologacao'])) ?></p>
                        <span class="inline-flex text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded <?= $h['resultado'] === 'aprovado' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400' ?>">
                            <?= $h['resultado'] ?>
                        </span>
                    <?php else: ?>
                        <div class="absolute w-4 h-4 bg-white dark:bg-slate-700 rounded-full border-4 border-slate-200 dark:border-slate-500 -left-[9px] top-1"></div>
                        <h4 class="font-bold text-slate-400 dark:text-slate-500 text-sm">Fechamento do Certificado Pendente</h4>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive panels based on status -->
    <div class="col-span-1 lg:col-span-8 flex flex-col gap-6">
        
        <!-- ================= FASE 1: LOGÍSTICA ================= -->
        <?php if ($h['status'] === 'aguardando_chegada'): ?>
            <?php if ($u['perfil'] === 'logistica' || $u['perfil'] === 'admin'): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow border border-slate-200 dark:border-slate-700/50 border-t-4 border-t-amber-500 overflow-hidden">
                    <div class="p-6">
                        <h5 class="text-xl font-bold text-amber-600 dark:text-amber-500 mb-2 flex items-center gap-2">
                            <i class="ph-fill ph-check-circle"></i> Ação Requerida (Logística)
                        </h5>
                        <p class="text-slate-600 dark:text-slate-300 text-sm mb-6">Confirme que o equipamento chegou ao setor e notifique automaticamente os técnicos de TI do pavilhão.</p>
                        
                        <form method="POST" class="bg-amber-50 dark:bg-amber-900/10 p-5 rounded-xl border border-amber-100 dark:border-amber-800/50">
                            <input type="hidden" name="acao" value="confirmar_recebimento">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Data Real do Recebimento</label>
                                    <input type="date" name="data_recebimento" value="<?= date('Y-m-d') ?>" required class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Observações da Carga Física</label>
                                    <input type="text" name="observacoes_entrega" placeholder="Caixa rasgada, via sedex..." class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-500 dark:text-white">
                                </div>
                            </div>
                            <div class="flex">
                                <button type="submit" class="bg-amber-500 hover:bg-amber-600 focus:ring-4 focus:ring-amber-300 text-amber-950 font-bold rounded-lg text-sm px-6 py-3 transition-colors shadow-sm flex items-center gap-2">
                                    <i class="ph-bold ph-package"></i> Registrar Chegada da Peça
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 border-dashed rounded-xl p-10 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-full flex items-center justify-center mb-4">
                        <i class="ph-fill ph-clock text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Aguardando Desembarque Logístico</h4>
                    <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md">O processo físico está aguardando o time de Logística dar a baixa na peça. O Checklist técnico será exposto somente quando o Status da doca mudar. (Mude para "Fernanda" para simular).</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ================= FASE 2: TI INICIA TESTES ================= -->
        <?php if ($h['status'] === 'item_recebido'): ?>
            <?php if (($u['perfil'] === 'responsavel' && in_array($u['id'], $h['responsaveis'])) || $u['perfil'] === 'admin'): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow border border-slate-200 dark:border-slate-700/50 border-t-4 border-t-cyan-500 overflow-hidden">
                    <div class="p-6">
                        <h5 class="text-xl font-bold text-cyan-600 dark:text-cyan-500 mb-2 flex items-center gap-2">
                            <i class="ph-fill ph-play-circle"></i> Iniciar Esteira de Testes
                        </h5>
                        
                        <form method="POST" class="bg-cyan-50 dark:bg-cyan-900/10 p-5 rounded-xl border border-cyan-100 dark:border-cyan-800/50">
                            <input type="hidden" name="acao" value="iniciar_homologacao">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Data do Start da Homologação</label>
                                    <input type="date" name="data_inicio_homologacao" value="<?= date('Y-m-d') ?>" required class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Local</label>
                                    <select name="local_homologacao" required onchange="document.getElementById('div_cliente').style.display = this.value === 'cliente' ? 'flex' : 'none'" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                                        <option value="">Selecione...</option>
                                        <option value="laboratorio">Laboratório</option>
                                        <option value="cliente">Cliente</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5" id="div_cliente" style="display: none;">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Cliente</label>
                                    <input type="text" name="nome_cliente" placeholder="Qual cliente?" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-500 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Data da instalação no cliente</label>
                                    <input type="date" name="data_instalacao_cliente" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                                </div>
                            </div>

                            <div class="flex">
                                <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-300 text-white font-bold rounded-lg text-sm px-6 py-3 transition-colors shadow-sm flex items-center gap-2">
                                    <i class="ph-bold ph-play"></i> Preencher Check List de Homologação
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 border-dashed rounded-xl p-10 text-center flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 rounded-full flex items-center justify-center mb-4">
                        <i class="ph-fill ph-users text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Caixas aguardando Técnicos</h4>
                    <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md">O item já está nas mãos do prédio. O corpo de Engenharia foi notificado para coletar da doca e destrancar a homologação (Perfil Ti).</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ================= FASE 3 E 4: CHECKLIST E FORMULÁRIOS VIVOS ================= -->
        <?php if ($h['status'] === 'em_homologacao' || $h['status'] === 'concluida'): ?>
            <?php 
                $canEdit = ($h['status'] === 'em_homologacao' && ($u['perfil'] === 'admin' || str_contains($u['perfil'], 'respon'))); 
            ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow border border-slate-200 dark:border-slate-700/50 border-t-4 border-t-slate-800 dark:border-t-slate-200 overflow-hidden">
                <div class="bg-slate-50 dark:bg-slate-800/80 p-5 border-b border-slate-200 dark:border-slate-700/50 flex items-center justify-between">
                    <h5 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="ph-fill ph-list-checks text-primary-500 text-xl flex shrink-0"></i> 
                        Checklist Certificador (<?= $h['tipo_equipamento'] ?>)
                    </h5>
                    <?php if (!$canEdit): ?>
                        <span class="bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300 text-xs px-2 py-1 rounded font-bold uppercase tracking-widest"><i class="ph-bold ph-lock-key"></i> Readonly</span>
                    <?php endif; ?>
                </div>
                
                <div class="p-6">
                    <form method="POST" id="mainChecklistForm">
                        <?php if ($canEdit): ?>
                            <input type="hidden" name="acao" value="salvar_checklist">
                        <?php endif; ?>
                        
                        <div class="space-y-4 mb-8">
                            <?php foreach ($checklistItems as $key => $label): ?>
                                <?php 
                                    $val = $respostas[$key] ?? null;
                                    $isOk = $val === true;
                                    $isFail = $val === false;
                                ?>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border <?= $isFail ? 'bg-rose-50/50 border-rose-200 dark:bg-rose-900/10 dark:border-rose-900/50' : ($isOk ? 'bg-emerald-50/50 border-emerald-200 dark:bg-emerald-900/10 dark:border-emerald-900/50' : 'bg-slate-50 border-slate-200 dark:bg-slate-800 dark:border-slate-700') ?>">
                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-200 <?= $isOk ? 'opacity-80' : '' ?>"><?= $label ?></span>
                                    
                                    <?php if ($canEdit): ?>
                                    <div class="flex rounded-md shadow-sm" role="group">
                                        <input type="radio" class="hidden peer/none" name="checklist[<?= $key ?>]" id="<?= $key ?>_none" value="pendente" <?= ($val === null || $val === 'pendente' || $val === '') ? 'checked' : '' ?>>
                                        <label for="<?= $key ?>_none" class="px-3 py-1.5 text-xs font-medium bg-white text-amber-600 border border-slate-200 rounded-s-lg cursor-pointer hover:bg-amber-50 hover:text-amber-800 peer-checked/none:bg-amber-500 peer-checked/none:text-white peer-checked/none:border-amber-600 dark:bg-slate-900 dark:border-slate-700 dark:text-amber-500 dark:hover:text-white dark:hover:bg-slate-800 dark:peer-checked/none:bg-amber-600 dark:peer-checked/none:text-white dark:peer-checked/none:border-amber-600 transition-colors">
                                            <i class="ph-bold ph-clock"></i> PEND
                                        </label>

                                        <!-- Passed -->
                                        <input type="radio" class="hidden peer/ok" name="checklist[<?= $key ?>]" id="<?= $key ?>_ok" value="1" <?= $isOk ? 'checked' : '' ?>>
                                        <label for="<?= $key ?>_ok" class="px-3 py-1.5 text-xs font-medium bg-white text-emerald-600 border-t border-b border-emerald-200 cursor-pointer hover:bg-emerald-50 hover:text-emerald-800 peer-checked/ok:bg-emerald-600 peer-checked/ok:text-white peer-checked/ok:border-emerald-600 dark:bg-slate-900 dark:border-slate-700 dark:text-emerald-500 dark:hover:text-emerald-400 dark:hover:bg-slate-800 dark:peer-checked/ok:bg-emerald-600 dark:peer-checked/ok:text-white transition-colors">
                                            <i class="ph-bold ph-check"></i> PASS
                                        </label>

                                        <!-- Failed -->
                                        <input type="radio" class="hidden peer/fail" name="checklist[<?= $key ?>]" id="<?= $key ?>_fail" value="0" <?= $isFail ? 'checked' : '' ?>>
                                        <label for="<?= $key ?>_fail" class="px-3 py-1.5 text-xs font-medium bg-white text-rose-600 border border-rose-200 rounded-e-lg cursor-pointer hover:bg-rose-50 hover:text-rose-800 peer-checked/fail:bg-rose-600 peer-checked/fail:text-white peer-checked/fail:border-rose-600 dark:bg-slate-900 dark:border-slate-700 dark:text-rose-500 dark:hover:text-rose-400 dark:hover:bg-slate-800 dark:peer-checked/fail:bg-rose-600 dark:peer-checked/fail:text-white transition-colors">
                                            <i class="ph-bold ph-x"></i> FAIL
                                        </label>
                                    </div>
                                    <?php else: ?>
                                    <div class="shrink-0">
                                        <?php if ($val === null || $val === 'pendente' || $val === ''): ?>
                                            <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-1 rounded dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50"><i class="ph-bold ph-clock mr-1"></i> Pendente</span>
                                        <?php elseif ($isOk): ?>
                                            <span class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2.5 py-1 rounded dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50"><i class="ph-bold ph-check mr-1"></i> Checked</span>
                                        <?php else: ?>
                                            <span class="bg-rose-100 text-rose-800 text-xs font-medium px-2.5 py-1 rounded dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800/50"><i class="ph-bold ph-x mr-1"></i> Failed Issue</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 border-b border-slate-200 dark:border-slate-700 pb-2 flex items-center gap-2"><i class="ph-bold ph-pencil-simple"></i> Histórico de Observações Técnicas Parciais</label>
                            
                            <?php if ($h['observacoes_checklist']): ?>
                                <div class="bg-indigo-50/50 dark:bg-slate-900/50 rounded-lg p-4 text-sm text-slate-700 dark:text-slate-300 font-mono border border-slate-200 dark:border-slate-700 mb-3 max-h-60 overflow-y-auto whitespace-pre-wrap"><?= htmlspecialchars($h['observacoes_checklist']) ?></div>
                            <?php endif; ?>

                            <?php if ($canEdit): ?>
                                <textarea name="nova_observacao" rows="2" placeholder="Adicionar nova observação no diário de testes..." class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-slate-500 dark:text-white font-mono shadow-sm"></textarea>
                            <?php elseif (!$h['observacoes_checklist']): ?>
                                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 text-sm text-slate-600 dark:text-slate-400 italic font-mono border border-slate-200 dark:border-slate-700">
                                    Nenhuma ressalva preenchida pela equipe de TI.
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($canEdit): ?>
                            <div class="bg-primary-50 border border-primary-200 dark:bg-primary-900/10 dark:border-primary-900/50 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm mt-8">
                                <span class="text-primary-800 dark:text-primary-400 flex items-center gap-2 font-medium w-full lg:w-auto">
                                    <i class="ph-fill ph-check-circle text-lg"></i> Finalize a avaliação com base no preenchimento do checklist
                                </span>
                                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                                    <button type="button" onclick="copiarLinkPublico('<?= md5($h['id'] . 'token_seguro') ?>')" class="justify-center px-4 py-2 border border-primary-600 text-primary-600 hover:bg-primary-600 hover:text-white rounded-lg font-bold transition-colors shadow-sm focus:ring-4 focus:ring-primary-300 flex items-center gap-2">
                                        <i class="ph-bold ph-link text-lg"></i> Link Público
                                    </button>
                                    <button type="button" onclick="calcularResultadoEAbrirModal()" class="justify-center px-6 py-2 bg-primary-600 text-white hover:bg-primary-700 rounded-lg font-bold transition-colors shadow-sm focus:ring-4 focus:ring-primary-300">Concluir Veredito</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <?php if ($h['status'] === 'concluida' || ($h['status'] === 'em_homologacao' && !empty($h['parecer_final']))): ?>
            <!-- Parecer Final / Laudo Técnico (Somente Leitura) -->
            <?php 
                $resultado = $h['resultado'] ?? '';
                $successBorder = $resultado === 'aprovado' ? 'border-l-emerald-500' : ($resultado === 'reprovado' ? 'border-l-rose-500' : 'border-l-amber-500'); 
            ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow border border-slate-200 dark:border-slate-700/50 border-l-4 <?= $successBorder ?> mb-6">
                <div class="p-6">
                    <h5 class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                        <i class="ph-fill ph-certificate text-2xl text-slate-400"></i> Parecer do Departamento <?= $h['status'] === 'em_homologacao' ? '(Rascunho)' : 'Definitivo' ?>
                    </h5>
                    
                    <div class="flex flex-col md:flex-row gap-8">
                        <div class="md:w-1/3">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Resultado Atual</span>
                            <span class="inline-flex items-center px-3 py-1 rounded text-sm font-bold uppercase <?= $resultado === 'aprovado' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : ($resultado === 'reprovado' ? 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400') ?>">
                                <?= $resultado ?: 'Pendente' ?>
                            </span>
                        </div>
                        <div class="md:w-2/3">
                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Relato Conclusivo / Laudo Final</span>
                            <div class="bg-slate-50 dark:bg-slate-900/50 border-l-2 border-slate-300 dark:border-slate-600 p-4 rounded-r-lg text-slate-800 dark:text-slate-200 font-medium whitespace-pre-wrap">
                                <?= $h['parecer_final'] ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<!-- Modal Conclusão TI SGQ Tail-->
<?php if ($h['status'] === 'em_homologacao' && $canEdit): ?>
<div id="modalFinalizar" class="modal-backend hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 text-left">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-xl mx-auto overflow-hidden animate-modal">
        <div class="bg-primary-600 p-5 flex justify-between items-center text-white">
            <h5 class="text-lg font-bold flex items-center gap-2"><i class="ph-fill ph-seal-check"></i> Emitir Parecer Definitivo</h5>
            <button type="button" onclick="closeModal('modalFinalizar')" class="text-primary-100 hover:text-white transition-colors"><i class="ph-bold ph-x text-xl"></i></button>
        </div>
        
        <form method="POST" id="formFinalizar" class="p-6" onsubmit="prepararFormularioFinal()">
            <input type="hidden" name="acao" value="finalizar_homologacao">
            <div id="hiddenChecklistData" class="hidden"></div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5 mt-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Decisão Oficial (Pass/Fail)</label>
                    <select name="resultado" required class="bg-slate-50 border border-primary-300 text-slate-900 font-bold text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                        <option value="">Julgamento...</option>
                        <option value="aprovado" <?= ($h['resultado']??'') === 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                        <option value="aprovado com restrições" <?= ($h['resultado']??'') === 'aprovado com restrições' ? 'selected' : '' ?>>Aprovado com restrições</option>
                        <option value="reprovado" <?= ($h['resultado']??'') === 'reprovado' ? 'selected' : '' ?>>Reprovado</option>
                        <option value="pendente" <?= ($h['resultado']??'') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Data do Encerramento</label>
                    <input type="date" name="data_fim_homologacao" value="<?= date('Y-m-d') ?>" required class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-slate-900 dark:border-slate-600 dark:text-white">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Histórico do Laudo Técnico (Texto Justificativo)</label>
                
                <?php if (!empty($h['parecer_final'])): ?>
                    <div class="bg-indigo-50/50 dark:bg-slate-900/50 border-l-2 border-indigo-300 dark:border-indigo-600 p-4 rounded-r-lg text-slate-800 dark:text-slate-200 text-sm mb-3 max-h-40 overflow-y-auto whitespace-pre-wrap font-mono"><?= htmlspecialchars($h['parecer_final']) ?></div>
                <?php endif; ?>

                <textarea name="novo_parecer_final" rows="3" <?= empty($h['parecer_final']) ? 'required' : '' ?> placeholder="<?= empty($h['parecer_final']) ? 'Disserte motivos práticos indicando a viabilidade...' : 'Adicionar novo comentário ao laudo...' ?>" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-3 dark:bg-slate-900 dark:border-slate-600 dark:text-white"></textarea>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeModal('modalFinalizar')" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 transition-colors">Abortar</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-lg focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 shadow-sm transition-colors flex items-center gap-2">
                    <i class="ph-bold ph-paper-plane-right"></i> Selar Homologação
                </button>
            </div>
        </form>
    </div>
<script>
    function calcularResultadoEAbrirModal() {
        const formChecklistInputs = document.querySelectorAll('input[name^="checklist"]:checked');
        let hasPass = false;
        let hasFail = false;
        let hasPendente = false;
        
        formChecklistInputs.forEach(inp => {
            if (inp.value === "1") hasPass = true;
            else if (inp.value === "0") hasFail = true;
            else if (inp.value === "pendente" || inp.value === "") hasPendente = true;
        });
        
        let resultado = '';
        if (hasPendente) {
            resultado = 'pendente';
        } else if (hasPass && !hasFail) {
            resultado = 'aprovado';
        } else if (!hasPass && hasFail) {
            resultado = 'reprovado';
        } else if (hasPass && hasFail) {
            resultado = 'aprovado com restrições';
        }
        
        const selectElement = document.querySelector('select[name="resultado"]');
        if (selectElement) {
            selectElement.value = resultado;
            
            // Block other options if there are pending items
            Array.from(selectElement.options).forEach(opt => {
                if (hasPendente && opt.value !== 'pendente') {
                    opt.disabled = true;
                } else {
                    opt.disabled = false;
                }
            });
        }
        
        openModal('modalFinalizar');
    }

    function prepararFormularioFinal() {
        // Clonar inputs originais pro hidden
        const formChecklistInputs = document.querySelectorAll('input[name^="checklist"]');
        const hiddenDiv = document.getElementById('hiddenChecklistData');
        hiddenDiv.innerHTML = '';
        formChecklistInputs.forEach(inp => {
            if (inp.checked) {
                const hiddenInp = document.createElement('input');
                hiddenInp.type = 'hidden';
                hiddenInp.name = inp.name;
                hiddenInp.value = inp.value;
                hiddenDiv.appendChild(hiddenInp);
            }
        });
        
        // Copiar obs se houver
        const obs = document.querySelector('textarea[name="nova_observacao"]');
        if(obs && obs.value.trim() !== '') {
            const hiddenObs = document.createElement('input');
            hiddenObs.type = 'hidden';
            hiddenObs.name = 'nova_observacao';
            hiddenObs.value = obs.value;
            hiddenDiv.appendChild(hiddenObs);
        }
        return true;
    }

    function copiarLinkPublico(token) {
        const url = window.location.origin + window.location.pathname.replace('detalhe_homologacao.php', 'checklist_publico.php') + '?token=' + token;
        navigator.clipboard.writeText(url).then(() => {
            alert("Link público copiado para a área de transferência!\nEnvie este link para quem fará os testes em campo.");
        }).catch(err => {
            alert("Erro ao copiar o link: " + url);
        });
    }
</script>
<?php endif; ?>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
