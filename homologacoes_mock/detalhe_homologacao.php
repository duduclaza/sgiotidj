<?php
require_once __DIR__ . '/init.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$h = getHomologacaoById($id);

if (!$h) {
    echo "<div class='bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 m-6 shadow-sm dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300'>Homologação não encontrada ou inválida.</div>";
    return;
}

// Global user profile switcher intercept
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trocar_usuario'])) {
    $_SESSION['usuario_logado_id'] = (int)$_POST['usuario_logado_id'];
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$u = getUsuarioLogado();
$data = getMockData();

// ===== AÇÕES DO FLUXO =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        $acao = $_POST['acao'];
        
        if ($acao === 'confirmar_recebimento' && ($u['perfil'] === 'logistica' || $u['perfil'] === 'super_admin' || $u['perfil'] === 'admin')) {
            atualizarHomologacaoMock($id, [
                'status' => 'item_recebido',
                'data_recebimento' => $_POST['data_recebimento'],
                'recebido_por' => $u['id']
            ]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Item recebido registrado com sucesso!'];
        }
        elseif ($acao === 'iniciar_homologacao' && ($u['perfil'] === 'responsavel' || $u['perfil'] === 'super_admin' || $u['perfil'] === 'admin')) {
            atualizarHomologacaoMock($id, [
                'status' => 'em_homologacao',
                'local_homologacao' => $_POST['local_homologacao'],
                'data_inicio_homologacao' => $_POST['data_inicio_homologacao'],
                'nome_cliente' => $_POST['nome_cliente'] ?? null,
                'data_instalacao_cliente' => $_POST['data_instalacao_cliente'] ?? null
            ]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Homologação iniciada. Preencha o checklist técnico!'];
        }
        elseif ($acao === 'salvar_checklist' && ($u['perfil'] === 'responsavel' || $u['perfil'] === 'super_admin' || $u['perfil'] === 'admin')) {
            $respostas = $_POST['checklist'] ?? [];
            $booleadas = [];
            foreach ($respostas as $k => $v) {
                if ($v === '1') $booleadas[$k] = true;
                elseif ($v === '0') $booleadas[$k] = false;
                elseif ($v === 'pendente') $booleadas[$k] = 'pendente';
                else $booleadas[$k] = null;
            }
            $obs_atual = $h['observacoes_checklist'] ?? '';
            $nova_obs = trim($_POST['nova_observacao'] ?? '');
            if ($nova_obs !== '') {
                $timestamp = date('d/m/Y \à\s H:i');
                $nome_usuario = $u['nome'] ?? 'Usuário';
                $bloco = "[$timestamp - $nome_usuario]\n$nova_obs";
                $obs_atual = $obs_atual === '' ? $bloco : $obs_atual . "\n\n" . $bloco;
            }

            atualizarHomologacaoMock($id, [
                'checklist_respostas' => $booleadas,
                'observacoes_checklist' => $obs_atual
            ]);
            $_SESSION['flash_message'] = ['type' => 'info', 'text' => 'Bateria de testes rascunhada e salva com sucesso.'];
        }
        elseif ($acao === 'finalizar_homologacao' && ($u['perfil'] === 'responsavel' || $u['perfil'] === 'super_admin' || $u['perfil'] === 'admin')) {
            $respostas = $_POST['checklist'] ?? [];
            $booleadas = [];
            $tem_pendente = false;
            foreach ($respostas as $k => $v) {
                if ($v === '1') $booleadas[$k] = true;
                elseif ($v === '0') $booleadas[$k] = false;
                elseif ($v === 'pendente') {
                    $booleadas[$k] = 'pendente';
                    $tem_pendente = true;
                } else {
                    $booleadas[$k] = null;
                    $tem_pendente = true;
                }
            }

            $resultado = $_POST['resultado'];
            if ($tem_pendente) {
                $resultado = 'pendente';
            }
            $novo_status = ($tem_pendente || $resultado === 'pendente') ? 'em_homologacao' : 'concluida';

            $obs_atual = $h['observacoes_checklist'] ?? '';
            $nova_obs = trim($_POST['nova_observacao'] ?? '');
            if ($nova_obs !== '') {
                $timestamp = date('d/m/Y \à\s H:i');
                $nome_usuario = $u['nome'] ?? 'Usuário';
                $bloco = "[$timestamp - $nome_usuario]\n$nova_obs";
                $obs_atual = $obs_atual === '' ? $bloco : $obs_atual . "\n\n" . $bloco;
            }

            $parecer_atual = $h['parecer_final'] ?? '';
            $novo_parecer = trim($_POST['novo_parecer_final'] ?? '');
            if ($novo_parecer !== '') {
                $timestamp = date('d/m/Y \à\s H:i');
                $nome_usuario = $u['nome'] ?? 'Usuário';
                $bloco = "[$timestamp - $nome_usuario]\n$novo_parecer";
                $parecer_atual = $parecer_atual === '' ? $bloco : $parecer_atual . "\n\n" . $bloco;
            }

            atualizarHomologacaoMock($id, [
                'status' => $novo_status,
                'data_fim_homologacao' => $_POST['data_fim_homologacao'],
                'resultado' => $resultado,
                'parecer_final' => $parecer_atual,
                'checklist_respostas' => $booleadas,
                'observacoes_checklist' => $obs_atual
            ]);
            
            if ($novo_status === 'em_homologacao') {
                $_SESSION['flash_message'] = ['type' => 'info', 'text' => 'Veredito bloqueado para "Pendente" pois o checklist ainda não foi 100% finalizado.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Processo de Homologação Assinado e Finalizado!'];
            }
        }
        
        header("Location: detalhe_homologacao.php?id=$id");
        exit;
    }
}

// Reload data if modified
$h = getHomologacaoById($id);

// Buscar checklist dos dados mock na sessão (sem banco de dados)
$checklistItems = $data['checklists'][$h['tipo_equipamento']] ?? [];

$respostas = $h['checklist_respostas'] ?? [];

$title = $h['codigo'] . " - Homologação";
$viewFile = __DIR__ . '/views/detalhe_homologacao.php';
require_once __DIR__ . '/../views/layouts/main.php';
?>
