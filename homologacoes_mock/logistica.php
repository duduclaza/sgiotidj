<?php
require_once __DIR__ . '/init.php';

// Tratar troca de usuarion o mock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trocar_usuario'])) {
    $_SESSION['usuario_logado_id'] = (int)$_POST['usuario_logado_id'];
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$u = getUsuarioLogado();

$data = getMockData();
$homologacoes = $data['homologacoes'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_recebimento_id'])) {
    $id = (int)$_POST['confirmar_recebimento_id'];
    atualizarHomologacaoMock($id, [
        'status' => 'item_recebido',
        'data_recebimento' => $_POST['data_recebimento'],
        'recebido_por' => $u['id']
    ]);
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Recebimento físico confirmado! As peças agora estão disponíveis para a equipe técnica designada. Eles receberão um e-mail sobre a chegada e mensagens diretas no chat."];
    header("Location: logistica.php");
    exit;
}

// Filtrar itens apenas Aguardando ou Recém recebidos
$aguardando = array_filter($homologacoes, fn($h) => $h['status'] === 'aguardando_chegada');
$recebidos  = array_filter($homologacoes, fn($h) => $h['status'] === 'item_recebido');

$title = "Painel Logística - Homologações 2.0";
$viewFile = __DIR__ . '/views/logistica.php';
require_once __DIR__ . '/../views/layouts/main.php';
?>
