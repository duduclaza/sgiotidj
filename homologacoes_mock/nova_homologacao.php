<?php
require_once __DIR__ . '/init.php';

$u = getUsuarioLogado();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_homologacao'])) {
    $responsaveis = isset($_POST['responsaveis']) ? $_POST['responsaveis'] : [];
    
    $novoRegistro = [
        'titulo' => $_POST['titulo'],
        'tipo_equipamento' => $_POST['tipo_equipamento'],
        'descricao' => $_POST['descricao'],
        'fornecedor' => $_POST['fornecedor'],
        'modelo' => $_POST['modelo'],
        'numero_serie' => $_POST['numero_serie'],
        'quantidade' => (int)($_POST['quantidade'] ?? 1),
        'tipo_aquisicao' => $_POST['tipo_aquisicao'] ?? 'comprado',
        'responsaveis' => array_map('intval', $responsaveis),
        'data_prevista_chegada' => $_POST['data_prevista_chegada'] ?: null,
        'dias_antecedencia_notif' => (int)$_POST['dias_antecedencia_notif'],
        'data_vencimento' => $_POST['data_vencimento'] ?: null,
        'dias_vencimento_notif' => (int)$_POST['dias_vencimento_notif'],
        'setor_responsavel' => $_POST['setor_responsavel'] ?? 'tecnico',
        'dados_comercial' => [
            'vendedor_nome' => $_POST['vendedor_nome'] ?? '',
            'vendedor_email' => $_POST['vendedor_email'] ?? '',
            'supervisor_email' => $_POST['supervisor_email'] ?? '',
        ],
        'notificar_envolvidos' => isset($_POST['notificar_envolvidos']) ? 1 : 0,
    ];
    
    $id = criarHomologacaoMock($novoRegistro);
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Homologação criada com sucesso! (ID: $id)"];
    header("Location: detalhe_homologacao.php?id=$id");
    exit;
}

$data = getMockData();
$responsaveis = array_filter($data['usuarios'], fn($u) => $u['perfil'] === 'responsavel');

// Inicializar tipos mock se necessário
if (!isset($_SESSION['mock_tipos_produto'])) {
    $_SESSION['mock_tipos_produto'] = [
        ['id' => 1, 'nome' => 'Impressora'],
        ['id' => 2, 'nome' => 'Notebook'],
        ['id' => 3, 'nome' => 'Suprimento de Impressora'],
        ['id' => 4, 'nome' => 'Peça de Impressora'],
    ];
}

// Fornecedores mock
$fornecedoresMock = [
    'HP do Brasil Ltda',
    'Dell Computadores do Brasil',
    'Lenovo do Brasil',
    'SupriMax Distribuidora',
    'TecPeças Importações',
];

$tiposReais = $_SESSION['mock_tipos_produto'];
$fornecedoresReais = $fornecedoresMock;

$title = "Nova Homologação - Homologações 2.0";
$viewFile = __DIR__ . '/views/nova_homologacao.php';
require_once __DIR__ . '/../views/layouts/main.php';
?>
