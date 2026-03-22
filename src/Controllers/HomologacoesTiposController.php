<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class HomologacoesTiposController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * List all product types
     */
    public function index(): void
    {
        PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'view');

        try {
            $stmt = $this->db->query("SELECT * FROM homologacao_tipos_produto ORDER BY nome ASC");
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $tipos = [];
        }

        $this->render('homologacoes/tipos-produto', [
            'title' => 'Tipos de Produto - Homologações',
            'tipos' => $tipos
        ]);
    }

    /**
     * Store a new product type
     */
    public function store(): void
    {
        PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'edit');

        $nome = trim($_POST['nome'] ?? '');

        if (empty($nome)) {
            flash('error', 'O nome do tipo de produto é obrigatório.');
            redirect('/homologacoes/tipos');
            return;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO homologacao_tipos_produto (nome) VALUES (:nome)");
            $stmt->execute([':nome' => $nome]);
            flash('success', 'Tipo de produto cadastrado com sucesso!');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao cadastrar: ' . $e->getMessage());
        }

        redirect('/homologacoes/tipos');
    }

    /**
     * Update an existing product type
     */
    public function update(): void
    {
        PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'edit');

        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if ($id <= 0 || empty($nome)) {
            flash('error', 'Dados inválidos.');
            redirect('/homologacoes/tipos');
            return;
        }

        try {
            $stmt = $this->db->prepare("UPDATE homologacao_tipos_produto SET nome = :nome, ativo = :ativo WHERE id = :id");
            $stmt->execute([
                ':nome' => $nome,
                ':ativo' => $ativo,
                ':id' => $id
            ]);
            flash('success', 'Tipo de produto atualizado!');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao atualizar: ' . $e->getMessage());
        }

        redirect('/homologacoes/tipos');
    }

    /**
     * Delete a product type (Soft delete logic is safer, but here we do hard delete if not used)
     */
    public function delete(): void
    {
        PermissionService::requirePermission($_SESSION['user_id'], 'homologacoes', 'delete');

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            flash('error', 'ID inválido.');
            redirect('/homologacoes/tipos');
            return;
        }

        try {
            // Check if it's being used first
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM homologacoes WHERE tipo_produto_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                flash('error', 'Este tipo de produto não pode ser excluído pois está sendo usado em homologações.');
                redirect('/homologacoes/tipos');
                return;
            }

            $stmt = $this->db->prepare("DELETE FROM homologacao_tipos_produto WHERE id = ?");
            $stmt->execute([$id]);
            flash('success', 'Tipo de produto excluído.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao excluir: ' . $e->getMessage());
        }

        redirect('/homologacoes/tipos');
    }

    /**
     * API: Get all active product types
     */
    public function listApi(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->query("SELECT id, nome FROM homologacao_tipos_produto WHERE ativo = 1 ORDER BY nome ASC");
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $tipos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        if (file_exists($viewFile)) {
            include $layout;
        } else {
            die("View not found: $viewFile");
        }
    }
}
