<?php
require_once __DIR__ . '/mock_data.php';

// Busca Usuário pelo ID
function getUserById($id) {
    if (!$id) return null;
    $usuarios = $_SESSION['mock_usuarios'] ?? [];
    foreach ($usuarios as $u) {
        if ($u['id'] == $id) return $u;
    }
    return null;
}

// Busca Homologação pelo ID
function getHomologacaoById($id) {
    $homologacoes = $_SESSION['mock_homologacoes'] ?? [];
    foreach ($homologacoes as $h) {
        if ($h['id'] == $id) return $h;
    }
    return null;
}

function getUsuarioLogado() {
    return getUserById($_SESSION['usuario_logado_id']);
}

// Labels formatadas para status
function getStatusLabel($status) {
    $labels = [
        'aguardando_chegada' => 'Aguardando Chegada',
        'item_recebido'      => 'Item Recebido - Aguardando Homologação',
        'em_homologacao'     => 'Em Homologação',
        'concluida'          => 'Concluída',
        'cancelada'          => 'Cancelada'
    ];
    return $labels[$status] ?? 'Desconhecido';
}

// Cor/Badge para Status (Tailwind)
function getBadgeClass($status) {
    $classes = [
        'aguardando_chegada' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        'item_recebido'      => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        'em_homologacao'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        'concluida'          => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        'cancelada'          => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400'
    ];
    return $classes[$status] ?? 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300';
}

// Cor da Borda / Border-color para Status (Tailwind)
function getBorderClass($status) {
    $classes = [
        'aguardando_chegada' => 'border-amber-500',
        'item_recebido'      => 'border-cyan-500',
        'em_homologacao'     => 'border-blue-500',
        'concluida'          => 'border-emerald-500',
        'cancelada'          => 'border-rose-500'
    ];
    return $classes[$status] ?? 'border-slate-500';
}

// Dias restantes
function calcularDiasRestantes($data_prevista) {
    if (!$data_prevista) return null;
    $hoje = new DateTime(date('Y-m-d'));
    $prevista = new DateTime($data_prevista);
    $intervalo = $hoje->diff($prevista);
    return (int)$intervalo->format('%R%a'); // retorna negativo se atrasado
}

// Obter Ícone Baseado no Tipo
function getIconForTipo($tipo) {
    switch ($tipo) {
        case 'Impressora': return 'ph-printer';
        case 'Notebook': return 'ph-laptop';
        case 'Suprimento de Impressora': return 'ph-drop';
        case 'Peça de Impressora': return 'ph-gear';
        default: return 'ph-box';
    }
}
?>
