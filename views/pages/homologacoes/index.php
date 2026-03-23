<?php // Renderizada via views/layouts/main.php; não incluir header/footer aqui ?>

<style>
/* Layout geral: faixa horizontal com scroll */
.kanban-wrap { overflow-x: auto; }
.kanban-row { display: flex; gap: 16px; padding-bottom: 8px; min-width: max-content; }

/* Scrollbar superior sincronizada */
.kanban-scroll-top { height: 14px; overflow-x: auto; overflow-y: hidden; margin-bottom: 8px; }
.kanban-scroll-top::-webkit-scrollbar { height: 12px; }
.kanban-scroll-top::-webkit-scrollbar-thumb { background: rgba(100,116,139,0.5); border-radius: 6px; }
.kanban-scroll-top::-webkit-scrollbar-track { background: rgba(148,163,184,0.2); }

/* Colunas com contraste mais forte */
.kanban-column {
    min-height: 520px;
    background: linear-gradient(180deg, #f1f5f9 0%, #f8fafc 100%);
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08), inset 0 0 0 1px rgba(15,23,42,0.05);
    width: 360px;
}

.kanban-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 14px 14px 40px 14px;
    margin-bottom: 12px;
    border-left: 4px solid #e2e8f0;
    box-shadow: 0 4px 8px rgba(15, 23, 42, 0.08);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.kanban-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
}

.kanban-card.dragging {
    opacity: 0.5;
    cursor: grabbing;
    transform: rotate(2deg);
}

