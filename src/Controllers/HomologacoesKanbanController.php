<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use App\Services\EmailService;
use PDO;
use Carbon\Carbon;

class HomologacoesKanbanController
{
    private $db;

    public function __construct()
    {
        // Database::getInstance() já retorna uma instância de PDO
        // Não existe método getConnection() no PDO; usar diretamente a instância
        $this->db = Database::getInstance();
        
        // Configurar timezone de Brasília para Carbon
        Carbon::setLocale('pt_BR');
        date_default_timezone_set('America/Sao_Paulo');
    }

    /**
     * Página principal do Kanban de Homologações
     */
    public function index()
    {
        try {
            // Verificar permissão
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            // Inicializar arrays vazios
            $homologacoes = [
                'aguardando_recebimento' => [],
                'recebido' => [],
                'em_analise' => [],
                'em_homologacao' => [],
                'aprovado' => [],
                'reprovado' => []
            ];

            // Buscar homologações do banco
            try {
                $stmt = $this->db->query("
                    SELECT h.*, 
                           u.name as criador_nome,
                           d.nome as departamento_nome,
                           dr.nome as departamento_resp_nome,
                           GROUP_CONCAT(DISTINCT ur.name SEPARATOR ', ') as responsaveis_nomes,
                           COUNT(DISTINCT a.id) as total_anexos,
                           h.data_vencimento,
                           h.dias_aviso,
                           h.departamento_resp_id,
                           DATEDIFF(h.data_vencimento, CURDATE()) as dias_restantes,
                           (
                               SELECT uap.name
                               FROM homologacoes_historico hh2
                               LEFT JOIN users uap ON uap.id = hh2.usuario_id
                               WHERE hh2.homologacao_id = h.id AND hh2.status_novo = 'aprovado'
                               ORDER BY hh2.created_at DESC
                               LIMIT 1
                           ) AS aprovado_por_nome,
                           (
                               SELECT urej.name
                               FROM homologacoes_historico hh3
                               LEFT JOIN users urej ON urej.id = hh3.usuario_id
                               WHERE hh3.homologacao_id = h.id AND hh3.status_novo = 'reprovado'
                               ORDER BY hh3.created_at DESC
                               LIMIT 1
                           ) AS reprovado_por_nome
                    FROM homologacoes h
                    LEFT JOIN users u ON h.created_by = u.id
                    LEFT JOIN departamentos d ON h.departamento_id = d.id
                    LEFT JOIN departamentos dr ON h.departamento_resp_id = dr.id
                    LEFT JOIN homologacoes_responsaveis hr ON h.id = hr.homologacao_id
                    LEFT JOIN users ur ON hr.user_id = ur.id
                    LEFT JOIN homologacoes_anexos a ON h.id = a.homologacao_id
                    GROUP BY h.id
                    ORDER BY h.created_at DESC
                ");
                $todasHomologacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Agrupar por status
                foreach ($todasHomologacoes as $h) {
                    $status = $h['status'] ?? 'aguardando_recebimento';
                    if (isset($homologacoes[$status])) {
                        $homologacoes[$status][] = $h;
                    }
                }
            } catch (\Exception $e) {
                error_log("Erro ao buscar homologações: " . $e->getMessage());
            }

            // Buscar usuários ativos
            $usuarios = [];
            try {
                $stmt = $this->db->query("
                    SELECT id, name, email 
                    FROM users 
                    WHERE status = 'active' 
                    ORDER BY name ASC
                ");
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log("Erro ao buscar usuários: " . $e->getMessage());
            }

            // Buscar departamentos
            $departamentos = [];
            try {
                $stmt = $this->db->query("
                    SELECT id, nome 
                    FROM departamentos 
                    ORDER BY nome ASC
                ");
                $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                error_log("Erro ao buscar departamentos: " . $e->getMessage());
            }

            // Verificar se pode criar (com base na permissão do perfil ou privilégios de admin)
            $canCreate = $this->canCreateHomologacao($_SESSION['user_id']);

            // Renderizar via layout principal
            $title = 'Homologações - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/homologacoes/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
            
        } catch (\Exception $e) {
            error_log("Erro no módulo Homologações: " . $e->getMessage());
            die("❌ ERRO no módulo Homologações: " . $e->getMessage() . "<br><br>Linha: " . $e->getLine() . "<br>Arquivo: " . $e->getFile());
        }
    }

    /**
     * Buscar e-mails dos responsáveis de uma homologação
     */
    private function getResponsaveisEmails(int $homologacaoId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT u.email FROM homologacoes_responsaveis hr LEFT JOIN users u ON u.id = hr.user_id WHERE hr.homologacao_id = ? AND u.status = 'active' AND u.email IS NOT NULL AND u.email <> ''");
            $stmt->execute([$homologacaoId]);
            return array_values(array_filter(array_map(function($r){return $r['email'] ?? null;}, $stmt->fetchAll(PDO::FETCH_ASSOC))));
        } catch (\Exception $e) {
            error_log('Erro ao buscar e-mails de responsáveis: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Enviar e-mail de atualização de status para responsáveis
     */
    private function enviarEmailStatusHomologacao(array $destinatarios, array $homologacao, string $novoStatus, string $obs = ''): void
    {
        try {
            if (empty($destinatarios)) return;
            $email = new EmailService();

            $assunto = "SGQ - Homologação #{$homologacao['id']} atualizada para: " . strtoupper($novoStatus);
            $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
            $body = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='font-family: Arial,sans-serif;line-height:1.6;color:#333;max-width:680px;margin:0 auto;padding:20px;'>"
                . "<div style='background:#6b7280;color:#fff;padding:18px 24px;border-radius:10px 10px 0 0;'><h2 style='margin:0;font-size:20px;'>SGQ OTI DJ • Atualização de Status</h2></div>"
                . "<div style='background:#fff;border:1px solid #e5e7eb;border-top:none;padding:20px'>"
                . "<p style='margin:0 0 8px'>Homologação: <strong>#" . htmlspecialchars((string)$homologacao['id']) . "</strong></p>"
                . "<p style='margin:0 0 8px'>Código: <strong>" . htmlspecialchars($homologacao['cod_referencia'] ?? '') . "</strong></p>"
                . "<p style='margin:0 0 8px'>Novo status: <strong>" . htmlspecialchars($novoStatus) . "</strong></p>"
                . (!empty($obs) ? ("<p style='margin:10px 0 0'><em>Observação:</em> " . nl2br(htmlspecialchars($obs)) . "</p>") : "")
                . "<div style='text-align:center;margin:22px 0'><a href='" . $appUrl . "/homologacoes' style='background:#2563eb;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold'>Abrir Homologações</a></div>"
                . "<p style='font-size:12px;color:#6b7280;margin-top:24px'>Este email foi enviado automaticamente pelo SGQ OTI DJ.</p>"
                . "</div></body></html>";

            $ok = $email->send($destinatarios, $assunto, $body, strip_tags($body));
            if (!$ok) {
                error_log('Falha ao enviar email de atualização de status: ' . ($email->getLastError() ?? 'sem detalhes'));
            }
        } catch (\Exception $e) {
            error_log('Erro ao enviar email de atualização de status: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se usuário pode criar homologações (departamento Compras)
     */
    private function canCreateHomologacao(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("
                SELECT department, profile_id
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            // Admin sempre pode
            if (PermissionService::hasAdminPrivileges($userId)) {
                return true;
            }

            // Verificar se tem permissão de 'edit' no módulo 'homologacoes'
            return PermissionService::hasPermission($userId, 'homologacoes', 'edit');

        } catch (\Exception $e) {
            error_log("Erro ao verificar permissão de criação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar homologações agrupadas por status para o Kanban
     */
    private function getHomologacoesKanban(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    h.*,
                    u.name as criador_nome,
                    COUNT(DISTINCT ha.id) as total_anexos,
                    GROUP_CONCAT(DISTINCT ur.name ORDER BY ur.name SEPARATOR ', ') as responsaveis_nomes
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                LEFT JOIN homologacoes_responsaveis hr ON h.id = hr.homologacao_id
                LEFT JOIN users ur ON hr.user_id = ur.id
                LEFT JOIN homologacoes_anexos ha ON h.id = ha.homologacao_id
                GROUP BY h.id
                ORDER BY h.created_at DESC
            ");

            $homologacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar por status
            $kanban = [
                'aguardando_recebimento' => [],
                'recebido' => [],
                'em_analise' => [],
                'em_homologacao' => [],
                'aprovado' => [],
                'reprovado' => []
            ];

            foreach ($homologacoes as $homologacao) {
                $status = $homologacao['status'] ?? 'aguardando_recebimento';
                if (isset($kanban[$status])) {
                    $kanban[$status][] = $homologacao;
                }
            }

            return $kanban;

        } catch (\Exception $e) {
            error_log("Erro ao buscar homologações: " . $e->getMessage());
            return [
                'aguardando_recebimento' => [],
                'recebido' => [],
                'em_analise' => [],
                'em_homologacao' => [],
                'aprovado' => [],
                'reprovado' => []
            ];
        }
    }

    /**
     * Buscar usuários ativos para dropdown de responsáveis
     */
    private function getUsuariosAtivos(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT id, name, email, department 
                FROM users 
                WHERE status = 'active' 
                ORDER BY name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            return [];
        }
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
                echo json_encode([
                    'success' => false, 
                    'message' => 'Você não tem permissão para criar homologações. Apenas o departamento de Compras pode criar.'
                ]);
                exit;
            }

            // Validar dados
            $codReferencia      = trim($_POST['cod_referencia'] ?? '');
            $descricao          = trim($_POST['descricao'] ?? '');
            $avisarLogistica    = isset($_POST['avisar_logistica']) && $_POST['avisar_logistica'] === '1';
            $observacao         = trim($_POST['observacao'] ?? '');
            $dataVencimento     = trim($_POST['data_vencimento'] ?? '');
            $diasAviso          = max(1, (int)($_POST['dias_aviso'] ?? 7));
            $departamentoRespId = !empty($_POST['departamento_resp_id']) ? (int)$_POST['departamento_resp_id'] : null;

            if (empty($codReferencia) || empty($descricao)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Preencha o Código de Referência e Descrição'
                ]);
                exit;
            }

            if (empty($departamentoRespId)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Selecione o Departamento responsável pela homologação'
                ]);
                exit;
            }

            if (empty($dataVencimento)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Informe a Data de Vencimento'
                ]);
                exit;
            }

            $this->db->beginTransaction();

            // Inserir homologação com status inicial
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes (
                    cod_referencia, 
                    descricao, 
                    avisar_logistica, 
                    observacao,
                    data_vencimento,
                    dias_aviso,
                    departamento_resp_id,
                    status, 
                    created_by, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'aguardando_recebimento', ?, NOW())
            ");
            $stmt->execute([
                $codReferencia,
                $descricao,
                $avisarLogistica ? 1 : 0,
                $observacao,
                $dataVencimento ?: null,
                $diasAviso,
                $departamentoRespId,
                $_SESSION['user_id']
            ]);

            $homologacaoId = $this->db->lastInsertId();

            // Registrar histórico
            $stmtHist = $this->db->prepare("
                INSERT INTO homologacoes_historico (
                    homologacao_id, 
                    status_novo, 
                    usuario_id, 
                    observacao, 
                    created_at
                )
                VALUES (?, 'aguardando_recebimento', ?, 'Homologação criada', NOW())
            ");
            $stmtHist->execute([$homologacaoId, $this->getUsuarioIdLog()]);

            $this->db->commit();

            // Notificar todos os usuários do departamento responsável
            $this->notificarDepartamento($homologacaoId, $departamentoRespId, $avisarLogistica);

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
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao criar homologação: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Atualizar contadores (contador_inicial e contador_final) de uma homologação
     */
    public function updateContadores($id)
    {
        header('Content-Type: application/json');

        try {
            $homologacaoId = (int)$id;

            if (!$homologacaoId) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            $json = file_get_contents('php://input');
            $data = json_decode($json, true) ?: [];

            $contadorInicial = $data['contador_inicial'] ?? null;
            $contadorFinal = $data['contador_final'] ?? null;

            // Normalizar valores: aceitar null ou inteiros
            $contadorInicial = ($contadorInicial === null || $contadorInicial === '') ? null : (int)$contadorInicial;
            $contadorFinal = ($contadorFinal === null || $contadorFinal === '') ? null : (int)$contadorFinal;

            // DEBUG: Log para verificar valores recebidos
            error_log("updateContadores - ID: $homologacaoId, Inicial: " . var_export($contadorInicial, true) . ", Final: " . var_export($contadorFinal, true));

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE homologacoes SET contador_inicial = ?, contador_final = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$contadorInicial, $contadorFinal, $homologacaoId]);
            
            $this->db->commit();

            echo json_encode(['success' => true, 'message' => 'Contadores atualizados com sucesso']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erro ao atualizar contadores da homologação: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar contadores: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Atualizar status da homologação
     */
    public function updateStatus()
    {
        header('Content-Type: application/json');

        try {
            $homologacaoId = (int)($_POST['homologacao_id'] ?? 0);
            $novoStatus = $_POST['status'] ?? '';
            $localHomologacao = trim($_POST['local_homologacao'] ?? '');
            $dataInicioHomologacao = trim($_POST['data_inicio_homologacao'] ?? '');
            $alertaFinalizacao = trim($_POST['alerta_finalizacao'] ?? '');
            $testeCliente = trim($_POST['teste_cliente'] ?? '');
            $observacao = trim($_POST['observacao'] ?? '');

            if (!$homologacaoId || !$novoStatus) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                exit;
            }

            // Pegar departamento_id apenas se for fornecido
            $departamentoId = null;
            if (isset($_POST['departamento_id']) && $_POST['departamento_id'] !== '') {
                $departamentoId = (int)$_POST['departamento_id'];
            }

            // Se mudar para "em_analise", departamento é obrigatório
            if ($novoStatus === 'em_analise' && empty($departamentoId)) {
                echo json_encode(['success' => false, 'message' => 'Selecione o Departamento (Localização) para status Em Análise']);
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

            // Preparar update dinâmico
            $updates = ["status = ?", "updated_at = NOW()"];
            $params = [$novoStatus];

            if (!empty($departamentoId)) {
                $updates[] = "departamento_id = ?";
                $params[] = $departamentoId;
            }

            if (!empty($localHomologacao)) {
                $updates[] = "local_homologacao = ?";
                $params[] = $localHomologacao;
            }

            if (!empty($dataInicioHomologacao)) {
                $updates[] = "data_inicio_homologacao = ?";
                $params[] = $dataInicioHomologacao;
            }

            if (!empty($alertaFinalizacao)) {
                $updates[] = "alerta_finalizacao = ?";
                $params[] = $alertaFinalizacao;
            }

            if (!empty($testeCliente)) {
                $updates[] = "teste_cliente = ?";
                $params[] = $testeCliente;
            }

            $params[] = $homologacaoId;

            // Atualizar status e campos adicionais
            $sql = "UPDATE homologacoes SET " . implode(", ", $updates) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            // Registrar no histórico
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes_historico (
                    homologacao_id, 
                    status_anterior, 
                    status_novo, 
                    usuario_id, 
                    observacao, 
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $homologacaoId,
                $homologacao['status'],
                $novoStatus,
                $this->getUsuarioIdLog(),
                $observacao ?: "Status alterado de {$homologacao['status']} para {$novoStatus}"
            ]);

            $this->db->commit();

            // Buscar dados completos da homologação já com o novo status
            $stmt = $this->db->prepare("SELECT * FROM homologacoes WHERE id = ?");
            $stmt->execute([$homologacaoId]);
            $homologacaoAtual = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => $homologacaoId, 'status' => $novoStatus];

            // Enviar e-mail para TODOS os responsáveis marcados desta homologação
            $emailsResp = $this->getResponsaveisEmails($homologacaoId);
            if (!empty($emailsResp)) {
                $this->enviarEmailStatusHomologacao($emailsResp, $homologacaoAtual, $novoStatus, $observacao);
            }

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
     * Atualizar status da homologação via ID (usado pelas setas e drag & drop)
     */
    public function updateStatusById($id)
    {
        header('Content-Type: application/json');

        try {
            $homologacaoId = (int)$id;
            
            // Ler JSON do body
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            $novoStatus    = $data['status'] ?? '';
            $observacao    = trim($data['observacao'] ?? '');

            if (!$homologacaoId || !$novoStatus) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                exit;
            }

            // Validar status
            $statusValidos = ['aguardando_recebimento', 'recebido', 'em_analise', 'em_homologacao', 'aprovado', 'reprovado'];
            if (!in_array($novoStatus, $statusValidos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                exit;
            }

            // Buscar homologação
            $stmt = $this->db->prepare("SELECT status FROM homologacoes WHERE id = ?");
            $stmt->execute([$homologacaoId]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                echo json_encode(['success' => false, 'message' => 'Homologação não encontrada']);
                exit;
            }

            $statusAnterior = $homologacao['status'];

            $this->db->beginTransaction();

            // Atualizar status
            $stmt = $this->db->prepare("
                UPDATE homologacoes 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$novoStatus, $homologacaoId]);

            // Registrar no histórico
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes_historico 
                (homologacao_id, status_anterior, status_novo, usuario_id, observacao, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $homologacaoId,
                $statusAnterior,
                $novoStatus,
                $this->getUsuarioIdLog(),
                $observacao ?: "Status alterado de {$statusAnterior} para {$novoStatus}"
            ]);

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
                'status_anterior' => $statusAnterior,
                'status_novo' => $novoStatus
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao atualizar status: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Buscar detalhes de uma homologação para exibição no card
     */
    public function details($id)
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare("
                SELECT 
                    h.*,
                    u.name as criador_nome,
                    u.email as criador_email
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                WHERE h.id = ?
            ");
            $stmt->execute([(int)$id]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                echo json_encode(['success' => false, 'message' => 'Homologação não encontrada']);
                exit;
            }

            // Buscar responsáveis
            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email
                FROM homologacoes_responsaveis hr
                LEFT JOIN users u ON hr.user_id = u.id
                WHERE hr.homologacao_id = ?
                ORDER BY u.name ASC
            ");
            $stmt->execute([(int)$id]);
            $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar histórico
            $stmt = $this->db->prepare("
                SELECT hh.*, u.name as usuario_nome
                FROM homologacoes_historico hh
                LEFT JOIN users u ON hh.usuario_id = u.id
                WHERE hh.homologacao_id = ?
                ORDER BY hh.created_at DESC
            ");
            $stmt->execute([(int)$id]);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT id, nome_arquivo, tipo_arquivo, tamanho_arquivo, created_at
                FROM homologacoes_anexos
                WHERE homologacao_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([(int)$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'homologacao' => $homologacao,
                'responsaveis' => $responsaveis,
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
     * Upload de evidências/anexos
     */
    public function uploadAnexo()
    {
        header('Content-Type: application/json');

        try {
            $homologacaoId = (int)($_POST['homologacao_id'] ?? 0);

            if (!$homologacaoId) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            if (!isset($_FILES['anexo']) || $_FILES['anexo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado']);
                exit;
            }

            $arquivo = $_FILES['anexo'];
            $nomeArquivo = $arquivo['name'];
            $tipoArquivo = $arquivo['type'];
            $tamanhoArquivo = $arquivo['size'];

            // Validar tamanho (5MB max)
            if ($tamanhoArquivo > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 5MB']);
                exit;
            }

            // Ler conteúdo do arquivo
            $conteudoArquivo = file_get_contents($arquivo['tmp_name']);

            // Inserir no banco
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes_anexos (
                    homologacao_id, 
                    nome_arquivo, 
                    arquivo_blob, 
                    tipo_arquivo, 
                    tamanho_arquivo, 
                    uploaded_by, 
                    created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $homologacaoId,
                $nomeArquivo,
                $conteudoArquivo,
                $tipoArquivo,
                $tamanhoArquivo,
                $_SESSION['user_id']
            ]);

            echo json_encode([
                'success' => true, 
                'message' => 'Anexo enviado com sucesso',
                'anexo_id' => $this->db->lastInsertId()
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao fazer upload: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao enviar anexo']);
        }
        exit;
    }

    /**
     * Download de anexo
     */
    public function downloadAnexo($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT nome_arquivo, arquivo_blob, tipo_arquivo
                FROM homologacoes_anexos
                WHERE id = ?
            ");
            $stmt->execute([(int)$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                http_response_code(404);
                echo "Anexo não encontrado";
                exit;
            }

            header('Content-Type: ' . $anexo['tipo_arquivo']);
            header('Content-Disposition: attachment; filename="' . $anexo['nome_arquivo'] . '"');
            header('Content-Length: ' . strlen($anexo['arquivo_blob']));

            echo $anexo['arquivo_blob'];

        } catch (\Exception $e) {
            error_log('Erro ao fazer download: ' . $e->getMessage());
            http_response_code(500);
            echo "Erro ao fazer download";
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

            $homologacaoId = (int)($_POST['id'] ?? 0);

            if (!$homologacaoId) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            $stmt = $this->db->prepare("DELETE FROM homologacoes WHERE id = ?");
            $stmt->execute([$homologacaoId]);

            echo json_encode(['success' => true, 'message' => 'Homologação excluída com sucesso']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir homologação']);
        }
        exit;
    }

    /**
     * Enviar notificações por email e sininho
     */
    private function enviarNotificacoes(int $homologacaoId, array $responsaveis, bool $avisarLogistica)
    {
        try {
            // Buscar dados da homologação
            $stmt = $this->db->prepare("
                SELECT h.*, u.name as criador_nome 
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                WHERE h.id = ?
            ");
            $stmt->execute([$homologacaoId]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                return;
            }

            // Notificar responsáveis (sininho + email)
            foreach ($responsaveis as $userId) {
                $this->criarNotificacao(
                    (int)$userId, 
                    $homologacaoId, 
                    "Você foi designado como responsável pela homologação #{$homologacaoId} - {$homologacao['cod_referencia']}"
                );
            }

            // Buscar emails dos responsáveis
            $emailsResponsaveis = [];
            if (!empty($responsaveis)) {
                $in  = str_repeat('?,', count($responsaveis) - 1) . '?';
                $stmtEmails = $this->db->prepare("SELECT name, email FROM users WHERE id IN ($in) AND status = 'active'");
                $stmtEmails->execute(array_map('intval', $responsaveis));
                $emailsResponsaveis = $stmtEmails->fetchAll(PDO::FETCH_ASSOC);
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
                    $this->criarNotificacao(
                        (int)$user['id'], 
                        $homologacaoId, 
                        "Nova homologação aguardando recebimento: #{$homologacaoId} - {$homologacao['cod_referencia']}"
                    );
                }

                // Enviar email para logística
                $emailsLogistica = array_values(array_filter(array_map(function($u){ return $u['email'] ?? null; }, $logisticaUsers)));

                if (!empty($emailsLogistica)) {
                    $this->enviarEmailHomologacao($emailsLogistica, $homologacao, 'logistica');
                }
            }

            // Enviar email para responsáveis
            if (!empty($emailsResponsaveis)) {
                $emails = array_values(array_filter(array_map(function($r){ return $r['email'] ?? null; }, $emailsResponsaveis)));
                if (!empty($emails)) {
                    $this->enviarEmailHomologacao($emails, $homologacao, 'responsavel');
                }
            }

        } catch (\Exception $e) {
            error_log("Erro ao enviar notificações: " . $e->getMessage());
        }
    }

    /**
     * Notificar todos os membros ativos de um departamento sobre nova homologação
     */
    private function notificarDepartamento(int $homologacaoId, int $departamentoId, bool $avisarLogistica): void
    {
        try {
            // Buscar dados da homologação
            $stmt = $this->db->prepare("
                SELECT h.*, u.name as criador_nome, d.nome as dept_nome
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                LEFT JOIN departamentos d ON h.departamento_resp_id = d.id
                WHERE h.id = ?
            ");
            $stmt->execute([$homologacaoId]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$homologacao) return;

            // Buscar usuários ativos do departamento responsável (por setor ou por tabela departamentos)
            $stmtUsers = $this->db->prepare("
                SELECT u.id, u.name, u.email
                FROM users u
                WHERE u.status = 'active'
                  AND u.id != ?
                  AND (
                      u.setor = (SELECT nome FROM departamentos WHERE id = ?)
                      OR u.department = (SELECT nome FROM departamentos WHERE id = ?)
                  )
            ");
            $stmtUsers->execute([$_SESSION['user_id'] ?? 0, $departamentoId, $departamentoId]);
            $membros = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

            $mensagem = "Nova homologação #{$homologacaoId} ({$homologacao['cod_referencia']}) atribuída ao seu departamento.";
            $emails = [];

            foreach ($membros as $membro) {
                $this->criarNotificacao((int)$membro['id'], $homologacaoId, $mensagem);
                if (!empty($membro['email']) && filter_var($membro['email'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $membro['email'];
                }
            }

            // Enviar email em lote para o departamento
            if (!empty($emails)) {
                $this->enviarEmailHomologacao($emails, $homologacao, 'departamento');
            }

            // Notificar logística se solicitado
            if ($avisarLogistica) {
                $stmtLog = $this->db->query("SELECT id, email FROM users WHERE LOWER(department) = 'logistica' AND status = 'active'");
                $logUsers = $stmtLog->fetchAll(PDO::FETCH_ASSOC);
                $emailsLog = [];
                foreach ($logUsers as $u) {
                    $this->criarNotificacao((int)$u['id'], $homologacaoId,
                        "Nova homologação aguardando recebimento: #{$homologacaoId} - {$homologacao['cod_referencia']}");
                    if (!empty($u['email']) && filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
                        $emailsLog[] = $u['email'];
                    }
                }
                if (!empty($emailsLog)) {
                    $this->enviarEmailHomologacao($emailsLog, $homologacao, 'logistica');
                }
            }
        } catch (\Exception $e) {
            error_log("Erro ao notificar departamento: " . $e->getMessage());
        }
    }

    /**
     * Criar notificação no sistema (sininho)
     */
    private function criarNotificacao(int $userId, int $homologacaoId, string $mensagem)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    user_id, 
                    type, 
                    title, 
                    message, 
                    reference_type, 
                    reference_id, 
                    created_at
                )
                VALUES (?, 'homologacao', 'Nova Homologação', ?, 'homologacao', ?, NOW())
            ");
            $stmt->execute([$userId, $mensagem, $homologacaoId]);
        } catch (\Exception $e) {
            error_log("Erro ao criar notificação: " . $e->getMessage());
        }
    }

    /**
     * Envia email para responsáveis e/ou logística sobre a homologação
     */
    private function enviarEmailHomologacao(array $destinatarios, array $homologacao, string $tipo): void
    {
        try {
            if (empty($destinatarios)) return;
            $email = new EmailService();

            $assunto = ($tipo === 'logistica')
                ? "SGQ - Aguardando Recebimento: {$homologacao['cod_referencia']} (#{$homologacao['id']})"
                : "SGQ - Nova Homologação atribuída: {$homologacao['cod_referencia']}";

            $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
            $body = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='font-family: Arial,sans-serif;line-height:1.6;color:#333;max-width:680px;margin:0 auto;padding:20px;'>" .
                "<div style='background:#1e40af;color:#fff;padding:18px 24px;border-radius:10px 10px 0 0;'><h2 style='margin:0;font-size:20px;'>SGQ OTI DJ • Homologações</h2></div>" .
                "<div style='background:#fff;border:1px solid #e5e7eb;border-top:none;padding:20px'>" .
                "<p style='margin:0 0 12px'>Código: <strong>" . htmlspecialchars($homologacao['cod_referencia']) . "</strong></p>" .
                "<p style='margin:0 0 12px'>Descrição: " . nl2br(htmlspecialchars($homologacao['descricao'])) . "</p>" .
                "<p style='margin:0 0 12px'>Status: <strong>" . htmlspecialchars($homologacao['status']) . "</strong></p>" .
                "<div style='text-align:center;margin:22px 0'>" .
                "<a href='" . $appUrl . "/homologacoes' style='background:#2563eb;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold'>Abrir Homologações</a>" .
                "</div>" .
                "<p style='font-size:12px;color:#6b7280;margin-top:24px'>Este email foi enviado automaticamente pelo SGQ OTI DJ.</p>" .
                "</div></body></html>";

            $ok = $email->send($destinatarios, $assunto, $body, strip_tags($body));
            if (!$ok) {
                error_log('Falha ao enviar email de homologação: ' . ($email->getLastError() ?? 'sem detalhes'));
            }
        } catch (\Exception $e) {
            error_log('Erro ao enviar email de homologação: ' . $e->getMessage());
        }
    }

    /**
     * Registrar dados específicos de uma etapa
     */
    public function registrarDadosEtapa()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            $homologacaoId = $_POST['homologacao_id'] ?? null;
            $etapa = $_POST['etapa'] ?? null;
            $dados = $_POST['dados'] ?? [];

            if (!$homologacaoId || !$etapa) {
                echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
                return;
            }

            $userId = $_SESSION['user_id'];
            $userName = $_SESSION['user_name'] ?? 'Usuário';

            // Salvar cada campo da etapa
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes_etapas_dados (homologacao_id, etapa, campo, valor, usuario_id)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                valor = VALUES(valor), 
                usuario_id = VALUES(usuario_id),
                created_at = CURRENT_TIMESTAMP
            ");

            $camposSalvos = 0;
            foreach ($dados as $campo => $valor) {
                if (!empty($valor)) {
                    $stmt->execute([$homologacaoId, $etapa, $campo, $valor, $userId]);
                    $camposSalvos++;
                }
            }

            // Registrar no histórico detalhado
            $this->registrarHistoricoDetalhado($homologacaoId, $etapa, $dados, $userId, $userName);

            echo json_encode([
                'success' => true, 
                'message' => "Dados da etapa '$etapa' salvos com sucesso ($camposSalvos campos)"
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao registrar dados da etapa: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    /**
     * Registrar histórico detalhado com dados da etapa
     */
    private function registrarHistoricoDetalhado($homologacaoId, $etapa, $dados, $userId, $userName)
    {
        try {
            // Buscar etapa anterior
            $stmtAnterior = $this->db->prepare("
                SELECT etapa_nova 
                FROM homologacoes_historico 
                WHERE homologacao_id = ? 
                ORDER BY data_acao DESC 
                LIMIT 1
            ");
            $stmtAnterior->execute([$homologacaoId]);
            $etapaAnterior = $stmtAnterior->fetchColumn() ?: null;

            // Calcular tempo gasto na etapa anterior
            $tempoEtapa = null;
            if ($etapaAnterior) {
                $stmtTempo = $this->db->prepare("
                    SELECT TIMESTAMPDIFF(MINUTE, data_acao, NOW()) as minutos
                    FROM homologacoes_historico 
                    WHERE homologacao_id = ? AND etapa_nova = ?
                    ORDER BY data_acao DESC 
                    LIMIT 1
                ");
                $stmtTempo->execute([$homologacaoId, $etapaAnterior]);
                $tempoEtapa = $stmtTempo->fetchColumn();
            }

            // Preparar dados para JSON
            $dadosJson = json_encode($dados, JSON_UNESCAPED_UNICODE);

            // Gerar descrição da ação
            $acaoRealizada = $this->gerarDescricaoAcao($etapa, $dados);

            // Inserir no histórico
            $stmt = $this->db->prepare("
                INSERT INTO homologacoes_historico (
                    homologacao_id, etapa_anterior, etapa_nova, usuario_id, usuario_nome,
                    observacoes, dados_etapa, tempo_etapa, acao_realizada, detalhes_acao
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $homologacaoId,
                $etapaAnterior,
                $etapa,
                $userId,
                $userName,
                $dados['observacoes'] ?? null,
                $dadosJson,
                $tempoEtapa,
                $acaoRealizada,
                $this->gerarDetalhesAcao($dados)
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao registrar histórico detalhado: ' . $e->getMessage());
        }
    }

    /**
     * Gerar descrição da ação baseada na etapa
     */
    private function gerarDescricaoAcao($etapa, $dados)
    {
        switch ($etapa) {
            case 'recebido':
                return 'Material recebido e conferido';
            case 'em_analise':
                return 'Iniciada análise técnica do material';
            case 'em_homologacao':
                return 'Material em processo de homologação';
            case 'aprovado':
                return 'Homologação APROVADA - Material liberado';
            case 'reprovado':
                return 'Homologação REPROVADA - Material rejeitado';
            default:
                return 'Dados da etapa atualizados';
        }
    }

    /**
     * Gerar detalhes da ação
     */
    private function gerarDetalhesAcao($dados)
    {
        $detalhes = [];
        
        foreach ($dados as $campo => $valor) {
            if (!empty($valor) && $campo !== 'observacoes') {
                $nomeCampo = $this->formatarNomeCampo($campo);
                $detalhes[] = "$nomeCampo: $valor";
            }
        }
        
        return implode(' | ', $detalhes);
    }

    /**
     * Obter ID de usuário válido para registrar no histórico
     * Evita violação de FK quando a sessão não tem user_id válido
     */
    private function getUsuarioIdLog(): int
    {
        // Se houver user_id na sessão, usar esse
        if (!empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }

        // Fallback: buscar um usuário ativo qualquer para não quebrar FK
        try {
            $stmt = $this->db->query("SELECT id FROM users WHERE status = 'active' ORDER BY id ASC LIMIT 1");
            $id = $stmt->fetchColumn();
            if ($id) {
                return (int)$id;
            }
        } catch (\Exception $e) {
            // Se der erro aqui, deixa seguir para último fallback
        }

        // Último fallback: 1 (esperado admin padrão)
        return 1;
    }

    /**
     * Formatar nome do campo para exibição
     */
    private function formatarNomeCampo($campo)
    {
        $nomes = [
            'condicoes_material' => 'Condições do Material',
            'testes_realizados' => 'Testes Realizados',
            'resultados_testes' => 'Resultados dos Testes',
            'aprovacao_tecnica' => 'Aprovação Técnica',
            'recomendacoes' => 'Recomendações',
            'justificativa' => 'Justificativa',
            'data_recebimento' => 'Data de Recebimento',
            'responsavel_analise' => 'Responsável pela Análise'
        ];
        
        return $nomes[$campo] ?? ucfirst(str_replace('_', ' ', $campo));
    }

    /**
     * Gerar relatório completo da homologação
     */
    public function gerarRelatorio($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            // Buscar dados da homologação
            $stmt = $this->db->prepare("
                SELECT h.*, 
                       u.name as criador_nome,
                       d.nome as departamento_nome
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                LEFT JOIN departamentos d ON h.departamento_id = d.id
                WHERE h.id = ?
            ");
            $stmt->execute([$id]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                http_response_code(404);
                echo "Homologação não encontrada";
                return;
            }

            // Buscar histórico completo, resolvendo nome de usuário via JOIN
            $stmt = $this->db->prepare("
                SELECT 
                    h.*, 
                    COALESCE(h.data_acao, h.created_at) as data_acao_real,
                    COALESCE(h.usuario_nome, u.name) as usuario_responsavel
                FROM homologacoes_historico h
                LEFT JOIN users u ON u.id = h.usuario_id
                WHERE h.homologacao_id = ? 
                ORDER BY COALESCE(h.data_acao, h.created_at) ASC
            ");
            $stmt->execute([$id]);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Normalizar campos de histórico (datas e usuário) para o relatório
            foreach ($historico as &$item) {
                // Resolver nome do usuário responsável
                if (!empty($item['usuario_responsavel'])) {
                    $item['usuario_nome'] = $item['usuario_responsavel'];
                } elseif (empty($item['usuario_nome'])) {
                    $item['usuario_nome'] = 'Usuário não identificado';
                }

                // Resolver data de ação no fuso de Brasília
                $baseData = $item['data_acao_real'] ?? $item['created_at'] ?? null;
                if ($baseData) {
                    try {
                        // Assumir que o banco está em UTC e converter para America/Sao_Paulo
                        $dt = Carbon::parse($baseData, 'UTC')->timezone('America/Sao_Paulo');
                        $item['data_acao_br'] = $dt->format('d/m/Y H:i');
                    } catch (\Exception $e) {
                        $item['data_acao_br'] = null;
                    }
                } else {
                    $item['data_acao_br'] = null;
                }
            }
            unset($item);

            // Buscar dados específicos de cada etapa (se a tabela existir)
            $dadosEtapas = [];
            try {
                $stmt = $this->db->prepare("
                    SELECT etapa, campo, valor, created_at,
                           (SELECT name FROM users WHERE id = usuario_id) as usuario_nome
                    FROM homologacoes_etapas_dados 
                    WHERE homologacao_id = ? 
                    ORDER BY etapa, created_at ASC
                ");
                $stmt->execute([$id]);
                $dadosEtapas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                // Tabela não existe ainda, usar array vazio
                $dadosEtapas = [];
            }

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT 'geral' as etapa_upload, nome_arquivo, tipo_arquivo, 
                       tamanho_arquivo as tamanho_bytes, created_at,
                       (SELECT name FROM users WHERE id = uploaded_by) as usuario_nome
                FROM homologacoes_anexos 
                WHERE homologacao_id = ? 
                ORDER BY created_at ASC
            ");
            $stmt->execute([$id]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Gerar HTML do relatório
            $this->renderizarRelatorio($homologacao, $historico, $dadosEtapas, $anexos);

        } catch (\Exception $e) {
            error_log('Erro ao gerar relatório: ' . $e->getMessage());
            http_response_code(500);
            echo "Erro ao gerar relatório: " . $e->getMessage();
        }
    }

    /**
     * Renderizar HTML do relatório completo
     */
    private function renderizarRelatorio($homologacao, $historico, $dadosEtapas, $anexos)
    {
        // Organizar dados por etapa
        $etapasOrganizadas = [];
        foreach ($dadosEtapas as $dado) {
            $etapasOrganizadas[$dado['etapa']][] = $dado;
        }

        $anexosOrganizados = [];
        foreach ($anexos as $anexo) {
            $anexosOrganizados[$anexo['etapa_upload']][] = $anexo;
        }

        // Calcular tempo total usando histórico (quando existir) e Carbon em horário de Brasília
        $tzDb = 'UTC';
        $tzLocal = 'America/Sao_Paulo';

        if (!empty($historico)) {
            $primeiro = $historico[0];
            $ultimo   = $historico[count($historico) - 1];

            $inicio = Carbon::parse($primeiro['data_acao_real'] ?? $primeiro['created_at'], $tzDb)
                ->timezone($tzLocal);
            $fim    = Carbon::parse($ultimo['data_acao_real'] ?? $ultimo['created_at'], $tzDb)
                ->timezone($tzLocal);
        } else {
            // Fallback: usar created_at e data_finalizacao da homologação
            $inicio = Carbon::parse($homologacao['created_at'], $tzDb)->timezone($tzLocal);
            if (!empty($homologacao['data_finalizacao'])) {
                $fim = Carbon::parse($homologacao['data_finalizacao'], $tzDb)->timezone($tzLocal);
            } else {
                $fim = Carbon::now($tzLocal);
            }
        }

        // Intervalo total do processo
        $tempoTotal = $inicio->diff($fim);

        // Datas formatadas para o template
        $homologacao['data_inicio_br'] = $inicio->format('d/m/Y H:i');
        $homologacao['data_finalizacao_br'] = $fim ? $fim->format('d/m/Y H:i') : null;

        include __DIR__ . '/../../views/pages/homologacoes/relatorio.php';
    }

    /**
     * Buscar logs da homologação para modal
     */
    public function buscarLogs($id)
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            // Buscar dados da homologação
            $stmt = $this->db->prepare("
                SELECT id, cod_referencia, descricao, status
                FROM homologacoes 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                echo json_encode(['success' => false, 'message' => 'Homologação não encontrada']);
                return;
            }

            // Buscar histórico completo
            $stmt = $this->db->prepare("
                SELECT 
                    h.*,
                    COALESCE(h.data_acao, h.created_at) as data_acao_real,
                    DATE_FORMAT(COALESCE(h.data_acao, h.created_at), '%Y-%m-%d %H:%i:%s') as data_acao_formatada
                FROM homologacoes_historico h
                WHERE h.homologacao_id = ? 
                ORDER BY COALESCE(h.data_acao, h.created_at) ASC
            ");
            $stmt->execute([$id]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatar dados para o frontend usando Carbon (horário de Brasília)
            foreach ($logs as &$log) {
                // Usar Carbon para formatar a data no timezone de Brasília
                $dataAcao = Carbon::parse($log['data_acao_real'])->timezone('America/Sao_Paulo');
                $log['data_acao'] = $dataAcao->format('Y-m-d H:i:s');
                $log['data_acao_formatada'] = $dataAcao->format('d/m/Y H:i:s');
                
                // Garantir campos obrigatórios
                if (empty($log['acao_realizada'])) {
                    $log['acao_realizada'] = 'Mudança de status para ' . ($log['status_novo'] ?? $log['etapa_nova'] ?? 'nova etapa');
                }
                if (empty($log['usuario_nome'])) {
                    $log['usuario_nome'] = 'Usuário não identificado';
                }
                if (empty($log['etapa_nova'])) {
                    $log['etapa_nova'] = $log['status_novo'] ?? 'indefinido';
                }
                if (empty($log['etapa_anterior'])) {
                    $log['etapa_anterior'] = $log['status_anterior'] ?? null;
                }
                
                // Decodificar dados da etapa se existir
                if ($log['dados_etapa']) {
                    try {
                        $dadosEtapa = json_decode($log['dados_etapa'], true);
                        $log['dados_etapa_decoded'] = $dadosEtapa;
                    } catch (Exception $e) {
                        $log['dados_etapa_decoded'] = null;
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'homologacao' => $homologacao,
                'logs' => $logs,
                'total' => count($logs)
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao buscar logs: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    /**
     * Exportar logs em formato texto
     */
    public function exportarLogs($id)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            // Buscar dados da homologação
            $stmt = $this->db->prepare("
                SELECT h.*, u.name as criador_nome
                FROM homologacoes h
                LEFT JOIN users u ON h.created_by = u.id
                WHERE h.id = ?
            ");
            $stmt->execute([$id]);
            $homologacao = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$homologacao) {
                http_response_code(404);
                echo "Homologação não encontrada";
                return;
            }

            // Buscar histórico completo
            $stmt = $this->db->prepare("
                SELECT *,
                       COALESCE(data_acao, created_at) as data_acao_real
                FROM homologacoes_historico 
                WHERE homologacao_id = ? 
                ORDER BY COALESCE(data_acao, created_at) ASC
            ");
            $stmt->execute([$id]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Gerar arquivo de texto
            $conteudo = $this->gerarArquivoLogs($homologacao, $logs);
            
            // Headers para download
            $nomeArquivo = "logs_homologacao_{$homologacao['cod_referencia']}_" . date('Y-m-d_H-i-s') . ".txt";
            header('Content-Type: text/plain; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
            header('Content-Length: ' . strlen($conteudo));
            
            echo $conteudo;

        } catch (\Exception $e) {
            error_log('Erro ao exportar logs: ' . $e->getMessage());
            http_response_code(500);
            echo "Erro ao exportar logs: " . $e->getMessage();
        }
    }

    /**
     * Gerar conteúdo do arquivo de logs
     */
    private function gerarArquivoLogs($homologacao, $logs)
    {
        $conteudo = "=====================================\n";
        $conteudo .= "HISTÓRICO DE LOGS - HOMOLOGAÇÃO\n";
        $conteudo .= "=====================================\n\n";
        
        $conteudo .= "Código: {$homologacao['cod_referencia']}\n";
        $conteudo .= "Descrição: {$homologacao['descricao']}\n";
        $conteudo .= "Status Atual: {$homologacao['status']}\n";
        $conteudo .= "Criado por: {$homologacao['criador_nome']}\n";
        $conteudo .= "Data Criação: " . Carbon::parse($homologacao['created_at'])->timezone('America/Sao_Paulo')->format('d/m/Y H:i:s') . "\n";
        $conteudo .= "Relatório gerado em: " . Carbon::now('America/Sao_Paulo')->format('d/m/Y H:i:s') . "\n";
        $conteudo .= "Gerado por: " . ($_SESSION['user_name'] ?? 'Sistema') . "\n\n";
        
        $conteudo .= "=====================================\n";
        $conteudo .= "HISTÓRICO DETALHADO\n";
        $conteudo .= "=====================================\n\n";
        
        foreach ($logs as $index => $log) {
            $numero = $index + 1;
            // Usar Carbon para formatar no horário de Brasília
            $dataFormatada = Carbon::parse($log['data_acao_real'] ?? $log['created_at'])
                ->timezone('America/Sao_Paulo')
                ->format('d/m/Y H:i:s');
            $tempoEtapa = $log['tempo_etapa'] ? $this->formatarTempoTexto($log['tempo_etapa']) : 'N/A';
            
            $conteudo .= "#{$numero} - {$log['acao_realizada']}\n";
            $conteudo .= str_repeat("-", 50) . "\n";
            $conteudo .= "Data/Hora: {$dataFormatada}\n";
            $conteudo .= "Responsável: {$log['usuario_nome']}\n";
            $conteudo .= "Etapa: " . $this->formatarNomeEtapaTexto($log['etapa_nova']) . "\n";
            
            if ($log['etapa_anterior']) {
                $conteudo .= "Etapa Anterior: " . $this->formatarNomeEtapaTexto($log['etapa_anterior']) . "\n";
            }
            
            $conteudo .= "Tempo na Etapa: {$tempoEtapa}\n";
            
            if ($log['detalhes_acao']) {
                $conteudo .= "Detalhes: {$log['detalhes_acao']}\n";
            }
            
            if ($log['observacoes']) {
                $conteudo .= "Observações: {$log['observacoes']}\n";
            }
            
            // Dados específicos da etapa
            if ($log['dados_etapa']) {
                try {
                    $dados = json_decode($log['dados_etapa'], true);
                    if ($dados && is_array($dados)) {
                        $conteudo .= "Dados Registrados:\n";
                        foreach ($dados as $campo => $valor) {
                            if ($valor && $campo !== 'observacoes') {
                                $nomeCampo = $this->formatarNomeCampo($campo);
                                $conteudo .= "  - {$nomeCampo}: {$valor}\n";
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Ignorar erro de JSON
                }
            }
            
            $conteudo .= "\n";
        }
        
        $conteudo .= "=====================================\n";
        $conteudo .= "RESUMO\n";
        $conteudo .= "=====================================\n";
        $conteudo .= "Total de registros: " . count($logs) . "\n";
        $conteudo .= "Status final: {$homologacao['status']}\n";
        
        if ($homologacao['data_finalizacao']) {
            $dataInicio = new \DateTime($homologacao['created_at']);
            $dataFim = new \DateTime($homologacao['data_finalizacao']);
            $intervalo = $dataInicio->diff($dataFim);
            $conteudo .= "Tempo total: {$intervalo->days} dias, {$intervalo->h} horas, {$intervalo->i} minutos\n";
        }
        
        return $conteudo;
    }

    /**
     * Formatar tempo para texto
     */
    private function formatarTempoTexto($minutos)
    {
        if ($minutos < 60) {
            return "{$minutos} minutos";
        }
        
        $horas = floor($minutos / 60);
        $mins = $minutos % 60;
        
        if ($horas < 24) {
            return $mins > 0 ? "{$horas} horas e {$mins} minutos" : "{$horas} horas";
        }
        
        $dias = floor($horas / 24);
        $horasRestantes = $horas % 24;
        
        return "{$dias} dias, {$horasRestantes} horas";
    }

    /**
     * Formatar nome da etapa para texto
     */
    private function formatarNomeEtapaTexto($etapa)
    {
        $nomes = [
            'aguardando_recebimento' => 'Aguardando Recebimento',
            'recebido' => 'Recebido',
            'em_analise' => 'Em Análise',
            'em_homologacao' => 'Em Homologação',
            'aprovado' => 'Aprovado',
            'reprovado' => 'Reprovado'
        ];
        
        return $nomes[$etapa] ?? ucfirst(str_replace('_', ' ', $etapa));
    }
}
