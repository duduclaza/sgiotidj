<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class PrecificacaoColetaDescartesController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        try {
            if (!PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'precificacao_coleta_descartes', 'view')) {
                http_response_code(403);
                include __DIR__ . '/../../views/errors/403.php';
                return;
            }

            $title = 'Precificacao de Coleta de Descartes - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/controle-descartes/precificacao.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'Erro interno: ' . $e->getMessage();
        }
    }

    public function list(): void
    {
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json');

        try {
            if (!PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'precificacao_coleta_descartes', 'view')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissao para visualizar.']);
                return;
            }

            $mes = trim((string)($_GET['mes'] ?? ''));
            $where = '';
            $params = [];

            if ($mes !== '' && preg_match('/^\d{4}-\d{2}$/', $mes)) {
                $where = ' WHERE DATE_FORMAT(data_coleta, "%Y-%m") = ?';
                $params[] = $mes;
            }

            $stmt = $this->db->prepare(
                'SELECT id, data_coleta, valor_coleta, created_at
                 FROM precificacao_coleta_descartes' . $where . '
                 ORDER BY data_coleta DESC, id DESC'
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sumStmt = $this->db->prepare(
                'SELECT COALESCE(SUM(valor_coleta), 0) AS total
                 FROM precificacao_coleta_descartes' . $where
            );
            $sumStmt->execute($params);
            $totalPeriodo = (float)($sumStmt->fetchColumn() ?: 0);

            $sumGeralStmt = $this->db->query('SELECT COALESCE(SUM(valor_coleta), 0) AS total FROM precificacao_coleta_descartes');
            $totalGeral = (float)($sumGeralStmt->fetchColumn() ?: 0);

            echo json_encode([
                'success' => true,
                'data' => $rows,
                'totais' => [
                    'periodo' => round($totalPeriodo, 2),
                    'geral' => round($totalGeral, 2),
                    'quantidade' => count($rows),
                ],
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao listar: ' . $e->getMessage()]);
        }
    }

    public function create(): void
    {
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json');

        try {
            if (!PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'precificacao_coleta_descartes', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissao para criar.']);
                return;
            }

            $dataColeta = trim((string)($_POST['data_coleta'] ?? ''));
            $valor = $this->parseValor($_POST['valor_coleta'] ?? null);

            if (!$this->isValidDate($dataColeta)) {
                echo json_encode(['success' => false, 'message' => 'Data da coleta invalida.']);
                return;
            }

            if ($valor === null || $valor < 0) {
                echo json_encode(['success' => false, 'message' => 'Valor da coleta invalido.']);
                return;
            }

            $stmt = $this->db->prepare(
                'INSERT INTO precificacao_coleta_descartes (data_coleta, valor_coleta, created_by)
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([
                $dataColeta,
                $valor,
                (int)($_SESSION['user_id'] ?? 0),
            ]);

            echo json_encode(['success' => true, 'message' => 'Coleta adicionada com sucesso.']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar: ' . $e->getMessage()]);
        }
    }

    public function update(): void
    {
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json');

        try {
            if (!PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'precificacao_coleta_descartes', 'edit')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissao para editar.']);
                return;
            }

            $id = (int)($_POST['id'] ?? 0);
            $dataColeta = trim((string)($_POST['data_coleta'] ?? ''));
            $valor = $this->parseValor($_POST['valor_coleta'] ?? null);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID invalido.']);
                return;
            }

            if (!$this->isValidDate($dataColeta)) {
                echo json_encode(['success' => false, 'message' => 'Data da coleta invalida.']);
                return;
            }

            if ($valor === null || $valor < 0) {
                echo json_encode(['success' => false, 'message' => 'Valor da coleta invalido.']);
                return;
            }

            $stmt = $this->db->prepare(
                'UPDATE precificacao_coleta_descartes
                 SET data_coleta = ?, valor_coleta = ?, updated_by = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $dataColeta,
                $valor,
                (int)($_SESSION['user_id'] ?? 0),
                $id,
            ]);

            echo json_encode(['success' => true, 'message' => 'Coleta atualizada com sucesso.']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }

    public function delete(): void
    {
        if (ob_get_level()) {
            ob_clean();
        }
        header('Content-Type: application/json');

        try {
            if (!PermissionService::hasPermission((int)($_SESSION['user_id'] ?? 0), 'precificacao_coleta_descartes', 'delete')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissao para excluir.']);
                return;
            }

            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID invalido.']);
                return;
            }

            $stmt = $this->db->prepare('DELETE FROM precificacao_coleta_descartes WHERE id = ?');
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Coleta removida com sucesso.']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }

    private function parseValor($raw): ?float
    {
        if ($raw === null) {
            return null;
        }

        $value = trim((string)$raw);
        if ($value === '') {
            return null;
        }

        if (strpos($value, ',') !== false) {
            // Formato pt-BR: 1.234,56
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float)$value : null;
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $date);
        return $dt && $dt->format('Y-m-d') === $date;
    }
}
