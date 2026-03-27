<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class ControleDescartesController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureLogTableExists();
    }
    
    // Garantir que a tabela de log existe
    private function ensureLogTableExists()
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS controle_descartes_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    descarte_id INT NULL,
                    acao VARCHAR(50) NOT NULL,
                    dados_anteriores JSON NULL,
                    dados_novos JSON NULL,
                    usuario_id INT NOT NULL,
                    usuario_nome VARCHAR(255) NOT NULL,
                    ip_address VARCHAR(45) NULL,
                    descricao VARCHAR(500) NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_descarte_id (descarte_id),
                    INDEX idx_created_at (created_at),
                    INDEX idx_usuario_id (usuario_id),
                    INDEX idx_acao (acao)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            error_log('Erro ao criar tabela de log de descartes: ' . $e->getMessage());
        }
    }
    
    // Registrar ação no log
    private function logAcao($descarteId, $acao, $dadosAnteriores = null, $dadosNovos = null, $descricao = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO controle_descartes_log 
                (descarte_id, acao, dados_anteriores, dados_novos, usuario_id, usuario_nome, ip_address, descricao)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $descarteId,
                $acao,
                $dadosAnteriores ? json_encode($dadosAnteriores) : null,
                $dadosNovos ? json_encode($dadosNovos) : null,
                $_SESSION['user_id'] ?? 0,
                $_SESSION['user_name'] ?? 'Sistema',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $descricao
            ]);
        } catch (\Exception $e) {
            error_log('Erro ao registrar log de descartes: ' . $e->getMessage());
        }
    }

    // Página principal - Lista de descartes
    public function index()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $filiais = $this->getFiliais();
            $usuariosNotificacao = $this->getUsuariosParaNotificacao();
            
            // Usar o layout padrão com TailwindCSS
            $title = 'Controle de Descartes - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/controle-descartes/index.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Listar descartes com filtros e paginação
    public function listDescartes()
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar descartes']);
                return;
            }

            // Filtros
            $numero_serie = $_GET['numero_serie'] ?? '';
            $codigo_produto = $_GET['codigo_produto'] ?? '';
            $numero_os = $_GET['numero_os'] ?? '';
            $filial_id = $_GET['filial_id'] ?? '';
            $data_inicio = $_GET['data_inicio'] ?? '';
            $data_fim = $_GET['data_fim'] ?? '';
            $status_andamento = $_GET['status_andamento'] ?? '';
            
            // Paginação
            $page = max(1, intval($_GET['page'] ?? 1));
            $per_page = max(1, min(100, intval($_GET['per_page'] ?? 10))); // Limite entre 1 e 100
            $offset = ($page - 1) * $per_page;

            // Construir query base para contagem
            $countSql = "SELECT COUNT(*) as total FROM controle_descartes d WHERE 1=1";
            
            // Construir query base
            $sql = "
                SELECT d.*, 
                       f.nome as filial_nome,
                       uc.name as criado_por_nome,
                       ua.name as atualizado_por_nome
                FROM controle_descartes d
                LEFT JOIN filiais f ON d.filial_id = f.id
                LEFT JOIN users uc ON d.created_by = uc.id
                LEFT JOIN users ua ON d.updated_by = ua.id
                WHERE 1=1
            ";
            
            $params = [];
            $whereClause = "";

            // Filtros
            if ($numero_serie) {
                $whereClause .= " AND d.numero_serie LIKE ?";
                $params[] = "%{$numero_serie}%";
            }
            
            // Filtro por código do produto
            if ($codigo_produto) {
                $whereClause .= " AND d.codigo_produto LIKE ?";
                $params[] = "%{$codigo_produto}%";
            }
            
            if ($numero_os) {
                $whereClause .= " AND d.numero_os LIKE ?";
                $params[] = "%{$numero_os}%";
            }
            
            if ($filial_id) {
                $whereClause .= " AND d.filial_id = ?";
                $params[] = $filial_id;
            }
            
            if ($data_inicio) {
                $whereClause .= " AND d.data_descarte >= ?";
                $params[] = $data_inicio;
            }
            
            if ($data_fim) {
                $whereClause .= " AND d.data_descarte <= ?";
                $params[] = $data_fim;
            }
            
            // Filtro de status de andamento (para área técnica)
            if ($status_andamento) {
                $whereClause .= " AND COALESCE(d.status_andamento, 'Em aberto') = ?";
                $params[] = $status_andamento;
            }
            
            // Contar total de registros
            $countStmt = $this->db->prepare($countSql . $whereClause);
            $countStmt->execute($params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            $total_pages = ceil($total / $per_page);

            $sql .= $whereClause;
            $sql .= " ORDER BY d.data_descarte DESC, d.created_at DESC";
            $sql .= " LIMIT {$per_page} OFFSET {$offset}";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $descartes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Adicionar informação se tem anexo
            foreach ($descartes as &$descarte) {
                $descarte['tem_anexo'] = !empty($descarte['anexo_os_blob']);
                // Remover o blob da resposta para economizar bandwidth
                unset($descarte['anexo_os_blob']);
            }

            echo json_encode([
                'success' => true, 
                'data' => $descartes,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $per_page,
                    'total' => (int)$total,
                    'total_pages' => (int)$total_pages
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar descartes: ' . $e->getMessage()]);
        }
    }

    // Criar novo descarte
    public function create()
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para criar descartes']);
                return;
            }

            // Validações
            $required = ['numero_serie', 'filial_id', 'codigo_produto', 'descricao_produto', 'responsavel_tecnico'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }

            // Verificar duplicidade de OS
            $numero_os = $_POST['numero_os'] ?? null;
            if ($numero_os && ($duplicado = $this->isOsDuplicada($numero_os))) {
                $msg = "A Ordem de Servico (OS) '{$numero_os}' ja esta cadastrada no sistema.";
                $msg .= " (Série: {$duplicado['numero_serie']} | Filial: {$duplicado['filial_nome']})";
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            
            // Converter array de IDs em string separada por vírgula (opcional)
            $notificarUsuarios = null;
            if (!empty($_POST['notificar_usuarios']) && is_array($_POST['notificar_usuarios'])) {
                $notificarUsuarios = implode(',', array_map('intval', $_POST['notificar_usuarios']));
            }

            // Data do descarte (se não informada, usar hoje)
            $data_descarte = !empty($_POST['data_descarte']) ? $_POST['data_descarte'] : date('Y-m-d');

            // Processar upload do anexo
            $anexo_blob = null;
            $anexo_nome = null;
            $anexo_tipo = null;
            $anexo_tamanho = null;

            if (isset($_FILES['anexo_os']) && $_FILES['anexo_os']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['anexo_os'];
                
                // Validar tamanho (máximo 10MB)
                if ($file['size'] > 10 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB permitido.']);
                    return;
                }

                // Validar tipo de arquivo
                $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
                if (!in_array($file['type'], $allowed_types)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PNG, JPEG ou PDF.']);
                    return;
                }

                $anexo_blob = file_get_contents($file['tmp_name']);
                $anexo_nome = $file['name'];
                $anexo_tipo = $file['type'];
                $anexo_tamanho = $file['size'];
            }

            // Verificar se coluna notificar_usuarios existe
            $colunaNotificarExiste = false;
            try {
                $checkCol = $this->db->query("SHOW COLUMNS FROM controle_descartes LIKE 'notificar_usuarios'");
                $colunaNotificarExiste = $checkCol->rowCount() > 0;
            } catch (\Exception $e) {
                $colunaNotificarExiste = false;
            }

            // Inserir descarte com status inicial "Aguardando Descarte"
            if ($colunaNotificarExiste) {
                $stmt = $this->db->prepare("
                    INSERT INTO controle_descartes (
                        numero_serie, filial_id, codigo_produto, descricao_produto, 
                        data_descarte, numero_os, anexo_os_blob, anexo_os_nome, 
                        anexo_os_tipo, anexo_os_tamanho, responsavel_tecnico, 
                        observacoes, notificar_usuarios, status, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aguardando Descarte', ?)
                ");
                
                $stmt->execute([
                    $_POST['numero_serie'],
                    $_POST['filial_id'],
                    $_POST['codigo_produto'],
                    $_POST['descricao_produto'],
                    $data_descarte,
                    $_POST['numero_os'] ?? null,
                    $anexo_blob,
                    $anexo_nome,
                    $anexo_tipo,
                    $anexo_tamanho,
                    $_POST['responsavel_tecnico'],
                    $_POST['observacoes'] ?? null,
                    $notificarUsuarios,
                    $_SESSION['user_id']
                ]);
            } else {
                // Sem coluna notificar_usuarios
                $stmt = $this->db->prepare("
                    INSERT INTO controle_descartes (
                        numero_serie, filial_id, codigo_produto, descricao_produto, 
                        data_descarte, numero_os, anexo_os_blob, anexo_os_nome, 
                        anexo_os_tipo, anexo_os_tamanho, responsavel_tecnico, 
                        observacoes, status, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aguardando Descarte', ?)
                ");
                
                $stmt->execute([
                    $_POST['numero_serie'],
                    $_POST['filial_id'],
                    $_POST['codigo_produto'],
                    $_POST['descricao_produto'],
                    $data_descarte,
                    $_POST['numero_os'] ?? null,
                    $anexo_blob,
                    $anexo_nome,
                    $anexo_tipo,
                    $anexo_tamanho,
                    $_POST['responsavel_tecnico'],
                    $_POST['observacoes'] ?? null,
                    $_SESSION['user_id']
                ]);
            }

            $descarte_id = $this->db->lastInsertId();
            
            // Registrar log de criação
            $dadosNovos = [
                'numero_serie' => $_POST['numero_serie'],
                'filial_id' => $_POST['filial_id'],
                'codigo_produto' => $_POST['codigo_produto'],
                'descricao_produto' => $_POST['descricao_produto'],
                'data_descarte' => $data_descarte,
                'numero_os' => $_POST['numero_os'] ?? null,
                'responsavel_tecnico' => $_POST['responsavel_tecnico'],
                'observacoes' => $_POST['observacoes'] ?? null
            ];
            $this->logAcao(
                $descarte_id, 
                'INSERT', 
                null, 
                $dadosNovos, 
                "Novo descarte criado - Série: {$_POST['numero_serie']}"
            );
            
            // Enviar notificação por email para admins e qualidade
            try {
                $this->notificarNovoDescarte($descarte_id);
            } catch (\Exception $emailError) {
                error_log('Erro ao enviar notificação de novo descarte: ' . $emailError->getMessage());
                // Não falhar a criação se email falhar
            }

            echo json_encode(['success' => true, 'message' => 'Descarte registrado com sucesso!', 'descarte_id' => $descarte_id]);
        } catch (\PDOException $e) {
            error_log('Erro PDO no controle de descartes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('Erro geral no controle de descartes: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar descarte: ' . $e->getMessage()]);
        }
    }

    // Atualizar descarte
    public function update()
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte_id = $_POST['id'] ?? 0;

            if (!$descarte_id) {
                echo json_encode(['success' => false, 'message' => 'ID do descarte é obrigatório']);
                return;
            }

            // Verificar se o descarte existe
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para editar descartes']);
                return;
            }

            // Validações
            $required = ['numero_serie', 'filial_id', 'codigo_produto', 'descricao_produto', 'responsavel_tecnico'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    echo json_encode(['success' => false, 'message' => "Campo '{$field}' é obrigatório"]);
                    return;
                }
            }

            // Verificar duplicidade de OS (excluindo o atual)
            $numero_os = $_POST['numero_os'] ?? null;
            if ($numero_os && ($duplicado = $this->isOsDuplicada($numero_os, $descarte_id))) {
                $msg = "A Ordem de Servico (OS) '{$numero_os}' ja esta cadastrada em outro registro.";
                $msg .= " (Série: {$duplicado['numero_serie']} | Filial: {$duplicado['filial_nome']})";
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }

            // Data do descarte
            $data_descarte = !empty($_POST['data_descarte']) ? $_POST['data_descarte'] : $descarte['data_descarte'];

            // Processar upload do anexo (se houver)
            $anexo_blob = $descarte['anexo_os_blob'];
            $anexo_nome = $descarte['anexo_os_nome'];
            $anexo_tipo = $descarte['anexo_os_tipo'];
            $anexo_tamanho = $descarte['anexo_os_tamanho'];

            if (isset($_FILES['anexo_os']) && $_FILES['anexo_os']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['anexo_os'];
                
                // Validar tamanho (máximo 10MB)
                if ($file['size'] > 10 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB permitido.']);
                    return;
                }

                // Validar tipo de arquivo
                $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf'];
                if (!in_array($file['type'], $allowed_types)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use PNG, JPEG ou PDF.']);
                    return;
                }

                $anexo_blob = file_get_contents($file['tmp_name']);
                $anexo_nome = $file['name'];
                $anexo_tipo = $file['type'];
                $anexo_tamanho = $file['size'];
            }

            // Guardar dados anteriores para log
            $dadosAnteriores = [
                'numero_serie' => $descarte['numero_serie'],
                'filial_id' => $descarte['filial_id'],
                'codigo_produto' => $descarte['codigo_produto'],
                'descricao_produto' => $descarte['descricao_produto'],
                'data_descarte' => $descarte['data_descarte'],
                'numero_os' => $descarte['numero_os'],
                'responsavel_tecnico' => $descarte['responsavel_tecnico'],
                'observacoes' => $descarte['observacoes']
            ];

            // Atualizar descarte
            $stmt = $this->db->prepare("
                UPDATE controle_descartes SET 
                    numero_serie = ?, filial_id = ?, codigo_produto = ?, 
                    descricao_produto = ?, data_descarte = ?, numero_os = ?, 
                    anexo_os_blob = ?, anexo_os_nome = ?, anexo_os_tipo = ?, 
                    anexo_os_tamanho = ?, responsavel_tecnico = ?, 
                    observacoes = ?, updated_by = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['numero_serie'],
                $_POST['filial_id'],
                $_POST['codigo_produto'],
                $_POST['descricao_produto'],
                $data_descarte,
                $_POST['numero_os'] ?? null,
                $anexo_blob,
                $anexo_nome,
                $anexo_tipo,
                $anexo_tamanho,
                $_POST['responsavel_tecnico'],
                $_POST['observacoes'] ?? null,
                $_SESSION['user_id'],
                $descarte_id
            ]);
            
            // Registrar log de atualização
            $dadosNovos = [
                'numero_serie' => $_POST['numero_serie'],
                'filial_id' => $_POST['filial_id'],
                'codigo_produto' => $_POST['codigo_produto'],
                'descricao_produto' => $_POST['descricao_produto'],
                'data_descarte' => $data_descarte,
                'numero_os' => $_POST['numero_os'] ?? null,
                'responsavel_tecnico' => $_POST['responsavel_tecnico'],
                'observacoes' => $_POST['observacoes'] ?? null
            ];
            $this->logAcao(
                $descarte_id, 
                'UPDATE', 
                $dadosAnteriores, 
                $dadosNovos, 
                "Descarte atualizado - Série: {$_POST['numero_serie']}"
            );

            echo json_encode(['success' => true, 'message' => 'Descarte atualizado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar descarte: ' . $e->getMessage()]);
        }
    }

    // Excluir descarte (requer senha de admin)
    public function delete()
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte_id = $_POST['id'] ?? 0;
            $admin_password = $_POST['admin_password'] ?? '';

            if (!$descarte_id) {
                echo json_encode(['success' => false, 'message' => 'ID do descarte é obrigatório']);
                return;
            }
            
            // Verificar se senha foi fornecida
            if (empty($admin_password)) {
                echo json_encode(['success' => false, 'message' => 'Senha de administrador é obrigatória para exclusão']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'delete')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir descartes']);
                return;
            }
            
            // Verificar senha de um administrador
            $stmt = $this->db->prepare("
                SELECT id, password FROM users 
                WHERE role IN ('admin', 'super_admin') 
                AND status = 'active'
            ");
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $senhaValida = false;
            foreach ($admins as $admin) {
                if (password_verify($admin_password, $admin['password'])) {
                    $senhaValida = true;
                    break;
                }
            }
            
            if (!$senhaValida) {
                // Registrar tentativa de exclusão falha
                $this->logAcao(
                    $descarte_id, 
                    'DELETE_FAILED', 
                    null, 
                    null, 
                    "Tentativa de exclusão com senha inválida - ID: {$descarte_id}"
                );
                echo json_encode(['success' => false, 'message' => 'Senha de administrador inválida']);
                return;
            }

            // Verificar se o descarte existe
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }
            
            // Guardar dados para log antes de excluir
            $dadosAnteriores = [
                'numero_serie' => $descarte['numero_serie'],
                'filial_id' => $descarte['filial_id'],
                'codigo_produto' => $descarte['codigo_produto'],
                'descricao_produto' => $descarte['descricao_produto'],
                'data_descarte' => $descarte['data_descarte'],
                'numero_os' => $descarte['numero_os'],
                'responsavel_tecnico' => $descarte['responsavel_tecnico'],
                'observacoes' => $descarte['observacoes']
            ];

            // Excluir descarte
            $stmt = $this->db->prepare("DELETE FROM controle_descartes WHERE id = ?");
            $stmt->execute([$descarte_id]);
            
            // Registrar log de exclusão
            $this->logAcao(
                $descarte_id, 
                'DELETE', 
                $dadosAnteriores, 
                null, 
                "Descarte excluído - Série: {$descarte['numero_serie']}"
            );

            echo json_encode(['success' => true, 'message' => 'Descarte excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir descarte: ' . $e->getMessage()]);
        }
    }

    // Obter detalhes de um descarte
    public function getDescarte($id)
    {
        // Limpar qualquer output anterior
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte = $this->getDescarteById($id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }

            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar este descarte']);
                return;
            }

            // Adicionar informação se tem anexo (sem retornar o blob)
            $descarte['tem_anexo'] = !empty($descarte['anexo_os_blob']);
            unset($descarte['anexo_os_blob']); // Remover blob para economizar bandwidth

            echo json_encode(['success' => true, 'descarte' => $descarte]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar descarte: ' . $e->getMessage()]);
        }
    }

    // Download do anexo
    public function downloadAnexo($id)
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                http_response_code(403);
                echo 'Sem permissão para visualizar anexos';
                return;
            }

            $stmt = $this->db->prepare("
                SELECT anexo_os_blob, anexo_os_nome, anexo_os_tipo 
                FROM controle_descartes 
                WHERE id = ? AND anexo_os_blob IS NOT NULL
            ");
            $stmt->execute([$id]);
            $anexo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anexo) {
                http_response_code(404);
                echo 'Anexo não encontrado';
                return;
            }

            // Definir headers para download
            header('Content-Type: ' . $anexo['anexo_os_tipo']);
            header('Content-Disposition: attachment; filename="' . $anexo['anexo_os_nome'] . '"');
            header('Content-Length: ' . strlen($anexo['anexo_os_blob']));

            echo $anexo['anexo_os_blob'];
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao baixar anexo: ' . $e->getMessage();
        }
    }
    
    // Listar logs de ações
    public function listLogs()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar logs']);
                return;
            }
            
            // Filtros
            $acao = $_GET['acao'] ?? '';
            $data_inicio = $_GET['data_inicio'] ?? '';
            $data_fim = $_GET['data_fim'] ?? '';
            $page = max(1, intval($_GET['page'] ?? 1));
            $per_page = max(1, min(50, intval($_GET['per_page'] ?? 20)));
            $offset = ($page - 1) * $per_page;
            
            // Query de contagem
            $countSql = "SELECT COUNT(*) as total FROM controle_descartes_log WHERE 1=1";
            $params = [];
            $whereClause = "";
            
            if ($acao) {
                $whereClause .= " AND acao = ?";
                $params[] = $acao;
            }
            
            if ($data_inicio) {
                $whereClause .= " AND DATE(created_at) >= ?";
                $params[] = $data_inicio;
            }
            
            if ($data_fim) {
                $whereClause .= " AND DATE(created_at) <= ?";
                $params[] = $data_fim;
            }
            
            // Contar total
            $countStmt = $this->db->prepare($countSql . $whereClause);
            $countStmt->execute($params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            $total_pages = ceil($total / $per_page);
            
            // Query principal
            $sql = "
                SELECT id, descarte_id, acao, dados_anteriores, dados_novos, 
                       usuario_id, usuario_nome, ip_address, descricao, created_at
                FROM controle_descartes_log 
                WHERE 1=1" . $whereClause . "
                ORDER BY created_at DESC
                LIMIT {$per_page} OFFSET {$offset}
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decodificar JSON dos dados
            foreach ($logs as &$log) {
                if ($log['dados_anteriores']) {
                    $log['dados_anteriores'] = json_decode($log['dados_anteriores'], true);
                }
                if ($log['dados_novos']) {
                    $log['dados_novos'] = json_decode($log['dados_novos'], true);
                }
            }
            
            echo json_encode([
                'success' => true,
                'data' => $logs,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $per_page,
                    'total' => (int)$total,
                    'total_pages' => (int)$total_pages
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar logs: ' . $e->getMessage()]);
        }
    }
    
    // Obter data do primeiro registro
    public function getFirstRecordDate()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->query("SELECT MIN(data_descarte) as first_date FROM controle_descartes");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'first_date' => $result['first_date'] ?? null
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao obter data: ' . $e->getMessage()]);
        }
    }
    
    // Verificar senha de administrador (para validação prévia)
    public function verifyAdminPassword()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $password = $_POST['password'] ?? '';
            
            if (empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Senha não fornecida']);
                return;
            }
            
            // Buscar admins ativos
            $stmt = $this->db->prepare("
                SELECT id, password FROM users 
                WHERE role IN ('admin', 'super_admin') 
                AND status = 'active'
            ");
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $valid = false;
            foreach ($admins as $admin) {
                if (password_verify($password, $admin['password'])) {
                    $valid = true;
                    break;
                }
            }
            
            echo json_encode(['success' => true, 'valid' => $valid]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao verificar senha: ' . $e->getMessage()]);
        }
    }

    // Métodos auxiliares

    // Verificar se OS já está cadastrada (anti-duplicidade) - Retorna o registro se existir
    private function isOsDuplicada($numeroOs, $excludeId = null)
    {
        if (empty($numeroOs)) return false;

        $sql = "SELECT d.id, d.numero_serie, f.nome as filial_nome 
                FROM controle_descartes d 
                LEFT JOIN filiais f ON d.filial_id = f.id
                WHERE d.numero_os = ?";
        $params = [$numeroOs];

        if ($excludeId !== null) {
            $sql .= " AND d.id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    private function getDescarteById($id)
    {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   f.nome as filial_nome,
                   uc.name as criado_por_nome,
                   ua.name as atualizado_por_nome
            FROM controle_descartes d
            LEFT JOIN filiais f ON d.filial_id = f.id
            LEFT JOIN users uc ON d.created_by = uc.id
            LEFT JOIN users ua ON d.updated_by = ua.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getFiliais()
    {
        $stmt = $this->db->query("SELECT id, nome FROM filiais ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getUsuariosParaNotificacao()
    {
        // Buscar todos usuários com email (sem filtrar por status)
        // Alguns sistemas usam 'status', outros 'active', então buscar todos
        try {
            $stmt = $this->db->query("
                SELECT id, name, email, role 
                FROM users 
                WHERE email IS NOT NULL 
                AND email != ''
                ORDER BY name
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Erro ao buscar usuários para notificação: ' . $e->getMessage());
            return [];
        }
    }

    // Relatórios
    public function relatorios()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $filiais = $this->getFiliais();
            
            $title = 'Controle de Descartes - Relatórios - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/controle-descartes/relatorios.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    // Baixar template Excel
    public function downloadTemplate()
    {
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'import')) {
                http_response_code(403);
                echo 'Sem permissão para baixar template';
                return;
            }

            // Buscar filiais para o exemplo
            $filiais = $this->getFiliais();
            $filialExemplo = !empty($filiais) ? $filiais[0]['nome'] : 'Jundiaí';

            // Criar CSV com template
            $filename = 'template_descartes_' . date('Ymd') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Abrir output como arquivo
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8 (para Excel reconhecer acentos)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalhos (exatamente como no grid)
            $headers = [
                'Número de Série',
                'Filial',
                'Código do Produto',
                'Descrição do Produto',
                'Data do Descarte',
                'Número da OS',
                'Responsável Técnico',
                'Observações'
            ];
            fputcsv($output, $headers, ';');

            // Linha de exemplo
            $exemplo = [
                'SERIE12345',
                $filialExemplo,
                'PROD-001',
                'Impressora HP LaserJet Pro M404dn',
                date('Y-m-d'),
                'OS-2024-001',
                'João Silva',
                'Equipamento com defeito irreparável na placa principal'
            ];
            fputcsv($output, $exemplo, ';');

            fclose($output);
            exit;
        } catch (\Exception $e) {
            error_log('Erro ao gerar template: ' . $e->getMessage());
            http_response_code(500);
            echo 'Erro ao gerar template: ' . $e->getMessage();
        }
    }

    // Importar descartes via Excel/CSV
    public function importar()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            // Verificar permissão
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'import')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para importar descartes']);
                return;
            }

            // Verificar se arquivo foi enviado
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Nenhum arquivo foi enviado ou erro no upload']);
                return;
            }

            $file = $_FILES['arquivo'];

            // Validar tamanho (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 5MB permitido.']);
                return;
            }

            // Validar tipo
            $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extension, ['csv', 'xls', 'xlsx'])) {
                echo json_encode(['success' => false, 'message' => 'Formato de arquivo não suportado. Use CSV, XLS ou XLSX.']);
                return;
            }

            // Ler arquivo CSV
            $filePath = $file['tmp_name'];
            $handle = fopen($filePath, 'r');
            
            if ($handle === false) {
                echo json_encode(['success' => false, 'message' => 'Não foi possível abrir o arquivo']);
                return;
            }

            // Pular BOM se existir
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            // Ler cabeçalhos
            $headers = fgetcsv($handle, 0, ';');
            if (!$headers) {
                $headers = fgetcsv($handle, 0, ',');
            }
            
            if (!$headers) {
                fclose($handle);
                echo json_encode(['success' => false, 'message' => 'Arquivo vazio ou formato inválido']);
                return;
            }

            // Buscar mapa de filiais
            $filiais = $this->getFiliais();
            $filiaisMap = [];
            foreach ($filiais as $filial) {
                $filiaisMap[strtolower($filial['nome'])] = $filial['id'];
            }

            $imported = 0;
            $errors = [];
            $linha = 1; // Começar da linha 1 (cabeçalho)

            // Processar cada linha
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                $linha++;
                
                // Tentar com vírgula se ponto e vírgula não funcionou
                if (count($data) == 1 && strpos($data[0], ',') !== false) {
                    $data = str_getcsv($data[0], ',');
                }

                // Pular linhas vazias
                if (empty(array_filter($data))) {
                    continue;
                }

                try {
                    // Mapear dados
                    $numeroSerie = trim($data[0] ?? '');
                    $filialNome = trim($data[1] ?? '');
                    $codigoProduto = trim($data[2] ?? '');
                    $descricaoProduto = trim($data[3] ?? '');
                    $dataDescarte = trim($data[4] ?? '');
                    $numeroOs = trim($data[5] ?? '');
                    $responsavelTecnico = trim($data[6] ?? '');
                    $observacoes = trim($data[7] ?? '');

                    // Validar campos obrigatórios
                    if (empty($numeroSerie) || empty($filialNome) || empty($codigoProduto) || 
                        empty($descricaoProduto) || empty($responsavelTecnico)) {
                        $errors[] = "Linha $linha: Campos obrigatórios faltando";
                        continue;
                    }

                    // Buscar ID da filial
                    $filialId = $filiaisMap[strtolower($filialNome)] ?? null;
                    if (!$filialId) {
                        $errors[] = "Linha $linha: Filial '$filialNome' não encontrada";
                        continue;
                    }

                    // Data do descarte (se vazia, usar hoje)
                    if (empty($dataDescarte)) {
                        $dataDescarte = date('Y-m-d');
                    } else {
                        // Tentar converter data
                        $dataDescarte = date('Y-m-d', strtotime($dataDescarte));
                    }

                    // Verificar duplicidade de OS na importacao
                    if (!empty($numeroOs) && ($duplicado = $this->isOsDuplicada($numeroOs))) {
                        $errors[] = "Linha $linha: OS '$numeroOs' ja existe no sistema (Série: {$duplicado['numero_serie']} | Filial: {$duplicado['filial_nome']}) e foi pulada";
                        continue;
                    }

                    // Inserir descarte
                    $stmt = $this->db->prepare("
                        INSERT INTO controle_descartes (
                            numero_serie, filial_id, codigo_produto, descricao_produto, 
                            data_descarte, numero_os, responsavel_tecnico, 
                            observacoes, created_by
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->execute([
                        $numeroSerie,
                        $filialId,
                        $codigoProduto,
                        $descricaoProduto,
                        $dataDescarte,
                        !empty($numeroOs) ? $numeroOs : null,
                        $responsavelTecnico,
                        !empty($observacoes) ? $observacoes : null,
                        $_SESSION['user_id']
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Linha $linha: " . $e->getMessage();
                }
            }

            fclose($handle);

            // Retornar resultado
            echo json_encode([
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
                'message' => "Importação concluída: $imported registros importados"
            ]);

        } catch (\Exception $e) {
            error_log('Erro na importação: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao processar importação: ' . $e->getMessage()]);
        }
    }
    
    // Alterar status do descarte (apenas admin ou qualidade)
    public function alterarStatus()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte_id = $_POST['id'] ?? 0;
            $novo_status = $_POST['status'] ?? '';
            $justificativa = trim($_POST['justificativa'] ?? '');
            
            if (!$descarte_id) {
                echo json_encode(['success' => false, 'message' => 'ID do descarte é obrigatório']);
                return;
            }
            
            // Validar status
            $status_validos = ['Aguardando Descarte', 'Itens Descartados', 'Descartes Reprovados'];
            if (!in_array($novo_status, $status_validos)) {
                echo json_encode(['success' => false, 'message' => 'Status inválido']);
                return;
            }
            
            // Verificar se usuário tem permissão (admin ou super_admin)
            $user_role = $_SESSION['user_role'] ?? '';
            $user_id = $_SESSION['user_id'] ?? 0;
            
            $tem_permissao = ($user_role === 'admin' || $user_role === 'super_admin');
            
            if (!$tem_permissao) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão. Apenas Admin ou Qualidade podem alterar status.']);
                return;
            }
            
            // Verificar se descarte existe
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }
            
            // Atualizar status
            $stmt = $this->db->prepare("
                UPDATE controle_descartes 
                SET status = ?,
                    status_alterado_por = ?,
                    status_alterado_em = NOW(),
                    justificativa_status = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $novo_status,
                $user_id,
                $justificativa,
                $descarte_id
            ]);
            
            // Enviar notificações sobre mudança de status (não crítico)
            try {
                $this->notificarMudancaStatus($descarte_id, $novo_status);
            } catch (\Exception $e) {
                error_log("Erro ao enviar notificações de mudança de status (não crítico): " . $e->getMessage());
            }
            
            echo json_encode([
                'success' => true, 
                'message' => "Status alterado para '{$novo_status}' com sucesso!"
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao alterar status: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar status: ' . $e->getMessage()]);
        }
    }
    
    // Alterar status de andamento do descarte (para área técnica acompanhar)
    public function alterarStatusAndamento()
    {
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            $descarte_id = $_POST['id'] ?? 0;
            $novo_status = $_POST['status_andamento'] ?? '';
            
            if (!$descarte_id) {
                echo json_encode(['success' => false, 'message' => 'ID do descarte é obrigatório']);
                return;
            }
            
            // Validar status de andamento
            $status_validos = ['Em aberto', 'Concluído'];
            if (!in_array($novo_status, $status_validos)) {
                echo json_encode(['success' => false, 'message' => 'Status de andamento inválido']);
                return;
            }
            
            // Verificar se usuário tem permissão para visualizar descartes (qualquer um pode alterar andamento)
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para alterar status de andamento']);
                return;
            }
            
            $user_id = $_SESSION['user_id'] ?? 0;
            
            // Verificar se descarte existe
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                echo json_encode(['success' => false, 'message' => 'Descarte não encontrado']);
                return;
            }
            
            // Verificar se coluna existe antes de atualizar
            try {
                $checkCol = $this->db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'controle_descartes' AND column_name = 'status_andamento'");
                $colunaExiste = $checkCol->rowCount() > 0;
            } catch (\Exception $e) {
                $colunaExiste = false;
            }
            
            if (!$colunaExiste) {
                // Criar coluna se não existir
                try {
                    $this->db->exec("ALTER TABLE controle_descartes ADD COLUMN status_andamento VARCHAR(50) DEFAULT 'Em aberto'");
                    $this->db->exec("ALTER TABLE controle_descartes ADD COLUMN andamento_alterado_por INTEGER");
                    $this->db->exec("ALTER TABLE controle_descartes ADD COLUMN andamento_alterado_em TIMESTAMP");
                } catch (\Exception $e) {
                    error_log("Erro ao criar colunas de status_andamento: " . $e->getMessage());
                }
            }
            
            // Atualizar status de andamento
            $stmt = $this->db->prepare("
                UPDATE controle_descartes 
                SET status_andamento = ?,
                    andamento_alterado_por = ?,
                    andamento_alterado_em = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $novo_status,
                $user_id,
                $descarte_id
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => "Status de andamento alterado para '{$novo_status}' com sucesso!"
            ]);
            
        } catch (\Exception $e) {
            error_log('Erro ao alterar status de andamento: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao alterar status de andamento: ' . $e->getMessage()]);
        }
    }
    
    // Notificar usuários selecionados sobre novo descarte
    private function notificarNovoDescarte($descarte_id)
    {
        try {
            // Buscar dados do descarte
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                return;
            }
            
            $criadorId = $_SESSION['user_id'] ?? null;
            $criadorNome = $_SESSION['user_name'] ?? 'Usuário';
            
            // 1. BUSCAR ADMINS E SUPER ADMINS (sempre notificados)
            $adminsStmt = $this->db->prepare("
                SELECT id, name, email 
                FROM users 
                WHERE role IN ('admin', 'super_admin') 
                AND email IS NOT NULL 
                AND email != ''
                AND id != ?
            ");
            $adminsStmt->execute([$criadorId]);
            $admins = $adminsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 2. BUSCAR USUÁRIOS SELECIONADOS PARA NOTIFICAÇÃO
            $usuariosSelecionados = [];
            if (!empty($descarte['notificar_usuarios'])) {
                $usuariosIds = explode(',', $descarte['notificar_usuarios']);
                $usuariosIds = array_filter(array_map('intval', $usuariosIds));
                
                if (!empty($usuariosIds)) {
                    $placeholders = str_repeat('?,', count($usuariosIds) - 1) . '?';
                    $usuariosStmt = $this->db->prepare("
                        SELECT id, name, email 
                        FROM users 
                        WHERE id IN ($placeholders) 
                        AND email IS NOT NULL 
                        AND email != ''
                        AND id != ?
                    ");
                    $usuariosStmt->execute([...$usuariosIds, $criadorId]);
                    $usuariosSelecionados = $usuariosStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            
            // 3. COMBINAR TODOS OS DESTINATÁRIOS (sem duplicatas)
            $todosDestinatarios = [];
            $emailsJaAdicionados = [];
            
            // Adicionar admins
            foreach ($admins as $admin) {
                if (!in_array($admin['email'], $emailsJaAdicionados)) {
                    $todosDestinatarios[] = $admin;
                    $emailsJaAdicionados[] = $admin['email'];
                }
            }
            
            // Adicionar usuários selecionados
            foreach ($usuariosSelecionados as $usuario) {
                if (!in_array($usuario['email'], $emailsJaAdicionados)) {
                    $todosDestinatarios[] = $usuario;
                    $emailsJaAdicionados[] = $usuario['email'];
                }
            }
            
            if (empty($todosDestinatarios)) {
                error_log('Controle Descartes: Nenhum destinatário válido encontrado para descarte ID ' . $descarte_id);
                return;
            }
            
            // 4. ENVIAR EMAILS
            try {
                $emailService = new \App\Services\EmailService();
                $resultadoEmail = $emailService->enviarNotificacaoDescarte(
                    $descarte, 
                    $todosDestinatarios, 
                    $criadorNome
                );
                
                if ($resultadoEmail['success']) {
                    error_log("Controle Descartes: Email enviado com sucesso para " . count($todosDestinatarios) . " destinatário(s)");
                } else {
                    error_log("Controle Descartes: Erro ao enviar email - " . $resultadoEmail['message']);
                }
            } catch (\Exception $emailError) {
                error_log("Controle Descartes: Erro no serviço de email - " . $emailError->getMessage());
            }
            
            // 5. CRIAR NOTIFICAÇÕES INTERNAS (backup)
            $stmt = $this->db->prepare('
                INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');
            
            $titulo = "🗑️ Novo Descarte Registrado";
            $mensagem = "$criadorNome registrou um novo descarte: Série {$descarte['numero_serie']} - {$descarte['descricao_produto']} (Status: {$descarte['status']})";
            
            $notificados = 0;
            foreach ($todosDestinatarios as $destinatario) {
                try {
                    $stmt->execute([
                        $destinatario['id'],
                        $titulo,
                        $mensagem,
                        'warning', // Tipo warning para chamar atenção
                        'controle_descartes',
                        $descarte_id
                    ]);
                    $notificados++;
                } catch (\Exception $e) {
                    error_log("Erro ao criar notificação interna para usuário {$destinatario['id']}: " . $e->getMessage());
                }
            }
            
            error_log("Controle Descartes: $notificados notificação(ões) interna(s) criada(s) para descarte ID $descarte_id");
            
        } catch (\Exception $e) {
            error_log('Erro ao notificar novo descarte: ' . $e->getMessage());
        }
    }
    
    // Notificar sobre mudança de status
    private function notificarMudancaStatus($descarte_id, $novo_status)
    {
        try {
            $descarte = $this->getDescarteById($descarte_id);
            if (!$descarte) {
                return;
            }
            
            $adminNome = $_SESSION['user_name'] ?? 'Administrador';
            $criadorId = $descarte['created_by'];
            
            // Mapear ícones por status
            $statusIcons = [
                'Aguardando Descarte' => '⏳',
                'Itens Descartados' => '✅',
                'Descartes Reprovados' => '❌'
            ];
            $icon = $statusIcons[$novo_status] ?? '📊';
            
            // Mapear tipo de notificação por status
            $notifType = match($novo_status) {
                'Itens Descartados' => 'success',
                'Descartes Reprovados' => 'error',
                default => 'warning'
            };
            
            // 1. Notificar o CRIADOR
            $stmt = $this->db->prepare('
                INSERT INTO notifications (user_id, title, message, type, related_type, related_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');
            
            $stmt->execute([
                $criadorId,
                "$icon Status atualizado",
                "$adminNome alterou o status do descarte Série {$descarte['numero_serie']} para: $novo_status",
                $notifType,
                'controle_descartes',
                $descarte_id
            ]);
            
            // 2. Notificar os usuários selecionados (se houver)
            if (!empty($descarte['notificar_usuarios'])) {
                $usuariosIds = explode(',', $descarte['notificar_usuarios']);
                $usuariosIds = array_filter(array_map('intval', $usuariosIds));
                
                foreach ($usuariosIds as $userId) {
                    // Não notificar o criador duas vezes
                    if ($userId == $criadorId) continue;
                    
                    $stmt->execute([
                        $userId,
                        "$icon Status atualizado",
                        "$adminNome alterou o status do descarte Série {$descarte['numero_serie']} para: $novo_status",
                        $notifType,
                        'controle_descartes',
                        $descarte_id
                    ]);
                }
            }
            
            error_log("Notificações de mudança de status enviadas - Descarte ID: $descarte_id - Status: $novo_status");
            
        } catch (\Exception $e) {
            error_log("Erro ao notificar mudança de status: " . $e->getMessage());
        }
    }

    // Exportar descartes como CSV
    public function exportar()
    {
        try {
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'controle_descartes', 'export')) {
                http_response_code(403);
                echo 'Sem permissão para exportar descartes.';
                return;
            }

            $numero_serie    = $_GET['numero_serie'] ?? '';
            $codigo_produto  = $_GET['codigo_produto'] ?? '';
            $numero_os       = $_GET['numero_os'] ?? '';
            $filial_id       = $_GET['filial_id'] ?? '';
            $data_inicio     = $_GET['data_inicio'] ?? '';
            $data_fim        = $_GET['data_fim'] ?? '';
            $status_andamento = $_GET['status_andamento'] ?? '';

            $sql = "
                SELECT d.id,
                       d.numero_serie,
                       d.codigo_produto,
                       d.descricao_produto,
                       f.nome AS filial,
                       d.data_descarte,
                       d.numero_os,
                       d.responsavel_tecnico,
                       d.status,
                       COALESCE(d.status_andamento, 'Em aberto') AS status_andamento,
                       d.observacoes,
                       uc.name AS criado_por,
                       d.created_at
                FROM controle_descartes d
                LEFT JOIN filiais f ON d.filial_id = f.id
                LEFT JOIN users uc ON d.created_by = uc.id
                WHERE 1=1
            ";

            $params = [];

            if ($numero_serie) {
                $sql .= " AND d.numero_serie LIKE ?";
                $params[] = "%{$numero_serie}%";
            }
            if ($codigo_produto) {
                $sql .= " AND d.codigo_produto LIKE ?";
                $params[] = "%{$codigo_produto}%";
            }
            if ($numero_os) {
                $sql .= " AND d.numero_os LIKE ?";
                $params[] = "%{$numero_os}%";
            }
            if ($filial_id) {
                $sql .= " AND d.filial_id = ?";
                $params[] = $filial_id;
            }
            if ($data_inicio) {
                $sql .= " AND d.data_descarte >= ?";
                $params[] = $data_inicio;
            }
            if ($data_fim) {
                $sql .= " AND d.data_descarte <= ?";
                $params[] = $data_fim;
            }
            if ($status_andamento) {
                $sql .= " AND COALESCE(d.status_andamento, 'Em aberto') = ?";
                $params[] = $status_andamento;
            }

            $sql .= " ORDER BY d.data_descarte DESC, d.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $descartes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $filename = 'controle_descartes_' . date('Y-m-d_H-i-s') . '.csv';

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 para Excel

            fputcsv($output, [
                'ID',
                'Número de Série',
                'Código do Produto',
                'Descrição do Produto',
                'Filial',
                'Data do Descarte',
                'Número OS',
                'Responsável Técnico',
                'Status',
                'Status Andamento',
                'Observações',
                'Criado Por',
                'Data de Criação',
            ], ';');

            foreach ($descartes as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['numero_serie'],
                    $row['codigo_produto'],
                    $row['descricao_produto'],
                    $row['filial'],
                    $row['data_descarte'],
                    $row['numero_os'],
                    $row['responsavel_tecnico'],
                    $row['status'],
                    $row['status_andamento'],
                    $row['observacoes'],
                    $row['criado_por'],
                    $row['created_at'],
                ], ';');
            }

            fclose($output);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao exportar descartes: ' . $e->getMessage();
        }
    }
}
