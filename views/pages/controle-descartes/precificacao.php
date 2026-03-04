<?php
if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$canEdit = \App\Services\PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'precificacao_coleta_descartes', 'edit');
$canDelete = \App\Services\PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'precificacao_coleta_descartes', 'delete');
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Precificação de Coleta de Descartes</h1>
            <p class="mt-1 text-gray-600">Registre data e valor por coleta e acompanhe o total.</p>
        </div>
        <?php if ($canEdit): ?>
        <button onclick="abrirModalNovaColeta()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
            + Nova Coleta
        </button>
        <?php endif; ?>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="filtro-mes" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por mês</label>
                <input type="month" id="filtro-mes" class="w-full border border-gray-300 rounded-md px-3 py-2">
            </div>
            <div class="md:col-span-3 flex gap-2 flex-wrap">
                <button onclick="carregarColetas()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Buscar</button>
                <button onclick="limparFiltroMes()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-md">Limpar</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                <div class="text-xs text-blue-700 uppercase font-semibold">Coletas no período</div>
                <div id="kpi-quantidade" class="text-2xl font-bold text-blue-900">0</div>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-lg p-3">
                <div class="text-xs text-green-700 uppercase font-semibold">Total do período</div>
                <div id="kpi-periodo" class="text-2xl font-bold text-green-900">R$ 0,00</div>
            </div>
            <div class="bg-purple-50 border border-purple-100 rounded-lg p-3">
                <div class="text-xs text-purple-700 uppercase font-semibold">Total geral</div>
                <div id="kpi-geral" class="text-2xl font-bold text-purple-900">R$ 0,00</div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Lançamentos de Coleta</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Data da Coleta</th>
                        <th class="px-4 py-3 text-left">Valor</th>
                        <th class="px-4 py-3 text-left">Criado em</th>
                        <th class="px-4 py-3 text-left">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-coletas" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="sem-registros" class="hidden p-6 text-center text-gray-500">Nenhum registro encontrado.</div>
    </div>
</div>

<div id="modal-coleta" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg w-full max-w-md p-6">
        <h3 id="modal-titulo" class="text-lg font-semibold text-gray-900 mb-4">Nova Coleta</h3>
        <input type="hidden" id="coleta-id">
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data da Coleta</label>
                <input type="date" id="coleta-data" class="w-full border border-gray-300 rounded-md px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                <input type="text" id="coleta-valor" placeholder="Ex: 1250,90" class="w-full border border-gray-300 rounded-md px-3 py-2">
            </div>
        </div>
        <div class="mt-5 flex justify-end gap-2">
            <button onclick="fecharModalColeta()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md">Cancelar</button>
            <button onclick="salvarColeta()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Salvar</button>
        </div>
    </div>
</div>

<script>
const CAN_EDIT = <?= $canEdit ? 'true' : 'false' ?>;
const CAN_DELETE = <?= $canDelete ? 'true' : 'false' ?>;

function formatBRL(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value || 0));
}

function formatDateBR(dateIso) {
    if (!dateIso) return '-';
    const d = new Date(dateIso + 'T00:00:00');
    return d.toLocaleDateString('pt-BR');
}

function formatDateTimeBR(dateTime) {
    if (!dateTime) return '-';
    const d = new Date(dateTime.replace(' ', 'T'));
    return d.toLocaleString('pt-BR');
}

function limparFiltroMes() {
    document.getElementById('filtro-mes').value = '';
    carregarColetas();
}

function abrirModalNovaColeta() {
    document.getElementById('modal-titulo').textContent = 'Nova Coleta';
    document.getElementById('coleta-id').value = '';
    document.getElementById('coleta-data').value = '';
    document.getElementById('coleta-valor').value = '';
    document.getElementById('modal-coleta').classList.remove('hidden');
    document.getElementById('modal-coleta').classList.add('flex');
}

function abrirModalEditar(id, data, valor) {
    if (!CAN_EDIT) return;
    document.getElementById('modal-titulo').textContent = 'Editar Coleta';
    document.getElementById('coleta-id').value = id;
    document.getElementById('coleta-data').value = data;
    document.getElementById('coleta-valor').value = String(valor ?? '').replace('.', ',');
    document.getElementById('modal-coleta').classList.remove('hidden');
    document.getElementById('modal-coleta').classList.add('flex');
}

function fecharModalColeta() {
    document.getElementById('modal-coleta').classList.add('hidden');
    document.getElementById('modal-coleta').classList.remove('flex');
}

async function salvarColeta() {
    const id = document.getElementById('coleta-id').value;
    const data = document.getElementById('coleta-data').value;
    const valor = document.getElementById('coleta-valor').value;

    if (!data || !valor) {
        alert('Preencha data e valor.');
        return;
    }

    const url = id ? '/precificacao-coleta-descartes/update' : '/precificacao-coleta-descartes/create';
    const body = new URLSearchParams();
    if (id) body.append('id', id);
    body.append('data_coleta', data);
    body.append('valor_coleta', valor);

    const resp = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    });
    const json = await resp.json();
    if (!json.success) {
        alert(json.message || 'Erro ao salvar coleta.');
        return;
    }

    fecharModalColeta();
    carregarColetas();
}

async function excluirColeta(id) {
    if (!CAN_DELETE) return;
    if (!confirm('Deseja excluir esta coleta?')) return;

    const body = new URLSearchParams();
    body.append('id', String(id));

    const resp = await fetch('/precificacao-coleta-descartes/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    });
    const json = await resp.json();
    if (!json.success) {
        alert(json.message || 'Erro ao excluir coleta.');
        return;
    }

    carregarColetas();
}

async function carregarColetas() {
    const mes = document.getElementById('filtro-mes').value;
    const qs = mes ? ('?mes=' + encodeURIComponent(mes)) : '';

    const resp = await fetch('/precificacao-coleta-descartes/list' + qs);
    const json = await resp.json();

    if (!json.success) {
        alert(json.message || 'Erro ao carregar dados.');
        return;
    }

    document.getElementById('kpi-quantidade').textContent = String(json.totais?.quantidade ?? 0);
    document.getElementById('kpi-periodo').textContent = formatBRL(json.totais?.periodo ?? 0);
    document.getElementById('kpi-geral').textContent = formatBRL(json.totais?.geral ?? 0);

    const tbody = document.getElementById('tabela-coletas');
    const semRegistros = document.getElementById('sem-registros');

    if (!Array.isArray(json.data) || json.data.length === 0) {
        tbody.innerHTML = '';
        semRegistros.classList.remove('hidden');
        return;
    }

    semRegistros.classList.add('hidden');

    tbody.innerHTML = json.data.map(row => {
        const editBtn = CAN_EDIT
            ? `<button onclick="abrirModalEditar(${row.id}, '${row.data_coleta}', '${row.valor_coleta}')" class="px-2 py-1 text-xs bg-amber-100 text-amber-800 rounded">Editar</button>`
            : '';
        const deleteBtn = CAN_DELETE
            ? `<button onclick="excluirColeta(${row.id})" class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Excluir</button>`
            : '';

        return `
            <tr>
                <td class="px-4 py-3">${formatDateBR(row.data_coleta)}</td>
                <td class="px-4 py-3 font-semibold text-gray-900">${formatBRL(row.valor_coleta)}</td>
                <td class="px-4 py-3 text-gray-600">${formatDateTimeBR(row.created_at)}</td>
                <td class="px-4 py-3">
                    <div class="flex gap-2">${editBtn}${deleteBtn}</div>
                </td>
            </tr>
        `;
    }).join('');
}

document.addEventListener('DOMContentLoaded', carregarColetas);
</script>
