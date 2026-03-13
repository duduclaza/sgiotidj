<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class HomologacoesController
{
    private $db;

    public function __construct()
    {
        // Database lazy loading - só conecta quando necessário
        // $this->db = Database::getInstance();
    }

    /**
     * Exibir o Kanban de Homologações
     */
    public function index()
    {
        // TEMPORÁRIO: Página em construção - HTML standalone
        echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Em Construção - Homologações</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .icon { font-size: 100px; margin-bottom: 20px; animation: bounce 2s infinite; }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        h1 { color: #1e293b; font-size: 36px; margin-bottom: 15px; }
        p { color: #64748b; font-size: 18px; line-height: 1.6; }
        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover { transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚧</div>
        <h1>Módulo Homologações</h1>
        <p>Este módulo está em construção e estará disponível em breve.</p>
        <p style="font-size: 14px; margin-top: 10px; color: #94a3b8;">
            As tabelas foram criadas com sucesso no banco de dados.
        </p>
        <a href="/" class="btn">← Voltar ao Sistema</a>
    </div>
</body>
</html>';
        exit;
        
        // CÓDIGO ABAIXO NÃO EXECUTA (return acima)
        // TODO: Descomentar quando finalizar o módulo
        
        // Verificar permissão
        // PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'view');

        // Verificar se usuário pode criar homologações (Admin ou Compras)
        // $canCreate = $this->canCreateHomologacao($_SESSION['user_id']);

        // Buscar todos os cartões agrupados por status
        // $homologacoes = $this->getHomologacoesKanban();

        // Buscar usuários para dropdown de responsáveis
        // $usuarios = $this->getUsuariosAtivos();

        // require_once __DIR__ . '/../../views/homologacoes/kanban.php';
    }

    /**
     * Verificar se usuário pode criar homologações (Admin ou departamento Compras)
     */
    private function canCreateHomologacao(int $userId): bool
    {
        // Super Admin sempre pode
        if (PermissionService::isSuperAdmin($userId)) {
            return true;
        }

        // Verificar se tem permissão de edição no módulo
        if (!PermissionService::hasPermission($userId, 'homologacoes', 'edit')) {
            return false;
        }

        return PermissionService::hasPermission($userId, 'homologacoes', 'edit');
    }

    /**
     * Buscar homologações agrupadas por status (colunas do Kanban)
     */
    private function getHomologacoesKanban(): array
    {
        $stmt = $this->db->query("
            SELECT 
                h.*,
                u.name as criador_nome,
                GROUP_CONCAT(DISTINCT ur.name SEPARATOR ', ') as responsaveis_nomes,
                COUNT(DISTINCT ha.id) as total_anexos
            FROM homologacoes h
            LEFT JOIN users u ON h.created_by = u.id
            LEFT JOIN homologacoes_responsaveis hr ON h.id = hr.homologacao_id
            LEFT JOIN users ur ON hr.user_id = ur.id
            LEFT JOIN homologacoes_anexos ha ON h.id = ha.homologacao_id
            GROUP BY h.id
            ORDER BY h.ordem ASC, h.created_at DESC
        ");

        $homologacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por status
        $kanban = [
            'pendente_recebimento' => [],
            'em_analise' => [],
            'aprovado' => [],
            'reprovado' => []
        ];

        foreach ($homologacoes as $homologacao) {
            $status = $homologacao['status'] ?? 'pendente_recebimento';
            if (isset($kanban[$status])) {
                $kanban[$status][] = $homologacao;
            }
        }

        return $kanban;
    }

    /**
     * Buscar usuários ativos para dropdown
     */
    private function getUsuariosAtivos(): array
    {
        $stmt = $this->db->query("
            SELECT id, name, email, department 
            FROM users 
            WHERE status = 'active' 
            ORDER BY name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Criar nova homologação
     */
    public function store()
    {
        header('Content-Type: application/json');

        try {
            // Verificar permissão
            if (!$this->canCreateHomologacao($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para criar homologações']);
                exit;
            }

            // Validar dados
            $codigoProduto = trim($_POST['codigo_produto'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $fornecedor = trim($_POST['fornecedor'] ?? '');
            $motivoHomologacao = trim($_POST['motivo_homologacao'] ?? '');
            $responsaveis = $_POST['responsaveis'] ?? []; // Array de IDs
            $avisarLogistica = isset($_POST['avisar_logistica']) && $_POST['avisar_logistica'] === '1';

            if (empty($codigoProduto) || empty($descricao) || empty($fornecedor) || empty($motivoHomologacao)) {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
                exit;
            }

            if (empty($responsaveis) || !is_array($responsaveis)) {
                echo json_encode(['success' => false, 'message' => 'Selecione pelo menos um responsável']);
                exit;
            }

            $this->db->beginTransaction();

            // Inserir homologação
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes (
                    codigo_produto, descricao, fornecedor, motivo_homologacao,
                    avisar_logistica, status, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pendente_recebimento', ?, NOW())
            ");
            $stmt->execute([
                $codigoProduto,
                $descricao,
                $fornecedor,
                $motivoHomologacao,
                $avisarLogistica ? 1 : 0,
                $_SESSION['user_id']
            ]);

            $homologacaoId = $this->db->lastInsertId();

            // Inserir responsáveis
            $stmtResp = $this->db->prepare("
                INSERT INTO homologacoes_responsaveis (homologacao_id, user_id) 
                VALUES (?, ?)
            ");

            foreach ($responsaveis as $userId) {
                $stmtResp->execute([$homologacaoId, $userId]);
            }

            // Registrar histórico
            $stmtHist = $this->db->prepare("
                INSERT INTO homologacoes_historico (homologacao_id, status_novo, user_id, observacao, created_at)
                VALUES (?, 'pendente_recebimento', ?, 'Homologação criada', NOW())
            ");
            $stmtHist->execute([$homologacaoId, $_SESSION['user_id']]);

            $this->db->commit();

            // Enviar notificações
            $this->enviarNotificacoes($homologacaoId, $responsaveis, $avisarLogistica);

            echo json_encode([
                'success' => true,
                'message' => 'Homologação criada com sucesso',
                'homologacao_id' => $homologacaoId
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao criar homologação: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao criar homologação: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Enviar notificações por email e sininho
     */
    private function enviarNotificacoes(int $homologacaoId, array $responsaveis, bool $avisarLogistica)
    {
        // Buscar dados da homologação
        $stmt = $this->db->prepare("
            SELECT h.*, u.name as criador_nome 
            FROM homologacoes h
            LEFT JOIN users u ON h.created_by = u.id
            WHERE h.id = ?
        ");
        $stmt->execute([$homologacaoId]);
        $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

        // Notificar responsáveis
        foreach ($responsaveis as $userId) {
            $this->criarNotificacao($userId, $homologacaoId, 'responsavel', $homologacao);
            $this->enviarEmailResponsavel($userId, $homologacao);
        }

        // Notificar logística se solicitado
        if ($avisarLogistica) {
            $stmtLog = $this->db->query("
                SELECT id, email, name 
                FROM users 
                WHERE LOWER(department) = 'logistica' 
                AND status = 'active'
            ");
            $logisticaUsers = $stmtLog->fetchAll(PDO::FETCH_ASSOC);

            foreach ($logisticaUsers as $user) {
                $this->criarNotificacao($user['id'], $homologacaoId, 'logistica', $homologacao);
                $this->enviarEmailLogistica($user, $homologacao);
            }
        }
    }

    /**
     * Criar notificação no sininho
     */
    private function criarNotificacao(int $userId, int $homologacaoId, string $tipo, array $homologacao)
    {
        $mensagem = $tipo === 'responsavel' 
            ? "Você foi designado como responsável pela homologação #{$homologacaoId} - {$homologacao['codigo_produto']}"
            : "Nova homologação pendente de recebimento: #{$homologacaoId} - {$homologacao['codigo_produto']}";

        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id, created_at)
            VALUES (?, 'homologacao', 'Nova Homologação', ?, 'homologacao', ?, NOW())
        ");
        $stmt->execute([$userId, $mensagem, $homologacaoId]);
    }

    /**
     * Enviar email para responsável
     */
    private function enviarEmailResponsavel(int $userId, array $homologacao)
    {
        // Implementar envio de email usando PHPMailer
        // Similar ao sistema de POPs e ITs
    }

    /**
     * Enviar email para logística
     */
    private function enviarEmailLogistica(array $user, array $homologacao)
    {
        // Implementar envio de email para logística
    }

    /**
     * Atualizar status do cartão (mover no Kanban)
     */
    public function updateStatus()
    {
        header('Content-Type: application/json');

        try {
            PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'edit');

            $homologacaoId = $_POST['homologacao_id'] ?? 0;
            $novoStatus = $_POST['status'] ?? '';
            $observacao = trim($_POST['observacao'] ?? '');

            if (!$homologacaoId || !$novoStatus) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                exit;
            }

            // Buscar status anterior
            $stmt = $this->db->prepare("SELECT status FROM homologacoes WHERE id = ?");
            $stmt->execute([$homologacaoId]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                echo json_encode(['success' => false, 'message' => 'Homologação não encontrada']);
                exit;
            }

            $this->db->beginTransaction();

            // Atualizar status
            $stmt = $this->db->prepare("UPDATE homologacoes SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$novoStatus, $homologacaoId]);

            // Registrar no histórico
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes_historico (homologacao_id, status_anterior, status_novo, user_id, observacao, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $homologacaoId,
                $homologacao['status'],
                $novoStatus,
                $_SESSION['user_id'],
                $observacao ?: "Status alterado de {$homologacao['status']} para {$novoStatus}"
            ]);

            $this->db->commit();

            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao atualizar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
        }
        exit;
    }

    /**
     * Buscar detalhes de uma homologação
     */
    public function details($id)
    {
        header('Content-Type: application/json');

        try {
            PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'view');

            $stmt = $this->db->prepare("
                SELECT 
                    h.*,
                    u.name as criador_nome,
                    u.email as criador_email,
                    GROUP_CONCAT(DISTINCT ur.name SEPARATOR ', ') as responsaveis_nomes
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                LEFT JOIN homologacoes_responsaveis hr ON h.id = hr.homologacao_id
                LEFT JOIN users ur ON hr.user_id = ur.id
                WHERE h.id = ?
                GROUP BY h.id
            ");
            $stmt->execute([$id]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                echo json_encode(['success' => false, 'message' => 'Homologação não encontrada']);
                exit;
            }

            // Buscar histórico
            $stmt = $this->db->prepare("
                SELECT hh.*, u.name as usuario_nome
                FROM homologacoes_historico hh
                LEFT JOIN users u ON hh.user_id = u.id
                WHERE hh.homologacao_id = ?
                ORDER BY hh.created_at DESC
            ");
            $stmt->execute([$id]);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT id, nome_arquivo, tipo_arquivo, tamanho_arquivo, uploaded_at, uploaded_by
                FROM homologacoes_anexos
                WHERE homologacao_id = ?
                ORDER BY uploaded_at DESC
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'homologacao' => $homologacao,
                'historico' => $historico,
                'anexos' => $anexos
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao buscar detalhes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar detalhes']);
        }
        exit;
    }

    /**
     * Deletar homologação
     */
    public function delete()
    {
        header('Content-Type: application/json');

        try {
            PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'delete');

            $homologacaoId = $_POST['id'] ?? 0;

            if (!$homologacaoId) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            $stmt = $this->db->prepare("DELETE FROM homologacoes WHERE id = ?");
            $stmt->execute([$homologacaoId]);

            echo json_encode(['success' => true, 'message' => 'Homologação excluída com sucesso']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir homologação: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir homologação']);
        }
        exit;
    }
}
