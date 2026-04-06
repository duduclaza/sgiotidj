<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class Amostragens2Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Verifica se o usuário pode visualizar uma amostragem específica
     * Nova regra: quem tem acesso ao módulo pode ver TODAS as amostragens
     */
    private function podeVisualizarAmostragem($amostragemId): bool
    {
        $userId = $_SESSION['user_id'];
        
        // Quem tem permissão no módulo amostragens_2 pode ver todas as amostragens
        return PermissionService::hasPermission($userId, 'amostragens_2', 'view');
    }

    public function index(): void
    {
        try {
            // Verificar permissão
            $isAdmin = in_array($_SESSION['user_role'], ['admin', 'super_admin']);
            if (!$isAdmin && !PermissionService::hasPermission($_SESSION['user_id'], 'amostragens_2', 'view')) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }

            $userId = $_SESSION['user_id'];
            $userFilialId = $_SESSION['user_filial_id'] ?? null;

            // Buscar amostragens com filtros
            $where = [];
            $params = [];

            // Filtros
            if (!empty($_GET['numero_nf'])) {
                $where[] = "a.numero_nf LIKE :numero_nf";
                $params[':numero_nf'] = '%' . $_GET['numero_nf'] . '%';
            }

            if (!empty($_GET['codigo_produto'])) {
                $where[] = "a.codigo_produto LIKE :codigo";
                $params[':codigo'] = '%' . $_GET['codigo_produto'] . '%';
            }

            if (!empty($_GET['user_id'])) {
                $where[] = "a.user_id = :user_id";
                $params[':user_id'] = $_GET['user_id'];
            }

            if (!empty($_GET['filial_id'])) {
                $where[] = "a.filial_id = :filial_id";
                $params[':filial_id'] = $_GET['filial_id'];
            }

            if (!empty($_GET['fornecedor_id'])) {
                $where[] = "a.fornecedor_id = :fornecedor_id";
                $params[':fornecedor_id'] = $_GET['fornecedor_id'];
            }

            if (!empty($_GET['status'])) {
                $where[] = "a.status_final = :status";
                $params[':status'] = $_GET['status'];
            }

            if (!empty($_GET['data_inicio'])) {
                $where[] = "DATE(a.created_at) >= :data_inicio";
                $params[':data_inicio'] = $_GET['data_inicio'];
            }

            if (!empty($_GET['data_fim'])) {
                $where[] = "DATE(a.created_at) <= :data_fim";
                $params[':data_fim'] = $_GET['data_fim'];
            }

            // CONTROLE DE VISUALIZAÇÃO: 
            // Nova regra: Quem tem acesso ao módulo pode ver TODAS as amostragens
            // (filtro por usuário removido conforme nova política)

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            // PAGINAÇÃO
            $porPagina = isset($_GET['por_pagina']) && in_array($_GET['por_pagina'], [10, 50, 100]) 
                ? (int)$_GET['por_pagina'] 
                : 10; // Padrão: 10 registros por página
            
            $paginaAtual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0 
                ? (int)$_GET['pagina'] 
                : 1;
            
            $offset = ($paginaAtual - 1) * $porPagina;

            // Contar total de registros
            $stmtCount = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
                LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
                $whereClause
            ");
            $stmtCount->execute($params);
            $totalRegistros = (int)$stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPaginas = ceil($totalRegistros / $porPagina);

            // Buscar registros paginados
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.name as usuario_nome,
                       u.filial as filial_nome,
                       forn.nome as fornecedor_nome,
                       aprovador.name as aprovado_por_nome,
                       aprovador.email as aprovado_por_email,
                       (SELECT COUNT(*) FROM amostragens_2_evidencias WHERE amostragem_id = a.id) as total_evidencias
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
                LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
                $whereClause
                ORDER BY a.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            // Bind dos parâmetros de filtro
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            // Bind dos parâmetros de paginação
            $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $amostragens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Dados de paginação
            $paginacao = [
                'total_registros' => $totalRegistros,
                'total_paginas' => $totalPaginas,
                'pagina_atual' => $paginaAtual,
                'por_pagina' => $porPagina,
                'offset' => $offset
            ];

            // Buscar dados para dropdowns
            $stmt = $this->db->prepare('SELECT id, name FROM users WHERE status = "active" ORDER BY name');
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare('SELECT id, nome FROM filiais ORDER BY nome');
            $stmt->execute();
            $filiais = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $this->db->prepare('SELECT id, nome FROM fornecedores ORDER BY nome');
            $stmt->execute();
            $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Buscar produtos por tipo
            // Toners
            $stmt = $this->db->prepare('SELECT id, modelo as codigo, modelo as nome FROM toners ORDER BY modelo');
            $stmt->execute();
            $toners = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Máquinas
            $stmt = $this->db->prepare('SELECT id, cod_referencia as codigo, modelo as nome FROM cadastro_maquinas ORDER BY cod_referencia');
            $stmt->execute();
            $maquinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Peças
            $stmt = $this->db->prepare('SELECT id, codigo_referencia as codigo, descricao as nome FROM cadastro_pecas ORDER BY codigo_referencia');
            $stmt->execute();
            $pecas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $title = 'Amostragens 2.0 - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/amostragens-2/index.php';
            include __DIR__ . '/../../views/layouts/main.php';

        } catch (\Exception $e) {
            error_log("Erro em Amostragens 2.0: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao carregar o módulo: " . $e->getMessage();
        }
    }

    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            // Verificar se usuário está logado
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não está logado']);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            
            // Buscar usuário e sua filial do banco de dados
            $stmt = $this->db->prepare('SELECT id, filial FROM users WHERE id = :user_id');
            $stmt->execute([':user_id' => $userId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado no sistema']);
                return;
            }
            
            // Buscar ID da filial pelo nome no cadastro do usuário
            $filialId = null;
            if (!empty($usuario['filial'])) {
                $stmt = $this->db->prepare('SELECT id FROM filiais WHERE nome = :nome LIMIT 1');
                $stmt->execute([':nome' => $usuario['filial']]);
                $filialBuscada = $stmt->fetch(PDO::FETCH_ASSOC);
                $filialId = $filialBuscada['id'] ?? null;
            }
            
            // Se não encontrar filial, usar primeira disponível como fallback
            if (!$filialId) {
                $stmt = $this->db->prepare('SELECT id FROM filiais LIMIT 1');
                $stmt->execute();
                $filial = $stmt->fetch(PDO::FETCH_ASSOC);
                $filialId = $filial['id'] ?? 1;
                error_log("⚠️ Filial não encontrada para usuário #{$userId}. Usando filial padrão #{$filialId}");
            } else {
                error_log("✅ Filial encontrada para usuário #{$userId}: Filial #{$filialId} ({$usuario['filial']})");
            }

            // Validar dados
            $numeroNf = trim($_POST['numero_nf'] ?? '');
            $tipoProduto = $_POST['tipo_produto'] ?? '';
            $produtoId = (int)($_POST['produto_id'] ?? 0);
            $codigoProduto = trim($_POST['codigo_produto'] ?? '');
            $nomeProduto = trim($_POST['nome_produto'] ?? '');
            $quantidadeRecebida = (int)($_POST['quantidade_recebida'] ?? 0);
            
            // Processar status selecionado (pendente, aprovado, parcial, reprovado)
            $statusSelecionado = $_POST['status_selecionado'] ?? 'pendente';
            
            // Pegar valores dos campos hidden (já calculados no frontend)
            $quantidadeTestada = (int)($_POST['quantidade_testada'] ?? 0);
            $quantidadeAprovada = (int)($_POST['quantidade_aprovada'] ?? 0);
            $quantidadeReprovada = (int)($_POST['quantidade_reprovada'] ?? 0);
            
            // Definir status final baseado na seleção
            switch ($statusSelecionado) {
                case 'aprovado':
                    $statusFinal = 'Aprovado';
                    // Lote 100% aprovado: reprovada deve ser 0, aprovada = recebida
                    $quantidadeAprovada = $quantidadeRecebida;
                    $quantidadeReprovada = 0;
                    error_log("✅ Lote Aprovado - Testada: $quantidadeTestada, Aprovada: $quantidadeAprovada, Reprovada: 0");
                    break;
                    
                case 'parcial':
                    $statusFinal = 'Aprovado Parcialmente';
                    error_log("🔶 Parcial - Testada: $quantidadeTestada, Aprovada: $quantidadeAprovada, Reprovada: $quantidadeReprovada");
                    break;
                    
                case 'reprovado':
                    $statusFinal = 'Reprovado';
                    // Lote 100% reprovado: aprovada deve ser 0, reprovada = recebida
                    $quantidadeAprovada = 0;
                    $quantidadeReprovada = $quantidadeRecebida;
                    error_log("❌ Lote Reprovado - Testada: $quantidadeTestada, Aprovada: 0, Reprovada: $quantidadeReprovada");
                    break;
                    
                default: // pendente
                    $statusFinal = 'Pendente';
                    $quantidadeTestada = 0;
                    $quantidadeAprovada = 0;
                    $quantidadeReprovada = 0;
                    error_log("⏳ Lote Pendente - Aguardando análise");
                    break;
            }
            
            $fornecedorId = (int)($_POST['fornecedor_id'] ?? 0);
            $responsaveis = $_POST['responsaveis'] ?? [];
            $observacoes = trim($_POST['observacoes'] ?? '');

            error_log("Dados recebidos - NF: $numeroNf, Tipo: $tipoProduto, Produto: $produtoId, Fornecedor: $fornecedorId, Status: $statusFinal");

            if (empty($numeroNf) || empty($tipoProduto) || $produtoId <= 0 || $quantidadeRecebida <= 0 || $fornecedorId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios (NF, Tipo, Produto, Quantidade Recebida e Fornecedor)']);
                return;
            }
            
            // Verificar se o fornecedor existe
            $stmt = $this->db->prepare('SELECT id FROM fornecedores WHERE id = :fornecedor_id');
            $stmt->execute([':fornecedor_id' => $fornecedorId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Fornecedor selecionado não existe']);
                return;
            }

            // Processar anexo da NF
            $anexoNf = null;
            $anexoNfNome = null;
            $anexoNfTipo = null;
            $anexoNfTamanho = null;

            if (!empty($_FILES['anexo_nf']['tmp_name'])) {
                $anexoNf = file_get_contents($_FILES['anexo_nf']['tmp_name']);
                $anexoNfNome = $_FILES['anexo_nf']['name'];
                $anexoNfTipo = $_FILES['anexo_nf']['type'];
                $anexoNfTamanho = $_FILES['anexo_nf']['size'];

                if ($anexoNfTamanho > 10 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Anexo da NF deve ter no máximo 10MB']);
                    return;
                }
            }

            // Inserir amostragem
            $responsaveisStr = !empty($responsaveis) ? implode(',', $responsaveis) : '';

            $stmt = $this->db->prepare('
                INSERT INTO amostragens_2 (
                    user_id, filial_id, numero_nf, anexo_nf, anexo_nf_nome, anexo_nf_tipo, anexo_nf_tamanho,
                    tipo_produto, produto_id, codigo_produto, nome_produto,
                    quantidade_recebida, quantidade_testada, quantidade_aprovada, quantidade_reprovada,
                    fornecedor_id, responsaveis, status_final, observacoes, created_at
                ) VALUES (
                    :user_id, :filial_id, :numero_nf, :anexo_nf, :anexo_nf_nome, :anexo_nf_tipo, :anexo_nf_tamanho,
                    :tipo_produto, :produto_id, :codigo_produto, :nome_produto,
                    :quantidade_recebida, :quantidade_testada, :quantidade_aprovada, :quantidade_reprovada,
                    :fornecedor_id, :responsaveis, :status_final, :observacoes, NOW()
                )
            ');

            $stmt->execute([
                ':user_id' => $userId,
                ':filial_id' => $filialId,
                ':numero_nf' => $numeroNf,
                ':anexo_nf' => $anexoNf,
                ':anexo_nf_nome' => $anexoNfNome,
                ':anexo_nf_tipo' => $anexoNfTipo,
                ':anexo_nf_tamanho' => $anexoNfTamanho,
                ':tipo_produto' => $tipoProduto,
                ':produto_id' => $produtoId,
                ':codigo_produto' => $codigoProduto,
                ':nome_produto' => $nomeProduto,
                ':quantidade_recebida' => $quantidadeRecebida,
                ':quantidade_testada' => $quantidadeTestada,
                ':quantidade_aprovada' => $quantidadeAprovada,
                ':quantidade_reprovada' => $quantidadeReprovada,
                ':fornecedor_id' => $fornecedorId,
                ':responsaveis' => $responsaveisStr,
                ':status_final' => $statusFinal,
                ':observacoes' => $observacoes
            ]);

            $amostragemId = $this->db->lastInsertId();

            // Processar evidências (fotos)
            if (!empty($_FILES['evidencias']['tmp_name'][0])) {
                $this->processarEvidencias($amostragemId, $_FILES['evidencias']);
            }

            // Enviar email automático para responsáveis ao criar nova amostragem
            try {
                error_log("📧 Tentando enviar email para amostragem #{$amostragemId}");
                $emailEnviado = $this->enviarEmailNovaAmostragem($amostragemId);
                if ($emailEnviado) {
                    error_log("✅ Email de nova amostragem enviado automaticamente");
                } else {
                    error_log("⚠️ Falha ao enviar email automático (não crítico)");
                }
            } catch (\Exception $e) {
                // Log do erro mas não falha a operação
                error_log("⚠️ Erro ao enviar email: " . $e->getMessage());
            } catch (\Error $e) {
                // Log do erro fatal mas não falha a operação
                error_log("⚠️ Erro fatal ao enviar email: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Amostragem cadastrada com sucesso!',
                'redirect' => '/amostragens-2'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao salvar amostragem: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
    }

    private function processarEvidencias($amostragemId, $files): void
    {
        $maxFiles = 5;
        $maxSize = 10 * 1024 * 1024; // 10MB

        for ($i = 0; $i < min(count($files['name']), $maxFiles); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileSize = $files['size'][$i];
                $fileName = $files['name'][$i];
                $fileType = $files['type'][$i];

                if ($fileSize > $maxSize) {
                    continue;
                }

                if (!str_starts_with($fileType, 'image/')) {
                    continue;
                }

                $evidencia = file_get_contents($files['tmp_name'][$i]);

                $stmt = $this->db->prepare('
                    INSERT INTO amostragens_2_evidencias (amostragem_id, evidencia, nome, tipo, tamanho, ordem)
                    VALUES (:amostragem_id, :evidencia, :nome, :tipo, :tamanho, :ordem)
                ');

                $stmt->execute([
                    ':amostragem_id' => $amostragemId,
                    ':evidencia' => $evidencia,
                    ':nome' => $fileName,
                    ':tipo' => $fileType,
                    ':tamanho' => $fileSize,
                    ':ordem' => $i + 1
                ]);
            }
        }
    }

    private function enviarEmailNovaAmostragem(int $amostragemId): bool
    {
        try {
            error_log("=== ENVIANDO EMAIL DE NOVA AMOSTRAGEM ===");
            error_log("Amostragem ID: {$amostragemId}");
            
            // Buscar dados completos da amostragem
            $stmt = $this->db->prepare('
                SELECT 
                    a.*,
                    f.nome as fornecedor_nome,
                    u.name as criador_nome
                FROM amostragens_2 a
                LEFT JOIN fornecedores f ON a.fornecedor_id = f.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = :id
            ');
            $stmt->execute([':id' => $amostragemId]);
            $amostragem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$amostragem) {
                error_log("❌ Amostragem #{$amostragemId}: Não encontrada");
                return false;
            }

            error_log("✅ Amostragem encontrada: NF {$amostragem['numero_nf']}");
            error_log("Responsáveis (IDs): " . ($amostragem['responsaveis'] ?? 'VAZIO'));

            // Buscar emails dos responsáveis se houver
            if (!empty($amostragem['responsaveis'])) {
                $responsaveisIds = array_map('trim', explode(',', $amostragem['responsaveis']));
                error_log("IDs dos responsáveis: " . implode(', ', $responsaveisIds));
                
                $placeholders = str_repeat('?,', count($responsaveisIds) - 1) . '?';
                $stmt = $this->db->prepare("SELECT name, email FROM users WHERE id IN ($placeholders) AND email IS NOT NULL AND email != ''");
                $stmt->execute($responsaveisIds);
                $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Responsáveis encontrados: " . count($responsaveis));
                foreach ($responsaveis as $resp) {
                    error_log("  - {$resp['name']} ({$resp['email']})");
                }
                
                $emails = array_column($responsaveis, 'email');
                
                // Buscar emails de admins com permissão de aprovar amostragens
                try {
                    $stmtAdmins = $this->db->prepare("
                        SELECT email 
                        FROM users 
                        WHERE role = 'admin' 
                        AND pode_aprovar_amostragens = 1 
                        AND status = 'active'
                        AND email IS NOT NULL 
                        AND email != ''
                    ");
                    $stmtAdmins->execute();
                    $adminsEmails = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (!empty($adminsEmails)) {
                        error_log("📧 Admins com permissão encontrados: " . count($adminsEmails));
                        $emails = array_merge($emails, $adminsEmails);
                        $emails = array_unique($emails); // Remove duplicatas
                    }
                } catch (\Exception $e) {
                    error_log("⚠️ Erro ao buscar admins com permissão (coluna pode não existir ainda): " . $e->getMessage());
                }

                if (empty($emails)) {
                    error_log("❌ Amostragem #{$amostragemId}: Nenhum email válido encontrado");
                    return false;
                }

                error_log("📧 Tentando enviar email para: " . implode(', ', $emails));

                // Enviar email
                if (!class_exists('\App\Services\EmailService')) {
                    error_log("❌ Classe EmailService não encontrada");
                    return false;
                }
                
                $emailService = new \App\Services\EmailService();
                error_log("EmailService criado");
                
                $enviado = $emailService->sendAmostragemNotification($amostragem, $emails, 'nova');

                if ($enviado) {
                    error_log("✅ Email de nova amostragem enviado para amostragem #{$amostragemId} para: " . implode(', ', $emails));
                    return true;
                } else {
                    error_log("❌ Falha ao enviar email de nova amostragem para amostragem #{$amostragemId}");
                    return false;
                }
            } else {
                error_log("⚠️ Amostragem #{$amostragemId}: Sem responsáveis cadastrados");
                return false;
            }

        } catch (\Exception $e) {
            error_log("Erro ao enviar email de nova amostragem: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    private function enviarEmailMudancaStatusAmostragem(int $amostragemId, string $novoStatus): bool
    {
        try {
            error_log("=== ENVIANDO EMAIL DE MUDANÇA DE STATUS AMOSTRAGEM ===");
            error_log("Amostragem ID: {$amostragemId}, Novo Status: {$novoStatus}");
            
            // Buscar dados completos da amostragem
            $stmt = $this->db->prepare('
                SELECT 
                    a.*,
                    f.nome as fornecedor_nome,
                    u.name as criador_nome
                FROM amostragens_2 a
                LEFT JOIN fornecedores f ON a.fornecedor_id = f.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = :id
            ');
            $stmt->execute([':id' => $amostragemId]);
            $amostragem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$amostragem) {
                error_log("❌ Amostragem #{$amostragemId}: Não encontrada");
                return false;
            }

            error_log("✅ Amostragem encontrada: NF {$amostragem['numero_nf']}");
            error_log("Responsáveis (IDs): " . ($amostragem['responsaveis'] ?? 'VAZIO'));

            // Buscar emails dos responsáveis se houver
            if (!empty($amostragem['responsaveis'])) {
                $responsaveisIds = array_map('trim', explode(',', $amostragem['responsaveis']));
                error_log("IDs dos responsáveis: " . implode(', ', $responsaveisIds));
                
                $placeholders = str_repeat('?,', count($responsaveisIds) - 1) . '?';
                $stmt = $this->db->prepare("SELECT name, email FROM users WHERE id IN ($placeholders) AND email IS NOT NULL AND email != ''");
                $stmt->execute($responsaveisIds);
                $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Responsáveis encontrados: " . count($responsaveis));
                foreach ($responsaveis as $resp) {
                    error_log("  - {$resp['name']} ({$resp['email']})");
                }
                
                $emails = array_column($responsaveis, 'email');

                if (empty($emails)) {
                    error_log("❌ Amostragem #{$amostragemId}: Nenhum email válido encontrado para os responsáveis");
                    return false;
                }

                error_log("📧 Tentando enviar email para: " . implode(', ', $emails));

                // Enviar email com template específico do status
                $emailService = new \App\Services\EmailService();
                error_log("EmailService criado");
                
                $enviado = $emailService->sendAmostragemNotification($amostragem, $emails, 'status', $novoStatus);

                if ($enviado) {
                    error_log("✅ Email de mudança de status enviado para amostragem #{$amostragemId} para: " . implode(', ', $emails));
                    return true;
                } else {
                    error_log("❌ Falha ao enviar email de mudança de status para amostragem #{$amostragemId}");
                    return false;
                }
            } else {
                error_log("⚠️ Amostragem #{$amostragemId}: Sem responsáveis cadastrados");
                return false;
            }

        } catch (\Exception $e) {
            error_log("Erro ao enviar email de mudança de status: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function enviarEmailDetalhes(): void
    {
        // Limpar qualquer output anterior
        while (ob_get_level()) { ob_end_clean(); }
        
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }
            
            // Verificar se a amostragem existe
            $stmt = $this->db->prepare('SELECT id FROM amostragens_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                return;
            }
            
            $ok = $this->enviarEmailNovaAmostragem($id);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => '📧 Email enviado com sucesso aos responsáveis!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao enviar email - verifique se há responsáveis com email cadastrado']);
            }
            
        } catch (\Throwable $e) {
            error_log('Erro ao enviar email manual: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Erro interno ao enviar email: ' . $e->getMessage()]);
        }
    }

    public function downloadNf($id = null): void
    {
        try {
            $id = (int)$id;
            
            // Verificar permissão de visualização
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }
            
            $stmt = $this->db->prepare('
                SELECT anexo_nf, anexo_nf_nome, anexo_nf_tipo 
                FROM amostragens_2 
                WHERE id = :id
            ');
            $stmt->execute([':id' => $id]);
            $amostra = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$amostra || !$amostra['anexo_nf']) {
                http_response_code(404);
                echo "Anexo não encontrado";
                return;
            }
            
            header('Content-Type: ' . $amostra['anexo_nf_tipo']);
            header('Content-Disposition: attachment; filename="' . $amostra['anexo_nf_nome'] . '"');
            header('Content-Length: ' . strlen($amostra['anexo_nf']));
            echo $amostra['anexo_nf'];
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Erro ao baixar anexo";
        }
    }

    public function getEvidencias($id = null): void
    {
        header('Content-Type: application/json');
        
        try {
            $id = (int)$id;
            
            // Verificar permissão de visualização
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acesso negado']);
                return;
            }
            
            error_log("Buscando evidências para amostragem ID: $id");
            
            $stmt = $this->db->prepare('
                SELECT id, nome, tipo, tamanho, ordem
                FROM amostragens_2_evidencias 
                WHERE amostragem_id = :id
                ORDER BY ordem
            ');
            $stmt->execute([':id' => $id]);
            $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Evidências encontradas: " . count($evidencias));
            
            echo json_encode([
                'success' => true,
                'evidencias' => $evidencias,
                'count' => count($evidencias)
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar evidências: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar evidências: ' . $e->getMessage()]);
        }
    }

    public function downloadEvidencia($id = null, $evidenciaId = null): void
    {
        try {
            $id = (int)$id;
            $evidenciaId = (int)$evidenciaId;
            
            // Verificar permissão de visualização
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }
            
            $stmt = $this->db->prepare('
                SELECT evidencia, nome, tipo 
                FROM amostragens_2_evidencias 
                WHERE id = :id
            ');
            $stmt->execute([':id' => $evidenciaId]);
            $evidencia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$evidencia) {
                http_response_code(404);
                echo "Evidência não encontrada";
                return;
            }
            
            header('Content-Type: ' . $evidencia['tipo']);
            header('Content-Disposition: attachment; filename="' . $evidencia['nome'] . '"');
            header('Content-Length: ' . strlen($evidencia['evidencia']));
            echo $evidencia['evidencia'];
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo "Erro ao baixar evidência";
        }
    }

    public function details($id = null): void
    {
        try {
            $id = (int)$id;
            
            if ($id <= 0) {
                echo "ID inválido";
                return;
            }
            
            // Verificar permissão de visualização
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo "<h1>Acesso Negado</h1><p>Você não tem permissão para visualizar esta amostragem.</p>";
                return;
            }
            
            error_log("🔍 Carregando página de detalhes da amostragem ID: $id");
            
            // Buscar dados completos da amostragem com joins
            $stmt = $this->db->prepare('
                SELECT 
                    a.*,
                    u.name as criador_nome,
                    f.nome as filial_nome,
                    forn.nome as fornecedor_nome
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN filiais f ON a.filial_id = f.id
                LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
                WHERE a.id = :id
            ');
            
            $stmt->execute([':id' => $id]);
            $amostragem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$amostragem) {
                echo "Amostragem não encontrada";
                return;
            }
            
            // Buscar responsáveis
            $responsaveis = [];
            if (!empty($amostragem['responsaveis'])) {
                $responsaveisIds = explode(',', $amostragem['responsaveis']);
                $placeholders = str_repeat('?,', count($responsaveisIds) - 1) . '?';
                $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id IN ($placeholders)");
                $stmt->execute($responsaveisIds);
                $responsaveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Buscar evidências
            $stmt = $this->db->prepare('
                SELECT id, nome, tipo, tamanho, ordem
                FROM amostragens_2_evidencias 
                WHERE amostragem_id = :id
                ORDER BY ordem
            ');
            $stmt->execute([':id' => $id]);
            $evidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $title = 'Detalhes da Amostragem - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/amostragens-2/details.php';
            include __DIR__ . '/../../views/layouts/main.php';
            
        } catch (\Exception $e) {
            error_log("❌ Erro ao carregar detalhes: " . $e->getMessage());
            echo "Erro ao carregar detalhes: " . $e->getMessage();
        }
    }
    
    public function getDetailsJson($id = null): void
    {
        try {
            // Limpar qualquer output anterior
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Configurar headers antes de qualquer output
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');
            
            $id = (int)$id;
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }
            
            // Verificar permissão de visualização
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para visualizar esta amostragem']);
                exit;
            }
            
            // Excluir campos BLOB para evitar problemas de memória/encoding
            $stmt = $this->db->prepare('
                SELECT 
                    id, user_id, filial_id, numero_nf, 
                    anexo_nf_nome, anexo_nf_tipo, anexo_nf_tamanho,
                    tipo_produto, produto_id, codigo_produto, nome_produto,
                    quantidade_recebida, quantidade_testada, quantidade_aprovada, quantidade_reprovada,
                    fornecedor_id, responsaveis, status_final, observacoes, created_at, updated_at
                FROM amostragens_2 
                WHERE id = :id
            ');
            
            $stmt->execute([':id' => $id]);
            $amostragem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$amostragem) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'amostragem' => $amostragem
            ]);
            exit;
            
        } catch (\Exception $e) {
            error_log("❌ Erro ao buscar detalhes JSON: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar detalhes']);
            exit;
        }
    }

    public function update(): void
    {
        header('Content-Type: application/json');

        try {
            // Aceitar tanto 'id' quanto 'amostragem_id'
            $id = (int)($_POST['id'] ?? $_POST['amostragem_id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            // Verificar permissão de visualização/edição
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para editar esta amostragem']);
                return;
            }

            // Buscar dados atuais da amostragem
            $stmt = $this->db->prepare("SELECT * FROM amostragens_2 WHERE id = ?");
            $stmt->execute([$id]);
            $amostragemAtual = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$amostragemAtual) {
                echo json_encode(['success' => false, 'message' => 'Amostragem não encontrada']);
                return;
            }

            // Campos - usar valores atuais se não fornecidos
            $numeroNf = trim($_POST['numero_nf'] ?? $amostragemAtual['numero_nf']);
            $tipoProduto = $_POST['tipo_produto'] ?? $amostragemAtual['tipo_produto'];
            $produtoId = (int)($_POST['produto_id'] ?? $amostragemAtual['produto_id']);
            $codigoProduto = trim($_POST['codigo_produto'] ?? $amostragemAtual['codigo_produto']);
            $nomeProduto = trim($_POST['nome_produto'] ?? $amostragemAtual['nome_produto']);
            $quantidadeRecebida = (int)($_POST['quantidade_recebida'] ?? $amostragemAtual['quantidade_recebida']);
            
            // Campos opcionais de teste
            $quantidadeTestada = !empty($_POST['quantidade_testada']) ? (int)$_POST['quantidade_testada'] : null;
            $quantidadeAprovada = !empty($_POST['quantidade_aprovada']) ? (int)$_POST['quantidade_aprovada'] : null;
            $quantidadeReprovada = !empty($_POST['quantidade_reprovada']) ? (int)$_POST['quantidade_reprovada'] : null;
            
            $fornecedorId = (int)($_POST['fornecedor_id'] ?? $amostragemAtual['fornecedor_id']);
            $responsaveis = $_POST['responsaveis'] ?? explode(',', $amostragemAtual['responsaveis'] ?? '');
            $statusFinal = $_POST['status_final'] ?? $amostragemAtual['status_final'];
            $observacoes = trim($_POST['observacoes'] ?? $amostragemAtual['observacoes'] ?? '');
            
            $responsaveisStr = !empty($responsaveis) ? (is_array($responsaveis) ? implode(',', $responsaveis) : $responsaveis) : '';

            // Processar novo anexo da NF se enviado
            $updateAnexoNf = '';
            $anexoNfParams = [];
            
            if (!empty($_FILES['anexo_nf']['tmp_name'])) {
                $anexoNf = file_get_contents($_FILES['anexo_nf']['tmp_name']);
                $anexoNfNome = $_FILES['anexo_nf']['name'];
                $anexoNfTipo = $_FILES['anexo_nf']['type'];
                $anexoNfTamanho = $_FILES['anexo_nf']['size'];

                if ($anexoNfTamanho > 10 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Anexo da NF deve ter no máximo 10MB']);
                    return;
                }
                
                $updateAnexoNf = ', anexo_nf = :anexo_nf, anexo_nf_nome = :anexo_nf_nome, anexo_nf_tipo = :anexo_nf_tipo, anexo_nf_tamanho = :anexo_nf_tamanho';
                $anexoNfParams = [
                    ':anexo_nf' => $anexoNf,
                    ':anexo_nf_nome' => $anexoNfNome,
                    ':anexo_nf_tipo' => $anexoNfTipo,
                    ':anexo_nf_tamanho' => $anexoNfTamanho
                ];
            }

            $stmt = $this->db->prepare("
                UPDATE amostragens_2 SET
                    numero_nf = :numero_nf,
                    tipo_produto = :tipo_produto,
                    produto_id = :produto_id,
                    codigo_produto = :codigo_produto,
                    nome_produto = :nome_produto,
                    quantidade_recebida = :quantidade_recebida,
                    quantidade_testada = :quantidade_testada,
                    quantidade_aprovada = :quantidade_aprovada,
                    quantidade_reprovada = :quantidade_reprovada,
                    fornecedor_id = :fornecedor_id,
                    responsaveis = :responsaveis,
                    status_final = :status_final,
                    observacoes = :observacoes,
                    updated_at = NOW()
                    {$updateAnexoNf}
                WHERE id = :id
            ");

            $params = [
                ':id' => $id,
                ':numero_nf' => $numeroNf,
                ':tipo_produto' => $tipoProduto,
                ':produto_id' => $produtoId,
                ':codigo_produto' => $codigoProduto,
                ':nome_produto' => $nomeProduto,
                ':quantidade_recebida' => $quantidadeRecebida,
                ':quantidade_testada' => $quantidadeTestada,
                ':quantidade_aprovada' => $quantidadeAprovada,
                ':quantidade_reprovada' => $quantidadeReprovada,
                ':fornecedor_id' => $fornecedorId,
                ':responsaveis' => $responsaveisStr,
                ':status_final' => $statusFinal,
                ':observacoes' => $observacoes
            ];
            
            // Merge anexo NF params se existir
            $params = array_merge($params, $anexoNfParams);
            
            $stmt->execute($params);

            // Processar novas evidências se enviadas
            if (!empty($_FILES['evidencias']['tmp_name'][0])) {
                $this->processarEvidencias($id, $_FILES['evidencias']);
            }

            // Enviar email automático para responsáveis ao atualizar status
            try {
                error_log("📧 Tentando enviar email de atualização para amostragem #{$id}");
                $emailEnviado = $this->enviarEmailMudancaStatusAmostragem($id, $statusFinal);
                if ($emailEnviado) {
                    error_log("✅ Email de mudança de status enviado - Status: {$statusFinal}");
                } else {
                    error_log("⚠️ Falha ao enviar email (não crítico)");
                }
            } catch (\Exception $e) {
                // Log do erro mas não falha a operação
                error_log("⚠️ Erro ao enviar email: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Amostragem atualizada com sucesso!',
                'redirect' => '/amostragens-2'
            ]);

        } catch (\Exception $e) {
            error_log('Erro ao atualizar amostragem: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(): void
    {
        // Limpar buffer de saída
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }
            
            // Verificar permissão de visualização/edição
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para alterar o status desta amostragem']);
                exit;
            }
            
            // Status válidos
            $statusValidos = ['Pendente', 'Aprovado', 'Aprovado Parcialmente', 'Reprovado'];
            if (!in_array($status, $statusValidos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                exit;
            }
            
            // Atualizar status e registrar quem/quando aprovou
            $userId = $_SESSION['user_id'];
            
            error_log("🔍 DEBUG - Amostragem #{$id} - Status: {$status} - User ID: {$userId}");
            
            // Buscar quantidade testada para preencher automaticamente aprovada/reprovada
            $stmtQtd = $this->db->prepare('SELECT quantidade_testada, quantidade_recebida FROM amostragens_2 WHERE id = :id');
            $stmtQtd->execute([':id' => $id]);
            $amostraData = $stmtQtd->fetch(\PDO::FETCH_ASSOC);
            $qtdTestada = (int)($amostraData['quantidade_testada'] ?? 0);
            $qtdRecebida = (int)($amostraData['quantidade_recebida'] ?? 0);
            
            // Se não testou nada, usa quantidade recebida como base
            $qtdBase = $qtdTestada > 0 ? $qtdTestada : $qtdRecebida;
            
            // Se o status está mudando para Aprovado, Aprovado Parcialmente ou Reprovado, registrar aprovação
            if (in_array($status, ['Aprovado', 'Aprovado Parcialmente', 'Reprovado'])) {
                error_log("✅ Registrando aprovação - User ID: {$userId}");
                
                // Calcular quantidades baseado no status
                $qtdAprovada = null;
                $qtdReprovada = null;
                
                if ($status === 'Aprovado') {
                    // Tudo aprovado
                    $qtdAprovada = $qtdBase;
                    $qtdReprovada = 0;
                    error_log("📊 AUTO: Aprovando tudo - Aprovada: {$qtdAprovada}, Reprovada: {$qtdReprovada}");
                } elseif ($status === 'Reprovado') {
                    // Tudo reprovado
                    $qtdAprovada = 0;
                    $qtdReprovada = $qtdBase;
                    error_log("📊 AUTO: Reprovando tudo - Aprovada: {$qtdAprovada}, Reprovada: {$qtdReprovada}");
                }
                // Se "Aprovado Parcialmente", não preenche automaticamente (usuário deve editar)
                
                if ($qtdAprovada !== null && $qtdReprovada !== null) {
                    // Atualizar com quantidades preenchidas
                    $stmt = $this->db->prepare('
                        UPDATE amostragens_2 SET 
                            status_final = :status,
                            quantidade_aprovada = :qtd_aprovada,
                            quantidade_reprovada = :qtd_reprovada,
                            aprovado_por = :aprovado_por,
                            aprovado_em = NOW(),
                            updated_at = NOW()
                        WHERE id = :id
                    ');
                    
                    $stmt->execute([
                        ':id' => $id,
                        ':status' => $status,
                        ':qtd_aprovada' => $qtdAprovada,
                        ':qtd_reprovada' => $qtdReprovada,
                        ':aprovado_por' => $userId
                    ]);
                } else {
                    // Aprovado Parcialmente - só atualiza status e aprovador
                    $stmt = $this->db->prepare('
                        UPDATE amostragens_2 SET 
                            status_final = :status,
                            aprovado_por = :aprovado_por,
                            aprovado_em = NOW(),
                            updated_at = NOW()
                        WHERE id = :id
                    ');
                    
                    $stmt->execute([
                        ':id' => $id,
                        ':status' => $status,
                        ':aprovado_por' => $userId
                    ]);
                }
                
                $rowsAffected = $stmt->rowCount();
                error_log("📝 UPDATE executado - Linhas afetadas: {$rowsAffected}");
                
                // Verificar se foi atualizado
                $checkStmt = $this->db->prepare('SELECT aprovado_por, aprovado_em, quantidade_aprovada, quantidade_reprovada FROM amostragens_2 WHERE id = :id');
                $checkStmt->execute([':id' => $id]);
                $check = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                error_log("✅ Verificação - aprovado_por: " . ($check['aprovado_por'] ?? 'NULL') . " | aprovado_em: " . ($check['aprovado_em'] ?? 'NULL') . " | qtd_aprovada: " . ($check['quantidade_aprovada'] ?? 'NULL') . " | qtd_reprovada: " . ($check['quantidade_reprovada'] ?? 'NULL'));
            } else {
                // Se voltando para Pendente, limpar aprovação E quantidades
                $stmt = $this->db->prepare('
                    UPDATE amostragens_2 SET 
                        status_final = :status,
                        quantidade_aprovada = NULL,
                        quantidade_reprovada = NULL,
                        aprovado_por = NULL,
                        aprovado_em = NULL,
                        updated_at = NOW()
                    WHERE id = :id
                ');
                
                $stmt->execute([
                    ':id' => $id,
                    ':status' => $status
                ]);
                
                error_log("📊 Status voltou para Pendente - Limpou quantidades aprovada/reprovada");
            }
            
            error_log("✅ Status da amostragem #{$id} atualizado para: {$status}");
            
            // Enviar email automático para responsáveis
            try {
                error_log("📧 Tentando enviar email de mudança de status para amostragem #{$id}");
                $emailEnviado = $this->enviarEmailMudancaStatusAmostragem($id, $status);
                if ($emailEnviado) {
                    error_log("✅ Email de mudança de status enviado - Status: {$status}");
                } else {
                    error_log("⚠️ Falha ao enviar email (não crítico)");
                }
            } catch (\Exception $e) {
                error_log("⚠️ Erro ao enviar email: " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Status atualizado com sucesso!'
            ]);
            exit;
            
        } catch (\Exception $e) {
            error_log('❌ Erro ao atualizar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
            exit;
        }
    }

    public function delete(): void
    {
        header('Content-Type: application/json');

        try {
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                return;
            }

            // Verificar permissão de visualização/edição
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para excluir esta amostragem']);
                return;
            }

            // Excluir evidências primeiro (CASCADE deve fazer isso automaticamente)
            $stmt = $this->db->prepare('DELETE FROM amostragens_2_evidencias WHERE amostragem_id = :id');
            $stmt->execute([':id' => $id]);

            // Excluir amostragem
            $stmt = $this->db->prepare('DELETE FROM amostragens_2 WHERE id = :id');
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Amostragem excluída com sucesso!']);

        } catch (\Exception $e) {
            error_log('Erro ao excluir amostragem: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }

    public function exportExcel(): void
    {
        try {
            // Buscar filtros
            $filters = [];
            $params = [];
            
            if (!empty($_GET['codigo_produto'])) {
                $filters[] = "codigo_produto LIKE :codigo_produto";
                $params[':codigo_produto'] = '%' . $_GET['codigo_produto'] . '%';
            }
            
            if (!empty($_GET['user_id'])) {
                $filters[] = "user_id = :user_id";
                $params[':user_id'] = $_GET['user_id'];
            }
            
            if (!empty($_GET['filial_id'])) {
                $filters[] = "filial_id = :filial_id";
                $params[':filial_id'] = $_GET['filial_id'];
            }
            
            if (!empty($_GET['fornecedor_id'])) {
                $filters[] = "fornecedor_id = :fornecedor_id";
                $params[':fornecedor_id'] = $_GET['fornecedor_id'];
            }
            
            if (!empty($_GET['status_final'])) {
                $filters[] = "status_final = :status_final";
                $params[':status_final'] = $_GET['status_final'];
            }
            
            if (!empty($_GET['data_inicio'])) {
                $filters[] = "DATE(created_at) >= :data_inicio";
                $params[':data_inicio'] = $_GET['data_inicio'];
            }
            
            if (!empty($_GET['data_fim'])) {
                $filters[] = "DATE(created_at) <= :data_fim";
                $params[':data_fim'] = $_GET['data_fim'];
            }
            
            $whereClause = !empty($filters) ? 'WHERE ' . implode(' AND ', $filters) : '';
            
            // Buscar dados
            $stmt = $this->db->prepare("
                SELECT 
                    a.*,
                    u.name as usuario_nome,
                    f.nome as filial_nome,
                    forn.nome as fornecedor_nome
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN filiais f ON a.filial_id = f.id
                LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
                $whereClause
                ORDER BY a.created_at DESC
            ");
            $stmt->execute($params);
            $amostragens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($amostragens)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum registro encontrado']);
                return;
            }
            
            // Gerar arquivo Excel (CSV com formatação)
            $filename = 'amostragens_2_' . date('Y-m-d_H-i-s') . '.csv';
            
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeçalhos
            fputcsv($output, [
                'Data',
                'Número NF',
                'Usuário',
                'Filial',
                'Tipo Produto',
                'Código Produto',
                'Nome Produto',
                'Qtd Recebida',
                'Qtd Testada',
                'Qtd Aprovada',
                'Qtd Reprovada',
                'Fornecedor',
                'Responsáveis',
                'Status Final',
                'Observações'
            ], ';');
            
            // Dados
            foreach ($amostragens as $amostra) {
                fputcsv($output, [
                    date('d/m/Y H:i', strtotime($amostra['created_at'])),
                    $amostra['numero_nf'],
                    $amostra['usuario_nome'],
                    $amostra['filial_nome'],
                    $amostra['tipo_produto'],
                    $amostra['codigo_produto'],
                    $amostra['nome_produto'],
                    $amostra['quantidade_recebida'],
                    $amostra['quantidade_testada'],
                    $amostra['quantidade_aprovada'],
                    $amostra['quantidade_reprovada'],
                    $amostra['fornecedor_nome'],
                    $amostra['responsaveis'],
                    $amostra['status_final'],
                    $amostra['observacoes'] ?? ''
                ], ';');
            }
            
            fclose($output);
            
        } catch (\Exception $e) {
            error_log('Erro ao exportar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    public function graficos(): void
    {
        // TODO: Implementar página de gráficos
        echo "Gráficos em desenvolvimento";
    }

    /**
     * Página dedicada para editar resultados dos testes
     */
    public function editarResultados($id): void
    {
        try {
            $id = (int)$id;
            
            // Verificar permissão de visualização
            if (!$this->podeVisualizarAmostragem($id)) {
                http_response_code(403);
                echo "<h1>Acesso Negado</h1><p>Você não tem permissão para editar esta amostragem.</p>";
                return;
            }
            
            // Buscar dados completos da amostragem
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.name as usuario_nome,
                       f.nome as filial_nome,
                       forn.nome as fornecedor_nome
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN filiais f ON a.filial_id = f.id
                LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
                WHERE a.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $amostragem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$amostragem) {
                http_response_code(404);
                echo "Amostragem não encontrada";
                return;
            }
            
            // Carregar view dedicada SEM layout
            $viewFile = __DIR__ . '/../../views/pages/amostragens-2/editar-resultados.php';
            include $viewFile;
            
        } catch (\Exception $e) {
            error_log("Erro ao carregar página de edição: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao carregar página";
        }
    }

    /**
     * Extrai dados de um arquivo XML de NF-e
     */
    public function parseXml(): void
    {
        header('Content-Type: application/json');

        try {
            if (empty($_FILES['xml_file']['tmp_name'])) {
                echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado']);
                return;
            }

            $xmlPath = $_FILES['xml_file']['tmp_name'];
            $xml = simplexml_load_file($xmlPath);

            if (!$xml) {
                echo json_encode(['success' => false, 'message' => 'Erro ao ler arquivo XML']);
                return;
            }

            // Registrar namespaces da NF-e
            $namespaces = $xml->getNamespaces(true);
            $nfeNs = $namespaces[''] ?? $namespaces['nfe'] ?? 'http://www.portalfiscal.inf.br/nfe';
            $xml->registerXPathNamespace('nfe', $nfeNs);

            // Extrair dados básicos
            $nNF = (string)$xml->xpath('//nfe:ide/nfe:nNF')[0] ?? '';
            $emitCNPJ = (string)$xml->xpath('//nfe:emit/nfe:CNPJ')[0] ?? '';
            $emitNome = (string)$xml->xpath('//nfe:emit/nfe:xNome')[0] ?? '';

            // Tentar encontrar fornecedor pelo CNPJ ou Nome
            $fornecedorEncontrado = null;
            if (!empty($emitCNPJ)) {
                $stmt = $this->db->prepare('SELECT id, nome FROM fornecedores WHERE cnpj = :cnpj OR nome LIKE :nome LIMIT 1');
                $stmt->execute([
                    ':cnpj' => $emitCNPJ,
                    ':nome' => '%' . $emitNome . '%'
                ]);
                $fornecedorEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Extrair Itens
            $itens = [];
            $detalhes = $xml->xpath('//nfe:det');

            foreach ($detalhes as $det) {
                if (isset($det->prod)) {
                    $prod = $det->prod;
                    $itens[] = [
                        'nItem' => (string)$det['nItem'],
                        'codigo' => (string)$prod->cProd,
                        'nome' => (string)$prod->xProd,
                        'quantidade' => (float)$prod->qCom,
                        'unidade' => (string)$prod->uCom
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'numero_nf' => $nNF,
                    'fornecedor' => [
                        'cnpj' => $emitCNPJ,
                        'nome' => $emitNome,
                        'id' => $fornecedorEncontrado['id'] ?? null,
                        'sistema_nome' => $fornecedorEncontrado['nome'] ?? null
                    ],
                    'itens' => $itens
                ]
            ]);

        } catch (\Exception $e) {
            error_log("Erro ao processar XML: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno ao processar XML: ' . $e->getMessage()]);
        }
    }

    /**
     * Salva múltiplas amostragens vindas da importação XML
     */
    public function storeImported(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || empty($data['items'])) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos ou lista de itens vazia']);
                return;
            }

            $userId = $_SESSION['user_id'];
            $filialId = $_SESSION['user_filial_id'] ?? 1; // Fallback para filial 1
            
            $fornecedorId = (int)$data['fornecedor_id'];
            $tipoProduto = $data['tipo_produto'];
            $numeroNf = $data['numero_nf'];
            $responsaveis = $data['responsaveis'] ?? [];
            $responsaveisStr = implode(',', $responsaveis);

            $this->db->beginTransaction();

            $stmt = $this->db->prepare('
                INSERT INTO amostragens_2 (
                    user_id, filial_id, numero_nf, tipo_produto, 
                    produto_id, codigo_produto, nome_produto,
                    quantidade_recebida, fornecedor_id, responsaveis, 
                    status_final, created_at
                ) VALUES (
                    :user_id, :filial_id, :numero_nf, :tipo_produto, 
                    :produto_id, :codigo_produto, :nome_produto,
                    :quantidade_recebida, :fornecedor_id, :responsaveis, 
                    "Pendente", NOW()
                )
            ');

            $importados = 0;
            foreach ($data['items'] as $item) {
                $stmt->execute([
                    ':user_id' => $userId,
                    ':filial_id' => $filialId,
                    ':numero_nf' => $numeroNf,
                    ':tipo_produto' => $tipoProduto,
                    ':produto_id' => (int)$item['produto_id'],
                    ':codigo_produto' => $item['codigo'],
                    ':nome_produto' => $item['nome'],
                    ':quantidade_recebida' => (int)$item['quantidade'],
                    ':fornecedor_id' => $fornecedorId,
                    ':responsaveis' => $responsaveisStr
                ]);
                $importados++;
            }

            $this->db->commit();

            echo json_encode(['success' => true, 'message' => "{$importados} itens importados com sucesso!", 'redirect' => '/amostragens-2']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro na importação em lote: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar importação: ' . $e->getMessage()]);
        }
    }
}
