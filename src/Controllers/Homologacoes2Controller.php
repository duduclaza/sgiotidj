<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\Homologacoes2Service;
use App\Services\PermissionService;

class Homologacoes2Controller
{
    private Homologacoes2Service $service;

    public function __construct()
    {
        require_once __DIR__ . '/../Support/homologacoes2_helpers.php';
        $this->service = new Homologacoes2Service(Database::getInstance());
    }

    public function index(): void
    {
        $u = $this->requireModuleAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCancelFromListing($u, '/homologacoes-2');
            return;
        }

        $dados = $this->service->getDashboardData($_GET);
        $homologacoes = $dados['homologacoes'];
        $this->prepareViewContext($homologacoes);

        extract($dados, EXTR_SKIP);
        $title = 'Homologações 2.0 - Dashboard';
        $viewFile = dirname(__DIR__, 2) . '/homologacoes_mock/views/index.php';
        include dirname(__DIR__, 2) . '/views/layouts/main.php';
    }

    public function create(): void
    {
        $u = $this->requireModuleAccess();
        if (!$this->canCreate($u)) {
            $this->deny();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_homologacao'])) {
            try {
                $_POST['tipo_id'] = $_POST['tipo_equipamento'] ?? null;
                $this->normalizeCreatePayload();
                $id = $this->service->createHomologacao($_POST, (int) $_SESSION['user_id']);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Homologação criada com sucesso! (ID: {$id})"];
                redirect('/homologacoes-2/' . $id);
            } catch (\Throwable $e) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'text' => $e->getMessage()];
            }
        }

        $dados = $this->service->getCreateFormData($u);
        $dados['responsaveisPorSetor'] = $this->groupResponsaveisBySetor($this->service->getActiveUsers());
        extract($dados, EXTR_SKIP);
        $title = 'Nova Homologação - Homologações 2.0';
        $viewFile = dirname(__DIR__, 2) . '/homologacoes_mock/views/nova_homologacao.php';
        include dirname(__DIR__, 2) . '/views/layouts/main.php';
    }

    public function queue(): void
    {
        $u = $this->requireModuleAccess();
        $dados = $this->service->getQueueData((int) $_SESSION['user_id']);
        $data = ['checklists' => $this->service->getChecklistMapByType()];
        $minha_fila = $dados['minhaFila'];
        $historico = $dados['historico'];
        $this->prepareViewContext($dados['homologacoes']);

        $title = 'Minha Fila - Homologações 2.0';
        $viewFile = dirname(__DIR__, 2) . '/homologacoes_mock/views/minha_fila.php';
        include dirname(__DIR__, 2) . '/views/layouts/main.php';
    }

    public function logistics(): void
    {
        $u = $this->requireModuleAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_recebimento_id'])) {
            try {
                $this->service->registerRecebimento((int) $_POST['confirmar_recebimento_id'], $_POST, $u, $_FILES);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Recebimento físico confirmado com sucesso.'];
            } catch (\Throwable $e) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'text' => $e->getMessage()];
            }

            redirect('/homologacoes-2/logistica');
        }

        $dados = $this->service->getLogisticsData();
        extract($dados, EXTR_SKIP);
        $this->prepareViewContext($homologacoes);

        $title = 'Painel Logística - Homologações 2.0';
        $viewFile = dirname(__DIR__, 2) . '/homologacoes_mock/views/logistica.php';
        include dirname(__DIR__, 2) . '/views/layouts/main.php';
    }

    public function monitoring(): void
    {
        $u = $this->requireModuleAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCancelFromListing($u, '/homologacoes-2/monitoramento');
            return;
        }

        $dados = $this->service->getMonitoringData();
        $homologacoes = $dados['homologacoes'];
        $this->prepareViewContext($homologacoes);

        $title = 'Monitoramento - Homologações 2.0';
        $viewFile = dirname(__DIR__, 2) . '/homologacoes_mock/views/monitoramento.php';
        include dirname(__DIR__, 2) . '/views/layouts/main.php';
    }

    public function manage(): void
    {
        $u = $this->requireModuleAccess();
        if (!$this->canManageCadastros($u)) {
            $this->deny();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleManagementPost();
            return;
        }

        $dados = $this->service->getManagementData();
        extract($dados, EXTR_SKIP);

        $title = 'Gestão de Homologações 2.0';
        $viewFile = dirname(__DIR__, 2) . '/homologacoes_mock/views/gerenciar_cadastros.php';
        include dirname(__DIR__, 2) . '/views/layouts/main.php';
    }

    public function detail($id): void
    {
        $u = $this->requireModuleAccess();
        $id = (int) $id;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDetailPost($id, $u);
            return;
        }

        $h = $this->service->getHomologacao($id);
        if (!$h) {
            http_response_code(404);
            echo 'Homologação não encontrada.';
            return;
        }

        $homologacoes = $this->service->getHomologacoes();
        $this->prepareViewContext($homologacoes);

        $data = ['checklists' => $this->service->getChecklistMapByType()];
        $checklistItems = $this->service->getChecklistMapByType()[$h['tipo_equipamento']] ?? [];
        $respostas = $this->convertChecklistValuesForView($this->service->getChecklistValuesForHomologacao($id));
        $canEdit = $this->canEditHomologacao($u, $h);

        $title = $h['codigo'] . ' - Homologação';
        $viewFile = dirname(__DIR__, 2) . '/homologacoes_mock/views/detalhe_homologacao.php';
        include dirname(__DIR__, 2) . '/views/layouts/main.php';
    }

    public function publicChecklist($token): void
    {
        $homologacao = $this->service->getPublicHomologacao((string) $token);
        if (!$homologacao) {
            http_response_code(404);
            echo 'Homologação não encontrada.';
            return;
        }

        $checklistItems = $this->service->getChecklistMapByType()[$homologacao['tipo_equipamento']] ?? [];
        $concluido = $homologacao['status'] === 'concluida';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$concluido) {
            try {
                $this->service->submitPublicChecklist((string) $token, $_POST);
                header('Location: /homologacoes-2/public/' . urlencode((string) $token) . '?sucesso=1');
                exit;
            } catch (\Throwable $e) {
                http_response_code(500);
                echo 'Erro ao salvar checklist: ' . htmlspecialchars($e->getMessage());
                return;
            }
        }

        include dirname(__DIR__, 2) . '/views/pages/homologacoes-2/public_checklist.php';
    }

    public function apiTipos(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->service->getTiposProduto()]);
    }

    public function apiFornecedores(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->service->getFornecedores()]);
    }

    public function apiClientes(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->service->getClientes()]);
    }

    public function apiChecklists(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->service->getChecklistMapByType()]);
    }

    public function apiHomologacao($id): void
    {
        header('Content-Type: application/json');
        $homologacao = $this->service->getHomologacao((int) $id);
        if (!$homologacao) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Homologação não encontrada']);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $homologacao,
            'checklist' => $this->service->getChecklistValuesForHomologacao((int) $id),
        ]);
    }

    private function handleManagementPost(): void
    {
        try {
            if (isset($_POST['acao_produto'])) {
                if ($_POST['acao_produto'] === 'adicionar') {
                    $this->service->addTipoProduto(trim((string) ($_POST['nome_produto'] ?? '')));
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Tipo de produto adicionado com sucesso.'];
                } elseif ($_POST['acao_produto'] === 'excluir') {
                    $this->service->deleteTipoProduto((int) ($_POST['id_produto'] ?? 0));
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Tipo de produto removido com sucesso.'];
                }
            }

            if (isset($_POST['acao_checklist'])) {
                if ($_POST['acao_checklist'] === 'excluir') {
                    $this->service->deleteChecklist((int) ($_POST['id_checklist'] ?? 0));
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Checklist removido com sucesso.'];
                } else {
                    $this->service->saveChecklist($_POST, (int) $_SESSION['user_id']);
                    $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Checklist salvo com sucesso.'];
                }
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'text' => $e->getMessage()];
        }

        redirect('/homologacoes-2/gerenciar');
    }

    private function handleDetailPost(int $id, array $u): void
    {
        try {
            $acao = $_POST['acao'] ?? '';

            if ($acao === 'confirmar_recebimento') {
                $this->service->registerRecebimento($id, $_POST, $u, $_FILES);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Item recebido registrado com sucesso.'];
            } elseif ($acao === 'iniciar_homologacao') {
                $this->service->startHomologacao($id, $_POST, $u);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Homologação iniciada.'];
            } elseif ($acao === 'salvar_checklist') {
                $this->service->saveChecklistDraft($id, $_POST, $u);
                $_SESSION['flash_message'] = ['type' => 'info', 'text' => 'Checklist salvo em rascunho.'];
            } elseif ($acao === 'finalizar_homologacao') {
                $this->service->finalizarHomologacao($id, $_POST, $_FILES, $u);
                $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Homologação atualizada com sucesso.'];
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'text' => $e->getMessage()];
        }

        redirect('/homologacoes-2/' . $id);
    }

    private function handleCancelFromListing(array $u, string $redirectTo): void
    {
        try {
            if (($_POST['acao'] ?? '') === 'cancelar_homologacao') {
                $this->service->cancelHomologacao(
                    (int) ($_POST['id'] ?? 0),
                    (($_POST['excluir_definitivo'] ?? '0') === '1'),
                    $u
                );

                $_SESSION['flash_message'] = [
                    'type' => (($_POST['excluir_definitivo'] ?? '0') === '1') ? 'success' : 'warning',
                    'text' => (($_POST['excluir_definitivo'] ?? '0') === '1')
                        ? 'Homologação removida da fila geral.'
                        : 'Homologação cancelada com sucesso.',
                ];
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'text' => $e->getMessage()];
        }

        redirect($redirectTo);
    }

    private function prepareViewContext(array $homologacoes): void
    {
        $users = $this->service->getActiveUsers();
        h2_register_view_context([
            'users' => $this->service->buildUsersLookup($users),
            'homologacoes' => $this->service->buildHomologacoesLookup($homologacoes),
            'versions' => $this->service->getVersionMap($homologacoes),
        ]);
    }

    private function convertChecklistValuesForView(array $values): array
    {
        $mapped = [];
        foreach ($values as $key => $value) {
            $mapped[$key] = match ($value) {
                '1' => true,
                '0' => false,
                default => 'pendente',
            };
        }

        return $mapped;
    }

    private function requireModuleAccess(): array
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('/login');
        }

        $u = $this->service->getCurrentUser((int) $_SESSION['user_id']);
        if (!$u) {
            redirect('/login');
        }

        $isAdmin = in_array($_SESSION['user_role'] ?? '', ['admin', 'super_admin', 'superadmin'], true);
        $hasPermission = $isAdmin || PermissionService::hasPermission((int) $_SESSION['user_id'], 'homologacoes_2', 'view');
        if (!$hasPermission && !in_array($u['perfil'], ['compras', 'logistica', 'qualidade', 'tecnico', 'admin'], true)) {
            $this->deny();
            exit;
        }

        return $u;
    }

    private function canCreate(array $u): bool
    {
        if (in_array($u['perfil'], ['compras', 'qualidade', 'admin'], true)) {
            return true;
        }

        return PermissionService::hasPermission((int) $_SESSION['user_id'], 'homologacoes_2', 'edit');
    }

    private function canManageCadastros(array $u): bool
    {
        // Permite admin, super_admin e compras gerenciarem cadastros (inclusive exclusão)
        return in_array($u['perfil'], ['compras', 'admin', 'super_admin', 'superadmin'], true)
            || PermissionService::hasPermission((int) $_SESSION['user_id'], 'homologacoes_2', 'edit');
    }

    private function canEditHomologacao(array $u, array $h): bool
    {
        return in_array($u['perfil'], ['qualidade', 'tecnico', 'admin'], true)
            || (int) $h['criado_por'] === (int) $u['id']
            || in_array((int) $u['id'], $h['responsaveis'], true);
    }

    private function normalizeCreatePayload(): void
    {
        $setor = (string) ($_POST['setor_responsavel'] ?? 'tecnico');

        if ($setor === 'comercial') {
            $vendedorNome = trim((string) ($_POST['vendedor_nome'] ?? ''));
            $vendedorEmail = trim((string) ($_POST['vendedor_email'] ?? ''));
            $supervisorEmail = trim((string) ($_POST['supervisor_email'] ?? ''));

            if ($vendedorNome === '' || $vendedorEmail === '' || $supervisorEmail === '') {
                throw new \RuntimeException('Preencha os dados do vendedor e do supervisor comercial.');
            }

            if (!filter_var($vendedorEmail, FILTER_VALIDATE_EMAIL) || !filter_var($supervisorEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Informe e-mails comerciais validos.');
            }

            if (empty($_POST['responsaveis'])) {
                $_POST['responsaveis'] = [(int) $_SESSION['user_id']];
            }

            return;
        }

        if (!isset($_POST['responsaveis'])) {
            $_POST['responsaveis'] = [];
            return;
        }

        if (!is_array($_POST['responsaveis'])) {
            $_POST['responsaveis'] = [$_POST['responsaveis']];
        }
    }

    private function groupResponsaveisBySetor(array $users): array
    {
        $grupos = [
            'tecnico' => [],
            'qualidade' => [],
        ];

        foreach ($users as $user) {
            $setores = $this->inferResponsavelSetores($user);
            foreach ($setores as $setor) {
                $grupos[$setor][] = $user;
            }
        }

        return $grupos;
    }

    private function inferResponsavelSetores(array $user): array
    {
        $role = strtolower((string) ($user['role'] ?? ''));
        $perfil = strtolower((string) ($user['perfil'] ?? ''));
        $setor = $this->normalizeSetorNome((string) ($user['setor'] ?? ''));

        if (in_array($role, ['admin', 'super_admin', 'superadmin'], true) || $perfil === 'admin') {
            return ['tecnico', 'qualidade'];
        }

        if ($setor === 'qualidade') {
            return ['qualidade'];
        }

        if ($setor === 'area tecnica') {
            return ['tecnico'];
        }

        return [];
    }

    private function normalizeSetorNome(string $value): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));
        $value = str_replace(
            ['á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'],
            ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'],
            $value
        );
        $value = preg_replace('/\s+/', ' ', $value) ?: $value;

        return $value;
    }

    private function deny(): void
    {
        http_response_code(403);
        echo 'Acesso negado';
    }
}
