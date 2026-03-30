<?php
require_once __DIR__ . '/init.php';

$u = getUsuarioLogado();

// Verificar permissão: apenas Compras e Qualidade podem criar
if (!$u || ($u['perfil'] !== 'compras' && $u['perfil'] !== 'qualidade' && $u['perfil'] !== 'admin' && $u['perfil'] !== 'super_admin')) {
    echo "<div class='bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 m-6 shadow-sm dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300 flex items-center gap-3'><i class='ph-fill ph-warning-circle text-xl'></i> <strong>Acesso negado.</strong> Apenas Compras e Qualidade podem criar homologações. Seu perfil: <strong>{$u['perfil']}</strong></div>";
    return;
}

// Determinar tipo de homologação baseado no setor do usuário
$tipoHomologacao = 'primeira'; //Default
if ($u && strtolower($u['perfil']) === 'qualidade') {
    $tipoHomologacao = 'rehomologacao';
}

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
        'tipo_homologacao' => $_POST['tipo_homologacao'] ?? $tipoHomologacao,
        'homologacao_anterior_id' => isset($_POST['homologacao_anterior_id']) && $_POST['homologacao_anterior_id'] 
            ? (int)$_POST['homologacao_anterior_id'] 
            : null,
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
    
    try {
        $id = criarHomologacaoMock($novoRegistro);
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Homologação criada com sucesso! (ID: $id)"];
        header("Location: detalhe_homologacao.php?id=$id");
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => $e->getMessage()];
    }
}

$data = getMockData();
$responsaveis = array_filter($data['usuarios'], fn($u) => $u['perfil'] === 'tecnico');
$ultimasHomologacoes = getUltimasHomologacoesPorProduto();

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
