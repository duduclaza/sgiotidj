<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;

class ChecklistsController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // Criar checklist
    public function create()
    {
        // SEMPRE retornar JSON, mesmo com erro
        header('Content-Type: application/json');
        
        // Capturar TODOS os erros
        set_error_handler(function($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        try {
            error_log("ChecklistsController::create - Iniciando");
            
            if (!isset($_SESSION['user_id'])) {
                error_log("ChecklistsController::create - Usuário não autenticado");
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            error_log("ChecklistsController::create - User ID: " . $user_id);
            
            // Verificar se é admin ou super admin
            $isAdmin = PermissionService::isAdmin($user_id);
            $isSuperAdmin = PermissionService::isSuperAdmin($user_id);
            error_log("ChecklistsController::create - isAdmin: " . ($isAdmin ? 'true' : 'false') . ", isSuperAdmin: " . ($isSuperAdmin ? 'true' : 'false'));
            
            if (!$isAdmin && !$isSuperAdmin) {
                error_log("ChecklistsController::create - Sem permissão");
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                return;
            }

            $rawData = file_get_contents('php://input');
            error_log("ChecklistsController::create - Raw data: " . $rawData);
            
            $data = json_decode($rawData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("ChecklistsController::create - JSON decode error: " . json_last_error_msg());
                echo json_encode(['success' => false, 'message' => 'JSON inválido: ' . json_last_error_msg()]);
                return;
            }
            
            $titulo = trim($data['titulo'] ?? '');
            $descricao = trim($data['descricao'] ?? '');
            $itens = $data['itens'] ?? [];

            if (empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'Título obrigatório']);
                return;
            }

            if (empty($itens)) {
                echo json_encode(['success' => false, 'message' => 'Adicione pelo menos um item']);
                return;
            }

            // Iniciar transação
            $this->db->beginTransaction();

            // Inserir checklist
            $stmt = $this->db->prepare("
                INSERT INTO homologacao_checklists (titulo, descricao, criado_por)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$titulo, $descricao, $user_id]);
            $checklist_id = $this->db->lastInsertId();

            // Inserir itens
            $stmt = $this->db->prepare("
                INSERT INTO homologacao_checklist_itens 
                (checklist_id, titulo, ordem, tipo_resposta)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($itens as $item) {
                $stmt->execute([
                    $checklist_id,
                    $item['titulo'],
                    $item['ordem'],
                    $item['tipo_resposta']
                ]);
            }

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Checklist criado com sucesso',
                'checklist_id' => $checklist_id
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("ChecklistsController::create - Exceção: " . $e->getMessage());
            error_log("ChecklistsController::create - Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao criar checklist: ' . $e->getMessage()
            ]);
        }
    }

    // Listar checklists
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.id,
                    c.titulo,
                    c.descricao,
                    c.criado_em,
                    u.name as criado_por_nome,
                    COUNT(i.id) as total_itens
                FROM homologacao_checklists c
                LEFT JOIN users u ON c.criado_por = u.id
                LEFT JOIN homologacao_checklist_itens i ON c.id = i.checklist_id
                WHERE c.ativo = 1
                GROUP BY c.id
                ORDER BY c.criado_em DESC
            ");
            $stmt->execute();
            $checklists = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $checklists]);

        } catch (\Exception $e) {
            error_log("Erro ao listar checklists: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao carregar checklists']);
        }
    }

    // Buscar checklist por ID
    public function show($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Buscar checklist
            $stmt = $this->db->prepare("
                SELECT * FROM homologacao_checklists WHERE id = ? AND ativo = 1
            ");
            $stmt->execute([$id]);
            $checklist = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$checklist) {
                echo json_encode(['success' => false, 'message' => 'Checklist não encontrado']);
                return;
            }

            // Buscar itens
            $stmt = $this->db->prepare("
                SELECT * FROM homologacao_checklist_itens 
                WHERE checklist_id = ? 
                ORDER BY ordem
            ");
            $stmt->execute([$id]);
            $checklist['itens'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $checklist]);

        } catch (\Exception $e) {
            error_log("Erro ao buscar checklist: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar checklist']);
        }
    }

    // Excluir checklist (soft delete)
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            
            // Verificar se é admin ou super admin
            if (!PermissionService::isAdmin($user_id) && !PermissionService::isSuperAdmin($user_id)) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                return;
            }

            // Soft delete
            $stmt = $this->db->prepare("
                UPDATE homologacao_checklists 
                SET ativo = 0 
                WHERE id = ?
            ");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Checklist excluído']);

        } catch (\Exception $e) {
            error_log("Erro ao excluir checklist: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir checklist']);
        }
    }

    // Salvar respostas do checklist
    public function salvarRespostas()
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            $data = json_decode(file_get_contents('php://input'), true);
            
            $homologacao_id = $data['homologacao_id'] ?? 0;
            $checklist_id = $data['checklist_id'] ?? 0;
            $respostas = $data['respostas'] ?? [];

            if (!$homologacao_id || !$checklist_id) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
                return;
            }

            // Iniciar transação
            $this->db->beginTransaction();

            // Limpar respostas anteriores
            $stmt = $this->db->prepare("
                DELETE FROM homologacao_checklist_respostas 
                WHERE homologacao_id = ? AND checklist_id = ?
            ");
            $stmt->execute([$homologacao_id, $checklist_id]);

            // Inserir novas respostas
            $stmt = $this->db->prepare("
                INSERT INTO homologacao_checklist_respostas 
                (homologacao_id, checklist_id, item_id, resposta, concluido, respondido_por)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($respostas as $resposta) {
                $stmt->execute([
                    $homologacao_id,
                    $checklist_id,
                    $resposta['item_id'],
                    $resposta['resposta'],
                    $resposta['concluido'] ? 1 : 0,
                    $user_id
                ]);
            }

            // IMPORTANTE: Atualizar checklist_id na tabela homologacoes
            // Isso garante que o checklist apareça mesmo após mudar de status
            $stmtUpdate = $this->db->prepare("
                UPDATE homologacoes 
                SET checklist_id = ? 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$checklist_id, $homologacao_id]);

            $this->db->commit();

            echo json_encode(['success' => true, 'message' => 'Respostas salvas com sucesso']);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao salvar respostas: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar respostas']);
        }
    }

    // Buscar respostas de uma homologação
    public function buscarRespostas($homologacao_id)
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    r.*,
                    i.titulo as item_titulo,
                    i.tipo_resposta
                FROM homologacao_checklist_respostas r
                JOIN homologacao_checklist_itens i ON r.item_id = i.id
                WHERE r.homologacao_id = ?
                ORDER BY i.ordem
            ");
            $stmt->execute([$homologacao_id]);
            $respostas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $respostas]);

        } catch (\Exception $e) {
            error_log("Erro ao buscar respostas: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar respostas']);
        }
    }

    // Atualizar checklist existente
    public function update($id)
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                return;
            }

            $user_id = $_SESSION['user_id'];
            
            // Verificar se é admin ou super admin
            if (!PermissionService::isAdmin($user_id) && !PermissionService::isSuperAdmin($user_id)) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão']);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            $titulo = trim($data['titulo'] ?? '');
            $descricao = trim($data['descricao'] ?? '');
            $itens = $data['itens'] ?? [];

            if (empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'Título obrigatório']);
                return;
            }

            if (empty($itens)) {
                echo json_encode(['success' => false, 'message' => 'Adicione pelo menos um item']);
                return;
            }

            // Verificar se checklist existe
            $stmt = $this->db->prepare("SELECT id FROM homologacao_checklists WHERE id = ? AND ativo = 1");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Checklist não encontrado']);
                return;
            }

            // Iniciar transação
            $this->db->beginTransaction();

            // Atualizar checklist
            $stmt = $this->db->prepare("
                UPDATE homologacao_checklists 
                SET titulo = ?, descricao = ?, atualizado_em = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$titulo, $descricao, $id]);

            // Remover itens antigos
            $stmt = $this->db->prepare("DELETE FROM homologacao_checklist_itens WHERE checklist_id = ?");
            $stmt->execute([$id]);

            // Inserir novos itens
            $stmt = $this->db->prepare("
                INSERT INTO homologacao_checklist_itens 
                (checklist_id, titulo, ordem, tipo_resposta)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($itens as $item) {
                $stmt->execute([
                    $id,
                    $item['titulo'],
                    $item['ordem'],
                    $item['tipo_resposta']
                ]);
            }

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Checklist atualizado com sucesso'
            ]);

        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erro ao atualizar checklist: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar checklist']);
        }
    }
}
