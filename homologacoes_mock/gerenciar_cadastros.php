<?php
require_once __DIR__ . '/init.php';

// Tratar a troca de usuário no mock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trocar_usuario'])) {
    $_SESSION['usuario_logado_id'] = (int)$_POST['usuario_logado_id'];
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$u = getUsuarioLogado();

// Inicializar dados mock de tipos e checklists na sessão
if (!isset($_SESSION['mock_tipos_produto'])) {
    $_SESSION['mock_tipos_produto'] = [
        ['id' => 1, 'nome' => 'Impressora'],
        ['id' => 2, 'nome' => 'Notebook'],
        ['id' => 3, 'nome' => 'Suprimento de Impressora'],
        ['id' => 4, 'nome' => 'Peça de Impressora'],
        ['id' => 5, 'nome' => 'Coletor'],
        ['id' => 6, 'nome' => 'TOTEM'],
        ['id' => 7, 'nome' => 'Servidor'],
    ];
}

if (!isset($_SESSION['mock_checklists_cadastrados'])) {
    $_SESSION['mock_checklists_cadastrados'] = [];
}

// --- AÇÕES DE PRODUTOS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_produto'])) {
    $nome = trim($_POST['nome_produto'] ?? '');
    if ($_POST['acao_produto'] === 'adicionar' && !empty($nome)) {
        $maxId = empty($_SESSION['mock_tipos_produto']) ? 0 : max(array_column($_SESSION['mock_tipos_produto'], 'id'));
        $_SESSION['mock_tipos_produto'][] = ['id' => $maxId + 1, 'nome' => $nome];

        if (!isset($_SESSION['mock_checklists_por_tipo'][$nome])) {
            $_SESSION['mock_checklists_por_tipo'][$nome] = [];
        }

        $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Tipo de produto '$nome' adicionado!"];
    } elseif ($_POST['acao_produto'] === 'excluir' && !empty($_POST['id_produto'])) {
        $_SESSION['mock_tipos_produto'] = array_values(array_filter(
            $_SESSION['mock_tipos_produto'],
            fn($t) => $t['id'] != $_POST['id_produto']
        ));
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Tipo de produto removido!"];
    }
    header("Location: gerenciar_cadastros.php");
    exit;
}

// --- AÇÕES DE CHECKLISTS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_checklist'])) {

    if ($_POST['acao_checklist'] === 'adicionar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $tipo_nome = $_POST['tipo_produto_nome'] ?? '';
        $itens = $_POST['itens'] ?? [];
        $itens = array_filter(array_map('trim', $itens));

        if (!empty($titulo) && !empty($itens)) {
            $maxId = empty($_SESSION['mock_checklists_cadastrados']) ? 0 : max(array_column($_SESSION['mock_checklists_cadastrados'], 'id'));
            $_SESSION['mock_checklists_cadastrados'][] = [
                'id' => $maxId + 1,
                'titulo' => $titulo,
                'tipo_produto_nome' => $tipo_nome,
                'itens' => array_values($itens),
                'criado_em' => date('Y-m-d H:i:s'),
            ];

            // Sincronizar com mock_checklists_por_tipo
            if (!empty($tipo_nome)) {
                $checklistForType = [];
                foreach ($itens as $item) {
                    $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $item));
                    $checklistForType[$key] = $item;
                }
                $_SESSION['mock_checklists_por_tipo'][$tipo_nome] = $checklistForType;
            }

            $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Checklist '$titulo' criado!"];
        }

    } elseif ($_POST['acao_checklist'] === 'editar_existente') {
        // Editar um checklist pré-existente (mock_checklists_por_tipo)
        $tipo_nome = $_POST['tipo_produto_nome'] ?? '';
        $itens = $_POST['itens'] ?? [];
        $itens = array_filter(array_map('trim', $itens));

        if (!empty($tipo_nome) && !empty($itens)) {
            $checklistForType = [];
            foreach ($itens as $item) {
                $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $item));
                $checklistForType[$key] = $item;
            }
            $_SESSION['mock_checklists_por_tipo'][$tipo_nome] = $checklistForType;
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Checklist de '$tipo_nome' atualizado!"];
        }

    } elseif ($_POST['acao_checklist'] === 'editar_custom') {
        // Editar um checklist criado pelo usuário (mock_checklists_cadastrados)
        $id = (int)($_POST['id_checklist'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $tipo_nome = $_POST['tipo_produto_nome'] ?? '';
        $itens = $_POST['itens'] ?? [];
        $itens = array_filter(array_map('trim', $itens));

        if ($id > 0 && !empty($titulo) && !empty($itens)) {
            foreach ($_SESSION['mock_checklists_cadastrados'] as &$ch) {
                if ($ch['id'] === $id) {
                    $ch['titulo'] = $titulo;
                    $ch['tipo_produto_nome'] = $tipo_nome;
                    $ch['itens'] = array_values($itens);
                    break;
                }
            }
            unset($ch);

            // Sincronizar com mock_checklists_por_tipo
            if (!empty($tipo_nome)) {
                $checklistForType = [];
                foreach ($itens as $item) {
                    $key = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $item));
                    $checklistForType[$key] = $item;
                }
                $_SESSION['mock_checklists_por_tipo'][$tipo_nome] = $checklistForType;
            }

            $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Checklist '$titulo' atualizado!"];
        }

    } elseif ($_POST['acao_checklist'] === 'excluir' && !empty($_POST['id_checklist'])) {
        $_SESSION['mock_checklists_cadastrados'] = array_values(array_filter(
            $_SESSION['mock_checklists_cadastrados'],
            fn($c) => $c['id'] != $_POST['id_checklist']
        ));
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Checklist removido!"];

    } elseif ($_POST['acao_checklist'] === 'excluir_existente') {
        $tipo_nome = $_POST['tipo_produto_nome'] ?? '';
        if (!empty($tipo_nome) && isset($_SESSION['mock_checklists_por_tipo'][$tipo_nome])) {
            $_SESSION['mock_checklists_por_tipo'][$tipo_nome] = [];
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Checklist de '$tipo_nome' limpo!"];
        }
    }

    header("Location: gerenciar_cadastros.php");
    exit;
}

$tipos = $_SESSION['mock_tipos_produto'];
$checklists = $_SESSION['mock_checklists_cadastrados'];

$title = "Gerenciar Produtos & Checklists - Homologações 2.0";
$viewFile = __DIR__ . '/views/gerenciar_cadastros.php';
require_once __DIR__ . '/../views/layouts/main.php';
