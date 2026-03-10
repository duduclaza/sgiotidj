<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\EmailService;
use PDO;

class GarantiasController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Página principal
    public function index()
    {
        try {
            $fornecedores = $this->getFornecedores();
            $filiais = $this->getFiliais();
            $usuarios = $this->getUsuarios();
            
            $title = 'Registro de Garantias - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/garantias/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    // Página da Ficha de Garantia (standalone)
    public function ficha()
    {
        // Página standalone (sem layout)
        include __DIR__ . '/../../views/pages/garantias/ficha.php';
    }
    
    // Página de Requisição de Garantias
    public function requisicao()
    {
        try {
            // Buscar departamentos para checkboxes de notificação
            try {
                $stmt = $this->db->query("SELECT id, nome FROM departamentos ORDER BY nome ASC");
                $departamentos_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $departamentos_lista = [];
            }

            $title = 'Requisição de Garantias - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/garantias/requisicao.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    // Criar nova requisição de garantia (formulário simples)
    public function criarRequisicao()
    {
        header('Content-Type: application/json');
        
        try {
            $nome_requisitante = trim($_POST['nome_requisitante'] ?? '');
            $produto = trim($_POST['produto'] ?? '');
            $descricao_defeito = trim($_POST['descricao_defeito'] ?? '');
            $usuario_id = $_SESSION['user_id'] ?? null;
            $notificar_setores = $_POST['notificar_setores'] ?? [];

            // Validações
            if (empty($nome_requisitante)) {
                echo json_encode(['success' => false, 'message' => 'Nome do requisitante é obrigatório']);
                return;
            }
            
            if (empty($produto)) {
                echo json_encode(['success' => false, 'message' => 'Produto é obrigatório']);
                return;
            }
            
            if (empty($descricao_defeito)) {
                echo json_encode(['success' => false, 'message' => 'Descrição do defeito é obrigatória']);
                return;
            }

            if (empty($notificar_setores) || !is_array($notificar_setores)) {
                echo json_encode(['success' => false, 'message' => 'Selecione ao menos um setor para notificar.']);
                return;
            }
            
            // Gerar ticket único: RQG-YYYYMMDD-XXXX
            $prefixo = "RQG-" . date('Ymd');
            
            $stmt = $this->db->prepare("
                SELECT ticket FROM requisicoes_garantia 
                WHERE ticket LIKE ? 
                ORDER BY ticket DESC 
                LIMIT 1
            ");
            $stmt->execute(["{$prefixo}-%"]);
            $ultimoTicket = $stmt->fetchColumn();
            
            if ($ultimoTicket) {
                $partes = explode('-', $ultimoTicket);
                $sequencial = intval(end($partes)) + 1;
            } else {
                $sequencial = 1;
            }
            
            $ticket = sprintf("%s-%04d", $prefixo, $sequencial);
            
            // Processar imagens
            $imagens = [];
            if (!empty($_FILES['imagens'])) {
                foreach ($_FILES['imagens']['tmp_name'] as $key => $tmp_name) {
                    if (!empty($tmp_name) && is_uploaded_file($tmp_name)) {
                        $nome = $_FILES['imagens']['name'][$key];
                        $tipo = $_FILES['imagens']['type'][$key];
                        $conteudo = base64_encode(file_get_contents($tmp_name));
                        
                        $imagens[] = [
                            'nome' => $nome,
                            'tipo' => $tipo,
                            'conteudo' => $conteudo
                        ];
                    }
                }
            }
            
            // Inserir no banco
            $stmt = $this->db->prepare("
                INSERT INTO requisicoes_garantia (
                    ticket, nome_requisitante, produto, descricao_defeito, imagens, usuario_id, status
                ) VALUES (?, ?, ?, ?, ?, ?, 'Aguardando Recebimento')
            ");
            
            $stmt->execute([
                $ticket,
                $nome_requisitante,
                $produto,
                $descricao_defeito,
                !empty($imagens) ? json_encode($imagens) : null,
                $usuario_id
            ]);
            
            $id = $this->db->lastInsertId();
            
            // Enviar notificação por email/in-app para setores selecionados + admins
            $this->enviarNotificacaoNovaRequisicao([
                'id'               => (int)$id,
                'ticket'           => $ticket,
                'nome_requisitante'=> $nome_requisitante,
                'produto'          => $produto,
                'descricao_defeito'=> $descricao_defeito,
                'data'             => date('d/m/Y H:i'),
                'qtd_imagens'      => count($imagens),
                'notificar_setores'=> $notificar_setores,
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Requisição criada com sucesso!',
                'data' => [
                    'id' => $id,
                    'ticket' => $ticket,
                    'nome_requisitante' => $nome_requisitante,
                    'produto' => $produto,
                    'descricao_defeito' => $descricao_defeito,
                    'qtd_imagens' => count($imagens),
                    'data' => date('d/m/Y H:i')
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao criar requisição: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao criar requisição: ' . $e->getMessage()
            ]);
        }
    }

    // Enviar notificação de nova requisição por email e in-app (por setor)
    private function enviarNotificacaoNovaRequisicao(array $dados)
    {
        try {
            $setoresSelecionados = $dados['notificar_setores'] ?? [];
            $notifTitle   = "🔔 Nova Requisição de Garantia";
            $notifMessage = "Ticket {$dados['ticket']} | {$dados['nome_requisitante']} | {$dados['produto']}";
            $reqId        = (int)($dados['id'] ?? 0);
            $appUrl       = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';

            // 1. Notificação in-app para admins/super_admins (sininho)
            try {
                $admStmt = $this->db->query("
                    SELECT DISTINCT id FROM users
                    WHERE role IN ('admin', 'super_admin', 'superadmin')
                      AND (status = 'active' OR active = 1 OR (status IS NULL AND active IS NULL))
                ");
                $admins = $admStmt ? $admStmt->fetchAll(PDO::FETCH_COLUMN) : [];
            } catch (\Throwable $e) {
                try {
                    $admStmt = $this->db->query("SELECT DISTINCT id FROM users WHERE role IN ('admin','super_admin','superadmin')");
                    $admins = $admStmt ? $admStmt->fetchAll(PDO::FETCH_COLUMN) : [];
                } catch (\Throwable $e2) {
                    $admins = [];
                }
            }
            foreach ($admins as $adminId) {
                NotificationsController::create(
                    (int)$adminId,
                    $notifTitle,
                    $notifMessage,
                    'garantias_requisicao',
                    'garantias_requisicao',
                    $reqId
                );
            }

            // 2. Notificação in-app + email para usuários dos setores selecionados
            if (empty($setoresSelecionados)) {
                error_log("🔔 Nenhum setor selecionado para notificação de requisição {$dados['ticket']}");
                return;
            }

            $placeholders = implode(',', array_fill(0, count($setoresSelecionados), '?'));
            try {
                $usersStmt = $this->db->prepare("
                    SELECT DISTINCT id, name, email FROM users
                    WHERE setor IN ($placeholders)
                      AND (status = 'active' OR active = 1 OR (status IS NULL AND active IS NULL))
                ");
                $usersStmt->execute($setoresSelecionados);
                $usersDoSetor = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                // fallback sem filtro de status
                try {
                    $usersStmt = $this->db->prepare("SELECT DISTINCT id, name, email FROM users WHERE setor IN ($placeholders)");
                    $usersStmt->execute($setoresSelecionados);
                    $usersDoSetor = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (\Throwable $e2) {
                    $usersDoSetor = [];
                    error_log("Erro ao buscar usuarios dos setores: " . $e2->getMessage());
                }
            }

            $emails = [];
            foreach ($usersDoSetor as $u) {
                // In-app
                NotificationsController::create(
                    (int)$u['id'],
                    $notifTitle,
                    $notifMessage,
                    'garantias_requisicao',
                    'garantias_requisicao',
                    $reqId
                );
                // Email
                $email = trim((string)($u['email'] ?? ''));
                if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
            $emails = array_values(array_unique($emails));

            if (empty($emails)) {
                error_log("🔔 Notificações in-app criadas para setores, mas sem emails válidos para o ticket {$dados['ticket']}");
                return;
            }

            error_log("📧 Enviando notificação por email para setores [" . implode(', ', $setoresSelecionados) . "]: " . implode(', ', $emails));

            $emailService   = new EmailService();
            $subject        = "🔔 Nova Requisição de Garantia: {$dados['ticket']}";
            $setoresTexto   = implode(', ', $setoresSelecionados);

            $body = "
<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Requisição de Garantia</title></head>
<body style='font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px;'>
<div style='background:linear-gradient(135deg,#f97316,#ea580c);padding:30px;text-align:center;border-radius:10px 10px 0 0;'>
  <h1 style='color:white;margin:0;font-size:24px;'>📋 Nova Requisição de Garantia</h1>
  <p style='color:#ffe4d0;margin:5px 0 0 0;'>SGQ OTI DJ</p>
</div>
<div style='background:#fff;padding:25px;border:1px solid #e5e7eb;border-top:none;'>
  <div style='background:#fef3c7;border-left:4px solid #f59e0b;padding:15px;margin-bottom:20px;border-radius:0 8px 8px 0;'>
    <p style='margin:0;font-weight:bold;color:#92400e;'>Ticket: {$dados['ticket']}</p>
    <p style='margin:5px 0 0 0;color:#a16207;'>Status: Aguardando Recebimento</p>
  </div>
  <table style='width:100%;border-collapse:collapse;'>
    <tr><td style='padding:10px 0;border-bottom:1px solid #e5e7eb;font-weight:bold;color:#374151;width:140px;'>Requisitante:</td>
        <td style='padding:10px 0;border-bottom:1px solid #e5e7eb;color:#4b5563;'>" . htmlspecialchars($dados['nome_requisitante']) . "</td></tr>
    <tr><td style='padding:10px 0;border-bottom:1px solid #e5e7eb;font-weight:bold;color:#374151;'>Produto:</td>
        <td style='padding:10px 0;border-bottom:1px solid #e5e7eb;color:#4b5563;'>" . htmlspecialchars($dados['produto']) . "</td></tr>
    <tr><td style='padding:10px 0;border-bottom:1px solid #e5e7eb;font-weight:bold;color:#374151;'>Data:</td>
        <td style='padding:10px 0;border-bottom:1px solid #e5e7eb;color:#4b5563;'>{$dados['data']}</td></tr>
    <tr><td style='padding:10px 0;border-bottom:1px solid #e5e7eb;font-weight:bold;color:#374151;'>Setores:</td>
        <td style='padding:10px 0;border-bottom:1px solid #e5e7eb;color:#4b5563;'>" . htmlspecialchars($setoresTexto) . "</td></tr>
    <tr><td style='padding:10px 0;border-bottom:1px solid #e5e7eb;font-weight:bold;color:#374151;'>Imagens:</td>
        <td style='padding:10px 0;border-bottom:1px solid #e5e7eb;color:#4b5563;'>{$dados['qtd_imagens']} anexada(s)</td></tr>
  </table>
  <div style='margin-top:20px;'>
    <p style='font-weight:bold;color:#374151;margin-bottom:10px;'>Descrição do Defeito:</p>
    <div style='background:#f9fafb;padding:15px;border-radius:8px;color:#4b5563;'>
      " . nl2br(htmlspecialchars($dados['descricao_defeito'])) . "
    </div>
  </div>
  <div style='margin-top:25px;text-align:center;'>
    <a href='{$appUrl}/garantias/pendentes' style='display:inline-block;background:#2563eb;color:white;padding:12px 30px;text-decoration:none;border-radius:8px;font-weight:bold;'>Ver Requisições Pendentes</a>
  </div>
</div>
<div style='background:#f3f4f6;padding:15px;border-radius:0 0 10px 10px;text-align:center;border:1px solid #e5e7eb;border-top:none;'>
  <p style='margin:0;color:#6b7280;font-size:12px;'>SGQ OTI DJ - Sistema de Gestão da Qualidade<br>Esta é uma mensagem automática, por favor não responda.</p>
</div></body></html>";

            $altBody = "SGQ - Requisição de Garantia\nTicket: {$dados['ticket']}\nRequisitante: {$dados['nome_requisitante']}\nProduto: {$dados['produto']}\nSetores: {$setoresTexto}\nAcesse: {$appUrl}/garantias/pendentes";
            $emailService->send($emails, $subject, $body, $altBody);

        } catch (\Exception $e) {
            error_log("❌ Erro ao enviar notificação de requisição: " . $e->getMessage());
        }
    }
    
    // Página de Requisições Pendentes
    public function pendentes()
    {
        try {
            $title = 'Requisições Pendentes - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/garantias/pendentes.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    // Página de Consulta de Garantias
    public function consulta()
    {
        try {
            $title = 'Consulta de Garantias - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/garantias/consulta.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    // Buscar garantia por ticket ou número de série (API)
    public function buscarGarantia()
    {
        header('Content-Type: application/json');
        
        try {
            $termo = trim($_GET['termo'] ?? '');
            
            if (empty($termo)) {
                echo json_encode(['success' => false, 'message' => 'Informe um termo de busca']);
                return;
            }
            
            // Primeiro buscar na tabela de garantias registradas
            // Colunas: numero_serie, numero_lote, numero_ticket_os, numero_ticket_interno, status
            $termoLike = '%' . $termo . '%';
            $stmt = $this->db->prepare("
                SELECT g.*,
                       'registrada' as origem
                FROM garantias g
                WHERE g.numero_ticket_os LIKE ?
                   OR g.numero_serie LIKE ?
                   OR g.numero_ticket_interno LIKE ?
                   OR g.numero_lote LIKE ?
                ORDER BY g.created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([$termoLike, $termoLike, $termoLike, $termoLike]);
            $garantia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($garantia) {
                $garantia['tipo_registro'] = 'Garantia Registrada';
                echo json_encode([
                    'success' => true,
                    'data' => $garantia
                ]);
                return;
            }
            
            // Se não encontrou, buscar nas requisições pendentes
            $stmt = $this->db->prepare("
                SELECT r.*,
                       r.nome_requisitante as nome_cliente,
                       r.ticket as numero_ticket,
                       r.descricao_defeito,
                       'pendente' as origem
                FROM requisicoes_garantia r
                WHERE r.ticket LIKE ?
                   OR r.produto LIKE ?
                ORDER BY r.created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([$termoLike, $termoLike]);
            $requisicao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($requisicao) {
                $requisicao['tipo_registro'] = 'Requisição Pendente';
                $requisicao['numero_ticket'] = $requisicao['ticket'];
                $requisicao['produto_nome'] = $requisicao['produto'];
                echo json_encode([
                    'success' => true,
                    'data' => $requisicao
                ]);
                return;
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'Nenhuma garantia ou requisição encontrada com o termo: ' . $termo
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar garantia: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao realizar busca: ' . $e->getMessage()
            ]);
        }
    }
    
    // Listar requisições pendentes (API)
    public function listarRequisicoes()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM requisicoes_garantia 
                WHERE status NOT IN ('Processada', 'Cancelada')
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            $requisicoes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $requisicoes
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao listar requisições: ' . $e->getMessage()
            ]);
        }
    }
    
    // Obter requisição específica (API)
    public function getRequisicao($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM requisicoes_garantia WHERE id = ?");
            $stmt->execute([$id]);
            $requisicao = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$requisicao) {
                echo json_encode(['success' => false, 'message' => 'Requisição não encontrada']);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $requisicao
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar requisição: ' . $e->getMessage()
            ]);
        }
    }
    
    // Marcar requisição como processada
    public function marcarRequisicaoProcessada($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                UPDATE requisicoes_garantia 
                SET status = 'Processada', updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Requisição marcada como processada'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar requisição: ' . $e->getMessage()
            ]);
        }
    }
    
    // Excluir requisição (apenas admins)
    public function excluirRequisicao($id)
    {
        header('Content-Type: application/json');
        
        // Verificar se é admin
        $role = strtolower($_SESSION['user_role'] ?? '');
        if (!in_array($role, ['admin', 'super_admin', 'superadmin', 'administrador', 'master'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Acesso negado. Apenas administradores podem excluir requisições.'
            ]);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM requisicoes_garantia WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Requisição não encontrada']);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Requisição excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao excluir requisição: ' . $e->getMessage()
            ]);
        }
    }
    
    // Gerar número de ticket único
    public function gerarTicket()
    {
        header('Content-Type: application/json');
        
        try {
            // Gerar ticket único no formato: TKG-YYYYMMDD-XXXX
            $ano = date('Y');
            $mes = date('m');
            $dia = date('d');
            $prefixo = "TKG-{$ano}{$mes}{$dia}";
            
            // Buscar último ticket do dia
            $stmt = $this->db->prepare("
                SELECT numero_ticket_interno 
                FROM garantias 
                WHERE numero_ticket_interno LIKE ? 
                ORDER BY numero_ticket_interno DESC 
                LIMIT 1
            ");
            $stmt->execute(["{$prefixo}-%"]);
            $ultimoTicket = $stmt->fetchColumn();
            
            if ($ultimoTicket) {
                // Extrair número sequencial e incrementar
                $partes = explode('-', $ultimoTicket);
                $sequencial = intval(end($partes)) + 1;
            } else {
                // Primeiro ticket do dia
                $sequencial = 1;
            }
            
            // Montar ticket com padding de 4 dígitos
            $numeroTicket = sprintf("%s-%04d", $prefixo, $sequencial);
            
            error_log("🎫 Ticket gerado: {$numeroTicket}");
            
            echo json_encode([
                'success' => true,
                'ticket' => $numeroTicket,
                'message' => 'Ticket gerado com sucesso'
            ]);
            
        } catch (\Exception $e) {
            error_log("❌ Erro ao gerar ticket: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao gerar ticket: ' . $e->getMessage()
            ]);
        }
    }

    // Criar nova garantia
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            $fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);
            $filial_id = !empty($_POST['filial_id']) ? (int)$_POST['filial_id'] : null;
            $origem_garantia = $_POST['origem_garantia'] ?? '';
            $numero_nf_compras = trim($_POST['numero_nf_compras'] ?? '');
            $numero_nf_remessa_simples = trim($_POST['numero_nf_remessa_simples'] ?? '');
            $numero_nf_remessa_devolucao = trim($_POST['numero_nf_remessa_devolucao'] ?? '');
            $numero_serie = trim($_POST['numero_serie'] ?? '');
            $numero_lote = trim($_POST['numero_lote'] ?? '');
            $numero_ticket_os = trim($_POST['numero_ticket_os'] ?? '');
            $numero_ticket_interno = trim($_POST['numero_ticket_interno'] ?? '');
            $usuario_notificado_id = !empty($_POST['usuario_notificado_id']) ? (int)$_POST['usuario_notificado_id'] : null;
            $status = $_POST['status'] ?? 'Em andamento';
            $observacao = trim($_POST['observacao'] ?? '');
            $descricao_defeito = trim($_POST['descricao_defeito'] ?? '');
            $user_id = $_SESSION['user_id'];

            // Validações
            if ($fornecedor_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Fornecedor é obrigatório']);
                return;
            }

            if (empty($origem_garantia)) {
                echo json_encode(['success' => false, 'message' => 'Origem da garantia é obrigatória']);
                return;
            }

            // Validar observação obrigatória para status específicos
            $statusComObservacao = ['Finalizado', 'Garantia Expirada', 'Garantia não coberta'];
            if (in_array($status, $statusComObservacao) && empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação é obrigatória para este status']);
                return;
            }

            // Validar itens
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            error_log("DEBUG - Itens recebidos: " . print_r($itens, true));
            
            if (empty($itens)) {
                echo json_encode(['success' => false, 'message' => 'Pelo menos um item é obrigatório']);
                return;
            }

            $this->db->beginTransaction();

            // Inserir garantia
            $stmt = $this->db->prepare("
                INSERT INTO garantias (
                    fornecedor_id, filial_id, origem_garantia, numero_nf_compras, numero_nf_remessa_simples, 
                    numero_nf_remessa_devolucao, numero_serie, numero_lote, 
                    numero_ticket_os, numero_ticket_interno, usuario_notificado_id, status, observacao, descricao_defeito
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $fornecedor_id, $filial_id, $origem_garantia, $numero_nf_compras, $numero_nf_remessa_simples,
                $numero_nf_remessa_devolucao, $numero_serie, $numero_lote,
                $numero_ticket_os, $numero_ticket_interno, $usuario_notificado_id, $status, $observacao, $descricao_defeito
            ]);
            
            $garantia_id = $this->db->lastInsertId();
            
            // Registrar histórico inicial
            $this->registrarHistoricoStatus($garantia_id, null, $status, 'Garantia criada');
            
            // Enviar notificação se houver usuário configurado
            if ($usuario_notificado_id) {
                $this->enviarNotificacaoStatus($garantia_id, $status);
            }

            // Inserir itens
            foreach ($itens as $index => $item) {
                error_log("DEBUG - Processando item $index: " . print_r($item, true));
                
                if (empty($item['descricao']) || $item['quantidade'] <= 0 || $item['valor_unitario'] < 0) {
                    error_log("DEBUG - Item $index ignorado - dados inválidos");
                    continue;
                }

                $stmt = $this->db->prepare("
                    INSERT INTO garantias_itens (
                        garantia_id, descricao, quantidade, valor_unitario,
                        tipo_produto, produto_id, codigo_produto, nome_produto
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $result = $stmt->execute([
                    $garantia_id, 
                    trim($item['descricao']), 
                    (int)$item['quantidade'], 
                    (float)$item['valor_unitario'],
                    !empty($item['tipo_produto']) ? $item['tipo_produto'] : null,
                    !empty($item['produto_id']) ? (int)$item['produto_id'] : null,
                    !empty($item['codigo_produto']) ? trim($item['codigo_produto']) : null,
                    !empty($item['nome_produto']) ? trim($item['nome_produto']) : null
                ]);
                error_log("DEBUG - Item $index inserido: " . ($result ? 'SUCESSO' : 'ERRO'));
                
                if (!$result) {
                    error_log("DEBUG - Erro SQL: " . print_r($stmt->errorInfo(), true));
                }
            }
            
            // Processar anexos
            $this->processarAnexos($garantia_id);
            
            // Processar imagens vindas da requisição (se houver)
            $this->processarImagensRequisicao($garantia_id);
            
            // Processar dados de logística se fornecidos
            $this->processarLogistica($garantia_id);

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Garantia criada com sucesso!', 'id' => $garantia_id]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao criar garantia: ' . $e->getMessage()]);
        }
    }

    // Listar garantias
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT g.*, 
                       f.nome as fornecedor_nome,
                       fil.nome as filial_nome,
                       COUNT(DISTINCT ga.id) as total_anexos,
                       COALESCE(g.total_itens, 0) as total_itens,
                       COALESCE(g.valor_total, 0) as valor_total
                FROM garantias g
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                LEFT JOIN filiais fil ON g.filial_id = fil.id
                LEFT JOIN garantias_anexos ga ON g.id = ga.garantia_id
                GROUP BY g.id, g.total_itens, g.valor_total
                ORDER BY g.created_at DESC
            ");
            $stmt->execute();
            $garantias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar produtos e quantidades dos itens para cada garantia
            $stmtItens = $this->db->prepare("
                SELECT codigo_produto, nome_produto, tipo_produto, descricao, quantidade
                FROM garantias_itens
                WHERE garantia_id = ?
                ORDER BY id
            ");
            
            // Garantir que os valores numéricos estejam corretos e adicionar produtos
            foreach ($garantias as &$garantia) {
                $garantia['total_itens'] = (int)($garantia['total_itens'] ?? 0);
                $garantia['valor_total'] = (float)($garantia['valor_total'] ?? 0);
                $garantia['total_anexos'] = (int)($garantia['total_anexos'] ?? 0);
                
                // Buscar produtos dos itens
                $stmtItens->execute([$garantia['id']]);
                $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
                
                // Calcular total de quantidade somando todas as quantidades dos itens
                $total_quantidade = 0;
                foreach ($itens as $item) {
                    $total_quantidade += (int)($item['quantidade'] ?? 0);
                }
                $garantia['total_quantidade'] = $total_quantidade;
                
                // Criar lista de produtos (máximo 3 para não ficar muito grande)
                $produtos = [];
                foreach ($itens as $index => $item) {
                    if ($index >= 3) break; // Limitar a 3 produtos
                    
                    $produto = '';
                    if (!empty($item['tipo_produto'])) {
                        $produto .= $item['tipo_produto'] . ': ';
                    }
                    if (!empty($item['codigo_produto'])) {
                        $produto .= $item['codigo_produto'];
                    } elseif (!empty($item['nome_produto'])) {
                        $produto .= $item['nome_produto'];
                    } elseif (!empty($item['descricao'])) {
                        $produto .= $item['descricao'];
                    }
                    
                    if (!empty($produto)) {
                        $produtos[] = $produto;
                    }
                }
                
                // Se tem mais de 3 itens, adicionar indicador
                if (count($itens) > 3) {
                    $produtos[] = '+ ' . (count($itens) - 3) . ' mais';
                }
                
                $garantia['produtos_lista'] = !empty($produtos) ? implode(', ', $produtos) : null;
            }

            echo json_encode(['success' => true, 'data' => $garantias]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar garantias: ' . $e->getMessage()]);
        }
    }

    // Página de detalhes da garantia
    public function detalhes($id)
    {
        try {
            // Buscar garantia com dados relacionados
            $stmt = $this->db->prepare("
                SELECT g.*, 
                       f.nome as fornecedor_nome,
                       u.name as usuario_notificado_nome,
                       u.email as usuario_notificado_email
                FROM garantias g
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                LEFT JOIN users u ON g.usuario_notificado_id = u.id
                WHERE g.id = ?
            ");
            $stmt->execute([$id]);
            $garantia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$garantia) {
                header('Location: /garantias');
                exit;
            }
            
            // Buscar itens
            $stmt = $this->db->prepare("SELECT * FROM garantias_itens WHERE garantia_id = ? ORDER BY id");
            $stmt->execute([$id]);
            $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar histórico REAL de status
            $stmt = $this->db->prepare("
                SELECT h.*, u.name as usuario_nome
                FROM garantias_historico_status h
                LEFT JOIN users u ON h.usuario_id = u.id
                WHERE h.garantia_id = ?
                ORDER BY h.data_mudanca DESC
            ");
            $stmt->execute([$id]);
            $historicoStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular tempo REAL por status
            $temposPorStatus = [];
            $tempoTotalSegundos = 0;
            
            for ($i = count($historicoStatus) - 1; $i >= 0; $i--) {
                $atual = $historicoStatus[$i];
                $status = $atual['status_novo'];
                
                // Data de início deste status
                $dataInicio = strtotime($atual['data_mudanca']);
                
                // Data de fim: próximo status ou agora
                if ($i > 0) {
                    $dataFim = strtotime($historicoStatus[$i - 1]['data_mudanca']);
                } else {
                    $dataFim = time(); // Status atual
                }
                
                $segundos = $dataFim - $dataInicio;
                $tempoTotalSegundos += $segundos;
                
                // Somar ao status (pode ter ficado mais de uma vez no mesmo status)
                if (!isset($temposPorStatus[$status])) {
                    $temposPorStatus[$status] = ['segundos' => 0];
                }
                $temposPorStatus[$status]['segundos'] += $segundos;
            }
            
            // Formatar tempos
            foreach ($temposPorStatus as $status => &$dados) {
                $dados['tempo_formatado'] = $this->formatarTempo($dados['segundos']);
            }
            $tempoTotal = $this->formatarTempo($tempoTotalSegundos);
            
            $title = 'Registro de Garantia #' . $garantia['id'] . ' - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/garantias/detalhes.php';
            include __DIR__ . '/../../views/layouts/main.php';
            
        } catch (\Exception $e) {
            error_log("Erro ao exibir detalhes da garantia: " . $e->getMessage());
            header('Location: /garantias');
            exit;
        }
    }
    
    private function formatarTempo($segundos)
    {
        if ($segundos < 60) {
            return $segundos . ' seg';
        } elseif ($segundos < 3600) {
            $minutos = floor($segundos / 60);
            return $minutos . ' min';
        } elseif ($segundos < 86400) {
            $horas = floor($segundos / 3600);
            $minutos = floor(($segundos % 3600) / 60);
            return $horas . 'h ' . $minutos . 'min';
        } else {
            $dias = floor($segundos / 86400);
            $horas = floor(($segundos % 86400) / 3600);
            return $dias . 'd ' . $horas . 'h';
        }
    }
    
    // Obter detalhes de uma garantia (JSON)
    public function show($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Buscar garantia
            $stmt = $this->db->prepare("
                SELECT g.*, f.nome as fornecedor_nome
                FROM garantias g
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                WHERE g.id = ?
            ");
            $stmt->execute([$id]);
            $garantia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$garantia) {
                echo json_encode(['success' => false, 'message' => 'Garantia não encontrada']);
                return;
            }

            // Buscar itens
            $stmt = $this->db->prepare("
                SELECT * FROM garantias_itens 
                WHERE garantia_id = ? 
                ORDER BY id
            ");
            $stmt->execute([$id]);
            $garantia['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar anexos
            $stmt = $this->db->prepare("
                SELECT id, tipo_anexo, nome_arquivo, tamanho_bytes, created_at
                FROM garantias_anexos 
                WHERE garantia_id = ?
                ORDER BY tipo_anexo, created_at
            ");
            $stmt->execute([$id]);
            $garantia['anexos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar dados de logística
            $stmt = $this->db->prepare("
                SELECT * FROM logistica_garantias 
                WHERE garantia_id = ?
            ");
            $stmt->execute([$id]);
            $logistica = $stmt->fetch(PDO::FETCH_ASSOC);
            $garantia['logistica'] = $logistica ?: null;

            echo json_encode(['success' => true, 'data' => $garantia]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar garantia: ' . $e->getMessage()]);
        }
    }

    // Atualizar garantia
    public function update($id)
    {
        header('Content-Type: application/json');
        
        try {
            $fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);
            $filial_id = !empty($_POST['filial_id']) ? (int)$_POST['filial_id'] : null;
            $origem_garantia = $_POST['origem_garantia'] ?? '';
            $numero_nf_compras = trim($_POST['numero_nf_compras'] ?? '');
            $numero_nf_remessa_simples = trim($_POST['numero_nf_remessa_simples'] ?? '');
            $numero_nf_remessa_devolucao = trim($_POST['numero_nf_remessa_devolucao'] ?? '');
            $numero_serie = trim($_POST['numero_serie'] ?? '');
            $numero_lote = trim($_POST['numero_lote'] ?? '');
            $numero_ticket_os = trim($_POST['numero_ticket_os'] ?? '');
            $numero_ticket_interno = trim($_POST['numero_ticket_interno'] ?? '');
            $usuario_notificado_id = !empty($_POST['usuario_notificado_id']) ? (int)$_POST['usuario_notificado_id'] : null;
            $status = $_POST['status'] ?? 'Em andamento';
            $observacao = trim($_POST['observacao'] ?? '');
            $descricao_defeito = trim($_POST['descricao_defeito'] ?? '');
            
            // Buscar status anterior para comparar
            $stmtAnterior = $this->db->prepare("SELECT status FROM garantias WHERE id = ?");
            $stmtAnterior->execute([$id]);
            $garantiaAnterior = $stmtAnterior->fetch(PDO::FETCH_ASSOC);
            $statusAnterior = $garantiaAnterior['status'] ?? null;

            // Validações
            if ($fornecedor_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Fornecedor é obrigatório']);
                return;
            }

            if (empty($origem_garantia)) {
                echo json_encode(['success' => false, 'message' => 'Origem da garantia é obrigatória']);
                return;
            }

            // Validar observação obrigatória para status específicos
            $statusComObservacao = ['Finalizado', 'Garantia Expirada', 'Garantia não coberta'];
            if (in_array($status, $statusComObservacao) && empty($observacao)) {
                echo json_encode(['success' => false, 'message' => 'Observação é obrigatória para este status']);
                return;
            }

            $this->db->beginTransaction();

            // Atualizar garantia
            $stmt = $this->db->prepare("
                UPDATE garantias SET
                    fornecedor_id = ?, filial_id = ?, numero_nf_compras = ?, numero_nf_remessa_simples = ?,
                    numero_nf_remessa_devolucao = ?, numero_serie = ?, numero_lote = ?,
                    numero_ticket_os = ?, numero_ticket_interno = ?, usuario_notificado_id = ?,
                    origem_garantia = ?, status = ?, observacao = ?, descricao_defeito = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([
                $fornecedor_id, $filial_id, $numero_nf_compras, $numero_nf_remessa_simples,
                $numero_nf_remessa_devolucao, $numero_serie, $numero_lote,
                $numero_ticket_os, $numero_ticket_interno, $usuario_notificado_id,
                $origem_garantia, $status, $observacao, $descricao_defeito, $id
            ]);
            
            // Verificar se houve mudança de status
            if ($statusAnterior && $statusAnterior !== $status) {
                $this->registrarHistoricoStatus($id, $statusAnterior, $status, 'Status atualizado');
                
                // Enviar notificação se houver usuário configurado
                if ($usuario_notificado_id) {
                    $this->enviarNotificacaoStatus($id, $status);
                }
            }

            // Atualizar itens se fornecidos
            if (isset($_POST['itens'])) {
                $itens = json_decode($_POST['itens'], true);
                error_log("DEBUG UPDATE - Itens recebidos: " . print_r($itens, true));
                
                // Remover itens existentes
                $stmt = $this->db->prepare("DELETE FROM garantias_itens WHERE garantia_id = ?");
                $stmt->execute([$id]);
                error_log("DEBUG UPDATE - Itens antigos removidos para garantia $id");

                // Inserir novos itens
                foreach ($itens as $index => $item) {
                    error_log("DEBUG UPDATE - Processando item $index: " . print_r($item, true));
                    
                    if (empty($item['descricao']) || $item['quantidade'] <= 0 || $item['valor_unitario'] < 0) {
                        error_log("DEBUG UPDATE - Item $index ignorado - dados inválidos");
                        continue;
                    }

                    $stmt = $this->db->prepare("
                        INSERT INTO garantias_itens (
                            garantia_id, descricao, quantidade, valor_unitario,
                            tipo_produto, produto_id, codigo_produto, nome_produto
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $result = $stmt->execute([
                        $id,
                        trim($item['descricao']),
                        (int)$item['quantidade'],
                        (float)$item['valor_unitario'],
                        !empty($item['tipo_produto']) ? $item['tipo_produto'] : null,
                        !empty($item['produto_id']) ? (int)$item['produto_id'] : null,
                        !empty($item['codigo_produto']) ? trim($item['codigo_produto']) : null,
                        !empty($item['nome_produto']) ? trim($item['nome_produto']) : null
                    ]);
                    error_log("DEBUG UPDATE - Item $index inserido: " . ($result ? 'SUCESSO' : 'ERRO'));
                    
                    if (!$result) {
                        error_log("DEBUG UPDATE - Erro SQL: " . print_r($stmt->errorInfo(), true));
                    }
                }
            }

            // Processar novos anexos se houver
            if (!empty($_FILES)) {
                $this->processarAnexos($id);
            }
            
            // Processar dados de logística
            $this->processarLogistica($id);

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Garantia atualizada com sucesso!']);

        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar garantia: ' . $e->getMessage()]);
        }
    }

    // Excluir garantia
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("DELETE FROM garantias WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Garantia não encontrada']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Garantia excluída com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir garantia: ' . $e->getMessage()]);
        }
    }

    // Atualizar apenas o status da garantia (COM NOTIFICAÇÃO)
    public function updateStatus($id)
    {
        header('Content-Type: application/json');
        
        try {
            $status = $_POST['status'] ?? '';
            $tratativa_final = isset($_POST['tratativa_final']) ? trim($_POST['tratativa_final']) : null;
            
            error_log("📧 updateStatus chamado para garantia #{$id} com novo status: {$status}");
            
            // Validar status
            $statusValidos = [
                'Em andamento', 'Aguardando Fornecedor', 'Aguardando Recebimento',
                'Aguardando Item Chegar ao laboratório', 'Aguardando Emissão de NF',
                'Aguardando Despache', 'Aguardando Testes', 'Finalizado',
                'Garantia Expirada', 'Garantia não coberta'
            ];
            
            if (!in_array($status, $statusValidos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }
            
            // Validar tratativa final obrigatória para status Finalizado
            if ($status === 'Finalizado' && empty($tratativa_final)) {
                echo json_encode(['success' => false, 'message' => 'A tratativa final é obrigatória para finalizar a garantia']);
                return;
            }
            
            // Buscar status anterior e usuario_notificado_id ANTES de atualizar
            $stmt = $this->db->prepare("
                SELECT status, usuario_notificado_id 
                FROM garantias 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $garantiaAtual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$garantiaAtual) {
                echo json_encode(['success' => false, 'message' => 'Garantia não encontrada']);
                return;
            }
            
            $statusAnterior = $garantiaAtual['status'];
            $usuarioNotificadoId = $garantiaAtual['usuario_notificado_id'];
            
            error_log("📊 Status anterior: {$statusAnterior}, Usuário notificado: " . ($usuarioNotificadoId ?: 'nenhum'));
            
            // Verificar se houve mudança de status
            if ($statusAnterior === $status && empty($tratativa_final)) {
                echo json_encode(['success' => true, 'message' => 'Status já está atualizado']);
                return;
            }
            
            // Atualizar status e tratativa final (se Finalizado)
            if ($status === 'Finalizado' && !empty($tratativa_final)) {
                $stmt = $this->db->prepare("
                    UPDATE garantias 
                    SET status = ?, tratativa_final = ?, data_finalizacao = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$status, $tratativa_final, $id]);
                error_log("✅ Status atualizado para Finalizado com tratativa: {$tratativa_final}");
            } else {
                $stmt = $this->db->prepare("
                    UPDATE garantias 
                    SET status = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$status, $id]);
            }
            
            // Registrar no histórico de status
            $observacaoHistorico = 'Status atualizado via grid';
            if (!empty($tratativa_final)) {
                $observacaoHistorico .= '. Tratativa: ' . substr($tratativa_final, 0, 200);
            }
            $this->registrarHistoricoStatus($id, $statusAnterior, $status, $observacaoHistorico);
            error_log("✅ Histórico de status registrado");
            
            // Enviar notificação se houver usuário configurado
            if ($usuarioNotificadoId) {
                error_log("📧 Enviando notificação para usuário #{$usuarioNotificadoId}");
                $this->enviarNotificacaoStatus($id, $status);
            } else {
                error_log("⚠️ Nenhum usuário configurado para notificação");
            }
            
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
            
        } catch (\Exception $e) {
            error_log("❌ Erro em updateStatus: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
        }
    }

    // Atualizar apenas a tratativa final (sem mudar status)
    public function updateTratativa($id)
    {
        header('Content-Type: application/json');
        
        try {
            $tratativa_final = isset($_POST['tratativa_final']) ? trim($_POST['tratativa_final']) : null;
            
            error_log("📝 updateTratativa chamado para garantia #{$id}");
            
            // Verificar se a garantia existe
            $stmt = $this->db->prepare("SELECT id FROM garantias WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Garantia não encontrada']);
                return;
            }
            
            // Atualizar tratativa
            if (empty($tratativa_final)) {
                // Excluir tratativa
                $stmt = $this->db->prepare("
                    UPDATE garantias 
                    SET tratativa_final = NULL, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$id]);
                error_log("🗑️ Tratativa final excluída para garantia #{$id}");
            } else {
                // Atualizar tratativa
                $stmt = $this->db->prepare("
                    UPDATE garantias 
                    SET tratativa_final = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$tratativa_final, $id]);
                error_log("✅ Tratativa final atualizada para garantia #{$id}");
            }
            
            echo json_encode(['success' => true, 'message' => 'Tratativa atualizada com sucesso!']);
            
        } catch (\Exception $e) {
            error_log("❌ Erro em updateTratativa: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar tratativa: ' . $e->getMessage()]);
        }
    }

    // Download de anexo
    public function downloadAnexo($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT nome_arquivo, tipo_mime, tamanho_bytes, conteudo_arquivo
                FROM garantias_anexos 
                WHERE id = ?
            ");
            $stmt->execute([$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                http_response_code(404);
                echo 'Anexo não encontrado';
                return;
            }

            header('Content-Type: ' . $anexo['tipo_mime']);
            header('Content-Length: ' . $anexo['tamanho_bytes']);
            header('Content-Disposition: attachment; filename="' . $anexo['nome_arquivo'] . '"');
            
            echo $anexo['conteudo_arquivo'];
            exit();

        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao baixar anexo: ' . $e->getMessage();
        }
    }

    // Download de todos os anexos em ZIP
    public function downloadAllAnexos($garantiaId)
    {
        try {
            // Buscar todos os anexos da garantia
            $stmt = $this->db->prepare("
                SELECT nome_arquivo, tipo_mime, conteudo_arquivo, tipo_anexo
                FROM garantias_anexos 
                WHERE garantia_id = ?
                ORDER BY tipo_anexo, nome_arquivo
            ");
            $stmt->execute([$garantiaId]);
            $anexos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($anexos)) {
                http_response_code(404);
                echo 'Nenhum anexo encontrado para esta garantia';
                return;
            }

            // Criar arquivo ZIP temporário
            $zipFilename = tempnam(sys_get_temp_dir(), 'garantia_' . $garantiaId . '_anexos_');
            $zip = new \ZipArchive();
            
            if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('Não foi possível criar o arquivo ZIP');
            }

            // Adicionar cada anexo ao ZIP
            foreach ($anexos as $anexo) {
                $filename = $anexo['tipo_anexo'] . '_' . $anexo['nome_arquivo'];
                $zip->addFromString($filename, $anexo['conteudo_arquivo']);
            }

            $zip->close();

            // Enviar o arquivo ZIP
            $zipContent = file_get_contents($zipFilename);
            unlink($zipFilename); // Limpar arquivo temporário

            header('Content-Type: application/zip');
            header('Content-Length: ' . strlen($zipContent));
            header('Content-Disposition: attachment; filename="garantia_' . $garantiaId . '_anexos.zip"');
            
            echo $zipContent;
            exit();

        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao criar arquivo ZIP: ' . $e->getMessage();
        }
    }

    // Excluir anexo
    public function deleteAnexo($id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("DELETE FROM garantias_anexos WHERE id = ?");
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'message' => 'Anexo não encontrado']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Anexo excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir anexo: ' . $e->getMessage()]);
        }
    }

    // Listar fornecedores (endpoint AJAX)
    public function listFornecedores()
    {
        header('Content-Type: application/json');
        
        try {
            $fornecedores = $this->getFornecedores();
            
            // Debug adicional
            $debug = [
                'count' => count($fornecedores),
                'query_executed' => true,
                'sample' => array_slice($fornecedores, 0, 3) // Primeiros 3 registros
            ];
            
            echo json_encode([
                'success' => true, 
                'data' => $fornecedores,
                'debug' => $debug
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao carregar fornecedores: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }

    // Métodos auxiliares
    private function processarAnexos($garantia_id)
    {
        $tiposAnexos = [
            'anexo_nf_compras' => 'nf_compras',
            'anexo_nf_remessa_simples' => 'nf_remessa_simples',
            'anexo_nf_remessa_devolucao' => 'nf_remessa_devolucao',
            'anexo_laudo_tecnico' => 'laudo_tecnico'
        ];

        // Processar anexos específicos
        foreach ($tiposAnexos as $campo => $tipo) {
            if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                $this->salvarAnexo($garantia_id, $_FILES[$campo], $tipo);
            }
        }

        // Processar evidências (múltiplas imagens)
        if (isset($_FILES['anexo_evidencias']) && is_array($_FILES['anexo_evidencias']['tmp_name'])) {
            foreach ($_FILES['anexo_evidencias']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['anexo_evidencias']['error'][$index] === UPLOAD_ERR_OK) {
                    $arquivo = [
                        'name' => $_FILES['anexo_evidencias']['name'][$index],
                        'type' => $_FILES['anexo_evidencias']['type'][$index],
                        'tmp_name' => $tmpName,
                        'size' => $_FILES['anexo_evidencias']['size'][$index]
                    ];
                    $this->salvarAnexo($garantia_id, $arquivo, 'evidencia');
                }
            }
        }
    }
    
    // Processar imagens vindas de uma requisição de garantia
    private function processarImagensRequisicao($garantia_id)
    {
        if (empty($_POST['imagens_requisicao'])) {
            return;
        }
        
        try {
            $imagens = json_decode($_POST['imagens_requisicao'], true);
            
            if (!$imagens || !is_array($imagens)) {
                return;
            }
            
            error_log("📷 Processando " . count($imagens) . " imagens da requisição");
            
            foreach ($imagens as $img) {
                if (empty($img['conteudo']) || empty($img['tipo']) || empty($img['nome'])) {
                    continue;
                }
                
                // Decodificar base64
                $conteudo = base64_decode($img['conteudo']);
                $tamanho = strlen($conteudo);
                
                // Inserir como anexo de evidência
                $stmt = $this->db->prepare("
                    INSERT INTO garantias_anexos (
                        garantia_id, tipo_anexo, nome_arquivo, tipo_mime, tamanho_bytes, conteudo_arquivo
                    ) VALUES (?, 'evidencia', ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $garantia_id,
                    $img['nome'],
                    $img['tipo'],
                    $tamanho,
                    $conteudo
                ]);
                
                error_log("✅ Imagem '{$img['nome']}' salva como evidência");
            }
        } catch (\Exception $e) {
            error_log("❌ Erro ao processar imagens da requisição: " . $e->getMessage());
        }
    }

    private function salvarAnexo($garantia_id, $arquivo, $tipo)
    {
        // Validar tipo de arquivo
        $tiposPermitidos = [
            'application/pdf', 
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 
            'image/jpg',
            'image/png', 
            'image/gif',
            'image/webp'
        ];
        
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            throw new \Exception('Tipo de arquivo não permitido: ' . $arquivo['name']);
        }

        // Validar tamanho (10MB para PDFs/DOCs, 5MB para imagens)
        $maxSize = in_array($arquivo['type'], ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']) 
                   ? 5 * 1024 * 1024  // 5MB para imagens
                   : 10 * 1024 * 1024; // 10MB para documentos
        
        if ($arquivo['size'] > $maxSize) {
            $maxSizeMB = $maxSize / (1024 * 1024);
            throw new \Exception('Arquivo muito grande: ' . $arquivo['name'] . '. Máximo ' . $maxSizeMB . 'MB');
        }

        $conteudo = file_get_contents($arquivo['tmp_name']);

        $stmt = $this->db->prepare("
            INSERT INTO garantias_anexos (
                garantia_id, tipo_anexo, nome_arquivo, tipo_mime, 
                tamanho_bytes, conteudo_arquivo
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $garantia_id,
            $tipo,
            $arquivo['name'],
            $arquivo['type'],
            $arquivo['size'],
            $conteudo
        ]);
    }

    private function getFornecedores(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM fornecedores ORDER BY nome");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Log do erro para debug
            error_log("Erro ao buscar fornecedores: " . $e->getMessage());
            return [];
        }
    }
    
    private function getFiliais(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, nome FROM filiais ORDER BY nome");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Log do erro para debug
            error_log("Erro ao buscar filiais: " . $e->getMessage());
            return [];
        }
    }
    
    private function getUsuarios(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Erro ao buscar usuários: " . $e->getMessage());
            return [];
        }
    }
    
    private function registrarHistoricoStatus($garantia_id, $status_anterior, $status_novo, $observacao = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO garantias_historico_status 
                (garantia_id, status_anterior, status_novo, usuario_id, observacao)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $garantia_id,
                $status_anterior,
                $status_novo,
                $_SESSION['user_id'],
                $observacao
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao registrar histórico de status: " . $e->getMessage());
        }
    }
    
    private function enviarNotificacaoStatus($garantia_id, $status_novo)
    {
        try {
            // Buscar dados da garantia e usuário notificado
            $stmt = $this->db->prepare("
                SELECT g.*, u.email, u.name as nome_notificado, f.nome as fornecedor_nome
                FROM garantias g
                LEFT JOIN users u ON g.usuario_notificado_id = u.id
                LEFT JOIN fornecedores f ON g.fornecedor_id = f.id
                WHERE g.id = ? AND g.usuario_notificado_id IS NOT NULL
            ");
            $stmt->execute([$garantia_id]);
            $garantia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$garantia || !$garantia['email']) {
                error_log("Garantia #{$garantia_id}: Sem usuário notificado ou email não encontrado");
                return; // Sem usuário para notificar
            }
            
            // Buscar produtos da garantia
            $stmt = $this->db->prepare("
                SELECT tipo_produto, codigo_produto, nome_produto, descricao, quantidade
                FROM garantias_itens
                WHERE garantia_id = ?
                ORDER BY id
            ");
            $stmt->execute([$garantia_id]);
            $garantia['produtos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar histórico de status
            $stmt = $this->db->prepare("
                SELECT status_anterior, status_novo, data_mudanca
                FROM garantias_historico_status
                WHERE garantia_id = ?
                ORDER BY data_mudanca ASC
            ");
            $stmt->execute([$garantia_id]);
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular tempo em cada status
            $temposPorStatus = [];
            for ($i = 0; $i < count($historico); $i++) {
                $atual = $historico[$i];
                $status = $atual['status_novo'];
                
                $dataInicio = strtotime($atual['data_mudanca']);
                
                // Data fim: próximo status ou agora
                if ($i < count($historico) - 1) {
                    $dataFim = strtotime($historico[$i + 1]['data_mudanca']);
                } else {
                    $dataFim = time(); // Status atual
                }
                
                $segundos = $dataFim - $dataInicio;
                $dias = floor($segundos / 86400);
                
                if (!isset($temposPorStatus[$status])) {
                    $temposPorStatus[$status] = 0;
                }
                $temposPorStatus[$status] += $dias;
            }
            
            $garantia['tempos_por_status'] = $temposPorStatus;
            
            error_log("📧 Preparando email de notificação para: {$garantia['email']}");
            
            // Usar EmailService para enviar
            $emailService = new \App\Services\EmailService();
            
            $subject = "SGQ - Garantia #{$garantia_id} - Status Atualizado 🔔";
            $body = $this->buildGarantiaEmailTemplate($garantia, $status_novo, $garantia_id);
            
            $altBody = "Atualização de Garantia\n\n";
            $altBody .= "Garantia: #{$garantia_id}\n";
            $altBody .= "Fornecedor: {$garantia['fornecedor_nome']}\n";
            $altBody .= "Novo Status: {$status_novo}\n";
            $altBody .= "NF Compras: {$garantia['numero_nf_compras']}\n";
            if ($garantia['numero_ticket_os']) {
                $altBody .= "Ticket/OS: {$garantia['numero_ticket_os']}\n";
            }
            $altBody .= "\nAcesse o sistema para mais detalhes.\n";
            
            $resultado = $emailService->send($garantia['email'], $subject, $body, $altBody);
            
            if ($resultado) {
                error_log("✅ Email de garantia enviado com sucesso para {$garantia['email']}");
            } else {
                error_log("❌ Falha ao enviar email: " . $emailService->getLastError());
            }
            
        } catch (\Exception $e) {
            error_log("❌ Erro ao enviar notificação de garantia: " . $e->getMessage());
        }
    }
    
    private function buildGarantiaEmailTemplate($garantia, $status_novo, $garantia_id)
    {
        $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
        
        $statusColors = [
            'Em andamento' => '#3B82F6',
            'Aguardando Fornecedor' => '#F59E0B',
            'Aguardando Recebimento' => '#8B5CF6',
            'Aguardando Testes' => '#EC4899',
            'Finalizado' => '#10B981',
            'Garantia Expirada' => '#EF4444',
            'Garantia não coberta' => '#6B7280'
        ];
        
        $statusColor = $statusColors[$status_novo] ?? '#3B82F6';
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Atualização de Garantia</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>🛡️ Atualização de Garantia</h1>
                <p style='color: #f0f0f0; margin: 5px 0 0 0;'>SGQ OTI DJ - Sistema de Garantias</p>
            </div>
            
            <div style='background: white; padding: 30px; border: 1px solid #e0e0e0; border-top: none;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h2 style='color: #374151; margin: 0 0 10px 0;'>Olá, {$garantia['nome_notificado']}!</h2>
                    <p style='color: #666; font-size: 16px; margin: 0;'>A garantia que você está acompanhando foi atualizada.</p>
                </div>
                
                <div style='text-align: center; margin-bottom: 30px;'>
                    <div style='background: {$statusColor}; color: white; padding: 15px 25px; border-radius: 25px; display: inline-block; font-weight: bold; font-size: 18px;'>
                        {$status_novo}
                    </div>
                </div>
                
                <h3 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Detalhes da Garantia</h3>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold; width: 40%;'>Garantia:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>#{$garantia_id}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Fornecedor:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$garantia['fornecedor_nome']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Status:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'><strong style='color: {$statusColor};'>{$status_novo}</strong></td>
                    </tr>";
        
        if (!empty($garantia['numero_nf_compras'])) {
            $html .= "
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>NF Compras:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$garantia['numero_nf_compras']}</td>
                    </tr>";
        }
        
        if (!empty($garantia['numero_ticket_os'])) {
            $html .= "
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Ticket/OS:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$garantia['numero_ticket_os']}</td>
                    </tr>";
        }
        
        if (!empty($garantia['origem_garantia'])) {
            $html .= "
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Origem:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>{$garantia['origem_garantia']}</td>
                    </tr>";
        }
        
        $html .= "
                    <tr>
                        <td style='padding: 12px; background: #f8f9fa; border: 1px solid #e9ecef; font-weight: bold;'>Data da Atualização:</td>
                        <td style='padding: 12px; border: 1px solid #e9ecef;'>" . date('d/m/Y H:i') . "</td>
                    </tr>
                </table>";
        
        // Adicionar seção de produtos
        if (!empty($garantia['produtos']) && count($garantia['produtos']) > 0) {
            $html .= "
                <h3 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-top: 30px;'>📦 Produtos</h3>
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <thead style='background: #f8f9fa;'>
                        <tr>
                            <th style='padding: 10px; border: 1px solid #e9ecef; text-align: left; font-weight: bold;'>Tipo</th>
                            <th style='padding: 10px; border: 1px solid #e9ecef; text-align: left; font-weight: bold;'>Produto</th>
                            <th style='padding: 10px; border: 1px solid #e9ecef; text-align: center; font-weight: bold;'>Qtd</th>
                        </tr>
                    </thead>
                    <tbody>";
            
            foreach ($garantia['produtos'] as $produto) {
                $nomeProduto = $produto['codigo_produto'] ?: ($produto['nome_produto'] ?: $produto['descricao']);
                $tipoProduto = $produto['tipo_produto'] ?: 'N/A';
                
                $html .= "
                        <tr>
                            <td style='padding: 10px; border: 1px solid #e9ecef;'><span style='background: #dbeafe; color: #1e40af; padding: 3px 8px; border-radius: 4px; font-size: 12px;'>{$tipoProduto}</span></td>
                            <td style='padding: 10px; border: 1px solid #e9ecef;'>{$nomeProduto}</td>
                            <td style='padding: 10px; border: 1px solid #e9ecef; text-align: center;'>{$produto['quantidade']}</td>
                        </tr>";
            }
            
            $html .= "
                    </tbody>
                </table>";
        }
        
        // Adicionar seção de tempo por status
        if (!empty($garantia['tempos_por_status']) && count($garantia['tempos_por_status']) > 0) {
            $html .= "
                <h3 style='color: #333; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-top: 30px;'>⏱️ Tempo em Cada Status</h3>
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tbody>";
            
            foreach ($garantia['tempos_por_status'] as $status => $dias) {
                $diasTexto = $dias == 0 ? 'Menos de 1 dia' : ($dias == 1 ? '1 dia' : "{$dias} dias");
                
                $html .= "
                        <tr>
                            <td style='padding: 10px; border: 1px solid #e9ecef; background: #f8f9fa; font-weight: bold; width: 60%;'>{$status}:</td>
                            <td style='padding: 10px; border: 1px solid #e9ecef;'><strong style='color: #667eea;'>{$diasTexto}</strong></td>
                        </tr>";
            }
            
            $html .= "
                    </tbody>
                </table>";
        }
        
        $html .= "
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$appUrl}/garantias/{$garantia_id}/detalhes' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        👁️ Ver Detalhes Completos
                    </a>
                </div>
                
                <div style='background: #f0f9ff; border: 2px solid #bfdbfe; border-radius: 10px; padding: 20px; margin: 20px 0;'>
                    <p style='margin: 0; color: #1e40af; font-size: 14px;'>
                        <strong>💡 Dica:</strong> Você está recebendo este email porque foi configurado como pessoa notificada desta garantia. 
                        Acesse o sistema para acompanhar todas as atualizações e histórico completo.
                    </p>
                </div>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0; border-top: none;'>
                <p style='margin: 0; color: #666; font-size: 12px;'>
                    © " . date('Y') . " SGQ OTI DJ - Sistema de Gestão da Qualidade<br>
                    Este email foi enviado automaticamente, não responda.
                </p>
            </div>
        </body>
        </html>";
        
        return $html;
    }
    
    // Processar dados de logística
    private function processarLogistica($garantia_id)
    {
        // Coletar dados de logística do POST
        $nome_transportadora = trim($_POST['nome_transportadora'] ?? '');
        $cnpj_transportadora = trim($_POST['cnpj_transportadora'] ?? '');
        $peso_total = $_POST['peso_total_logistica'] ?? null;
        $altura = $_POST['altura'] ?? null;
        $largura = $_POST['largura'] ?? null;
        $profundidade = $_POST['profundidade'] ?? null;
        $observacoes_logistica = trim($_POST['observacoes_logistica'] ?? '');
        
        // Verificar se há dados de logística para salvar
        $temDados = !empty($nome_transportadora) || !empty($cnpj_transportadora) || 
                   !empty($peso_total) || !empty($altura) || !empty($largura) || 
                   !empty($profundidade) || !empty($observacoes_logistica);
        
        if (!$temDados) {
            // Se não há dados, remover registro existente se houver
            $stmt = $this->db->prepare("DELETE FROM logistica_garantias WHERE garantia_id = ?");
            $stmt->execute([$garantia_id]);
            return;
        }
        
        // Verificar se já existe registro de logística para esta garantia
        $stmt = $this->db->prepare("SELECT id FROM logistica_garantias WHERE garantia_id = ?");
        $stmt->execute([$garantia_id]);
        $existeLogistica = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Converter valores vazios para NULL
        $peso_total = !empty($peso_total) ? (float)$peso_total : null;
        $altura = !empty($altura) ? (float)$altura : null;
        $largura = !empty($largura) ? (float)$largura : null;
        $profundidade = !empty($profundidade) ? (float)$profundidade : null;
        $nome_transportadora = !empty($nome_transportadora) ? $nome_transportadora : null;
        $cnpj_transportadora = !empty($cnpj_transportadora) ? $cnpj_transportadora : null;
        $observacoes_logistica = !empty($observacoes_logistica) ? $observacoes_logistica : null;
        
        if ($existeLogistica) {
            // Atualizar registro existente
            $stmt = $this->db->prepare("
                UPDATE logistica_garantias SET
                    nome_transportadora = ?,
                    cnpj_transportadora = ?,
                    peso_total = ?,
                    altura = ?,
                    largura = ?,
                    profundidade = ?,
                    observacoes_logistica = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE garantia_id = ?
            ");
            
            $stmt->execute([
                $nome_transportadora,
                $cnpj_transportadora,
                $peso_total,
                $altura,
                $largura,
                $profundidade,
                $observacoes_logistica,
                $garantia_id
            ]);
        } else {
            // Criar novo registro
            $stmt = $this->db->prepare("
                INSERT INTO logistica_garantias (
                    garantia_id,
                    nome_transportadora,
                    cnpj_transportadora,
                    peso_total,
                    altura,
                    largura,
                    profundidade,
                    observacoes_logistica
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $garantia_id,
                $nome_transportadora,
                $cnpj_transportadora,
                $peso_total,
                $altura,
                $largura,
                $profundidade,
                $observacoes_logistica
            ]);
        }
    }
}
