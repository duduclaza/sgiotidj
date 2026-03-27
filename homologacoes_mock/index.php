<?php
require_once __DIR__ . '/init.php';

// Tratar a troca de usuário no mock (Global para o módulo mock)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trocar_usuario'])) {
    $_SESSION['usuario_logado_id'] = (int)$_POST['usuario_logado_id'];
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Ações de Cancelamento/Exclusão do Mock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'cancelar_homologacao') {
        $id = (int)$_POST['id'];
        $excluir = isset($_POST['excluir_definitivo']) && $_POST['excluir_definitivo'] === '1';
        
        if ($excluir) {
            excluirHomologacaoMock($id);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Homologação excluída permanentemente. Todos os envolvidos foram notificados do cancelamento via e-mail e canais sistêmicos."];
        } else {
            atualizarHomologacaoMock($id, ['status' => 'cancelada']);
            $_SESSION['flash_message'] = ['type' => 'warning', 'text' => "Homologação cancelada. O registro agora aparece como inválido para auditoria."];
        }
        header("Location: index.php");
        exit;
    }
}

$data = getMockData();
$homologacoes = $data['homologacoes'];
$u = getUsuarioLogado();

// Contadores para os Cards
$totais = [
    'total' => count($homologacoes),
    'aguardando' => count(array_filter($homologacoes, fn($h) => $h['status'] === 'aguardando_chegada')),
    'em_andamento' => count(array_filter($homologacoes, fn($h) => in_array($h['status'], ['item_recebido', 'em_homologacao']))),
    'concluidas' => count(array_filter($homologacoes, fn($h) => $h['status'] === 'concluida')),
    'canceladas' => count(array_filter($homologacoes, fn($h) => $h['status'] === 'cancelada')),
];

// Filtros
$filtroStatus = $_GET['status'] ?? '';
$filtroTipo = $_GET['tipo'] ?? '';

$lista = array_filter($homologacoes, function($h) use ($filtroStatus, $filtroTipo) {
    if ($filtroStatus && $h['status'] !== $filtroStatus) return false;
    if ($filtroTipo && $h['tipo_equipamento'] !== $filtroTipo) return false;
    return true;
});

// Alertas de Notificação
$alertas = [];
foreach ($homologacoes as $h) {
    // 1. Alerta de Logística (Chegada)
    if ($h['status'] === 'aguardando_chegada' && !empty($h['data_prevista_chegada'])) {
        $diasChegada = calcularDiasRestantes($h['data_prevista_chegada']);
        if ($diasChegada !== null && $diasChegada <= ($h['dias_antecedencia_notif'] ?? 3)) {
            $alertas[] = [
                'tipo' => 'logistica',
                'msg' => "🚚 <strong>Logística:</strong> A homologação <strong>{$h['codigo']}</strong> ({$h['modelo']}) tem chegada prevista para daqui a <strong>{$diasChegada} dias</strong>!"
            ];
        }
    }

    // 2. Alerta de Equipe (Vencimento)
    if ($h['status'] !== 'concluida' && $h['status'] !== 'cancelada' && !empty($h['data_vencimento'])) {
        $diasVenc = calcularDiasRestantes($h['data_vencimento']);
        if ($diasVenc !== null && $diasVenc <= ($h['dias_vencimento_notif'] ?? 5)) {
            $setorTxt = ucfirst($h['setor_responsavel'] ?? 'tecnico');
            
            if ($diasVenc < 0) {
                $msgVenc = "⚠️ <strong>VENCIDO:</strong> A homologação <strong>{$h['codigo']}</strong> está atrasada há " . abs($diasVenc) . " dias! Equipe <strong>{$setorTxt}</strong> cobrada.";
            } else {
                $msgVenc = "📅 <strong>Prazo ({$setorTxt}):</strong> Vence em <strong>{$diasVenc} dias</strong>.";
                if (($h['setor_responsavel'] ?? '') === 'comercial' && !empty($h['dados_comercial']['vendedor_email'])) {
                    $msgVenc .= " <span class='block text-[11px] opacity-80 mt-1 italic'>📧 E-mail de SLA enviado para {$h['dados_comercial']['vendedor_nome']} e Supervisor.</span>";
                }
            }

            $alertas[] = [
                'tipo' => 'vencimento',
                'msg' => $msgVenc
            ];
        }
    }
}

// Carregar o Layout SGQ
$title = "Homologações 2.0 - Dashboard";
$viewFile = __DIR__ . '/views/index.php';
require_once __DIR__ . '/../views/layouts/main.php';
?>
