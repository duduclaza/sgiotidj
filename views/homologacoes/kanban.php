<?php
$pageTitle = 'Homologações - Kanban';
require_once __DIR__ . '/../partials/header.php';

$motivosHomologacao = [
    'novo_item' => 'Novo Item',
    'troca_fornecedor' => 'Troca de Fornecedor',
    'atualizacao_tecnica' => 'Atualização Técnica',
    'melhoria_custo' => 'Melhoria de Custo',
    'descontinuacao' => 'Descontinuação de Produto',
    'novo_fornecedor' => 'Novo Fornecedor'
];

$statusLabels = [
    'pendente_recebimento' => 'Pendente Recebimento',
    'em_analise' => 'Em Análise',
    'aprovado' => 'Aprovado',
    'reprovado' => 'Reprovado'
];

$statusColors = [
    'pendente_recebimento' => 'bg-yellow-100 border-yellow-300',
    'em_analise' => 'bg-blue-100 border-blue-300',
    'aprovado' => 'bg-green-100 border-green-300',
    'reprovado' => 'bg-red-100 border-red-300'
];
?>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📋 Homologações</h1>
            <p class="text-gray-600 mt-1">Gerenciamento de homologações de produtos e serviços</p>
        </div>
    </div>

    <!-- Faixa de atualização do módulo -->
    <div id="homologUpdateBanner" class="mb-6 rounded-lg p-3 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-3 h-3 rounded-full bg-yellow-500 animate-ping"></div>
            <div>
                <strong>Atualização em andamento:</strong>
                <span class="block text-sm">Este módulo está sendo atualizado — podem ocorrer bugs ou instabilidades.</span>
            </div>
        </div>
        <button id="closeBannerBtn" class="text-yellow-800 hover:text-yellow-900">Fechar ✖</button>
    </div>

    <?php if ($canCreate): ?>
    <!-- Formulário Inline de Criação -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">✨ Solicitação de Homologação</h2>
            <button type="button" id="toggleFormBtn" class="text-blue-600 hover:text-blue-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <form id="formHomologacao" class="space-y-4" style="display: block;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Código do Produto -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Código do Produto/Serviço <span class="text-red-500">*</span>
                        <span class="relative group">
                            <svg class="inline w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="invisible group-hover:visible absolute left-0 top-6 bg-gray-800 text-white text-xs rounded py-1 px-2 w-48 z-10">
                                Coloque o código de referência cadastrado no seu ERP
                            </span>
                        </span>
                    </label>
                    <input type="text" name="codigo_produto" id="codigo_produto" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ex: PROD-12345">
                </div>

                <!-- Fornecedor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Fornecedor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="fornecedor" id="fornecedor" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Nome do fornecedor">
                </div>
            </div>

            <!-- Descrição -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Descrição <span class="text-red-500">*</span>
                </label>
                <textarea name="descricao" id="descricao" required rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Descreva o produto/serviço que será homologado"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Motivo da Homologação -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Motivo da Homologação <span class="text-red-500">*</span>
                    </label>
                    <select name="motivo_homologacao" id="motivo_homologacao" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione...</option>
                        <?php foreach ($motivosHomologacao as $value => $label): ?>
                            <option value="<?= $value ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Responsáveis -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Responsável(is) pela Homologação <span class="text-red-500">*</span>
                    </label>
                    <select name="responsaveis[]" id="responsaveis" multiple required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            style="height: 100px;">
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= $usuario['id'] ?>"><?= htmlspecialchars($usuario['name']) ?> (<?= htmlspecialchars($usuario['department'] ?? 'N/A') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Segure Ctrl/Cmd para selecionar múltiplos</p>
                </div>
            </div>

            <!-- Avisar Logística -->
            <div class="flex items-center">
                <input type="checkbox" name="avisar_logistica" id="avisar_logistica" value="1"
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                <label for="avisar_logistica" class="ml-2 text-sm text-gray-700">
                    🚚 Deseja avisar a logística que esta homologação está por chegar?
                </label>
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" id="btnCancelar"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="btnSalvar"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Solicitar Homologação</span>
                    <span class="ml-3 renovation-badge" title="Módulo em reformulação">🚧 Em reforma</span>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Kanban Board -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <?php foreach ($statusLabels as $status => $label): ?>
        <div class="bg-gray-100 rounded-lg p-4 min-h-[600px]">
            <!-- Cabeçalho da Coluna -->
            <div class="flex items-center justify-between mb-4 pb-3 border-b-2 border-gray-300">
                <h3 class="font-semibold text-gray-900 text-lg"><?= $label ?></h3>
                <span class="bg-gray-600 text-white text-xs font-bold rounded-full px-2 py-1">
                    <?= count($homologacoes[$status] ?? []) ?>
                </span>
            </div>

            <!-- Cartões -->
            <div class="space-y-3 kanban-column" data-status="<?= $status ?>">
                <?php if (isset($homologacoes[$status]) && count($homologacoes[$status]) > 0): ?>
                    <?php foreach ($homologacoes[$status] as $item): ?>
                        <div class="kanban-card bg-white rounded-lg shadow-sm border-l-4 <?= $statusColors[$status] ?> p-4 cursor-pointer hover:shadow-md transition-shadow"
                             data-id="<?= $item['id'] ?>"
                             data-status="<?= $item['status'] ?>"
                             onclick="verDetalhes(<?= $item['id'] ?>)">
                            
                            <!-- Código do Produto -->
                            <div class="flex items-start justify-between mb-2">
                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">
                                    <?= htmlspecialchars($item['codigo_produto']) ?>
                                </span>
                                <button onclick="event.stopPropagation(); openCardMenu(<?= $item['id'] ?>)" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Descrição -->
                            <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                <?= htmlspecialchars($item['descricao']) ?>
                            </h4>

                            <!-- Fornecedor -->
                            <p class="text-sm text-gray-600 mb-2">
                                🏢 <?= htmlspecialchars($item['fornecedor']) ?>
                            </p>

                            <!-- Motivo -->
                            <div class="mb-3">
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                    <?= $motivosHomologacao[$item['motivo_homologacao']] ?? $item['motivo_homologacao'] ?>
                                </span>
                            </div>

                            <!-- Footer do Card -->
                            <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t">
                                <div class="flex items-center space-x-2">
                                    <!-- Responsáveis -->
                                    <div class="flex items-center" title="Responsáveis: <?= htmlspecialchars($item['responsaveis_nomes'] ?? 'N/A') ?>">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                                        </svg>
                                        <span><?= substr_count($item['responsaveis_nomes'] ?? '', ',') + 1 ?></span>
                                    </div>

                                    <!-- Anexos -->
                                    <?php if ($item['total_anexos'] > 0): ?>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span><?= $item['total_anexos'] ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Aviso Logística -->
                                    <?php if ($item['avisar_logistica']): ?>
                                    <div class="flex items-center text-orange-600" title="Logística notificada">
                                        🚚
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Data -->
                                <span><?= date('d/m/Y', strtotime($item['data_solicitacao'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-gray-400 py-8">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p>Nenhum cartão</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal de Detalhes -->
<div id="modalDetalhes" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Detalhes da Homologação</h3>
            <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="modalContent" class="p-6">
            <!-- Conteúdo carregado via AJAX -->
        </div>
    </div>
</div>

<script>
// Toggle do formulário
document.getElementById('toggleFormBtn')?.addEventListener('click', function() {
    const form = document.getElementById('formHomologacao');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('btnCancelar')?.addEventListener('click', function() {
    document.getElementById('formHomologacao').reset();
    document.getElementById('formHomologacao').style.display = 'none';
});

// Submit do formulário
document.getElementById('formHomologacao')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btnSalvar = document.getElementById('btnSalvar');
    btnSalvar.disabled = true;
    btnSalvar.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Salvando...';
    
    try {
        const formData = new FormData(this);
        const response = await fetch('/homologacoes/store', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Homologação criada com sucesso!');
            location.reload();
        } else {
            alert('❌ ' + result.message);
        }
    } catch (error) {
        alert('❌ Erro ao criar homologação: ' + error.message);
    } finally {
        btnSalvar.disabled = false;
        btnSalvar.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Solicitar Homologação';
    }
});

// Ver detalhes
async function verDetalhes(id) {
    document.getElementById('modalDetalhes').classList.remove('hidden');
    document.getElementById('modalContent').innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div><p class="mt-4 text-gray-600">Carregando...</p></div>';
    
    try {
        const response = await fetch(`/homologacoes/${id}/details`);
        const result = await response.json();
        
        if (result.success) {
            mostrarDetalhes(result);
        } else {
            document.getElementById('modalContent').innerHTML = '<p class="text-red-600">Erro ao carregar detalhes</p>';
        }
    } catch (error) {
        document.getElementById('modalContent').innerHTML = '<p class="text-red-600">Erro: ' + error.message + '</p>';
    }
}

function mostrarDetalhes(data) {
    const h = data.homologacao;
    const motivosLabels = <?= json_encode($motivosHomologacao) ?>;
    
    let html = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Código do Produto</label>
                    <p class="font-mono text-lg">${h.codigo_produto}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Data Solicitação</label>
                    <p>${new Date(h.data_solicitacao).toLocaleString('pt-BR')}</p>
                </div>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-500">Descrição</label>
                <p class="text-gray-900">${h.descricao}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-500">Fornecedor</label>
                    <p>${h.fornecedor}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Motivo</label>
                    <p>${motivosLabels[h.motivo_homologacao] || h.motivo_homologacao}</p>
                </div>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-500">Responsáveis</label>
                <p>${h.responsaveis_nomes}</p>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-500">Criado por</label>
                <p>${h.criador_nome} (${h.criador_email})</p>
            </div>
            
            <div>
                <label class="text-sm font-medium text-gray-500">Avisar Logística</label>
                <p>${h.avisar_logistica ? '✅ Sim' : '❌ Não'}</p>
            </div>
        </div>
    `;
    
    document.getElementById('modalContent').innerHTML = html;
}

function fecharModal() {
    document.getElementById('modalDetalhes').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('modalDetalhes')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});

// Banner: fechar e persistir preferência
document.getElementById('closeBannerBtn')?.addEventListener('click', function() {
    const b = document.getElementById('homologUpdateBanner');
    if (b) {
        b.style.display = 'none';
        try { localStorage.setItem('homologBannerClosed', '1'); } catch (e) {}
    }
});

// Esconder banner se usuário já fechou antes
try {
    if (localStorage.getItem('homologBannerClosed') === '1') {
        const b = document.getElementById('homologUpdateBanner');
        if (b) b.style.display = 'none';
    }
} catch (e) {}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.kanban-card {
    transition: all 0.2s ease;
}

.kanban-card:hover {
    transform: translateY(-2px);
}

.renovation-badge {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(90deg, rgba(255,200,0,0.12), rgba(255,200,0,0.02));
    color: #92400e;
    padding: 0.15rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.85rem;
    animation: pulse 1.8s infinite;
}

@keyframes pulse {
    0% { transform: translateY(0); opacity: 1; }
    50% { transform: translateY(-2px); opacity: 0.85; }
    100% { transform: translateY(0); opacity: 1; }
}
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
