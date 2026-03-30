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

// Obter homologações aprovadas e vigentes (últimas de cada cadeia)
// Retorna array com as últimas homologações de cada produto original
function getUltimasHomologacoesPorProduto() {
    $homologacoes = $_SESSION['mock_homologacoes'] ?? [];
    $produtos = []; // Indexado por produto_original_id ou id (se primeira)
    
    // Agrupar por produto e pegar cronologicamente a última
    foreach ($homologacoes as $h) {
        $chave = ($h['tipo_homologacao'] === 'primeira') ? $h['id'] : $h['produto_original_id'];
        
        if (!isset($produtos[$chave]) || strtotime($h['data_criacao']) > strtotime($produtos[$chave]['data_criacao'])) {
            $produtos[$chave] = $h;
        }
    }
    
    return array_values($produtos);
}

// Obter toda a cadeia de homologações de um produto original
// Se passar ID de primeira homologação, retorna: [HOM-001, HOM-002, HOM-003...]
function getSequenciaHomologacaoProduto($id_ou_produto_original) {
    $homologacoes = $_SESSION['mock_homologacoes'] ?? [];
    $sequencia = [];
    
    // Encontrar a primeira homologação deste produto
    $primeira = null;
    foreach ($homologacoes as $h) {
        if ($h['tipo_homologacao'] === 'primeira' && $h['id'] == $id_ou_produto_original) {
            $primeira = $h;
            break;
        }
    }
    
    if (!$primeira) return [];
    
    $sequencia[] = $primeira;
    $proxima_id = $primeira['id'];
    
    // Construir cadeia de rehomologações
    while (true) {
        $encontrou = false;
        foreach ($homologacoes as $h) {
            if ($h['tipo_homologacao'] === 'rehomologacao' && 
                $h['homologacao_anterior_id'] == $proxima_id) {
                $sequencia[] = $h;
                $proxima_id = $h['id'];
                $encontrou = true;
                break;
            }
        }
        if (!$encontrou) break;
    }
    
    return $sequencia;
}

// Obter a versão (posição) de uma homologação na sua cadeia
// Retorna: 1 (primeira), 2 (segunda rehomologação), 3 (terceira), etc
function getVersaoHomologacao($id) {
    $homologacao = getHomologacaoById($id);
    if (!$homologacao) return null;
    
    // Se for primeira, versão é 1
    if ($homologacao['tipo_homologacao'] === 'primeira') {
        return 1;
    }
    
    // Se for rehomologação, contar quantas anteriores existem
    $versao = 1;
    $proxima_id = $homologacao['homologacao_anterior_id'];
    
    while ($proxima_id) {
        $versao++;
        $anterior = getHomologacaoById($proxima_id);
        if (!$anterior) break;
        $proxima_id = $anterior['homologacao_anterior_id'];
    }
    
    return $versao;
}

// Obter rótulo legível da versão (1ª, 2ª, 3ª, 4ª, etc)
function getRotuloVersao($versao) {
    if (!$versao) return '—';
    
    $sufixos = [
        1 => '1ª Homologação',
        2 => '2ª Homologação (Rehom)',
        3 => '3ª Homologação (Rehom)',
        4 => '4ª Homologação (Rehom)',
        5 => '5ª Homologação (Rehom)',
    ];
    
    return $sufixos[$versao] ?? $versao . 'ª Homologação';
}
?>