.kanban-column.drag-over {
    background: linear-gradient(180deg, #e0e7ff 0%, #e0f2fe 100%);
    border: 2px dashed #3b82f6;
}

/* Botões de navegação entre etapas */
.card-nav-buttons {
    display: flex;
    gap: 4px;
    position: absolute;
    bottom: 8px;
    left: 8px;
    z-index: 10;
}

.card-nav-btn {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(100, 116, 139, 0.3);
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-nav-btn:hover {
    background: rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
    transform: scale(1.1);
}

.card-nav-btn:active {
    transform: scale(0.95);
}

.card-nav-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
    pointer-events: none;
}

/* Botões coloridos de log detalhado */
.card-nav-btn.bg-blue-500 {
    background: #3b82f6 !important;
    color: white !important;
    border-color: #2563eb !important;
}

.card-nav-btn.bg-blue-500:hover {
    background: #2563eb !important;
    transform: scale(1.1);
}

.card-nav-btn.bg-purple-500 {
    background: #8b5cf6 !important;
    color: white !important;
    border-color: #7c3aed !important;
}

.card-nav-btn.bg-purple-500:hover {
    background: #7c3aed !important;
    transform: scale(1.1);
}

.card-nav-btn.bg-green-500 {
    background: #10b981 !important;
    color: white !important;
    border-color: #059669 !important;
}

.card-nav-btn.bg-green-500:hover {
    background: #059669 !important;
    transform: scale(1.1);
}

/* Cores por status */
.status-aguardando_recebimento { border-left-color: #ca8a04; background: #fef9c3; }
.status-recebido { border-left-color: #1d4ed8; background: #dbeafe; }
.status-em_analise { border-left-color: #c2410c; background: #ffedd5; }
.status-em_homologacao { border-left-color: #7c3aed; background: #ede9fe; }
.status-aprovado { border-left-color: #16a34a; background: #dcfce7; }
.status-reprovado { border-left-color: #dc2626; background: #fee2e2; }

.badge-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.badge-aguardando_recebimento { background: #eab308; }
.badge-recebido { background: #3b82f6; }
.badge-em_analise { background: #f97316; }
.badge-em_homologacao { background: #a855f7; }
.badge-aprovado { background: #22c55e; }
.badge-reprovado { background: #ef4444; }

/* === VENCIMENTO === */
@keyframes blink-expiring {
    0%,100% { box-shadow: 0 4px 8px rgba(15,23,42,0.08); border-color: inherit; }
    50% { box-shadow: 0 0 18px 4px rgba(220,38,38,0.7); outline: 2px solid #dc2626; }
}
.card-expiring {
    animation: blink-expiring 1.2s ease-in-out infinite !important;
}
.badge-vencimento {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 700;
}
.badge-venc-ok   { background:#dcfce7; color:#166534; border:1px solid #86efac; }
.badge-venc-warn { background:#fef9c3; color:#854d0e; border:1px solid #fde047; }
.badge-venc-late { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }

/* Funil pills */
.funil-pill {
    padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;
    border: 2px solid #e2e8f0; background: #f8fafc; color: #475569;
    cursor: pointer; transition: all 0.2s; white-space: nowrap;
}
.funil-pill:hover { border-color: #3b82f6; color: #2563eb; background: #eff6ff; }
.funil-pill.active { border-color: #2563eb; background: #2563eb; color: #fff; }

/* Renovação - Badge animada */
.renovation-badge-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    animation: float-badge 1.8s ease-in-out infinite;
    font-size: 1rem;
}

@keyframes float-badge {
    0% { transform: translateY(0) scale(1); opacity: 1; }
    50% { transform: translateY(-6px) scale(1.1); opacity: 0.9; }
    100% { transform: translateY(0) scale(1); opacity: 1; }
}
</style>

<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Homologações</h1>
            <p class="text-slate-600 mt-1">Gestão de homologações de produtos</p>
        </div>
        <div class="flex gap-3">
            <?php if ($canCreate): ?>
            <button onclick="openModalNovaHomologacao()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <span>➕</span>
                <span>Nova Homologação</span>
                <span class="ml-2 renovation-badge-button">🚧</span>
            </button>
            <?php endif; ?>
            
            <?php if ($isAdmin || $isSuperAdmin): ?>
            <button onclick="openModalChecklists()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <span>📋</span>
                <span>Cadastros de Checklist</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Faixa de atualização do módulo -->
    <div id="homologUpdateBanner" style="display:block;" class="mb-6 rounded-lg p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 flex items-center justify-between shadow-md">
        <div class="flex items-center space-x-4">
            <div class="w-3 h-3 rounded-full bg-yellow-500 animate-ping"></div>
            <div>
                <strong class="text-base">⚙️ Atualização em andamento</strong>
                <span class="block text-sm text-yellow-700">Este módulo está sendo atualizado — podem ocorrer bugs ou instabilidades.</span>
            </div>
        </div>
        <button id="closeBannerBtn" class="text-yellow-800 hover:text-yellow-900 font-semibold text-lg" onclick="(function(){document.getElementById('homologUpdateBanner').style.display='none'; try{localStorage.setItem('homologBannerClosed','1')}catch(e){}})();">✖</button>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6 border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-slate-700 flex items-center gap-2">
                <span>🔍</span>
                <span>Filtros</span>
            </h3>
            <button onclick="limparFiltros()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                🔄 Limpar Filtros
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Pesquisa -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Pesquisar</label>
                <input 
                    type="text" 
                    id="filtroPesquisa" 
                    placeholder="Código ou descrição..." 
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    oninput="aplicarFiltros()">
            </div>
            
            <!-- Localização -->
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Localização</label>
                <select 
                    id="filtroLocalizacao" 
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    onchange="aplicarFiltros()">
                    <option value="">Todos</option>
                    <?php foreach ($departamentos as $dept): ?>
                    <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Departamento (Funil) -->
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Departamento (Funil)</label>
                <select 
                    id="filtroDepartamento" 
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    onchange="aplicarFiltros()">
                    <option value="">Todos</option>
                    <?php foreach ($departamentos as $dept): ?>
                    <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Período -->
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Período</label>
                <div class="flex gap-2">
                    <input 
                        type="date" 
                        id="filtroDataInicio" 
                        class="w-1/2 px-2 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        onchange="aplicarFiltros()"
                        title="Data Inicial">
                    <input 
                        type="date" 
                        id="filtroDataFim" 
                        class="w-1/2 px-2 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        onchange="aplicarFiltros()"
                        title="Data Final">
                </div>
            </div>
        </div>
        
        <!-- Contador de Resultados -->
        <div class="mt-3 pt-3 border-t border-slate-200">
            <p class="text-sm text-slate-600" id="contadorFiltros">
                Exibindo <span class="font-semibold text-blue-600" id="totalFiltrado">0</span> de 
                <span class="font-semibold" id="totalGeral">0</span> homologações
            </p>
        </div>
    </div>


    <!-- Scrollbar superior -->

    <div class="kanban-scroll-top" id="kanbanScrollTop"><div id="kanbanScrollTopInner" style="height:1px"></div></div>

    <!-- Kanban Board (horizontal scroll) -->
    <div class="kanban-wrap" id="kanbanWrap">
      <div class="kanban-row">
        <!-- Coluna: Aguardando Recebimento -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">📦</span>
                <h3 class="font-bold text-slate-800">Aguardando Recebimento</h3>
                </div>
                <span class="bg-yellow-600/15 text-yellow-800 text-xs px-2 py-1 rounded-full border border-yellow-600/20 font-semibold"><?= count($homologacoes['aguardando_recebimento']) ?></span>
            </div>
            <div class="kanban-column" data-status="aguardando_recebimento">
                <?php foreach ($homologacoes['aguardando_recebimento'] as $h): ?>
                    <div class="kanban-card status-aguardando_recebimento relative<?php
                             $dr = (int)($h['dias_restantes'] ?? 99999);
                             $da = (int)($h['dias_aviso'] ?? 7);
                             $venc = $h['data_vencimento'] ?? null;
                             $isExpiring = $venc && $dr <= $da && !in_array($h['status'],['aprovado','reprovado']);
                             echo $isExpiring ? ' card-expiring' : ''; ?>" 
                         data-id="<?= $h['id'] ?>" 
                         data-status="aguardando_recebimento"
                         data-departamento-id="<?= (int)($h['departamento_resp_id'] ?? 0) ?>"
                         data-vencimento="<?= htmlspecialchars($h['data_vencimento'] ?? '') ?>"
                         draggable="true"
                         onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">
                            🗑️
                        </button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <?php if (!empty($h['departamento_resp_nome'])): ?>
                        <div class="text-xs text-indigo-700 font-medium mb-1 flex items-center gap-1">
                            <span>🏢</span><span><?= e($h['departamento_resp_nome']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($h['data_vencimento'])): ?>
                        <?php
                            $diasR = (int)($h['dias_restantes'] ?? 99999);
                            $diasA = (int)($h['dias_aviso'] ?? 7);
                            if ($diasR < 0) { $vClass='badge-venc-late'; $vIcon='🔴'; $vTxt='Vencido '.(abs($diasR)).'d'; }
                            elseif ($diasR <= $diasA) { $vClass='badge-venc-warn'; $vIcon='🟡'; $vTxt='Vence em '.$diasR.'d'; }
                            else { $vClass='badge-venc-ok'; $vIcon='🟢'; $vTxt='Vence em '.$diasR.'d'; }
                        ?>
                        <span class="badge-vencimento <?= $vClass ?> mb-2"><?= $vIcon ?> <?= $vTxt ?></span>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs mb-6">
                            <?php if ($h['total_anexos'] > 0): ?>
                            <span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span>
                            <?php else: ?><span></span><?php endif; ?>
                            <span class="text-slate-400 text-[10px]"><?= date('d/m/y', strtotime($h['created_at'])) ?></span>
                        </div>
                        <!-- Botões de Navegação -->
                        <div class="card-nav-buttons">
                            <button type="button" class="card-nav-btn" 
                                    onclick="event.stopPropagation(); moverParaEtapaAnterior(<?= $h['id'] ?>, 'aguardando_recebimento')"
                                    title="Retornar para etapa anterior"
                                    disabled>
                                ⬅️
                            </button>
                            <button type="button" class="card-nav-btn" 
                                    onclick="event.stopPropagation(); moverParaProximaEtapa(<?= $h['id'] ?>, 'aguardando_recebimento')"
                                    title="Enviar para próxima etapa">
                                ➡️
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Recebido -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">✅</span>
                <h3 class="font-bold text-slate-800">Recebido</h3>
                </div>
                <span class="bg-blue-600/15 text-blue-800 text-xs px-2 py-1 rounded-full border border-blue-600/20 font-semibold"><?= count($homologacoes['recebido']) ?></span>
            </div>
            <div class="kanban-column" data-status="recebido">
                <?php foreach ($homologacoes['recebido'] as $h): ?>
                    <div class="kanban-card status-recebido relative<?php
                             $dr2 = (int)($h['dias_restantes'] ?? 99999); $da2 = (int)($h['dias_aviso'] ?? 7);
                             $venc2 = $h['data_vencimento'] ?? null;
                             echo ($venc2 && $dr2 <= $da2 && !in_array($h['status'],['aprovado','reprovado'])) ? ' card-expiring' : ''; ?>" 
                         data-id="<?= $h['id'] ?>" 
                         data-status="recebido"
                         data-departamento-id="<?= (int)($h['departamento_resp_id'] ?? 0) ?>"
                         data-vencimento="<?= htmlspecialchars($h['data_vencimento'] ?? '') ?>"
                         draggable="true"
                         onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <?php if (!empty($h['departamento_resp_nome'])): ?>
                        <div class="text-xs text-indigo-700 font-medium mb-1 flex items-center gap-1"><span>🏢</span><span><?= e($h['departamento_resp_nome']) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($h['data_vencimento'])): ?>
                        <?php $dr2=(int)($h['dias_restantes']??99999);$da2=(int)($h['dias_aviso']??7); if($dr2<0){$vc='badge-venc-late';$vi='🔴';$vt='Vencido '.abs($dr2).'d';}elseif($dr2<=$da2){$vc='badge-venc-warn';$vi='🟡';$vt='Vence em '.$dr2.'d';}else{$vc='badge-venc-ok';$vi='🟢';$vt='Vence em '.$dr2.'d';} ?>
                        <span class="badge-vencimento <?= $vc ?> mb-2"><?= $vi ?> <?= $vt ?></span>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs">
                            <?php if ($h['total_anexos'] > 0): ?><span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span><?php else: ?><span></span><?php endif; ?>
                            <span class="text-slate-400 text-[10px]"><?= date('d/m/y', strtotime($h['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Em Análise -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">🔍</span>
                <h3 class="font-bold text-slate-800">Em Análise</h3>
                </div>
                <span class="bg-orange-600/15 text-orange-800 text-xs px-2 py-1 rounded-full border border-orange-600/20 font-semibold"><?= count($homologacoes['em_analise']) ?></span>
            </div>
            <div class="kanban-column" data-status="em_analise">
                <?php foreach ($homologacoes['em_analise'] as $h): ?>
                    <div class="kanban-card status-em_analise relative<?php
                             $drA=(int)($h['dias_restantes']??99999);$daA=(int)($h['dias_aviso']??7);
                             echo ($h['data_vencimento']??null)&&$drA<=$daA&&!in_array($h['status'],['aprovado','reprovado'])?' card-expiring':'';
                             ?>" 
                         data-id="<?= $h['id'] ?>" 
                         data-status="em_analise"
                         data-departamento-id="<?= (int)($h['departamento_resp_id'] ?? 0) ?>"
                         data-vencimento="<?= htmlspecialchars($h['data_vencimento'] ?? '') ?>"
                         draggable="true"
                         onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <?php if (!empty($h['aprovado_por_nome'])): ?>
                        <div class="text-xs text-green-700 font-medium mb-1 flex items-center gap-1">
                            <span>🟢</span><span>Aprovado por: <?= e($h['aprovado_por_nome']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($h['departamento_resp_nome'])): ?>
                        <div class="text-xs text-indigo-700 font-medium mb-1 flex items-center gap-1"><span>🏢</span><span><?= e($h['departamento_resp_nome']) ?></span></div>
                        <?php elseif (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-1 flex items-center gap-1"><span>📍</span><span><?= e($h['departamento_nome']) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($h['data_vencimento'])): ?>
                        <?php $drA=(int)($h['dias_restantes']??99999);$daA=(int)($h['dias_aviso']??7); if($drA<0){$vcA='badge-venc-late';$viA='🔴';$vtA='Vencido '.abs($drA).'d';}elseif($drA<=$daA){$vcA='badge-venc-warn';$viA='🟡';$vtA='Vence em '.$drA.'d';}else{$vcA='badge-venc-ok';$viA='🟢';$vtA='Vence em '.$drA.'d';} ?>
                        <span class="badge-vencimento <?= $vcA ?> mb-2"><?= $viA ?> <?= $vtA ?></span>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs">
                            <?php if ($h['total_anexos'] > 0): ?><span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span><?php else: ?><span></span><?php endif; ?>
                            <span class="text-slate-400 text-[10px]"><?= date('d/m/y', strtotime($h['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Em Homologação -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">🧪</span>
                <h3 class="font-bold text-slate-800">Em Homologação</h3>
                </div>
                <span class="bg-purple-600/15 text-purple-800 text-xs px-2 py-1 rounded-full border border-purple-600/20 font-semibold"><?= count($homologacoes['em_homologacao']) ?></span>
            </div>
            <div class="kanban-column" data-status="em_homologacao">
                <?php foreach ($homologacoes['em_homologacao'] as $h): ?>
                    <div class="kanban-card status-em_homologacao relative<?php
                             $drH=(int)($h['dias_restantes']??99999);$daH=(int)($h['dias_aviso']??7);
                             echo ($h['data_vencimento']??null)&&$drH<=$daH&&!in_array($h['status'],['aprovado','reprovado'])?' card-expiring':'';
                             ?>" 
                         data-id="<?= $h['id'] ?>" 
                         data-status="em_homologacao"
                         data-departamento-id="<?= (int)($h['departamento_resp_id'] ?? 0) ?>"
                         data-vencimento="<?= htmlspecialchars($h['data_vencimento'] ?? '') ?>"
                         draggable="true"
                         onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <?php if (!empty($h['aprovado_por_nome'])): ?>
                        <div class="text-xs text-green-700 font-medium mb-1 flex items-center gap-1"><span>🟢</span><span>Aprovado por: <?= e($h['aprovado_por_nome']) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($h['departamento_resp_nome'])): ?>
                        <div class="text-xs text-indigo-700 font-medium mb-1 flex items-center gap-1"><span>🏢</span><span><?= e($h['departamento_resp_nome']) ?></span></div>
                        <?php elseif (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-1 flex items-center gap-1"><span>📍</span><span><?= e($h['departamento_nome']) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($h['data_vencimento'])): ?>
                        <?php $drH=(int)($h['dias_restantes']??99999);$daH=(int)($h['dias_aviso']??7); if($drH<0){$vcH='badge-venc-late';$viH='🔴';$vtH='Vencido '.abs($drH).'d';}elseif($drH<=$daH){$vcH='badge-venc-warn';$viH='🟡';$vtH='Vence em '.$drH.'d';}else{$vcH='badge-venc-ok';$viH='🟢';$vtH='Vence em '.$drH.'d';} ?>
                        <span class="badge-vencimento <?= $vcH ?> mb-2"><?= $viH ?> <?= $vtH ?></span>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs">
                            <?php if ($h['total_anexos'] > 0): ?><span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span><?php else: ?><span></span><?php endif; ?>
                            <span class="text-slate-400 text-[10px]"><?= date('d/m/y', strtotime($h['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Aprovado -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">✔️</span>
                <h3 class="font-bold text-slate-800">Aprovado</h3>
                </div>
                <span class="bg-green-600/15 text-green-800 text-xs px-2 py-1 rounded-full border border-green-600/20 font-semibold"><?= count($homologacoes['aprovado']) ?></span>
            </div>
            <div class="kanban-column" data-status="aprovado">
                <?php foreach ($homologacoes['aprovado'] as $h): ?>
                    <div class="kanban-card status-aprovado relative"
                         data-id="<?= $h['id'] ?>" 
                         data-status="aprovado"
                         data-departamento-id="<?= (int)($h['departamento_resp_id'] ?? 0) ?>"
                         data-vencimento="<?= htmlspecialchars($h['data_vencimento'] ?? '') ?>"
                         draggable="true"
                         onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <?php if (!empty($h['aprovado_por_nome'])): ?>
                        <div class="text-xs text-green-700 font-medium mb-1 flex items-center gap-1"><span>🟢</span><span>Aprovado por: <?= e($h['aprovado_por_nome']) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($h['departamento_resp_nome'])): ?>
                        <div class="text-xs text-indigo-700 font-medium mb-1 flex items-center gap-1"><span>🏢</span><span><?= e($h['departamento_resp_nome']) ?></span></div>
                        <?php elseif (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-1 flex items-center gap-1"><span>📍</span><span><?= e($h['departamento_nome']) ?></span></div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs">
                            <?php if ($h['total_anexos'] > 0): ?><span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span><?php else: ?><span></span><?php endif; ?>
                            <span class="text-slate-400 text-[10px]"><?= date('d/m/y', strtotime($h['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna: Reprovado -->
        <div class="kanban-col">
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center gap-2">
                <span class="text-2xl">❌</span>
                <h3 class="font-bold text-slate-800">Reprovado</h3>
                </div>
                <span class="bg-red-600/15 text-red-800 text-xs px-2 py-1 rounded-full border border-red-600/20 font-semibold"><?= count($homologacoes['reprovado']) ?></span>
            </div>
            <div class="kanban-column" data-status="reprovado">
                <?php foreach ($homologacoes['reprovado'] as $h): ?>
                    <div class="kanban-card status-reprovado relative"
                         data-id="<?= $h['id'] ?>" 
                         data-status="reprovado"
                         data-departamento-id="<?= (int)($h['departamento_resp_id'] ?? 0) ?>"
                         data-vencimento="<?= htmlspecialchars($h['data_vencimento'] ?? '') ?>"
                         draggable="true"
                         onclick="openCardDetails(<?= $h['id'] ?>)">
                        <button type="button" title="Excluir" onclick="event.stopPropagation(); deleteHomologacao(<?= $h['id'] ?>)" class="absolute top-2 right-2 text-slate-400 hover:text-red-600">🗑️</button>
                        <div class="text-sm font-bold text-slate-700 mb-1"><?= e($h['cod_referencia']) ?></div>
                        <div class="text-xs text-slate-600 mb-2 line-clamp-2"><?= e($h['descricao']) ?></div>
                        <?php if (!empty($h['reprovado_por_nome'])): ?>
                        <div class="text-xs text-red-700 font-medium mb-1 flex items-center gap-1"><span>🔴</span><span>Reprovado por: <?= e($h['reprovado_por_nome']) ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($h['departamento_resp_nome'])): ?>
                        <div class="text-xs text-indigo-700 font-medium mb-1 flex items-center gap-1"><span>🏢</span><span><?= e($h['departamento_resp_nome']) ?></span></div>
                        <?php elseif (!empty($h['departamento_nome'])): ?>
                        <div class="text-xs text-purple-700 font-medium mb-1 flex items-center gap-1"><span>📍</span><span><?= e($h['departamento_nome']) ?></span></div>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs">
                            <?php if ($h['total_anexos'] > 0): ?><span class="text-slate-500">📎 <?= $h['total_anexos'] ?></span><?php else: ?><span></span><?php endif; ?>
                            <span class="text-slate-400 text-[10px]"><?= date('d/m/y', strtotime($h['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
      </div>
    </div>
</div>

<!-- Modal: Nova Homologação -->
<div id="modalNovaHomologacao" class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) closeModalNovaHomologacao()">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[85vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-slate-800">📋 Nova Homologação</h2>
            <button onclick="closeModalNovaHomologacao()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>

        <form id="formNovaHomologacao" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cód. Referência <span class="text-red-500">*</span></label>
                    <input type="text" name="cod_referencia" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Departamento Responsável <span class="text-red-500">*</span></label>
                    <select name="departamento_resp_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione...</option>
                        <?php foreach ($departamentos as $dept): ?>
                        <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Todos do departamento serão notificados</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Descrição <span class="text-red-500">*</span></label>
                <textarea name="descricao" required rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">📅 Data de Vencimento <span class="text-red-500">*</span></label>
                    <input type="date" name="data_vencimento" required min="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">🔔 Avisar com X dias de antecedência</label>
                    <input type="number" name="dias_aviso" value="7" min="1" max="60" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">O card piscará em vermelho quando faltar esse número de dias</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Avisar Logística da chegada?</label>
                <div class="flex gap-4">
                    <label class="flex items-center"><input type="radio" name="avisar_logistica" value="1" class="mr-2"><span>Sim</span></label>
                    <label class="flex items-center"><input type="radio" name="avisar_logistica" value="0" checked class="mr-2"><span>Não</span></label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Observação</label>
                <textarea name="observacao" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg"></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Registrar Nova Homologação</button>
                <button type="button" onclick="closeModalNovaHomologacao()" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Cadastros de Checklist -->
<div id="modalChecklists" class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) closeModalChecklists()">
    <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">📋 Gerenciar Checklists</h2>
            <button onclick="closeModalChecklists()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-6">
            <button onclick="switchChecklistTab('novo')" id="tabNovoChecklist" class="px-4 py-2 border-b-2 border-blue-600 text-blue-600 font-medium">
                ➕ Novo Checklist
            </button>
            <button onclick="switchChecklistTab('lista')" id="tabListaChecklists" class="px-4 py-2 border-b-2 border-transparent text-gray-600 hover:text-blue-600">
                📋 Lista de Checklists
            </button>
        </div>

        <!-- Tab: Novo Checklist -->
        <div id="checklistTabNovo">
            <form id="formNovoChecklist" onsubmit="salvarChecklist(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Título do Checklist *</label>
                    <input type="text" id="checklistTitulo" required 
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Ex: Checklist de Homologação de Toners">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Descrição (opcional)</label>
                    <textarea id="checklistDescricao" rows="2" 
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                              placeholder="Descrição do checklist..."></textarea>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-slate-700">Itens do Checklist *</label>
                        <button type="button" onclick="adicionarItemChecklist()" 
                                class="text-sm px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200">
                            + Adicionar Item
                        </button>
                    </div>
                    
                    <div id="checklistItens" class="space-y-2">
                        <!-- Itens adicionados dinamicamente -->
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        💾 Salvar Checklist
                    </button>
                    <button type="button" onclick="cancelarNovoChecklist()" 
                            class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tab: Lista de Checklists -->
        <div id="checklistTabLista" class="hidden">
            <div id="listaChecklists" class="space-y-3">
                <!-- Carregado via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalhes -->
<div id="modalCardDetails" class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto" onclick="if(event.target === this) closeCardDetails()">
    <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[85vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-slate-800">Detalhes da Homologação</h2>
            <button onclick="closeCardDetails()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>
        <div id="cardDetailsContent"><p class="text-center text-slate-500">Carregando...</p></div>
    </div>
</div>

<script>
// Variáveis globais
const usuarios = <?= json_encode($usuarios) ?>;
const isUserAdmin = <?= json_encode($isAdmin || $isSuperAdmin) ?>;

// Util: mover modais para o container global para sobrepor sidebar e layout
document.addEventListener('DOMContentLoaded', () => {
    // Esconder faixa de atualização se usuário já fechou
    try {
        if (localStorage.getItem('homologBannerClosed') === '1') {
            const bannerEl = document.getElementById('homologUpdateBanner');
            if (bannerEl) bannerEl.style.display = 'none';
        }
    } catch (e) {}

    const globalContainer = document.getElementById('global-modals-container');
    if (globalContainer) {
        const nova = document.getElementById('modalNovaHomologacao');
        const detalhes = document.getElementById('modalCardDetails');
        if (nova && nova.parentElement !== globalContainer) globalContainer.appendChild(nova);
        if (detalhes && detalhes.parentElement !== globalContainer) globalContainer.appendChild(detalhes);
    }

    // Sincronizar scrollbar superior com o container principal
    const wrap = document.getElementById('kanbanWrap');
    const topBar = document.getElementById('kanbanScrollTop');
    const topInner = document.getElementById('kanbanScrollTopInner');
    if (wrap && topBar && topInner) {
        const syncWidths = () => { topInner.style.width = wrap.scrollWidth + 'px'; };
        syncWidths();
        window.addEventListener('resize', syncWidths);
        // Sync scroll positions
        let syncing = false;
        wrap.addEventListener('scroll', () => {
            if (syncing) return; syncing = true; topBar.scrollLeft = wrap.scrollLeft; syncing = false;
        });
        topBar.addEventListener('scroll', () => {
            if (syncing) return; syncing = true; wrap.scrollLeft = topBar.scrollLeft; syncing = false;
        });
    }
    
    // Inicializar contadores
    atualizarContadores();
});

// Salvar contadores (contador_inicial e contador_final) para uma homologação via modal de detalhes
async function salvarContadores(homologacaoId) {
    const inputInicial = document.getElementById(`contador_inicial_${homologacaoId}`);
    const inputFinal = document.getElementById(`contador_final_${homologacaoId}`);

    if (!inputInicial || !inputFinal) {
        alert('❌ Campos de contador não encontrados.');
        return;
    }

    const contadorInicial = inputInicial.value !== '' ? parseInt(inputInicial.value, 10) : null;
    const contadorFinal = inputFinal.value !== '' ? parseInt(inputFinal.value, 10) : null;

    if (Number.isNaN(contadorInicial) && inputInicial.value !== '') {
        alert('❌ Contador inicial inválido.');
        return;
    }

    if (Number.isNaN(contadorFinal) && inputFinal.value !== '') {
        alert('❌ Contador final inválido.');
        return;
    }

    try {
        const response = await fetch(`/homologacoes/${homologacaoId}/contadores`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                contador_inicial: contadorInicial,
                contador_final: contadorFinal
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ Contadores salvos com sucesso!');
        } else {
            alert('❌ ' + (result.message || 'Erro ao salvar contadores'));
        }
    } catch (error) {
        console.error('Erro ao salvar contadores:', error);
        alert('❌ Erro ao salvar contadores');
    }
}

// ===== SISTEMA DE FILTROS =====
function aplicarFiltros() {
    const pesquisa = document.getElementById('filtroPesquisa').value.toLowerCase().trim();
    const localizacao = document.getElementById('filtroLocalizacao').value;
    const filtroDept = document.getElementById('filtroDepartamento').value;
    const dataInicio = document.getElementById('filtroDataInicio').value;
    const dataFim = document.getElementById('filtroDataFim').value;
    
    const cards = document.querySelectorAll('.kanban-card');
    let totalFiltrado = 0;
    
    cards.forEach(card => {
        let mostrar = true;
        
        if (pesquisa) {
            const codigo = card.querySelector('.text-sm.font-bold')?.textContent.toLowerCase() || '';
            const descricao = card.querySelector('.text-xs.text-slate-600')?.textContent.toLowerCase() || '';
            if (!codigo.includes(pesquisa) && !descricao.includes(pesquisa)) mostrar = false;
        }
        
        if (localizacao) {
            const cardLocalizacao = card.querySelector('.text-purple-700')?.textContent || '';
            const selectOption = Array.from(document.getElementById('filtroLocalizacao').options)
                .find(opt => opt.value === localizacao);
            const nomeDept = selectOption ? selectOption.textContent : '';
            if (!cardLocalizacao.includes(nomeDept)) mostrar = false;
        }

        if (filtroDept) {
            const cardDeptId = card.getAttribute('data-departamento-id') || '';
            if (cardDeptId !== filtroDept) mostrar = false;
        }
        
        if (mostrar) {
            card.style.display = 'block';
            totalFiltrado++;
        } else {
            card.style.display = 'none';
        }
    });
    
    document.getElementById('totalFiltrado').textContent = totalFiltrado;
    atualizarContagemColunas();
}

function selecionarFunil(deptId, btn) {
    // Atualizar pills
    document.querySelectorAll('.funil-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    // Sync select dropdown
    const sel = document.getElementById('filtroDepartamento');
    if (sel) sel.value = deptId;
    // Filtrar cards
    const cards = document.querySelectorAll('.kanban-card');
    let total = 0;
    cards.forEach(card => {
        const cardDeptId = card.getAttribute('data-departamento-id') || '';
        const show = !deptId || cardDeptId === String(deptId);
        card.style.display = show ? 'block' : 'none';
        if (show) total++;
    });
    document.getElementById('totalFiltrado').textContent = total;
    atualizarContagemColunas();
}

function limparFiltros() {
    document.getElementById('filtroPesquisa').value = '';
    document.getElementById('filtroLocalizacao').value = '';
    document.getElementById('filtroDepartamento').value = '';
    document.getElementById('filtroDataInicio').value = '';
    document.getElementById('filtroDataFim').value = '';
    
    // Reset funil pills
    document.querySelectorAll('.funil-pill').forEach(p => p.classList.remove('active'));
    const todosBtn = document.querySelector('.funil-pill[data-dept-id=""]');
    if (todosBtn) todosBtn.classList.add('active');
    
    // Mostrar todos os cards
    document.querySelectorAll('.kanban-card').forEach(card => {
        card.style.display = 'block';
    });
    
    atualizarContadores();
    atualizarContagemColunas();
}

// Mostrar campo de departamento quando seleciona "Em Análise"
function mostrarCampoDepartamento(homologacaoId) {
    const selectStatus = document.getElementById(`selectNovoStatus_${homologacaoId}`);
    const campoDept = document.getElementById(`campoDepartamento_${homologacaoId}`);
    const selectDept = document.getElementById(`selectDepartamento_${homologacaoId}`);
    
    if (selectStatus && campoDept && selectDept) {
        const novoStatus = selectStatus.value;
        // Mostrar apenas se o novo status for "em_analise"
        if (novoStatus === 'em_analise') {
            campoDept.style.display = 'block';
            selectDept.required = true;
        } else {
            campoDept.style.display = 'none';
            selectDept.required = false;
            selectDept.value = ''; // Limpar seleção quando ocultar
        }
    }
}

function alterarPorPagina(porPagina) {
    const totalCards = document.querySelectorAll('.kanban-card').length;
    const visibleCards = Array.from(document.querySelectorAll('.kanban-card'))
        .filter(card => card.style.display !== 'none').length;
    
    document.getElementById('totalGeral').textContent = totalCards;
    salvarEstatisticasHomologacoes(totalCards, visibleCards);
}

function salvarEstatisticasHomologacoes(totalCards, visibleCards) {
    fetch('/homologacoes/salvar-contadores', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ totalCards, visibleCards })
    })
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Erro ao salvar estatísticas de homologações:', error));
}

function atualizarContadores() {
    const totalCards = document.querySelectorAll('.kanban-card').length;
    const visibleCards = Array.from(document.querySelectorAll('.kanban-card'))
        .filter(card => card.style.display !== 'none').length;
    
    document.getElementById('totalGeral').textContent = totalCards;
    document.getElementById('totalFiltrado').textContent = visibleCards;
}

function atualizarContagemColunas() {
    // Atualizar badge de contagem em cada coluna
    const colunas = document.querySelectorAll('.kanban-col');
    colunas.forEach(coluna => {
        const badge = coluna.querySelector('span[class*="rounded-full"]');
        const cardsVisiveis = Array.from(coluna.querySelectorAll('.kanban-card'))
            .filter(card => card.style.display !== 'none').length;
        if (badge) {
            badge.textContent = cardsVisiveis;
        }
    });
}

// Helpers de scroll-lock
function lockBodyScroll() { document.documentElement.style.overflow = 'hidden'; document.body.style.overflow = 'hidden'; }
function unlockBodyScroll() { document.documentElement.style.overflow = ''; document.body.style.overflow = ''; }

// Modal Nova Homologação
function openModalNovaHomologacao() {
    document.getElementById('modalNovaHomologacao').classList.remove('hidden');
    lockBodyScroll();
}

function closeModalNovaHomologacao() {
    document.getElementById('modalNovaHomologacao').classList.add('hidden');
    unlockBodyScroll();
}

// Submit
document.getElementById('formNovaHomologacao').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/homologacoes/store', { method: 'POST', body: formData });
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        alert('❌ Erro ao criar homologação');
    }
});

// Detalhes
async function openCardDetails(id) {
    document.getElementById('modalCardDetails').classList.remove('hidden');
    lockBodyScroll();
    document.getElementById('cardDetailsContent').innerHTML = '<p class="text-center">Carregando...</p>';
    
    try {
        const response = await fetch(`/homologacoes/${id}/details`);
        const result = await response.json();
        
        if (result.success) {
            renderDetails(result);
            
            // Se status for "em_homologacao", carregar checklists no dropdown
            if (result.homologacao.status === 'em_homologacao') {
                setTimeout(() => {
                    carregarChecklistsNoCard(id);
                }, 100);
            }
        }
    } catch (error) {
        document.getElementById('cardDetailsContent').innerHTML = '<p class="text-center text-red-500">Erro</p>';
    }
}

function closeCardDetails() {
    document.getElementById('modalCardDetails').classList.add('hidden');
    unlockBodyScroll();
}

// Função para retornar próximos status (admins podem voltar)
function getProximosStatus(statusAtual) {
    const todosStatus = [
        { value: 'aguardando_recebimento', label: 'Aguardando Recebimento' },
        { value: 'recebido', label: 'Recebido' },
        { value: 'em_analise', label: 'Em Análise' },
        { value: 'em_homologacao', label: 'Em Homologação' },
        { value: 'aprovado', label: 'Aprovado' },
        { value: 'reprovado', label: 'Reprovado' }
    ];
    
    // Se for admin, permitir mover para qualquer status (exceto o atual)
    if (isUserAdmin) {
        const opcoes = todosStatus
            .filter(s => s.value !== statusAtual)
            .map(s => {
                const isBack = getStatusOrder(s.value) < getStatusOrder(statusAtual);
                const arrow = isBack ? '⬅️ ' : '➡️ ';
                return `<option value="${s.value}">${arrow}${s.label}</option>`;
            });
        
        if (opcoes.length === 0) {
            return '<option value="">Nenhuma alteração disponível</option>';
        }
        
        return opcoes.join('');
    }
    
    // Fluxo normal (apenas avançar) para usuários não-admin
    const fluxoStatus = {
        'aguardando_recebimento': [
            { value: 'recebido', label: 'Recebido' },
            { value: 'reprovado', label: 'Reprovado' }
        ],
        'recebido': [
            { value: 'em_analise', label: 'Em Análise' },
            { value: 'reprovado', label: 'Reprovado' }
        ],
        'em_analise': [
            { value: 'em_homologacao', label: 'Em Homologação' },
            { value: 'aprovado', label: 'Aprovado' },
            { value: 'reprovado', label: 'Reprovado' }
        ],
        'em_homologacao': [
            { value: 'aprovado', label: 'Aprovado' },
            { value: 'reprovado', label: 'Reprovado' }
        ],
        'aprovado': [],  // Status final
        'reprovado': []  // Status final
    };
    
    const proximos = fluxoStatus[statusAtual] || [];
    
    if (proximos.length === 0) {
        return '<option value="">✅ Status Final - Não pode ser alterado</option>';
    }
    
    return proximos.map(s => 
        `<option value="${s.value}">➡️ ${s.label}</option>`
    ).join('');
}

// Função auxiliar para determinar a ordem dos status
function getStatusOrder(status) {
    const ordem = {
        'aguardando_recebimento': 1,
        'recebido': 2,
        'em_analise': 3,
        'em_homologacao': 4,
        'aprovado': 5,
        'reprovado': 5
    };
    return ordem[status] || 0;
}

function renderDetails(data) {
    const h = data.homologacao;
    const statusLabels = {
        'aguardando_recebimento': 'Aguardando Recebimento',
        'recebido': 'Recebido',
        'em_analise': 'Em Análise',
        'em_homologacao': 'Em Homologação',
        'aprovado': 'Aprovado',
        'reprovado': 'Reprovado'
    };
    
    let html = `
        <div class="space-y-4">
            <div class="bg-slate-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-3">
                    <div><span class="text-sm text-slate-600">Código:</span><p class="font-bold">${h.cod_referencia}</p></div>
                    <div><span class="text-sm text-slate-600">Status:</span><span class="badge-status badge-${h.status}">${statusLabels[h.status]}</span></div>
                    <div class="col-span-2"><span class="text-sm text-slate-600">Descrição:</span><p class="mt-1">${h.descricao}</p></div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Contador inicial</label>
                        <input
                            type="number"
                            id="contador_inicial_${h.id}"
                            class="w-full px-2 py-1 border border-slate-300 rounded-md text-xs"
                            value="${h.contador_inicial !== null && h.contador_inicial !== undefined ? h.contador_inicial : ''}"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Contador final</label>
                        <input
                            type="number"
                            id="contador_final_${h.id}"
                            class="w-full px-2 py-1 border border-slate-300 rounded-md text-xs"
                            value="${h.contador_final !== null && h.contador_final !== undefined ? h.contador_final : ''}"
                        >
                    </div>
                </div>
                <div class="mt-2 flex justify-end">
                    <button
                        type="button"
                        class="px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700"
                        onclick="salvarContadores(${h.id})"
                    >
                        Salvar contadores
                    </button>
                </div>
            </div>
            
            <div>
                <h3 class="font-bold mb-2">👤 Responsáveis</h3>
                ${data.responsaveis.map(r => `<div class="bg-blue-50 px-3 py-2 rounded mb-2">${r.name} - ${r.email}</div>`).join('')}
            </div>
            
            ${h.status === 'em_homologacao' || h.checklist_id ? `
            <div class="bg-purple-50 p-4 rounded-lg border-2 border-purple-200">
                <h3 class="font-bold mb-3 text-purple-800">📋 Checklist de Homologação</h3>
                
                ${h.status === 'em_homologacao' ? `
                    <!-- MODO EDIÇÃO: Pode selecionar e preencher -->
                    <div id="checklistSection${h.id}">
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-2">Selecionar Checklist</label>
                            <select id="selectChecklist${h.id}" onchange="carregarItensChecklist(${h.id}, this.value)" 
                                    class="w-full px-3 py-2 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="">-- Escolha um checklist --</option>
                            </select>
                        </div>
                        <div id="itensChecklist${h.id}"></div>
                    </div>
                ` : h.checklist_id ? `
                    <!-- MODO VISUALIZAÇÃO: Apenas consulta do que foi preenchido -->
                    <div class="bg-blue-50 p-3 rounded-lg mb-3">
                        <span class="text-sm font-medium">✅ Checklist já preenchido</span>
                    </div>
                    <div id="checklistRespostas${h.id}"></div>
                ` : ''}
            </div>
            ` : ''}
            
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-bold mb-3">📎 Anexar Evidências</h3>
                <form id="formUploadAnexo" class="space-y-3">
                    <input type="hidden" name="homologacao_id" value="${h.id}">
                    <input type="file" name="anexo" required class="w-full px-3 py-2 border rounded-lg">
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Enviar Anexo</button>
                </form>
                ${data.anexos.length > 0 ? `
                    <div class="mt-3 space-y-2">
                        ${data.anexos.map(a => `
                            <div class="flex justify-between bg-white px-3 py-2 rounded">
                                <span class="text-sm">${a.nome_arquivo}</span>
                                <a href="/homologacoes/anexo/${a.id}" target="_blank" class="text-blue-600 text-sm">Download</a>
                            </div>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
            
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="flex items-center mb-3">
                    <span class="text-sm font-medium">Status Atual:</span>
                    <span class="ml-2 badge-status badge-${h.status}">${statusLabels[h.status]}</span>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                    <p class="text-sm text-blue-800">
                        <strong>💡 Dica:</strong> Use as setas nos cards ou arraste para mudar o status rapidamente!
                    </p>
                </div>
                <form id="formUpdateStatus" class="space-y-3" style="display: none;">
                    <input type="hidden" name="homologacao_id" value="${h.id}">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">${isUserAdmin ? 'Mover para Status' : 'Avanar para Status'}</label>
                            <select name="status" id="selectNovoStatus_${h.id}" required class="w-full px-3 py-2 border rounded-lg" onchange="mostrarCampoDepartamento(${h.id})">
                                ${getProximosStatus(h.status)}
                            </select>
                        </div>
                        <div id="campoDepartamento_${h.id}" style="display: none;">
                            <label class="block text-sm font-medium mb-1">📍 Localização (Departamento) *</label>
                            <select name="departamento_id" id="selectDepartamento_${h.id}" class="w-full px-3 py-2 border rounded-lg">
                                <option value="">-- Selecione --</option>
                                <?php foreach ($departamentos as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= e($dept['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Local</label>
                            <input type="text" name="local_homologacao" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Data Início</label>
                            <input type="date" name="data_inicio_homologacao" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Alerta</label>
                            <input type="date" name="alerta_finalizacao" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        ${['em_analise', 'em_homologacao', 'aprovado', 'reprovado'].includes(h.status) ? `
                        <div>
                            <label class="block text-sm font-medium mb-1">Teste no cliente:</label>
                            <textarea name="teste_cliente" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Descreva o teste realizado no cliente"></textarea>
                        </div>
                        ` : ''}
                    </div>
                    <textarea name="observacao" rows="2" placeholder="Observação" class="w-full px-3 py-2 border rounded-lg"></textarea>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Atualizar Status</button>
                </form>
            </div>
    `;

    // ===== TIMELINE DE OBSERVAÇÕES =====
    const statusIcons = {
        'aguardando_recebimento': '📦',
        'recebido': '✅',
        'em_analise': '🔍',
        'em_homologacao': '🧪',
        'aprovado': '✔️',
        'reprovado': '❌'
    };
    const statusColors = {
        'aguardando_recebimento': '#ca8a04',
        'recebido': '#1d4ed8',
        'em_analise': '#c2410c',
        'em_homologacao': '#7c3aed',
        'aprovado': '#16a34a',
        'reprovado': '#dc2626'
    };

    const historico = data.historico || [];
    // Inverter para mostrar do mais antigo ao mais recente
    const historicoOrdenado = [...historico].reverse();

    let timelineHtml = '';
    if (historicoOrdenado.length > 0) {
        const itens = historicoOrdenado.map((reg, idx) => {
            const etapa = reg.status_novo || reg.status_anterior || '';
            const icon = statusIcons[etapa] || '🔵';
            const color = statusColors[etapa] || '#64748b';
            const label = statusLabels[etapa] || etapa;
            const obs = (reg.observacao || '').trim();
            const usuario = reg.usuario_nome || 'Sistema';
            const dataHora = reg.created_at
                ? new Date(reg.created_at.replace(' ', 'T')).toLocaleString('pt-BR', { timeZone: 'America/Sao_Paulo' })
                : '';

            return `
                <div style="display:flex;gap:0;align-items:stretch;">
                    <!-- Linha vertical + bolinha -->
                    <div style="display:flex;flex-direction:column;align-items:center;width:32px;flex-shrink:0;">
                        <div style="width:28px;height:28px;border-radius:50%;background:${color};color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;">${icon}</div>
                        ${idx < historicoOrdenado.length - 1
                            ? `<div style="width:2px;background:#e2e8f0;flex:1;margin:2px 0 0;"></div>`
                            : ''}
                    </div>
                    <!-- Conteúdo -->
                    <div style="flex:1;padding:0 0 18px 12px;">
                        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:6px;margin-bottom:4px;">
                            <span style="font-size:13px;font-weight:700;color:${color};">${label}</span>
                            <span style="font-size:10px;color:#94a3b8;">• ${dataHora}</span>
                        </div>
                        <div style="font-size:12px;color:#475569;margin-bottom:4px;">
                            👤 <strong>${usuario}</strong>
                        </div>
                        ${obs
                            ? `<div style="background:#f8fafc;border-left:3px solid ${color};border-radius:0 6px 6px 0;padding:8px 10px;font-size:12px;color:#334155;margin-top:4px;">${obs.replace(/\n/g,'<br>')}</div>`
                            : `<div style="font-size:11px;color:#cbd5e1;font-style:italic;">Sem observação</div>`
                        }
                    </div>
                </div>
            `;
        }).join('');

        timelineHtml = `
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
                <h3 style="font-size:14px;font-weight:700;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:6px;">
                    📝 Observações por Etapa
                </h3>
                ${itens}
            </div>
        `;
    }

    // Fechar o wrapper space-y-4 e o container externo após a timeline
    html += timelineHtml + '</div></div>';

    // Renderizar o HTML completo no modal
    document.getElementById('cardDetailsContent').innerHTML = html;

    // Se tem checklist_id e não está em homologação, carregar respostas
    if (h.checklist_id && h.status !== 'em_homologacao') {
        carregarRespostasChecklist(h.id, h.checklist_id);
    }
    
    // Se está em homologação, carregar dropdown de checklists
    if (h.status === 'em_homologacao') {
        carregarChecklistsDropdown(h.id);
    }
    
    // Garantir exibição do campo de Departamento quando necessário
    // 1) Se o status atual já é em_analise, mostrar campo e pré-selecionar o departamento
    try {
        const campoDept = document.getElementById(`campoDepartamento_${h.id}`);
        const selectDept = document.getElementById(`selectDepartamento_${h.id}`);
        if (h.status === 'em_analise' && campoDept && selectDept) {
            campoDept.style.display = 'block';
            selectDept.required = true;
            if (h.departamento_id) {
                selectDept.value = String(h.departamento_id);
            }
        }
        // 2) Sincronizar visibilidade com o status escolhido no select de novo status (onchange também cuida)
        const selectStatus = document.getElementById(`selectNovoStatus_${h.id}`);
        if (selectStatus) {
            mostrarCampoDepartamento(h.id);
        }
    } catch (e) {
        // noop
    }
    
    // Event listeners
    document.getElementById('formUpdateStatus').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await fetch('/homologacoes/update-status', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) { alert('✅ ' + result.message); location.reload(); } 
            else { alert('❌ ' + result.message); }
        } catch (error) { alert('❌ Erro'); }
    });
    
    document.getElementById('formUploadAnexo').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await fetch('/homologacoes/upload-anexo', { method: 'POST', body: formData });
            const result = await response.json();
            if (result.success) { alert('✅ ' + result.message); openCardDetails(h.id); } 
            else { alert('❌ ' + result.message); }
        } catch (error) { alert('❌ Erro'); }
    });
}

// Excluir homologação (global)
async function deleteHomologacao(id) {
    try {
        if (!confirm('Tem certeza que deseja excluir esta homologação?')) return;
        const fd = new FormData();
        fd.append('id', id);
        const res = await fetch('/homologacoes/delete', { method: 'POST', body: fd });
        const result = await res.json();
        if (result.success) {
            alert('✅ ' + result.message);
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Erro ao excluir'));
        }
    } catch (e) {
        alert('❌ Erro ao excluir');
    }
}

// ===== FUNÇÕES DE CHECKLIST =====

let checklistItemCounter = 0;
let checklistEditandoId = null; // ID do checklist sendo editado (null = novo)

// Abrir modal de checklists
function openModalChecklists() {
    document.getElementById('modalChecklists').classList.remove('hidden');
    checklistEditandoId = null; // Resetar para modo criação
    atualizarTituloFormularioChecklist();
    switchChecklistTab('novo');
    lockBodyScroll();
}

// Atualizar título do formulário conforme modo (criar/editar)
function atualizarTituloFormularioChecklist() {
    const btnSubmit = document.querySelector('#formNovoChecklist button[type="submit"]');
    const tabNovo = document.getElementById('tabNovoChecklist');
    
    if (checklistEditandoId) {
        btnSubmit.innerHTML = '💾 Atualizar Checklist';
        tabNovo.innerHTML = '✏️ Editar Checklist';
    } else {
        btnSubmit.innerHTML = '💾 Salvar Checklist';
        tabNovo.innerHTML = '➕ Novo Checklist';
    }
}

// Fechar modal de checklists
function closeModalChecklists() {
    document.getElementById('modalChecklists').classList.add('hidden');
    unlockBodyScroll();
}

// Trocar entre abas
function switchChecklistTab(tab) {
    const tabLista = document.getElementById('tabListaChecklists');
    const tabNovo = document.getElementById('tabNovoChecklist');
    const contentLista = document.getElementById('checklistTabLista');
    const contentNovo = document.getElementById('checklistTabNovo');
    
    if (tab === 'lista') {
        tabLista.classList.add('border-blue-600', 'text-blue-600', 'font-medium');
        tabLista.classList.remove('border-transparent', 'text-gray-600');
        tabNovo.classList.remove('border-blue-600', 'text-blue-600', 'font-medium');
        tabNovo.classList.add('border-transparent', 'text-gray-600');
        contentLista.classList.remove('hidden');
        contentNovo.classList.add('hidden');
        
        // Carregar checklists ao abrir a aba
        carregarChecklists();
    } else {
        tabNovo.classList.add('border-blue-600', 'text-blue-600', 'font-medium');
        tabNovo.classList.remove('border-transparent', 'text-gray-600');
        tabLista.classList.remove('border-blue-600', 'text-blue-600', 'font-medium');
        tabLista.classList.add('border-transparent', 'text-gray-600');
        contentNovo.classList.remove('hidden');
        contentLista.classList.add('hidden');
        
        // Adicionar primeiro item automaticamente
        if (document.getElementById('checklistItens').children.length === 0) {
            adicionarItemChecklist();
        }
    }
}

// Adicionar item ao checklist
function adicionarItemChecklist() {
    const container = document.getElementById('checklistItens');
    const index = checklistItemCounter++;
    
    const itemHtml = `
        <div class="flex gap-2 items-start p-3 bg-gray-50 rounded-lg" id="checklistItem${index}">
            <div class="flex-1">
                <input type="text" 
                       id="checklistItemTitulo${index}" 
                       required
                       placeholder="Descrição do item (ex: Verificar qualidade de impressão)"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-32">
                <select id="checklistItemTipo${index}" 
                        class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    <option value="checkbox">✓ Checkbox</option>
                    <option value="sim_nao">Sim/Não</option>
                    <option value="texto">Texto</option>
                    <option value="numero">Número</option>
                </select>
            </div>
            <button type="button" onclick="removerItemChecklist(${index})" 
                    class="px-2 py-2 text-red-600 hover:bg-red-50 rounded">
                🗑️
            </button>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', itemHtml);
}

// Remover item do checklist
function removerItemChecklist(index) {
    const item = document.getElementById(`checklistItem${index}`);
    if (item) {
        item.remove();
    }
}

// Salvar checklist
async function salvarChecklist(event) {
    event.preventDefault();
    
    const titulo = document.getElementById('checklistTitulo').value;
    const descricao = document.getElementById('checklistDescricao').value;
    
    // Coletar itens
    const itens = [];
    const container = document.getElementById('checklistItens');
    const itemDivs = container.querySelectorAll('[id^="checklistItem"]');
    
    if (itemDivs.length === 0) {
        alert('⚠️ Adicione pelo menos um item ao checklist!');
        return;
    }
    
    itemDivs.forEach((div, ordem) => {
        const index = div.id.replace('checklistItem', '');
        const tituloItem = document.getElementById(`checklistItemTitulo${index}`)?.value;
        const tipo = document.getElementById(`checklistItemTipo${index}`)?.value;
        
        if (tituloItem) {
            itens.push({
                titulo: tituloItem,
                tipo_resposta: tipo,
                ordem: ordem
            });
        }
    });
    
    try {
        let url, successMessage;
        
        if (checklistEditandoId) {
            // Modo edição
            url = `/homologacoes/checklists/${checklistEditandoId}/update`;
            successMessage = '✅ Checklist atualizado com sucesso!';
        } else {
            // Modo criação
            url = '/homologacoes/checklists/create';
            successMessage = '✅ Checklist criado com sucesso!';
        }
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ titulo, descricao, itens })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(successMessage);
            cancelarNovoChecklist();
            switchChecklistTab('lista');
            carregarChecklists();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao salvar checklist');
    }
}

// Cancelar novo checklist
function cancelarNovoChecklist() {
    document.getElementById('formNovoChecklist').reset();
    document.getElementById('checklistItens').innerHTML = '';
    checklistItemCounter = 0;
    checklistEditandoId = null; // Resetar modo de edição
    atualizarTituloFormularioChecklist();
}

// Carregar lista de checklists
async function carregarChecklists() {
    try {
        const response = await fetch('/homologacoes/checklists/list');
        const result = await response.json();
        
        const container = document.getElementById('listaChecklists');
        
        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map(checklist => `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg text-gray-900">${checklist.titulo}</h3>
                            ${checklist.descricao ? `<p class="text-sm text-gray-600 mt-1">${checklist.descricao}</p>` : ''}
                            <p class="text-xs text-gray-500 mt-2">
                                ${checklist.total_itens} itens • 
                                Criado em ${new Date(checklist.criado_em).toLocaleDateString('pt-BR')}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editarChecklist(${checklist.id})" 
                                    class="px-3 py-1 text-sm bg-amber-100 text-amber-700 rounded hover:bg-amber-200">
                                ✏️ Editar
                            </button>
                            <button onclick="visualizarChecklist(${checklist.id})" 
                                    class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                👁️ Ver
                            </button>
                            <button onclick="excluirChecklist(${checklist.id})" 
                                    class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg mb-2">📋 Nenhum checklist cadastrado</p>
                    <p class="text-sm">Clique em "Novo Checklist" para criar o primeiro</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        document.getElementById('listaChecklists').innerHTML = '<p class="text-center text-red-500">Erro ao carregar checklists</p>';
    }
}

// Visualizar detalhes do checklist
async function visualizarChecklist(id) {
    try {
        const response = await fetch(`/homologacoes/checklists/${id}`);
        const result = await response.json();
        
        if (result.success) {
            const checklist = result.data;
            alert(`📋 ${checklist.titulo}\n\nItens:\n${checklist.itens.map((item, i) => `${i+1}. ${item.titulo} (${item.tipo_resposta})`).join('\n')}`);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

// Editar checklist existente
async function editarChecklist(id) {
    try {
        const response = await fetch(`/homologacoes/checklists/${id}`);
        const result = await response.json();
        
        if (result.success) {
            const checklist = result.data;
            
            // Definir modo de edição
            checklistEditandoId = id;
            
            // Preencher campos do formulário
            document.getElementById('checklistTitulo').value = checklist.titulo || '';
            document.getElementById('checklistDescricao').value = checklist.descricao || '';
            
            // Limpar itens existentes
            document.getElementById('checklistItens').innerHTML = '';
            checklistItemCounter = 0;
            
            // Adicionar itens do checklist
            if (checklist.itens && checklist.itens.length > 0) {
                checklist.itens.forEach(item => {
                    adicionarItemChecklist();
                    const index = checklistItemCounter - 1;
                    document.getElementById(`checklistItemTitulo${index}`).value = item.titulo || '';
                    document.getElementById(`checklistItemTipo${index}`).value = item.tipo_resposta || 'sim_nao';
                });
            } else {
                // Adicionar pelo menos um item vazio
                adicionarItemChecklist();
            }
            
            // Atualizar interface
            atualizarTituloFormularioChecklist();
            switchChecklistTab('novo'); // Ir para aba do formulário
        } else {
            alert('❌ Erro ao carregar checklist para edição');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao carregar checklist');
    }
}

// Excluir checklist
async function excluirChecklist(id) {
    if (!confirm('⚠️ Deseja realmente excluir este checklist?')) return;
    
    try {
        const response = await fetch(`/homologacoes/checklists/${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Checklist excluído!');
            carregarChecklists();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao excluir checklist');
    }
}

// ===== CHECKLIST NO CARD ===== 

// Carregar checklists disponíveis no dropdown do card
async function carregarChecklistsNoCard(homologacaoId) {
    try {
        const response = await fetch('/homologacoes/checklists/list');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const select = document.getElementById(`selectChecklist${homologacaoId}`);
            if (select) {
                result.data.forEach(checklist => {
                    const option = document.createElement('option');
                    option.value = checklist.id;
                    option.textContent = checklist.titulo;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar checklists:', error);
    }
}

// Carregar checklists disponíveis no dropdown
async function carregarChecklistsDropdown(homologacaoId) {
    try {
        const response = await fetch('/homologacoes/checklists/list');
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById(`selectChecklist${homologacaoId}`);
            if (select) {
                result.data.forEach(checklist => {
                    const option = document.createElement('option');
                    option.value = checklist.id;
                    option.textContent = `${checklist.titulo} (${checklist.total_itens} itens)`;
                    select.appendChild(option);
                });
            }
        }
    } catch (error) {
        console.error('Erro ao carregar checklists:', error);
    }
}

// Carregar itens do checklist selecionado
async function carregarItensChecklist(homologacaoId, checklistId) {
    if (!checklistId) {
        document.getElementById(`itensChecklist${homologacaoId}`).innerHTML = '';
        return;
    }
    
    try {
        const response = await fetch(`/homologacoes/checklists/${checklistId}`);
        const result = await response.json();
        
        if (result.success) {
            const checklist = result.data;
            renderizarItensChecklist(homologacaoId, checklistId, checklist.itens);
        }
    } catch (error) {
        console.error('Erro ao carregar itens:', error);
    }
}

// Renderizar itens do checklist
function renderizarItensChecklist(homologacaoId, checklistId, itens) {
    const container = document.getElementById(`itensChecklist${homologacaoId}`);
    
    const html = `
        <div class="space-y-3 mt-4">
            <h4 class="font-semibold text-sm text-purple-800 border-b pb-2">Preencha os itens abaixo:</h4>
            ${itens.map(item => renderizarItem(homologacaoId, checklistId, item)).join('')}
            <button onclick="salvarRespostasChecklist(${homologacaoId}, ${checklistId})" 
                    class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 mt-4">
                💾 Salvar Checklist
            </button>
        </div>
    `;
    
    container.innerHTML = html;
}

// Renderizar um item baseado no tipo
function renderizarItem(homologacaoId, checklistId, item) {
    const inputId = `item_${homologacaoId}_${item.id}`;
    
    switch (item.tipo_resposta) {
        case 'checkbox':
            return `
                <div class="flex items-center gap-2 p-3 bg-white rounded-lg border">
                    <input type="checkbox" id="${inputId}" class="w-4 h-4 text-purple-600">
                    <label for="${inputId}" class="text-sm flex-1">${item.titulo}</label>
                </div>
            `;
        
        case 'sim_nao':
            return `
                <div class="p-3 bg-white rounded-lg border">
                    <label class="text-sm font-medium mb-2 block">${item.titulo}</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="${inputId}" value="sim" class="text-purple-600">
                            <span class="text-sm">✓ Sim</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="${inputId}" value="nao" class="text-purple-600">
                            <span class="text-sm">✗ Não</span>
                        </label>
                    </div>
                </div>
            `;
        
        case 'texto':
            return `
                <div class="p-3 bg-white rounded-lg border">
                    <label for="${inputId}" class="text-sm font-medium mb-2 block">${item.titulo}</label>
                    <textarea id="${inputId}" rows="2" 
                              class="w-full px-3 py-2 border rounded-lg text-sm"
                              placeholder="Digite sua resposta..."></textarea>
                </div>
            `;
        
        case 'numero':
            return `
                <div class="p-3 bg-white rounded-lg border">
                    <label for="${inputId}" class="text-sm font-medium mb-2 block">${item.titulo}</label>
                    <input type="number" id="${inputId}" 
                           class="w-full px-3 py-2 border rounded-lg text-sm"
                           placeholder="Digite um número...">
                </div>
            `;
        
        default:
            return '';
    }
}

// Salvar respostas do checklist
async function salvarRespostasChecklist(homologacaoId, checklistId) {
    try {
        // Buscar checklist para pegar os itens
        const responseChecklist = await fetch(`/homologacoes/checklists/${checklistId}`);
        const resultChecklist = await responseChecklist.json();
        
        if (!resultChecklist.success) {
            alert('Erro ao buscar checklist');
            return;
        }
        
        const itens = resultChecklist.data.itens;
        const respostas = [];
        
        // Coletar respostas
        itens.forEach(item => {
            const inputId = `item_${homologacaoId}_${item.id}`;
            let resposta = '';
            let concluido = false;
            
            switch (item.tipo_resposta) {
                case 'checkbox':
                    const checkbox = document.getElementById(inputId);
                    concluido = checkbox?.checked || false;
                    resposta = concluido ? 'checked' : 'unchecked';
                    break;
                
                case 'sim_nao':
                    const radio = document.querySelector(`input[name="${inputId}"]:checked`);
                    resposta = radio?.value || '';
                    concluido = !!resposta;
                    break;
                
                case 'texto':
                case 'numero':
                    const input = document.getElementById(inputId);
                    resposta = input?.value || '';
                    concluido = !!resposta;
                    break;
            }
            
            respostas.push({
                item_id: item.id,
                resposta: resposta,
                concluido: concluido
            });
        });
        
        // Salvar via API
        const response = await fetch('/homologacoes/checklists/salvar-respostas', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                homologacao_id: homologacaoId,
                checklist_id: checklistId,
                respostas: respostas
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Checklist salvo com sucesso!');
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('❌ Erro ao salvar checklist');
    }
}

// Carregar e exibir respostas salvas do checklist (modo visualização)
async function carregarRespostasChecklist(homologacaoId, checklistId) {
    try {
        // Buscar informações do checklist
        const responseChecklist = await fetch(`/homologacoes/checklists/${checklistId}`);
        const resultChecklist = await responseChecklist.json();
        
        if (!resultChecklist.success) {
            console.error('Erro ao buscar checklist');
            return;
        }
        
        const checklist = resultChecklist.data;
        const container = document.getElementById(`checklistRespostas${homologacaoId}`);
        
        // Buscar respostas salvas
        const responseRespostas = await fetch(`/homologacoes/${homologacaoId}/checklist-respostas`);
        const resultRespostas = await responseRespostas.json();
        
        const respostas = resultRespostas.success ? resultRespostas.data : [];
        
        // Criar mapa de respostas por item_id
        const respostasMap = {};
        respostas.forEach(r => {
            respostasMap[r.item_id] = r;
        });
        
        // Renderizar respostas
        let html = `
            <div class="bg-white p-4 rounded-lg border border-purple-200">
                <h4 class="font-semibold text-purple-800 mb-3">${checklist.titulo}</h4>
                ${checklist.descricao ? `<p class="text-sm text-gray-600 mb-4">${checklist.descricao}</p>` : ''}
                
                <div class="space-y-3">
                    ${checklist.itens.map(item => {
                        const resposta = respostasMap[item.id];
                        const respostaTexto = resposta ? resposta.resposta : '-';
                        const concluido = resposta?.concluido;
                        
                        let respostaFormatada = respostaTexto;
                        if (item.tipo_resposta === 'checkbox') {
                            respostaFormatada = respostaTexto === 'checked' ? '✅ Sim' : '❌ Não';
                        } else if (item.tipo_resposta === 'sim_nao') {
                            respostaFormatada = respostaTexto === 'sim' ? '✅ Sim' : respostaTexto === 'nao' ? '❌ Não' : '-';
                        }
                        
                        return `
                            <div class="flex items-start justify-between p-3 bg-gray-50 rounded">
                                <div class="flex-1">
                                    <div class="font-medium text-sm">${item.titulo}</div>
                                    <div class="text-xs text-gray-500 mt-1">Tipo: ${
                                        item.tipo_resposta === 'checkbox' ? 'Checkbox' :
                                        item.tipo_resposta === 'sim_nao' ? 'Sim/Não' :
                                        item.tipo_resposta === 'texto' ? 'Texto' : 'Número'
                                    }</div>
                                </div>
                                <div class="ml-4 text-right">
                                    <div class="font-semibold ${concluido ? 'text-green-600' : 'text-gray-500'}">
                                        ${respostaFormatada}
                                    </div>
                                    ${concluido ? '<div class="text-xs text-green-600 mt-1">✓ Concluído</div>' : ''}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    } catch (error) {
        console.error('Erro ao carregar respostas:', error);
    }
}

// ==================== NAVEGAÇÃO ENTRE ETAPAS ====================

// Mapeamento de status e ordem
const statusFlow = [
    'aguardando_recebimento',
    'recebido',
    'em_analise',
    'em_homologacao',
    'aprovado',
    'reprovado'
];

const statusNames = {
    'aguardando_recebimento': 'Aguardando Recebimento',
    'recebido': 'Recebido',
    'em_analise': 'Em Análise',
    'em_homologacao': 'Em Homologação',
    'aprovado': 'Aprovado',
    'reprovado': 'Reprovado'
};

// Mover para próxima etapa
async function moverParaProximaEtapa(homologacaoId, statusAtual) {
    const currentIndex = statusFlow.indexOf(statusAtual);
    if (currentIndex === -1 || currentIndex >= statusFlow.length - 1) {
        alert('❌ Não há próxima etapa disponível');
        return;
    }
    
    const proximoStatus = statusFlow[currentIndex + 1];
    await mudarStatus(homologacaoId, proximoStatus, '➡️');
}

// Mover para etapa anterior
async function moverParaEtapaAnterior(homologacaoId, statusAtual) {
    const currentIndex = statusFlow.indexOf(statusAtual);
    if (currentIndex <= 0) {
        alert('❌ Não há etapa anterior disponível');
        return;
    }
    
    const statusAnterior = statusFlow[currentIndex - 1];
    await mudarStatus(homologacaoId, statusAnterior, '⬅️');
}

// Mini-modal de Observações para mudança de etapa
function abrirModalObservacao(homologacaoId, novoStatus, direcao) {
    // Remover modal anterior se existir
    const existente = document.getElementById('modalObsEtapa');
    if (existente) existente.remove();

    const overlay = document.createElement('div');
    overlay.id = 'modalObsEtapa';
    overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:16px;';

    const statusLabel = statusNames[novoStatus] || novoStatus;
    overlay.innerHTML = `
        <div style="background:#fff;border-radius:12px;padding:24px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,0.3);" onclick="event.stopPropagation()">
            <h3 style="font-size:18px;font-weight:700;color:#1e293b;margin:0 0 6px">${direcao} Mover Etapa</h3>
            <p style="font-size:13px;color:#475569;margin:0 0 16px">Mover para: <strong style="color:#2563eb">${statusLabel}</strong></p>
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px">Observações <span style="color:#94a3b8;font-weight:400">(opcional)</span></label>
            <textarea id="obsEtapaTexto" rows="3" placeholder="Descreva o motivo da mudança de etapa..." style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;resize:vertical;box-sizing:border-box;outline:none;transition:border-color .15s" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
            <div style="display:flex;gap:10px;margin-top:16px">
                <button id="btnConfirmarEtapa" style="flex:1;padding:10px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    ✅ Confirmar
                </button>
                <button onclick="document.getElementById('modalObsEtapa').remove()" style="padding:10px 20px;background:#f1f5f9;color:#475569;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    Cancelar
                </button>
            </div>
        </div>
    `;

    // Fechar ao clicar fora
    overlay.addEventListener('click', () => overlay.remove());

    document.body.appendChild(overlay);
    setTimeout(() => document.getElementById('obsEtapaTexto')?.focus(), 100);

    // Botão confirmar
    document.getElementById('btnConfirmarEtapa').addEventListener('click', async () => {
        const observacao = document.getElementById('obsEtapaTexto')?.value.trim() || '';
        overlay.remove();
        await executarMudancaStatus(homologacaoId, novoStatus, observacao);
    });
}

// Mudar status com observação
async function executarMudancaStatus(homologacaoId, novoStatus, observacao) {
    try {
        const response = await fetch(`/homologacoes/${homologacaoId}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: novoStatus, observacao: observacao })
        });
        const result = await response.json();
        if (result.success) {
            alert('✅ Status atualizado com sucesso!');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Erro ao atualizar status'));
        }
    } catch (error) {
        console.error('Erro ao mudar status:', error);
        alert('❌ Erro ao atualizar status');
    }
}

// Função para mudar status (chamada pelas setas)
function mudarStatus(homologacaoId, novoStatus, direcao) {
    abrirModalObservacao(homologacaoId, novoStatus, direcao);
}

// ==================== DRAG & DROP ====================

let draggedCard = null;

// Inicializar drag & drop após o carregamento da página
document.addEventListener('DOMContentLoaded', function() {
    inicializarDragAndDrop();
    adicionarBotoesNavegacao();
});

function inicializarDragAndDrop() {
    // Selecionar todos os cards
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-column');
    
    // Adicionar eventos de drag aos cards
    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });
    
    // Adicionar eventos de drop às colunas
    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('dragleave', handleDragLeave);
        column.addEventListener('drop', handleDrop);
    });
}

function handleDragStart(e) {
    draggedCard = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    
    // Remover highlight de todas as colunas
    document.querySelectorAll('.kanban-column').forEach(col => {
        col.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('drag-over');
    
    return false;
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    this.classList.remove('drag-over');
    
    if (draggedCard !== this) {
        const novoStatus = this.getAttribute('data-status');
        const homologacaoId = draggedCard.getAttribute('data-id');
        const statusAtual = draggedCard.getAttribute('data-status');
        
        if (novoStatus && homologacaoId && novoStatus !== statusAtual) {
            abrirModalObservacao(homologacaoId, novoStatus, '↔️');
        }
    }
    
    return false;
}

async function atualizarStatusViaApi(homologacaoId, novoStatus, observacao) {
    try {
        const response = await fetch(`/homologacoes/${homologacaoId}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: novoStatus, observacao: observacao || '' })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Status atualizado com sucesso!');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Erro ao atualizar status'));
        }
    } catch (error) {
        console.error('Erro ao atualizar status:', error);
        alert('❌ Erro ao atualizar status');
    }
}

// Adicionar botões de navegação dinamicamente a todos os cards
function adicionarBotoesNavegacao() {
    const cards = document.querySelectorAll('.kanban-card');
    
    cards.forEach(card => {
        // Verificar se já tem botões
        if (card.querySelector('.card-nav-buttons')) {
            return;
        }
        
        const homologacaoId = card.getAttribute('data-id');
        const statusAtual = card.getAttribute('data-status');
        const currentIndex = statusFlow.indexOf(statusAtual);
        
        // Criar container de botões
        const navButtons = document.createElement('div');
        navButtons.className = 'card-nav-buttons';
        
        // Botão voltar
        const btnAnterior = document.createElement('button');
        btnAnterior.type = 'button';
        btnAnterior.className = 'card-nav-btn';
        btnAnterior.innerHTML = '⬅️';
        btnAnterior.title = 'Retornar para etapa anterior';
        btnAnterior.disabled = currentIndex <= 0;
        btnAnterior.onclick = (e) => {
            e.stopPropagation();
            moverParaEtapaAnterior(homologacaoId, statusAtual);
        };
        
        // Botão avançar
        const btnProximo = document.createElement('button');
        btnProximo.type = 'button';
        btnProximo.className = 'card-nav-btn';
        btnProximo.innerHTML = '➡️';
        btnProximo.title = 'Enviar para próxima etapa';
        btnProximo.disabled = currentIndex >= statusFlow.length - 1;
        btnProximo.onclick = (e) => {
            e.stopPropagation();
            moverParaProximaEtapa(homologacaoId, statusAtual);
        };
        
        navButtons.appendChild(btnAnterior);
        navButtons.appendChild(btnProximo);
        
        // Adicionar apenas o botão Ver Relatório
        const btnRelatorio = document.createElement('button');
        btnRelatorio.type = 'button';
        btnRelatorio.className = 'card-nav-btn bg-green-500 hover:bg-green-600';
        btnRelatorio.innerHTML = '📊';
        btnRelatorio.title = 'Ver relatório completo';
        btnRelatorio.onclick = (e) => {
            e.stopPropagation();
            abrirRelatorioCompleto(homologacaoId);
        };
        
        navButtons.appendChild(btnRelatorio);
        
        card.appendChild(navButtons);
        
        // Adicionar margem inferior no conteúdo para os botões
        const lastInfo = card.querySelector('.flex.items-center.justify-between.text-xs');
        if (lastInfo) {
            lastInfo.classList.add('mb-6');
        }
    });
}

// ===== SISTEMA DE LOG DETALHADO =====

/**
 * Abrir modal de logs
 */
async function abrirModalLogs(homologacaoId) {
    try {
        const response = await fetch(`/homologacoes/${homologacaoId}/logs`);
        const data = await response.json();
        
        if (data.success) {
            mostrarModalLogs(data.homologacao, data.logs);
        } else {
            alert('Erro ao carregar logs: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar logs: ' + error.message);
    }
}

/**
 * Mostrar modal com logs
 */
function mostrarModalLogs(homologacao, logs) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold">Histórico de Logs - ${homologacao.cod_referencia}</h2>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[70vh]">
                ${logs.length > 0 ? formatarLogsParaModal(logs) : '<p class="text-center text-gray-500">Nenhum log encontrado</p>'}
            </div>
            <div class="flex justify-end p-6 border-t">
                <button onclick="exportarLogs(${homologacao.id})" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">
                    Exportar Logs
                </button>
                <button onclick="this.closest('.fixed').remove()" class="bg-gray-500 text-white px-4 py-2 rounded">
                    Fechar
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

/**
 * Formatar logs para exibição no modal
 */
function formatarLogsParaModal(logs) {
    return logs.map((log, index) => {
        const dataFormatada = new Date(log.data_acao).toLocaleString('pt-BR');
        return `
            <div class="border-l-4 border-blue-500 pl-4 mb-4">
                <div class="flex justify-between items-start">
                    <h4 class="font-semibold">${log.acao_realizada || 'Ação não especificada'}</h4>
                    <span class="text-sm text-gray-500">${dataFormatada}</span>
                </div>
                <p class="text-sm text-gray-600">Por: ${log.usuario_nome || 'Usuário não identificado'}</p>
                ${log.observacoes ? `<p class="mt-2 text-sm">${log.observacoes}</p>` : ''}
                ${log.detalhes_acao ? `<p class="mt-1 text-xs text-gray-500">${log.detalhes_acao}</p>` : ''}
            </div>
        `;
    }).join('');
}

/**
 * Abrir relatório completo
 */
function abrirRelatorioCompleto(homologacaoId) {
    window.open(`/homologacoes/${homologacaoId}/relatorio`, '_blank');
}

/**
 * Exportar logs
 */
function exportarLogs(homologacaoId) {
    window.open(`/homologacoes/${homologacaoId}/logs/export`, '_blank');
}

</script>

<?php // Fim da view de Homologações ?>
