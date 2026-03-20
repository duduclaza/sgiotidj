<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class Homologacoes2Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        try {
            // Verificar permissão
            $isAdmin = in_array($_SESSION['user_role'], ['admin', 'super_admin']);
            // Se nao tiver a permissao homologacoes_2 e nao for admin, bloquear
            if (!$isAdmin && !PermissionService::hasPermission($_SESSION['user_id'], 'homologacoes_2', 'view')) {
                http_response_code(403);
                echo "Acesso negado";
                return;
            }

            $title = 'Homologações 2.0 - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/pages/homologacoes-2/index.php';
            include __DIR__ . '/../../views/layouts/main.php';

        } catch (\Exception $e) {
            error_log("Erro em Homologações 2.0: " . $e->getMessage());
            http_response_code(500);
            echo "Erro ao carregar o módulo: " . $e->getMessage();
        }
    }
}
