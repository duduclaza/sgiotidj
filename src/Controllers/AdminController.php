<?php

namespace App\Controllers;

use App\Config\Database;
use App\Controllers\AuthController;

class AdminController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retorna faixas dinâmicas de retorno com base nos parâmetros da triagem.
     */
    private function getTriagemFaixasRetorno(): array
    {
        try {
            $tableExists = $this->db->query("SHOW TABLES LIKE 'triagem_toners_parametros'")->rowCount() > 0;
            if (!$tableExists) {
                return [
                    ['id' => 1, 'label' => '0% – 5%', 'percentual_min' => 0.0, 'percentual_max' => 5.0],
                    ['id' => 2, 'label' => '6% – 40%', 'percentual_min' => 6.0, 'percentual_max' => 40.0],
                    ['id' => 3, 'label' => '41% – 80%', 'percentual_min' => 41.0, 'percentual_max' => 80.0],
                    ['id' => 4, 'label' => '81% – 100%', 'percentual_min' => 81.0, 'percentual_max' => 100.0],
                ];
            }

            $stmt = $this->db->query("SELECT id, percentual_min, percentual_max FROM triagem_toners_parametros ORDER BY ordem ASC, id ASC");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $faixas = [];
            foreach ($rows as $row) {
                $min = max(0, min(100, (float)($row['percentual_min'] ?? 0)));
                $max = max(0, min(100, (float)($row['percentual_max'] ?? 0)));
                if ($min > $max) {
                    [$min, $max] = [$max, $min];
                }

                $faixas[] = [
                    'id' => (int)$row['id'],
                    'label' => rtrim(rtrim(number_format($min, 2, '.', ''), '0'), '.') . '% – ' . rtrim(rtrim(number_format($max, 2, '.', ''), '0'), '.') . '%',
                    'percentual_min' => $min,
                    'percentual_max' => $max,
                ];
            }

            if (!empty($faixas)) {
                return $faixas;
            }
        } catch (\Exception $e) {
            // fallback abaixo
        }

        return [
            ['id' => 1, 'label' => '0% – 5%', 'percentual_min' => 0.0, 'percentual_max' => 5.0],
            ['id' => 2, 'label' => '6% – 40%', 'percentual_min' => 6.0, 'percentual_max' => 40.0],
            ['id' => 3, 'label' => '41% – 80%', 'percentual_min' => 41.0, 'percentual_max' => 80.0],
            ['id' => 4, 'label' => '81% – 100%', 'percentual_min' => 81.0, 'percentual_max' => 100.0],
        ];
    }
    
    /**
     * Admin dashboard
     */
    public function dashboard()
    {
        // Verificar se usuário está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Verificar permissão de dashboard usando sistema de permissões
        if (!\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
            http_response_code(403);
            echo "<h1>⛔ Acesso Negado</h1>";
            echo "<p>Você não tem permissão para acessar o Dashboard.</p>";
            echo "<p>Entre em contato com o administrador para solicitar acesso.</p>";
            echo "<p><a href='/inicio' style='color: #3B82F6;'>← Voltar para Início</a></p>";
            return;
        }
        
        try {
            // Get statistics
            $stats = $this->getStats();
            
            // Get totais acumulados dos gráficos
            $totaisAcumulados = $this->getTotaisAcumuladosGraficos();
            
            // Buscar permissões de abas do dashboard
            $dashboardTabs = $this->getDashboardTabPermissions();
            
            $title = 'Painel Administrativo - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/dashboard.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $error = 'Erro ao carregar dashboard: ' . $e->getMessage();
            $totaisAcumulados = ['retornados_total' => 0, 'destinos_total' => 0, 'valor_recuperado' => 0];
            $dashboardTabs = ['retornados' => true, 'amostragens' => true, 'fornecedores' => true, 'garantias' => true, 'melhorias' => true];
            $title = 'Erro - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/dashboard.php';
            include __DIR__ . '/../../views/layouts/main.php';
        }
    }

    /**
     * Dashboard 2.0 com foco em Triagem de Toners
     */
    public function dashboard2()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if (!\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
            http_response_code(403);
            echo "<h1>⛔ Acesso Negado</h1>";
            echo "<p>Você não tem permissão para acessar o Dashboard 2.0.</p>";
            echo "<p><a href='/inicio' style='color: #3B82F6;'>← Voltar para Início</a></p>";
            return;
        }

        $modulo = strtolower(trim((string)($_GET['modulo'] ?? '')));

        try {
            $triagemStats = [
                'total_registros' => 0,
                'media_percentual' => 0,
                'total_estoque' => 0,
                'valor_recuperado' => 0,
                'por_destino' => [],
                'ultimos_registros' => [],
            ];

            if ($modulo === 'triagem') {
                $triagemStats = $this->getTriagemDashboard2Stats();
            }

            $title = 'Dashboard 2.0 - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/dashboard-2.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $triagemStats = [
                'total_registros' => 0,
                'media_percentual' => 0,
                'total_estoque' => 0,
                'valor_recuperado' => 0,
                'por_destino' => [],
                'ultimos_registros' => [],
            ];

            $title = 'Dashboard 2.0 - Erro';
            $viewFile = __DIR__ . '/../../views/admin/dashboard-2.php';
            include __DIR__ . '/../../views/layouts/main.php';
        }
    }

    /**
     * Dashboard 2.0 - Página individual da Triagem de Toners
     */
    public function dashboard2Triagem()
    {
        $_GET['modulo'] = 'triagem';
        $this->dashboard2();
    }

    /**
     * API JSON: Dados completos do Dashboard de Triagem com filtros dinâmicos
     * GET /dashboard-2/triagem/data?modelo=&cliente=&defeito=&data_inicio=&data_fim=
     */
    public function dashboard2TriagemData()
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id']) || !\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            $tableExists = $this->db->query("SHOW TABLES LIKE 'triagem_toners'")->rowCount() > 0;
            if (!$tableExists) {
                echo json_encode(['success' => true, 'kpis' => [], 'charts' => []]);
                exit;
            }

            $faixasRetorno = $this->getTriagemFaixasRetorno();

            // --- Build dynamic WHERE ---
            $where = '1=1';
            $params = [];

            if (!empty($_GET['modelo'])) {
                $where .= ' AND t.toner_modelo = ?';
                $params[] = trim($_GET['modelo']);
            }
            if (!empty($_GET['cliente'])) {
                $where .= ' AND t.cliente_nome = ?';
                $params[] = trim($_GET['cliente']);
            }
            if (!empty($_GET['defeito'])) {
                $where .= ' AND t.defeito_nome = ?';
                $params[] = trim($_GET['defeito']);
            }
            if (!empty($_GET['destino'])) {
                $where .= ' AND t.destino = ?';
                $params[] = trim($_GET['destino']);
            }
            if (!empty($_GET['filial'])) {
                $where .= ' AND COALESCE(t.filial_registro, \'\') LIKE ?';
                $params[] = '%' . trim($_GET['filial']) . '%';
            }

            $faixasSelecionadasRaw = trim((string)($_GET['faixa_ids'] ?? ''));
            if ($faixasSelecionadasRaw !== '') {
                $idsSelecionados = array_values(array_filter(array_map('intval', explode(',', $faixasSelecionadasRaw)), static fn($id) => $id > 0));
                if (!empty($idsSelecionados)) {
                    $mapFaixas = [];
                    foreach ($faixasRetorno as $fx) {
                        $mapFaixas[(int)$fx['id']] = $fx;
                    }

                    $condicoesFaixa = [];
                    foreach ($idsSelecionados as $idFaixa) {
                        if (!isset($mapFaixas[$idFaixa])) {
                            continue;
                        }
                        $fx = $mapFaixas[$idFaixa];
                        $condicoesFaixa[] = '(COALESCE(t.percentual_calculado, 0) >= ? AND COALESCE(t.percentual_calculado, 0) <= ?)';
                        $params[] = (float)$fx['percentual_min'];
                        $params[] = (float)$fx['percentual_max'];
                    }

                    if (!empty($condicoesFaixa)) {
                        $where .= ' AND (' . implode(' OR ', $condicoesFaixa) . ')';
                    }
                }
            }

            $dataInicio = !empty($_GET['data_inicio']) ? trim((string)$_GET['data_inicio']) : null;
            $dataFim = !empty($_GET['data_fim']) ? trim((string)$_GET['data_fim']) : null;

            $inicioDate = null;
            $fimDate = null;
            if ($dataInicio && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInicio)) {
                $inicioDate = $dataInicio;
            }
            if ($dataFim && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)) {
                $fimDate = $dataFim;
            }

            // Se o usuário informar invertido (fim antes de início), corrige automaticamente
            if ($inicioDate && $fimDate && $inicioDate > $fimDate) {
                [$inicioDate, $fimDate] = [$fimDate, $inicioDate];
            }

            if ($inicioDate) {
                $where .= ' AND t.created_at >= ?';
                $params[] = $inicioDate . ' 00:00:00';
            }
            if ($fimDate) {
                $where .= ' AND t.created_at < DATE_ADD(?, INTERVAL 1 DAY)';
                $params[] = $fimDate;
            }

            // --- KPIs ---
            $kpiSql = "SELECT
                COUNT(*) AS total_registros,
                COALESCE(AVG(t.percentual_calculado), 0) AS media_percentual,
                SUM(CASE WHEN t.destino = 'Estoque' THEN 1 ELSE 0 END) AS total_estoque,
                COALESCE(SUM(t.valor_recuperado), 0) AS valor_recuperado,
                SUM(CASE WHEN t.destino = 'Descarte' THEN 1 ELSE 0 END) AS total_descarte,
                SUM(CASE WHEN t.destino = 'Garantia' THEN 1 ELSE 0 END) AS total_garantia
                FROM triagem_toners t WHERE {$where}";
            $stmt = $this->db->prepare($kpiSql);
            $stmt->execute($params);
            $kpis = $stmt->fetch(\PDO::FETCH_ASSOC);

            // --- Chart 1: Top modelos por volume (barras verticais desc) ---
            $chart1Sql = "SELECT t.toner_modelo AS label, COUNT(*) AS total
                          FROM triagem_toners t WHERE {$where}
                          GROUP BY t.toner_modelo ORDER BY total DESC LIMIT 15";
            $stmt = $this->db->prepare($chart1Sql);
            $stmt->execute($params);
            $chart1 = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // --- Chart 2: Pareto de defeitos ---
            $chart2Sql = "SELECT COALESCE(NULLIF(t.defeito_nome,''), 'Não informado') AS label, COUNT(*) AS total
                          FROM triagem_toners t WHERE {$where}
                          GROUP BY label ORDER BY total DESC LIMIT 15";
            $stmt = $this->db->prepare($chart2Sql);
            $stmt->execute($params);
            $chart2Raw = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $grandTotal2 = array_sum(array_column($chart2Raw, 'total'));
            $acumulado = 0;
            $chart2 = [];
            foreach ($chart2Raw as $row) {
                $pctIndividual = $grandTotal2 > 0 ? round(((int)$row['total'] / $grandTotal2) * 100, 2) : 0;
                $acumulado += $pctIndividual;
                $chart2[] = [
                    'label' => $row['label'],
                    'total' => (int)$row['total'],
                    'pct' => $pctIndividual,
                    'pct_acumulado' => round($acumulado, 2),
                ];
            }

            // --- Chart 3: Faixas de percentual de retorno (dinâmico por parâmetros da triagem) ---
            $chart3SelectParts = [];
            $chart3Params = $params;
            foreach ($faixasRetorno as $i => $fx) {
                $alias = 'faixa_' . $i;
                $chart3SelectParts[] = "SUM(CASE WHEN COALESCE(t.percentual_calculado, 0) >= ? AND COALESCE(t.percentual_calculado, 0) <= ? THEN 1 ELSE 0 END) AS {$alias}";
                $chart3Params[] = (float)$fx['percentual_min'];
                $chart3Params[] = (float)$fx['percentual_max'];
            }

            $chart3 = [];
            if (!empty($chart3SelectParts)) {
                $chart3Sql = "SELECT " . implode(', ', $chart3SelectParts) . " FROM triagem_toners t WHERE {$where}";
                $stmt = $this->db->prepare($chart3Sql);
                $stmt->execute($chart3Params);
                $faixasTotals = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

                foreach ($faixasRetorno as $i => $fx) {
                    $alias = 'faixa_' . $i;
                    $chart3[] = [
                        'id' => (int)$fx['id'],
                        'label' => $fx['label'],
                        'total' => (int)($faixasTotals[$alias] ?? 0),
                        'percentual_min' => (float)$fx['percentual_min'],
                        'percentual_max' => (float)$fx['percentual_max'],
                    ];
                }
            }

            // --- Chart 4: Evolução mensal de reprovação (destino=Garantia) ---
            $chart4Sql = "SELECT
                DATE_FORMAT(t.created_at, '%Y-%m') AS mes,
                COUNT(*) AS total_avaliados,
                SUM(CASE WHEN t.destino = 'Garantia' THEN 1 ELSE 0 END) AS total_reprovados
                FROM triagem_toners t WHERE {$where}
                GROUP BY mes ORDER BY mes ASC";
            $stmt = $this->db->prepare($chart4Sql);
            $stmt->execute($params);
            $chart4Raw = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $chart4 = [];
            foreach ($chart4Raw as $row) {
                $pctReprova = (int)$row['total_avaliados'] > 0
                    ? round(((int)$row['total_reprovados'] / (int)$row['total_avaliados']) * 100, 2)
                    : 0;
                $chart4[] = [
                    'mes' => $row['mes'],
                    'total_avaliados' => (int)$row['total_avaliados'],
                    'total_reprovados' => (int)$row['total_reprovados'],
                    'pct_reprovacao' => $pctReprova,
                ];
            }

            // --- Distribuição por destino (donut) ---
            $destinoSql = "SELECT t.destino AS label, COUNT(*) AS total
                           FROM triagem_toners t WHERE {$where}
                           GROUP BY t.destino ORDER BY total DESC";
            $stmt = $this->db->prepare($destinoSql);
            $stmt->execute($params);
            $porDestino = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // --- Triagens (todas conforme filtros aplicados) ---
            $ultimosSql = "SELECT t.cliente_nome, t.toner_modelo, t.percentual_calculado, t.destino, t.valor_recuperado, t.created_at
                           FROM triagem_toners t WHERE {$where}
                           ORDER BY t.created_at DESC";
            $stmt = $this->db->prepare($ultimosSql);
            $stmt->execute($params);
            $ultimos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // --- Options para filtros ---
            $modelos = $this->db->query("SELECT DISTINCT toner_modelo FROM triagem_toners ORDER BY toner_modelo")->fetchAll(\PDO::FETCH_COLUMN);
            $clientes = $this->db->query("SELECT DISTINCT cliente_nome FROM triagem_toners WHERE cliente_nome IS NOT NULL AND cliente_nome != '' ORDER BY cliente_nome")->fetchAll(\PDO::FETCH_COLUMN);
            $defeitos = $this->db->query("SELECT DISTINCT defeito_nome FROM triagem_toners WHERE defeito_nome IS NOT NULL AND defeito_nome != '' ORDER BY defeito_nome")->fetchAll(\PDO::FETCH_COLUMN);
            $filiais = $this->getFiliaisList();
            if (empty($filiais)) {
                $filiais = $this->db->query("SELECT DISTINCT filial_registro FROM triagem_toners WHERE filial_registro IS NOT NULL AND filial_registro != '' ORDER BY filial_registro")->fetchAll(\PDO::FETCH_COLUMN);
            }

            echo json_encode([
                'success' => true,
                'kpis' => [
                    'total_registros' => (int)($kpis['total_registros'] ?? 0),
                    'media_percentual' => round((float)($kpis['media_percentual'] ?? 0), 2),
                    'total_estoque' => (int)($kpis['total_estoque'] ?? 0),
                    'valor_recuperado' => round((float)($kpis['valor_recuperado'] ?? 0), 2),
                    'total_descarte' => (int)($kpis['total_descarte'] ?? 0),
                    'total_garantia' => (int)($kpis['total_garantia'] ?? 0),
                ],
                'charts' => [
                    'modelos' => $chart1,
                    'defeitos_pareto' => $chart2,
                    'faixas_percentual' => $chart3,
                    'evolucao_mensal' => $chart4,
                    'por_destino' => $porDestino,
                ],
                'ultimos_registros' => $ultimos,
                'filter_options' => [
                    'modelos' => $modelos ?: [],
                    'clientes' => $clientes ?: [],
                    'defeitos' => $defeitos ?: [],
                    'filiais' => $filiais ?: [],
                    'faixas_retorno' => $faixasRetorno,
                ],
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API JSON: Detalhes dos toners reprovados (Garantia) de um mês
     * GET /dashboard-2/triagem/reprovados?mes=YYYY-MM
     */
    public function dashboard2TriagemReprovados()
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['user_id']) || !\App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        try {
            $mes = trim($_GET['mes'] ?? '');
            if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
                echo json_encode(['success' => false, 'message' => 'Parâmetro mes inválido (YYYY-MM)']);
                exit;
            }

            $inicioMes = $mes . '-01';
            $fimMes = (new \DateTime($inicioMes))->modify('first day of next month')->format('Y-m-d');

            $tableExists = $this->db->query("SHOW TABLES LIKE 'triagem_toners'")->rowCount() > 0;
            if (!$tableExists) {
                echo json_encode(['success' => true, 'mes' => $mes, 'registros' => []]);
                exit;
            }

            $sql = "SELECT t.id, t.cliente_nome, t.toner_modelo, t.defeito_nome, t.percentual_calculado,
                           t.fornecedor_nome, t.destino, t.valor_recuperado, t.observacoes, t.created_at
                    FROM triagem_toners t
                    WHERE t.destino = 'Garantia' AND t.created_at >= ? AND t.created_at < ?
                    ORDER BY t.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$inicioMes, $fimMes]);
            $registros = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Resumo por cliente
            $porCliente = [];
            foreach ($registros as $r) {
                $cli = $r['cliente_nome'] ?: 'Não informado';
                if (!isset($porCliente[$cli])) $porCliente[$cli] = 0;
                $porCliente[$cli]++;
            }
            arsort($porCliente);
            $resumoClientes = [];
            foreach ($porCliente as $nome => $qtd) {
                $resumoClientes[] = ['cliente' => $nome, 'total' => $qtd];
            }

            echo json_encode([
                'success' => true,
                'mes' => $mes,
                'total' => count($registros),
                'registros' => $registros,
                'por_cliente' => $resumoClientes,
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
        exit;
    }

    private function getTriagemDashboard2Stats(): array
    {
        $stats = [
            'total_registros' => 0,
            'media_percentual' => 0,
            'total_estoque' => 0,
            'valor_recuperado' => 0,
            'por_destino' => [],
            'ultimos_registros' => [],
        ];

        $tableExists = $this->db->query("SHOW TABLES LIKE 'triagem_toners'")->rowCount() > 0;
        if (!$tableExists) {
            return $stats;
        }

        $resumo = $this->db->query("SELECT COUNT(*) AS total_registros,
                                           COALESCE(AVG(percentual_calculado), 0) AS media_percentual,
                                           SUM(CASE WHEN destino = 'Estoque' THEN 1 ELSE 0 END) AS total_estoque,
                                           COALESCE(SUM(valor_recuperado), 0) AS valor_recuperado
                                    FROM triagem_toners")->fetch(\PDO::FETCH_ASSOC);

        if ($resumo) {
            $stats['total_registros'] = (int)$resumo['total_registros'];
            $stats['media_percentual'] = (float)$resumo['media_percentual'];
            $stats['total_estoque'] = (int)$resumo['total_estoque'];
            $stats['valor_recuperado'] = (float)$resumo['valor_recuperado'];
        }

        $porDestino = $this->db->query("SELECT destino, COUNT(*) AS total
                                        FROM triagem_toners
                                        GROUP BY destino
                                        ORDER BY total DESC")->fetchAll(\PDO::FETCH_ASSOC);
        $stats['por_destino'] = $porDestino ?: [];

        $ultimos = $this->db->query("SELECT cliente_nome, toner_modelo, percentual_calculado, destino, valor_recuperado, created_at
                                     FROM triagem_toners
                                     ORDER BY created_at DESC
                                     LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);
        $stats['ultimos_registros'] = $ultimos ?: [];

        return $stats;
    }

    /**
     * Diagnóstico detalhado do Dashboard
     * NÃO altera o comportamento normal, apenas ajuda a entender erros 500.
     * Rota sugerida: /admin/dashboard/diagnostico (GET)
     */
    public function diagnosticoDashboard()
    {
        // Forçar saída JSON para facilitar debug via navegador
        header('Content-Type: application/json; charset=utf-8');

        $resultado = [
            'auth' => false,
            'permissao_dashboard' => false,
            'passos' => [],
        ];

        try {
            // 1) Autenticação
            if (!isset($_SESSION['user_id'])) {
                $resultado['passos'][] = [
                    'etapa' => 'auth',
                    'ok' => false,
                    'mensagem' => 'Usuário não autenticado',
                ];
                echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }
            $resultado['auth'] = true;

            // 2) Permissão de dashboard
            $hasDashboard = \App\Services\PermissionService::hasPermission($_SESSION['user_id'], 'dashboard', 'view');
            $resultado['permissao_dashboard'] = $hasDashboard;
            if (!$hasDashboard) {
                $resultado['passos'][] = [
                    'etapa' => 'permissao_dashboard',
                    'ok' => false,
                    'mensagem' => 'Usuário não tem permissão para dashboard',
                ];
                echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return;
            }

            // 3) Testar getStats()
            try {
                $stats = $this->getStats();
                $resultado['passos'][] = [
                    'etapa' => 'getStats',
                    'ok' => true,
                    'resumo' => [
                        'keys' => array_keys((array)$stats),
                    ],
                ];
            } catch (\Throwable $e) {
                error_log('Dashboard diagnostico - getStats ERRO: ' . $e->getMessage());
                $resultado['passos'][] = [
                    'etapa' => 'getStats',
                    'ok' => false,
                    'mensagem' => $e->getMessage(),
                ];
            }

            // 4) Testar getTotaisAcumuladosGraficos()
            try {
                $totais = $this->getTotaisAcumuladosGraficos();
                $resultado['passos'][] = [
                    'etapa' => 'getTotaisAcumuladosGraficos',
                    'ok' => true,
                    'resumo' => [
                        'keys' => array_keys((array)$totais),
                    ],
                ];
            } catch (\Throwable $e) {
                error_log('Dashboard diagnostico - getTotaisAcumuladosGraficos ERRO: ' . $e->getMessage());
                $resultado['passos'][] = [
                    'etapa' => 'getTotaisAcumuladosGraficos',
                    'ok' => false,
                    'mensagem' => $e->getMessage(),
                ];
            }

            // 5) Testar getDashboardTabPermissions()
            try {
                $tabs = $this->getDashboardTabPermissions();
                $resultado['passos'][] = [
                    'etapa' => 'getDashboardTabPermissions',
                    'ok' => true,
                    'resumo' => $tabs,
                ];
            } catch (\Throwable $e) {
                error_log('Dashboard diagnostico - getDashboardTabPermissions ERRO: ' . $e->getMessage());
                $resultado['passos'][] = [
                    'etapa' => 'getDashboardTabPermissions',
                    'ok' => false,
                    'mensagem' => $e->getMessage(),
                ];
            }

            // 6) Testar endpoints de dados do dashboard (sem filtros complexos)
            // Fornecedores
            try {
                $dadosFornecedores = $this->fornecedoresData();
                $resultado['passos'][] = [
                    'etapa' => 'fornecedoresData',
                    'ok' => true,
                    'tipo' => gettype($dadosFornecedores),
                ];
            } catch (\Throwable $e) {
                error_log('Dashboard diagnostico - fornecedoresData ERRO: ' . $e->getMessage());
                $resultado['passos'][] = [
                    'etapa' => 'fornecedoresData',
                    'ok' => false,
                    'mensagem' => $e->getMessage(),
                ];
            }

            // Amostragens
            try {
                $dadosAmostragens = $this->getAmostragemsDashboardData();
                $resultado['passos'][] = [
                    'etapa' => 'getAmostragemsDashboardData',
                    'ok' => true,
                    'tipo' => gettype($dadosAmostragens),
                ];
            } catch (\Throwable $e) {
                error_log('Dashboard diagnostico - getAmostragemsDashboardData ERRO: ' . $e->getMessage());
                $resultado['passos'][] = [
                    'etapa' => 'getAmostragemsDashboardData',
                    'ok' => false,
                    'mensagem' => $e->getMessage(),
                ];
            }

            // Melhorias
            try {
                $dadosMelhorias = $this->getMelhoriasData();
                $resultado['passos'][] = [
                    'etapa' => 'getMelhoriasData',
                    'ok' => true,
                    'tipo' => gettype($dadosMelhorias),
                ];
            } catch (\Throwable $e) {
                error_log('Dashboard diagnostico - getMelhoriasData ERRO: ' . $e->getMessage());
                $resultado['passos'][] = [
                    'etapa' => 'getMelhoriasData',
                    'ok' => false,
                    'mensagem' => $e->getMessage(),
                ];
            }

            $resultado['sucesso'] = true;
        } catch (\Throwable $e) {
            error_log('Dashboard diagnostico - ERRO GERAL: ' . $e->getMessage());
            $resultado['sucesso'] = false;
            $resultado['erro_geral'] = $e->getMessage();
        }

        echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Manage users
     */
    public function users()
    {
        AuthController::requireAdmin();
        
        // Check if it's an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            try {
                // Verificar quais colunas de permissão existem
                $hasColumnNotificacoes = false;
                $hasColumnPopsIts = false;
                $hasColumnFluxogramas = false;
                $hasColumnAmostragens = false;
                
                try {
                    $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas'");
                    $hasColumnNotificacoes = $checkColumn->rowCount() > 0;
                    
                    $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its'");
                    $hasColumnPopsIts = $checkColumn->rowCount() > 0;
                    
                    $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_fluxogramas'");
                    $hasColumnFluxogramas = $checkColumn->rowCount() > 0;
                    
                    $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_amostragens'");
                    $hasColumnAmostragens = $checkColumn->rowCount() > 0;
                } catch (\Exception $e) {
                    // Colunas não existem ainda
                }
                
                // Construir query dinamicamente com todos os campos de permissão
                $notifColumn = $hasColumnNotificacoes ? 'u.notificacoes_ativadas,' : '1 as notificacoes_ativadas,';
                $popsItsColumn = $hasColumnPopsIts ? 'u.pode_aprovar_pops_its,' : '0 as pode_aprovar_pops_its,';
                $fluxogramasColumn = $hasColumnFluxogramas ? 'u.pode_aprovar_fluxogramas,' : '0 as pode_aprovar_fluxogramas,';
                $amostragemColumn = $hasColumnAmostragens ? 'u.pode_aprovar_amostragens,' : '0 as pode_aprovar_amostragens,';
                
                $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email, u.setor, u.filial, u.role, u.status, u.created_at, u.profile_id,
                       {$notifColumn}
                       {$popsItsColumn}
                       {$fluxogramasColumn}
                       {$amostragemColumn}
                       p.name as profile_name, p.description as profile_description
                FROM users u 
                LEFT JOIN profiles p ON u.profile_id = p.id 
                WHERE u.email != 'du.claza@gmail.com'
                ORDER BY u.created_at DESC
            ");
                $stmt->execute();
                $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Get setores usando a mesma lógica da API
                $setores = $this->getSetoresList();
                
                // Get filiais usando a mesma lógica da API  
                $filiais = $this->getFiliaisList();
                
                // Get profiles
                $profilesStmt = $this->db->prepare("SELECT id, name, description FROM profiles ORDER BY is_admin DESC, name ASC");
                $profilesStmt->execute();
                $profiles = $profilesStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Ocultar perfil Super Administrador para quem não é MasterUser
                if (!\App\Services\MasterUserService::isMasterUser()) {
                    $profiles = array_values(array_filter($profiles, function($p){
                        return strtolower((string)($p['name'] ?? '')) !== 'super administrador';
                    }));
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'users' => $users,
                    'setores' => $setores,
                    'filiais' => $filiais,
                    'profiles' => $profiles
                ]);
                return;
            } catch (\Exception $e) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                return;
            }
        }
        
        // Regular page load
        try {
            $title = 'Gerenciar Usuários - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/users.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $error = 'Erro ao carregar usuários: ' . $e->getMessage();
            $title = 'Erro - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/users.php';
            include __DIR__ . '/../../views/layouts/main.php';
        }
    }
    
    /**
     * Manage invitations
     */
    public function invitations()
    {
        AuthController::requireAdmin();
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM user_invitations ORDER BY created_at DESC");
            $stmt->execute();
            $invitations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $title = 'Solicitações de Acesso - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/invitations.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            $error = 'Erro ao carregar solicitações: ' . $e->getMessage();
            $title = 'Erro - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/invitations.php';
            include __DIR__ . '/../../views/layouts/main.php';
        }
    }
    
    /**
     * Create user
     */
    public function createUser()
    {
        try {
            // Limpar qualquer output anterior
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Headers JSON
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            
            // Verificar se é admin
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                exit;
            }
            
            if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado - apenas administradores']);
                exit;
            }
            
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $setor = $_POST['setor'] ?? '';
            $filial = $_POST['filial'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $profileId = $_POST['profile_id'] ?? null;
            
            // Receber valores explícitos dos checkboxes (0 ou 1)
            $podeAprovarPopsIts = isset($_POST['pode_aprovar_pops_its']) ? (int)$_POST['pode_aprovar_pops_its'] : 0;
            $podeAprovarFluxogramas = isset($_POST['pode_aprovar_fluxogramas']) ? (int)$_POST['pode_aprovar_fluxogramas'] : 0;
            $podeAprovarAmostragens = isset($_POST['pode_aprovar_amostragens']) ? (int)$_POST['pode_aprovar_amostragens'] : 0;
            $notificacoesAtivadas = isset($_POST['notificacoes_ativadas']) ? (int)$_POST['notificacoes_ativadas'] : 1; // Padrão: 1 (ativado)
            
            // Validar dados obrigatórios
            if (empty($name) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Nome e email são obrigatórios']);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email inválido']);
                exit;
            }
            
            // Verificar se usuário já existe
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Este email já está cadastrado']);
                exit;
            }
            
            // Se não especificou perfil, usar o padrão
            if (empty($profileId)) {
                $defaultProfileStmt = $this->db->prepare("SELECT id FROM profiles WHERE is_default = 1 LIMIT 1");
                $defaultProfileStmt->execute();
                $profileId = $defaultProfileStmt->fetchColumn();
                
                // Se não encontrou perfil padrão, usar o primeiro disponível
                if (!$profileId) {
                    $firstProfileStmt = $this->db->prepare("SELECT id FROM profiles LIMIT 1");
                    $firstProfileStmt->execute();
                    $profileId = $firstProfileStmt->fetchColumn();
                }
            }
            
            // Gerar senha temporária
            $tempPassword = $this->generateTempPassword();
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Verificar se colunas existem antes de inserir
            $columns = "name, email, password, setor, filial, role, profile_id, status";
            $placeholders = "?, ?, ?, ?, ?, ?, ?, 'active'";
            $params = [$name, $email, $hashedPassword, $setor, $filial, $role, $profileId];
            
            // Adicionar coluna pode_aprovar_pops_its se existir
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its'");
                if ($checkColumn->rowCount() > 0) {
                    $columns .= ", pode_aprovar_pops_its";
                    $placeholders .= ", ?";
                    $params[] = $podeAprovarPopsIts;
                }
            } catch (\Exception $e) {
                error_log("Coluna pode_aprovar_pops_its não existe ainda: " . $e->getMessage());
            }
            
            // Adicionar coluna pode_aprovar_fluxogramas se existir
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_fluxogramas'");
                if ($checkColumn->rowCount() > 0) {
                    $columns .= ", pode_aprovar_fluxogramas";
                    $placeholders .= ", ?";
                    $params[] = $podeAprovarFluxogramas;
                }
            } catch (\Exception $e) {
                error_log("Coluna pode_aprovar_fluxogramas não existe ainda: " . $e->getMessage());
            }
            
            // Adicionar coluna pode_aprovar_amostragens se existir
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_amostragens'");
                if ($checkColumn->rowCount() > 0) {
                    $columns .= ", pode_aprovar_amostragens";
                    $placeholders .= ", ?";
                    $params[] = $podeAprovarAmostragens;
                }
            } catch (\Exception $e) {
                error_log("Coluna pode_aprovar_amostragens não existe ainda: " . $e->getMessage());
            }
            
            // Adicionar coluna notificacoes_ativadas se existir
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas'");
                if ($checkColumn->rowCount() > 0) {
                    $columns .= ", notificacoes_ativadas";
                    $placeholders .= ", ?";
                    $params[] = $notificacoesAtivadas;
                }
            } catch (\Exception $e) {
                error_log("Coluna notificacoes_ativadas não existe ainda: " . $e->getMessage());
            }
            
            $stmt = $this->db->prepare("INSERT INTO users ($columns) VALUES ($placeholders)");
            $stmt->execute($params);
            
            $userId = $this->db->lastInsertId();
            
            // Retornar sucesso com a senha (sem tentar enviar email por enquanto)
            echo json_encode([
                'success' => true, 
                'message' => 'Usuário criado com sucesso! Senha temporária: ' . $tempPassword,
                'user_id' => $userId
            ]);
            
        } catch (\Exception $e) {
            error_log('Error creating user: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao criar usuário: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Send credentials via email
     */
    public function sendCredentials()
    {
        try {
            // Limpar qualquer output anterior
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Headers JSON
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');

            // Verificar sessão
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                exit;
            }

            // Verificar se é admin
            if (!\App\Services\PermissionService::hasAdminPrivileges($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Acesso negado - apenas administradores']);
                exit;
            }

            $userId = $_POST['user_id'] ?? $_POST['id'] ?? null;
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
                exit;
            }

            // Buscar usuário
            $stmt = $this->db->prepare("SELECT id, name, email, status FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                exit;
            }

            if ($user['status'] !== 'active') {
                echo json_encode(['success' => false, 'message' => 'Usuário não está ativo']);
                exit;
            }

            // Verificar configurações de email
            if (empty($_ENV['MAIL_HOST']) || empty($_ENV['MAIL_USERNAME'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Configurações de email não encontradas'
                ]);
                exit;
            }

            // Sempre enviar a senha temporária mudar@123
            $senhaTemporaria = 'mudar@123';

            // Enviar email de credenciais com a senha temporária
            $emailService = new \App\Services\EmailService();
            $emailSent = $emailService->sendWelcomeEmail($user, $senhaTemporaria);

            if ($emailSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Credenciais enviadas com sucesso para ' . $user['email'] . '! Senha: mudar@123'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Falha ao enviar email para ' . $user['email']
                ]);
            }

        } catch (\Exception $e) {
            error_log('Error in sendCredentials: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Generate temporary password
     */
    private function generateTempPassword(int $length = 8): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $charactersLength = strlen($characters);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $password;
    }

    
    /**
     * Send clean JSON response
     */
    private function sendJsonResponse($data)
    {
        // Limpar qualquer output anterior
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Enviar resposta JSON limpa
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send password change notification
     */
    private function sendPasswordChangeNotification($name, $email, $newPassword)
    {
        try {
            $emailService = new \App\Services\EmailService();
            
            $subject = 'SGQ-OTI DJ - Sua senha foi alterada';
            $loginUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/login';
            
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                        <h1 style='color: white; margin: 0; font-size: 28px;'>Senha Alterada</h1>
                    </div>
                    
                    <div style='padding: 30px; background: #f8f9fa;'>
                        <h2 style='color: #333; margin-bottom: 20px;'>Olá, {$name}!</h2>
                        
                        <p style='color: #666; font-size: 16px; line-height: 1.6;'>
                            Sua senha no sistema SGQ-OTI DJ foi alterada pelo administrador. Abaixo estão seus novos dados de acesso:
                        </p>
                        
                        <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;'>
                            <h3 style='color: #333; margin-top: 0;'>Novos Dados de Acesso:</h3>
                            <p style='margin: 10px 0;'><strong>Email:</strong> {$email}</p>
                            <p style='margin: 10px 0;'><strong>Nova Senha:</strong> {$newPassword}</p>
                        </div>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$loginUrl}' style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                                Acessar Sistema
                            </a>
                        </div>
                        
                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                            <p style='margin: 0; color: #856404;'>
                                <strong>Importante:</strong> Por segurança, recomendamos que você altere sua senha no primeiro acesso.
                            </p>
                        </div>
                        
                        <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                            Se você não solicitou esta alteração, entre em contato com o administrador do sistema imediatamente.
                        </p>
                    </div>
                    
                    <div style='background: #333; padding: 20px; text-align: center;'>
                        <p style='color: #ccc; margin: 0; font-size: 12px;'>
                            SGQ-OTI DJ - Sistema de Gestão da Qualidade
                        </p>
                    </div>
                </div>
            ";
            
            $emailService->send($email, $subject, $body);
        } catch (\Exception $e) {
            // Log error but don't fail user update
            error_log('Failed to send password change notification: ' . $e->getMessage());
        }
    }
    
    /**
     * Get setores list using robust query logic
     */
    private function getSetoresList(): array
    {
        $queries = [
            "SELECT name FROM departments WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM departments WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome",
            "SELECT name FROM departamentos WHERE name IS NOT NULL AND name <> '' ORDER BY name", 
            "SELECT nome as name FROM departamentos WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome",
            "SELECT name FROM setores WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM setores WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome"
        ];
        
        foreach ($queries as $query) {
            try {
                $stmt = $this->db->query($query);
                $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                if (!empty($result)) {
                    return $result;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Fallback: buscar dos usuários
        try {
            $stmt = $this->db->query("SELECT DISTINCT setor FROM users WHERE setor IS NOT NULL AND setor <> '' ORDER BY setor");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get filiais list using robust query logic
     */
    private function getFiliaisList(): array
    {
        $queries = [
            "SELECT name FROM filiais WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM filiais WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome",
            "SELECT name FROM branches WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM branches WHERE nome IS NOT NULL AND nome <> '' ORDER by nome",
            "SELECT name FROM subsidiarias WHERE name IS NOT NULL AND name <> '' ORDER BY name",
            "SELECT nome as name FROM subsidiarias WHERE nome IS NOT NULL AND nome <> '' ORDER BY nome"
        ];
        
        foreach ($queries as $query) {
            try {
                $stmt = $this->db->query($query);
                $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                if (!empty($result)) {
                    return $result;
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Fallback: buscar dos usuários
        try {
            $stmt = $this->db->query("SELECT DISTINCT filial FROM users WHERE filial IS NOT NULL AND filial <> '' ORDER BY filial");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Send welcome email to new user
     */
    private function sendWelcomeEmail($name, $email, $password)
    {
        try {
            $emailService = new \App\Services\EmailService();
            
            $subject = 'Bem-vindo ao SGQ-OTI DJ - Seus dados de acesso';
            $loginUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/login';
            
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center;'>
                        <h1 style='color: white; margin: 0; font-size: 28px;'>Bem-vindo ao SGQ-OTI DJ!</h1>
                    </div>
                    
                    <div style='padding: 30px; background: #f8f9fa;'>
                        <h2 style='color: #333; margin-bottom: 20px;'>Olá, {$name}!</h2>
                        
                        <p style='color: #666; font-size: 16px; line-height: 1.6;'>
                            Sua conta foi criada com sucesso no sistema SGQ-OTI DJ. Abaixo estão seus dados de acesso:
                        </p>
                        
                        <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;'>
                            <h3 style='color: #333; margin-top: 0;'>Dados de Acesso:</h3>
                            <p style='margin: 10px 0;'><strong>Email:</strong> {$email}</p>
                            <p style='margin: 10px 0;'><strong>Senha Temporária:</strong> {$password}</p>
                        </div>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$loginUrl}' style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                                Acessar Sistema
                            </a>
                        </div>
                        
                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>
                            <p style='margin: 0; color: #856404;'>
                                <strong>Importante:</strong> Por segurança, recomendamos que você altere sua senha no primeiro acesso.
                            </p>
                        </div>
                        
                        <p style='color: #666; font-size: 14px; margin-top: 30px;'>
                            Se você tiver alguma dúvida, entre em contato com o administrador do sistema.
                        </p>
                    </div>
                    
                    <div style='background: #333; padding: 20px; text-align: center;'>
                        <p style='color: #ccc; margin: 0; font-size: 12px;'>
                            SGQ-OTI DJ - Sistema de Gestão da Qualidade
                        </p>
                    </div>
                </div>
            ";
            
            $emailService->send($email, $subject, $body);
        } catch (\Exception $e) {
            // Log error but don't fail user creation
            error_log('Failed to send welcome email: ' . $e->getMessage());
        }
    }
    
    /**
     * Update user
     */
    public function updateUser()
    {
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set JSON headers first
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            // Log start of method
            error_log("=== UpdateUser method started ===");
            error_log("Session data: " . json_encode([
                'user_id' => $_SESSION['user_id'] ?? 'not set',
                'user_role' => $_SESSION['user_role'] ?? 'not set'
            ]));
            
            // Check authentication first
            if (!isset($_SESSION['user_id'])) {
                error_log("Authentication failed: user_id not in session");
                echo json_encode(['success' => false, 'message' => 'Não autenticado', 'redirect' => '/login']);
                exit;
            }
            
            // ⭐ Super Admin tem acesso total
            if (!in_array($_SESSION['user_role'], ['admin', 'super_admin'])) {
                error_log("Authorization failed: user role is " . ($_SESSION['user_role'] ?? 'undefined'));
                echo json_encode(['success' => false, 'message' => 'Acesso negado - apenas administradores']);
                exit;
            }
            
            // Accept both 'id' and 'user_id' for compatibility
            $userId = $_POST['id'] ?? $_POST['user_id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $setor = $_POST['setor'] ?? '';
            $filial = $_POST['filial'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $status = $_POST['status'] ?? 'active';
            $profileId = $_POST['profile_id'] ?? null;
            
            // Receber valores explícitos dos checkboxes (0 ou 1)
            $podeAprovarPopsIts = isset($_POST['pode_aprovar_pops_its']) ? (int)$_POST['pode_aprovar_pops_its'] : 0;
            $podeAprovarFluxogramas = isset($_POST['pode_aprovar_fluxogramas']) ? (int)$_POST['pode_aprovar_fluxogramas'] : 0;
            $podeAprovarAmostragens = isset($_POST['pode_aprovar_amostragens']) ? (int)$_POST['pode_aprovar_amostragens'] : 0;
            $notificacoesAtivadas = isset($_POST['notificacoes_ativadas']) ? (int)$_POST['notificacoes_ativadas'] : 1; // Padrão: 1 (ativado)
            
            // Debug log
            error_log("UpdateUser - UserID: $userId, Name: $name, Email: $email");
            error_log("Checkboxes - POPs/ITs: $podeAprovarPopsIts, Fluxogramas: $podeAprovarFluxogramas, Amostragens: $podeAprovarAmostragens, Notificações: $notificacoesAtivadas");
            
            // Test database connection and table structure
            try {
                $testStmt = $this->db->query("DESCRIBE users");
                $columns = $testStmt->fetchAll(\PDO::FETCH_COLUMN);
                error_log("Users table columns: " . implode(', ', $columns));
                
                // Test if user exists
                $checkStmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id = ?");
                $checkStmt->execute([$userId]);
                $existingUser = $checkStmt->fetch(\PDO::FETCH_ASSOC);
                if (!$existingUser) {
                    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                    exit;
                }
                error_log("Existing user found: " . json_encode($existingUser));
                
            } catch (\Exception $e) {
                error_log("Error checking users table: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro na estrutura da tabela users: ' . $e->getMessage()]);
                exit;
            }
            
            // Validation
            if (empty($userId) || empty($name) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Email inválido']);
                exit;
            }
            
            // Check database connection
            if (!$this->db) {
                echo json_encode(['success' => false, 'message' => 'Erro de conexão com banco de dados']);
                exit;
            }
            
            // Check if email is already used by another user
            error_log("Checking email duplication...");
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            $emailCount = $stmt->fetchColumn();
            error_log("Email count for other users: $emailCount");
            
            if ($emailCount > 0) {
                echo json_encode(['success' => false, 'message' => 'Este email já está sendo usado por outro usuário']);
                exit;
            }
            
            // Update user with or without password
            error_log("Starting user update...");
            
            // Verificar quais colunas extras existem
            $hasColumnPopsIts = false;
            $hasColumnFluxogramas = false;
            $hasColumnAmostragens = false;
            $hasColumnNotificacoes = false;
            
            try {
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_pops_its'");
                $hasColumnPopsIts = $checkColumn->rowCount() > 0;
                
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_fluxogramas'");
                $hasColumnFluxogramas = $checkColumn->rowCount() > 0;
                
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'pode_aprovar_amostragens'");
                $hasColumnAmostragens = $checkColumn->rowCount() > 0;
                
                $checkColumn = $this->db->query("SHOW COLUMNS FROM users LIKE 'notificacoes_ativadas'");
                $hasColumnNotificacoes = $checkColumn->rowCount() > 0;
            } catch (\Exception $e) {
                error_log("Erro ao verificar colunas: " . $e->getMessage());
            }
            
            if (!empty($password)) {
                error_log("Updating user with new password");
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Construir query dinamicamente
                $updateFields = "name = ?, email = ?, password = ?, setor = ?, filial = ?, role = ?, profile_id = ?, status = ?";
                $params = [$name, $email, $hashedPassword, $setor, $filial, $role, $profileId, $status];
                
                if ($hasColumnPopsIts) {
                    $updateFields .= ", pode_aprovar_pops_its = ?";
                    $params[] = $podeAprovarPopsIts;
                }
                if ($hasColumnFluxogramas) {
                    $updateFields .= ", pode_aprovar_fluxogramas = ?";
                    $params[] = $podeAprovarFluxogramas;
                }
                if ($hasColumnAmostragens) {
                    $updateFields .= ", pode_aprovar_amostragens = ?";
                    $params[] = $podeAprovarAmostragens;
                }
                if ($hasColumnNotificacoes) {
                    $updateFields .= ", notificacoes_ativadas = ?";
                    $params[] = $notificacoesAtivadas;
                }
                
                $params[] = $userId;
                $stmt = $this->db->prepare("UPDATE users SET {$updateFields} WHERE id = ?");
                $result = $stmt->execute($params);
                
                error_log("Update result with password: " . ($result ? 'success' : 'failed'));
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("SQL Error: " . implode(' - ', $errorInfo));
                    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário no banco de dados: ' . $errorInfo[2]]);
                    exit;
                }
                
                echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso! (Nova senha definida)']);
            } else {
                error_log("Updating user without password change");
                
                // Construir query dinamicamente
                $updateFields = "name = ?, email = ?, setor = ?, filial = ?, role = ?, profile_id = ?, status = ?";
                $params = [$name, $email, $setor, $filial, $role, $profileId, $status];
                
                if ($hasColumnPopsIts) {
                    $updateFields .= ", pode_aprovar_pops_its = ?";
                    $params[] = $podeAprovarPopsIts;
                }
                if ($hasColumnFluxogramas) {
                    $updateFields .= ", pode_aprovar_fluxogramas = ?";
                    $params[] = $podeAprovarFluxogramas;
                }
                if ($hasColumnAmostragens) {
                    $updateFields .= ", pode_aprovar_amostragens = ?";
                    $params[] = $podeAprovarAmostragens;
                }
                if ($hasColumnNotificacoes) {
                    $updateFields .= ", notificacoes_ativadas = ?";
                    $params[] = $notificacoesAtivadas;
                }
                
                $params[] = $userId;
                $stmt = $this->db->prepare("UPDATE users SET {$updateFields} WHERE id = ?");
                $result = $stmt->execute($params);
                
                error_log("Update result without password: " . ($result ? 'success' : 'failed'));
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    error_log("SQL Error: " . implode(' - ', $errorInfo));
                    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário no banco de dados: ' . $errorInfo[2]]);
                    exit;
                }
                
                echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso!']);
            }
            
        } catch (\Exception $e) {
            error_log('Error updating user: ' . $e->getMessage() . ' - Line: ' . $e->getLine() . ' - File: ' . $e->getFile());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
        
        exit; // Ensure no additional output
    }
    
    /**
     * Delete user
     */
    public function deleteUser()
    {
        // Clean output buffer to prevent HTML mixing with JSON
        if (ob_get_level()) {
            ob_clean();
        }
        
        AuthController::requireAdmin();
        
        // Set JSON headers
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        try {
            $userId = $_POST['user_id'] ?? '';
            
            if (empty($userId)) {
                echo json_encode(['success' => false, 'message' => 'ID do usuário não informado']);
                exit;
            }
            
            // Prevent deleting self
            if ($userId == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'Você não pode excluir sua própria conta']);
                exit;
            }
            
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true, 'message' => 'Usuário excluído com sucesso!']);
            
        } catch (\Exception $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor. Tente novamente.']);
        }
        
        exit; // Ensure no additional output
    }
    
    /**
     * Approve invitation
     */
    public function approveInvitation()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $invitationId = $_POST['invitation_id'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';
        
        if (empty($invitationId) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
            return;
        }
        
        try {
            // Get invitation
            $stmt = $this->db->prepare("SELECT * FROM user_invitations WHERE id = ? AND status = 'pending'");
            $stmt->execute([$invitationId]);
            $invitation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$invitation) {
                echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
                return;
            }
            
            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, setor, filial, role, status, email_verified_at) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([$invitation['name'], $invitation['email'], $hashedPassword, $invitation['setor'], $invitation['filial'], $role]);
            
            $userId = $this->db->lastInsertId();
            
            // Set default permissions
            $this->setDefaultPermissions($userId, $role);
            
            // Update invitation status
            $stmt = $this->db->prepare("UPDATE user_invitations SET status = 'approved', approved_by = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $invitationId]);
            
            // Send welcome email
            $this->sendWelcomeEmail($invitation['name'], $invitation['email'], $password);
            
            echo json_encode(['success' => true, 'message' => 'Usuário aprovado e criado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao aprovar solicitação']);
        }
    }
    
    /**
     * Reject invitation
     */
    public function rejectInvitation()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $invitationId = $_POST['invitation_id'] ?? '';
        
        if (empty($invitationId)) {
            echo json_encode(['success' => false, 'message' => 'ID da solicitação não informado']);
            return;
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE user_invitations SET status = 'rejected', approved_by = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $invitationId]);
            
            echo json_encode(['success' => true, 'message' => 'Solicitação rejeitada']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao rejeitar solicitação']);
        }
    }
    
    /**
     * Manage user permissions
     */
    public function userPermissions($userId)
    {
        AuthController::requireAdmin();
        
        // Check if it's an AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            
            try {
                // Get user
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if (!$user) {
                    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
                    return;
                }
                
                // Get permissions (or create default structure if table doesn't exist)
                $permissions = [];
                try {
                    $stmt = $this->db->prepare("SELECT * FROM user_permissions WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $dbPermissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    
                    foreach ($dbPermissions as $perm) {
                        $permissions[$perm['module']] = $perm;
                    }
                } catch (\Exception $e) {
                    // If table doesn't exist, return default permissions structure
                    $modules = ['dashboard', 'toners', 'homologacoes', 'amostragens', 'auditorias', 'garantias'];
                    foreach ($modules as $module) {
                        $permissions[$module] = [
                            'module' => $module,
                            'can_view' => 1,
                            'can_edit' => 0,
                            'can_delete' => 0
                        ];
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'user' => $user,
                    'permissions' => $permissions
                ]);
                return;
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                return;
            }
        }
        
        // Regular page load (fallback)
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user) {
                redirect('/admin/users');
                return;
            }
            
            $title = 'Permissões do Usuário - SGQ OTI DJ';
            $viewFile = __DIR__ . '/../../views/admin/user-permissions.php';
            include __DIR__ . '/../../views/layouts/main.php';
        } catch (\Exception $e) {
            redirect('/admin/users');
        }
    }
    
    /**
     * Update user permissions
     */
    public function updatePermissions()
    {
        AuthController::requireAdmin();
        header('Content-Type: application/json');
        
        $userId = $_POST['user_id'] ?? '';
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($userId)) {
            echo json_encode(['success' => false, 'message' => 'ID do usuário não informado']);
            return;
        }
        
        try {
            // Delete existing permissions
            $stmt = $this->db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Insert new permissions
            foreach ($permissions as $module => $perms) {
                $stmt = $this->db->prepare("INSERT INTO user_permissions (user_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $userId,
                    $module,
                    isset($perms['view']) ? 1 : 0,
                    isset($perms['edit']) ? 1 : 0,
                    isset($perms['delete']) ? 1 : 0,
                    isset($perms['import']) ? 1 : 0,
                    isset($perms['export']) ? 1 : 0
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Permissões atualizadas com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar permissões']);
        }
    }
    
    private function getStats(): array
    {
        $stats = [];
        
        try {
            // Total users
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            $stats['active_users'] = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $stats['active_users'] = 0;
        }
        
        try {
            // Pending access requests
            $stmt = $this->db->query("SELECT COUNT(*) FROM access_requests WHERE status = 'pendente'");
            $stats['pending_invitations'] = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $stats['pending_invitations'] = 0;
        }
        
        try {
            // Total amostragens
            $stmt = $this->db->query("SELECT COUNT(*) FROM amostragens");
            $stats['total_amostragens'] = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $stats['total_amostragens'] = 0;
        }
        
        try {
            // Total retornados (soma das quantidades)
            $stmt = $this->db->query("SELECT COALESCE(SUM(quantidade), 0) FROM retornados");
            $stats['total_retornados'] = $stmt->fetchColumn();
        } catch (\Exception $e) {
            $stats['total_retornados'] = 0;
        }
        
        return $stats;
    }

    /**
     * Get totais acumulados filtrados (para atualização dinâmica dos cards)
     */
    private function getTotaisAcumuladosFiltrados($filial = '', $codigoCliente = '', $dataInicial = '', $dataFinal = ''): array
    {
        $dateColumn = $this->getDateColumn();
        $filialColumn = $this->getFilialColumn();
        $destinoColumn = $this->getDestinoColumn();
        $valorColumn = $this->getValorColumn();
        
        $totais = [
            'retornados_total' => 0,
            'valor_recuperado' => 0
        ];
        
        try {
            // Query base para retornados total
            $sql = "SELECT COALESCE(SUM(quantidade), 0) as total FROM retornados WHERE 1=1";
            $params = [];
            
            if (!empty($filial)) {
                $sql .= " AND {$filialColumn} = ?";
                $params[] = $filial;
            }
            
            if (!empty($codigoCliente)) {
                $sql .= " AND codigo_cliente LIKE ?";
                $params[] = '%' . $codigoCliente . '%';
            }
            
            if (!empty($dataInicial)) {
                $sql .= " AND {$dateColumn} >= ?";
                $params[] = $dataInicial;
            }
            
            if (!empty($dataFinal)) {
                $sql .= " AND {$dateColumn} <= ?";
                $params[] = $dataFinal;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totais['retornados_total'] = (int)($result['total'] ?? 0);
            
            // Query para valor recuperado (destino = estoque)
            $sql = "SELECT COALESCE(SUM({$valorColumn}), 0) as total FROM retornados WHERE {$destinoColumn} = 'estoque'";
            $params = [];
            
            if (!empty($filial)) {
                $sql .= " AND {$filialColumn} = ?";
                $params[] = $filial;
            }
            
            if (!empty($codigoCliente)) {
                $sql .= " AND codigo_cliente LIKE ?";
                $params[] = '%' . $codigoCliente . '%';
            }
            
            if (!empty($dataInicial)) {
                $sql .= " AND {$dateColumn} >= ?";
                $params[] = $dataInicial;
            }
            
            if (!empty($dataFinal)) {
                $sql .= " AND {$dateColumn} <= ?";
                $params[] = $dataFinal;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totais['valor_recuperado'] = (float)($result['total'] ?? 0);
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar totais filtrados: " . $e->getMessage());
        }
        
        return $totais;
    }

    /**
     * Get totais acumulados dos gráficos até a data atual
     * Usa a mesma lógica dos gráficos existentes
     */
    private function getTotaisAcumuladosGraficos(): array
    {
        $totais = [
            'retornados_total' => 0,
            'destinos_total' => 0,
            'valor_recuperado' => 0
        ];

        try {
            // Usar mesma detecção de colunas dos gráficos
            $valorColumn = $this->getValorColumn();
            $destinoColumn = $this->getDestinoColumn();
            
            // 1. Total de Retornados (soma de todas as quantidades) - Igual ao gráfico de barras
            $stmt = $this->db->query("SELECT COALESCE(SUM(quantidade), 0) as total FROM retornados");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totais['retornados_total'] = (int)($result['total'] ?? 0);

            // 2. Total de registros processados - Igual ao gráfico de pizza
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM retornados");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totais['destinos_total'] = (int)($result['total'] ?? 0);

            // 3. Valor total recuperado - Igual ao gráfico de linha (destino = 'estoque')
            $stmt = $this->db->query("SELECT COALESCE(SUM({$valorColumn}), 0) as total FROM retornados WHERE {$destinoColumn} = 'estoque'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totais['valor_recuperado'] = (float)($result['total'] ?? 0);

        } catch (\Exception $e) {
            error_log("Erro ao buscar totais acumulados dos gráficos: " . $e->getMessage());
        }

        return $totais;
    }

    /**
     * Get dashboard chart data
     */
    public function getDashboardData()
    {
        header('Content-Type: application/json');
        
        try {
            // Debug: verificar se a tabela existe
            $tableExists = $this->checkTableExists();
            if (!$tableExists) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Tabela retornados não encontrada',
                    'debug' => ['table_exists' => false]
                ]);
                exit;
            }
            
            $filial = $_GET['filial'] ?? '';
            $codigoCliente = $_GET['codigo_cliente'] ?? '';
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            // Debug: verificar estrutura da tabela
            $tableStructure = $this->getTableStructure();
            
            $data = [
                'retornados_mes' => $this->getRetornadosPorMes($filial, $codigoCliente, $dataInicial, $dataFinal),
                'retornados_destino' => $this->getRetornadosPorDestino($filial, $codigoCliente, $dataInicial, $dataFinal),
                'toners_recuperados' => $this->getTonersRecuperados($filial, $codigoCliente, $dataInicial, $dataFinal),
                'totais_acumulados' => $this->getTotaisAcumuladosFiltrados($filial, $codigoCliente, $dataInicial, $dataFinal),
                'filiais' => $this->getFiliaisFromRetornados(),
                'debug' => [
                    'table_exists' => true,
                    'columns' => $tableStructure,
                    'date_column' => $this->getDateColumn(),
                    'filial_column' => $this->getFilialColumn(),
                    'destino_column' => $this->getDestinoColumn(),
                    'valor_column' => $this->getValorColumn()
                ]
            ];
            
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage(),
                'debug' => [
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ]
            ]);
        }
        exit;
    }

    /**
     * Get ranking de códigos de cliente
     */
    public function getRankingClientes()
    {
        header('Content-Type: application/json');
        
        try {
            $filial = $_GET['filial'] ?? '';
            $destino = $_GET['destino'] ?? '';
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            $dateColumn = $this->getDateColumn();
            $filialColumn = $this->getFilialColumn();
            $destinoColumn = $this->getDestinoColumn();
            
            $sql = "
                SELECT 
                    TRIM(LEADING '0' FROM r.codigo_cliente) as codigo_cliente,
                    MAX(c.nome) as nome_cliente,
                    SUM(r.quantidade) as total_retornados
                FROM retornados r
                LEFT JOIN clientes c ON TRIM(LEADING '0' FROM r.codigo_cliente) COLLATE utf8mb4_unicode_ci = TRIM(LEADING '0' FROM c.codigo) COLLATE utf8mb4_unicode_ci
                WHERE r.codigo_cliente IS NOT NULL 
                AND r.codigo_cliente != ''
                AND r.codigo_cliente REGEXP '[0-9]'
                AND TRIM(LEADING '0' FROM r.codigo_cliente) NOT IN ('', '1', '2852')
            ";
            
            $params = [];
            
            if (!empty($filial)) {
                $sql .= " AND r.{$filialColumn} = ?";
                $params[] = $filial;
            }
            
            if (!empty($destino)) {
                $sql .= " AND r.{$destinoColumn} = ?";
                $params[] = $destino;
            }
            
            if (!empty($dataInicial)) {
                $sql .= " AND r.{$dateColumn} >= ?";
                $params[] = $dataInicial;
            }
            
            if (!empty($dataFinal)) {
                $sql .= " AND r.{$dateColumn} <= ?";
                $params[] = $dataFinal;
            }
            
            $sql .= " GROUP BY TRIM(LEADING '0' FROM r.codigo_cliente) ORDER BY total_retornados DESC LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $labels = [];
            $data = [];
            $codigos = [];
            
            foreach ($results as $row) {
                // Prioriza o nome do cliente, se não tiver usa o código
                $label = $row['nome_cliente'] ?? null;
                if (empty($label)) {
                    $label = $row['codigo_cliente'] ?: 'Sem Código';
                }
                $labels[] = $label;
                $data[] = (int)$row['total_retornados'];
                $codigos[] = $row['codigo_cliente'];
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'data' => $data,
                    'codigos' => $codigos
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar ranking: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    
    /**
     * Get retornados por clientes (sem limite)
     */
    public function getRetornadosPorClientes()
    {
        header('Content-Type: application/json');
        
        try {
            $filial = $_GET['filial'] ?? '';
            $destino = $_GET['destino'] ?? '';
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            $dateColumn = $this->getDateColumn();
            $filialColumn = $this->getFilialColumn();
            $destinoColumn = $this->getDestinoColumn();
            
            $sql = "
                SELECT 
                    TRIM(LEADING '0' FROM r.codigo_cliente) as codigo_cliente,
                    MAX(c.nome) as nome_cliente,
                    SUM(r.quantidade) as total_retornados
                FROM retornados r
                LEFT JOIN clientes c ON TRIM(LEADING '0' FROM r.codigo_cliente) COLLATE utf8mb4_unicode_ci = TRIM(LEADING '0' FROM c.codigo) COLLATE utf8mb4_unicode_ci
                WHERE r.codigo_cliente IS NOT NULL 
                AND r.codigo_cliente != ''
                AND r.codigo_cliente REGEXP '[0-9]'
                AND TRIM(LEADING '0' FROM r.codigo_cliente) NOT IN ('', '1', '2852')
            ";
            
            $params = [];
            
            if (!empty($filial)) {
                $sql .= " AND r.{$filialColumn} = ?";
                $params[] = $filial;
            }
            
            if (!empty($destino)) {
                $sql .= " AND r.{$destinoColumn} = ?";
                $params[] = $destino;
            }
            
            if (!empty($dataInicial)) {
                $sql .= " AND r.{$dateColumn} >= ?";
                $params[] = $dataInicial;
            }
            
            if (!empty($dataFinal)) {
                $sql .= " AND r.{$dateColumn} <= ?";
                $params[] = $dataFinal;
            }
            
            $sql .= " GROUP BY TRIM(LEADING '0' FROM r.codigo_cliente) ORDER BY total_retornados DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $labels = [];
            $data = [];
            $codigos = [];
            
            foreach ($results as $row) {
                $label = $row['nome_cliente'] ?? null;
                if (empty($label)) {
                    $label = $row['codigo_cliente'] ?: 'Sem Código';
                }
                $labels[] = $label;
                $data[] = (int)$row['total_retornados'];
                $codigos[] = $row['codigo_cliente'];
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'data' => $data,
                    'codigos' => $codigos
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar retornados por clientes: ' . $e->getMessage()
            ]);
        }
        exit;
    }

/**
     * Get toners detalhados por cliente (para popup do ranking)
     */
    
    /**
     * Exportar Retornados por Clientes para Excel (.xlsx)
     * Usa os mesmos filtros locais do gráfico
     */
    public function exportRetornadosPorClientes()
    {
        try {
            $filial = $_GET['filial'] ?? '';
            $destino = $_GET['destino'] ?? '';
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            $dateColumn = $this->getDateColumn();
            $filialColumn = $this->getFilialColumn();
            $destinoColumn = $this->getDestinoColumn();
            
            // Query detalhada com modelo de toner
            $sql = "
                SELECT 
                    TRIM(LEADING '0' FROM r.codigo_cliente) as codigo_cliente,
                    MAX(c.nome) as nome_cliente,
                    r.modelo,
                    r.{$destinoColumn} as destino,
                    SUM(r.quantidade) as total_retornados
                FROM retornados r
                LEFT JOIN clientes c ON TRIM(LEADING '0' FROM r.codigo_cliente) COLLATE utf8mb4_unicode_ci = TRIM(LEADING '0' FROM c.codigo) COLLATE utf8mb4_unicode_ci
                WHERE r.codigo_cliente IS NOT NULL 
                AND r.codigo_cliente != ''
                AND r.codigo_cliente REGEXP '[0-9]'
                AND TRIM(LEADING '0' FROM r.codigo_cliente) NOT IN ('', '1', '2852')
            ";
            
            $params = [];
            
            if (!empty($filial)) {
                $sql .= " AND r.{$filialColumn} = ?";
                $params[] = $filial;
            }
            
            if (!empty($destino)) {
                $sql .= " AND r.{$destinoColumn} = ?";
                $params[] = $destino;
            }
            
            if (!empty($dataInicial)) {
                $sql .= " AND r.{$dateColumn} >= ?";
                $params[] = $dataInicial;
            }
            
            if (!empty($dataFinal)) {
                $sql .= " AND r.{$dateColumn} <= ?";
                $params[] = $dataFinal;
            }
            
            $sql .= " GROUP BY TRIM(LEADING '0' FROM r.codigo_cliente), r.modelo, r.{$destinoColumn} ORDER BY nome_cliente ASC, total_retornados DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Criar planilha com PhpSpreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Retornados por Clientes');
            
            // Cabeçalho com estilo
            $headers = ['Código Cliente', 'Nome Cliente', 'Modelo Toner', 'Destino', 'Quantidade'];
            foreach ($headers as $col => $header) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
                $sheet->setCellValue($cell, $header);
            }
            
            // Estilo do cabeçalho
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ];
            $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
            
            // Dados
            $row = 2;
            foreach ($results as $item) {
                $sheet->setCellValue("A{$row}", $item['codigo_cliente']);
                $sheet->setCellValue("B{$row}", $item['nome_cliente'] ?? 'Sem Nome');
                $sheet->setCellValue("C{$row}", $item['modelo'] ?? '');
                $sheet->setCellValue("D{$row}", ucfirst(strtolower($item['destino'] ?? 'N/A')));
                $sheet->setCellValue("E{$row}", (int)$item['total_retornados']);
                $row++;
            }
            
            // Bordas nos dados
            if ($row > 2) {
                $dataRange = "A2:E" . ($row - 1);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]]
                ]);
            }
            
            // Auto-dimensionar colunas
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Filtros aplicados (info na parte inferior)
            $infoRow = $row + 2;
            $sheet->setCellValue("A{$infoRow}", 'Filtros aplicados:');
            $sheet->getStyle("A{$infoRow}")->getFont()->setBold(true);
            $infoRow++;
            if (!empty($filial)) { $sheet->setCellValue("A{$infoRow}", "Filial: {$filial}"); $infoRow++; }
            if (!empty($destino)) { $sheet->setCellValue("A{$infoRow}", "Destino: {$destino}"); $infoRow++; }
            if (!empty($dataInicial)) { $sheet->setCellValue("A{$infoRow}", "Data Inicial: {$dataInicial}"); $infoRow++; }
            if (!empty($dataFinal)) { $sheet->setCellValue("A{$infoRow}", "Data Final: {$dataFinal}"); $infoRow++; }
            $sheet->setCellValue("A{$infoRow}", "Gerado em: " . date('d/m/Y H:i:s'));
            
            // Gerar nome do arquivo
            $nomeArquivo = 'retornados_clientes_' . date('Y-m-d_His') . '.xlsx';
            
            // Headers para download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$nomeArquivo}\"");
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
        exit;
    }

    public function getTonersPorCliente()
    {
        header('Content-Type: application/json');
        
        try {
            $codigoCliente = $_GET['codigo'] ?? '';
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            if (empty($codigoCliente)) {
                echo json_encode(['success' => false, 'message' => 'Código do cliente não informado']);
                exit;
            }
            
            $dateColumn = $this->getDateColumn();
            
            // Buscar nome do cliente
            $stmtNome = $this->db->prepare("SELECT nome FROM clientes WHERE codigo = ?");
            $stmtNome->execute([$codigoCliente]);
            $clienteRow = $stmtNome->fetch(\PDO::FETCH_ASSOC);
            $nomeCliente = $clienteRow['nome'] ?? $codigoCliente;
            
            $destinoColumn = $this->getDestinoColumn();
            
            // Buscar toners agrupados por modelo e destino
            $sql = "
                SELECT 
                    modelo,
                    {$destinoColumn} as destino,
                    SUM(quantidade) as total
                FROM retornados 
                WHERE codigo_cliente COLLATE utf8mb4_unicode_ci = ?
            ";
            
            $params = [$codigoCliente];
            
            if (!empty($dataInicial)) {
                $sql .= " AND {$dateColumn} >= ?";
                $params[] = $dataInicial;
            }
            
            if (!empty($dataFinal)) {
                $sql .= " AND {$dateColumn} <= ?";
                $params[] = $dataFinal;
            }
            
            $sql .= " GROUP BY modelo, {$destinoColumn} ORDER BY total DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $toners = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Calcular total geral
            $totalGeral = array_sum(array_column($toners, 'total'));
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'codigo' => $codigoCliente,
                    'nome' => $nomeCliente,
                    'toners' => $toners,
                    'total' => $totalGeral
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar toners: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Check if retornados table exists
     */
    private function checkTableExists()
    {
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'retornados'");
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get table structure for debugging
     */
    private function getTableStructure()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get retornados por mês
     */
    private function getRetornadosPorMes($filial = '', $codigoCliente = '', $dataInicial = '', $dataFinal = '')
    {
        // Verificar estrutura da tabela e ajustar query
        $dateColumn = $this->getDateColumn();
        $filialColumn = $this->getFilialColumn();
        
        $sql = "
            SELECT 
                MONTH({$dateColumn}) as mes,
                YEAR({$dateColumn}) as ano,
                SUM(quantidade) as quantidade
            FROM retornados 
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filial)) {
            $sql .= " AND {$filialColumn} = ?";
            $params[] = $filial;
        }
        
        if (!empty($codigoCliente)) {
            $sql .= " AND codigo_cliente LIKE ?";
            $params[] = '%' . $codigoCliente . '%';
        }
        
        if (!empty($dataInicial)) {
            $sql .= " AND {$dateColumn} >= ?";
            $params[] = $dataInicial;
        }
        
        if (!empty($dataFinal)) {
            $sql .= " AND {$dateColumn} <= ?";
            $params[] = $dataFinal;
        }
        
        $sql .= " GROUP BY YEAR({$dateColumn}), MONTH({$dateColumn}) ORDER BY ano, mes";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Preparar dados para o gráfico
            $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $dados = array_fill(0, 12, 0);
            
            foreach ($results as $row) {
                $mesIndex = $row['mes'] - 1;
                if ($mesIndex >= 0 && $mesIndex < 12) {
                    $dados[$mesIndex] = (int)$row['quantidade'];
                }
            }
            
            return [
                'labels' => $meses,
                'data' => $dados
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
            ];
        }
    }

    /**
     * Get retornados por destino
     */
    private function getRetornadosPorDestino($filial = '', $codigoCliente = '', $dataInicial = '', $dataFinal = '')
    {
        $dateColumn = $this->getDateColumn();
        $filialColumn = $this->getFilialColumn();
        $destinoColumn = $this->getDestinoColumn();
        
        $sql = "
            SELECT 
                COALESCE({$destinoColumn}, 'Não Informado') as destino,
                SUM(quantidade) as quantidade
            FROM retornados 
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filial)) {
            $sql .= " AND {$filialColumn} = ?";
            $params[] = $filial;
        }
        
        if (!empty($codigoCliente)) {
            $sql .= " AND codigo_cliente LIKE ?";
            $params[] = '%' . $codigoCliente . '%';
        }
        
        if (!empty($dataInicial)) {
            $sql .= " AND {$dateColumn} >= ?";
            $params[] = $dataInicial;
        }
        
        if (!empty($dataFinal)) {
            $sql .= " AND {$dateColumn} <= ?";
            $params[] = $dataFinal;
        }
        
        $sql .= " GROUP BY destino ORDER BY quantidade DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $labels = [];
            $data = [];
            
            foreach ($results as $row) {
                $labels[] = $row['destino'];
                $data[] = (int)$row['quantidade'];
            }
            
            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Sem Dados'],
                'data' => [0]
            ];
        }
    }

    /**
     * Get valor recuperado em toners
     */
    private function getTonersRecuperados($filial = '', $codigoCliente = '', $dataInicial = '', $dataFinal = '')
    {
        $dateColumn = $this->getDateColumn();
        $filialColumn = $this->getFilialColumn();
        $valorColumn = $this->getValorColumn();
        $destinoColumn = $this->getDestinoColumn();
        
        $sql = "
            SELECT 
                MONTH({$dateColumn}) as mes,
                YEAR({$dateColumn}) as ano,
                SUM(COALESCE({$valorColumn}, 0)) as valor_total,
                SUM(CASE WHEN {$destinoColumn} = 'estoque' THEN quantidade ELSE 0 END) as quantidade_estoque
            FROM retornados 
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($filial)) {
            $sql .= " AND {$filialColumn} = ?";
            $params[] = $filial;
        }
        
        if (!empty($codigoCliente)) {
            $sql .= " AND codigo_cliente LIKE ?";
            $params[] = '%' . $codigoCliente . '%';
        }
        
        if (!empty($dataInicial)) {
            $sql .= " AND {$dateColumn} >= ?";
            $params[] = $dataInicial;
        }
        
        if (!empty($dataFinal)) {
            $sql .= " AND {$dateColumn} <= ?";
            $params[] = $dataFinal;
        }
        
        $sql .= " GROUP BY YEAR({$dateColumn}), MONTH({$dateColumn}) ORDER BY ano, mes";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Preparar dados para o gráfico
            $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
            $valores = array_fill(0, 12, 0);
            $quantidades = array_fill(0, 12, 0);
            $percentuais = array_fill(0, 12, 0);
            $cores = array_fill(0, 12, 'gray');
            
            foreach ($results as $row) {
                $mesIndex = $row['mes'] - 1;
                if ($mesIndex >= 0 && $mesIndex < 12) {
                    $valores[$mesIndex] = (float)$row['valor_total'];
                    $quantidades[$mesIndex] = (int)$row['quantidade_estoque'];
                }
            }
            
            // Calcular percentuais e cores
            for ($i = 0; $i < 12; $i++) {
                if ($i > 0 && $valores[$i - 1] > 0) {
                    $percentuais[$i] = (($valores[$i] - $valores[$i - 1]) / $valores[$i - 1]) * 100;
                    $cores[$i] = $percentuais[$i] >= 0 ? 'green' : 'red';
                } else if ($i > 0 && $valores[$i] > 0) {
                    $percentuais[$i] = 100;
                    $cores[$i] = 'green';
                }
            }
            
            return [
                'labels' => $meses,
                'data' => $valores,
                'quantidades' => $quantidades,
                'percentuais' => $percentuais,
                'cores' => $cores
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'quantidades' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'percentuais' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'cores' => ['gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray', 'gray']
            ];
        }
    }

    /**
     * Get filiais from retornados table
     */
    private function getFiliaisFromRetornados()
    {
        try {
            $filialColumn = $this->getFilialColumn();
            $stmt = $this->db->query("SELECT DISTINCT {$filialColumn} FROM retornados WHERE {$filialColumn} IS NOT NULL AND {$filialColumn} != '' ORDER BY {$filialColumn}");
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get the correct date column name from retornados table
     */
    private function getDateColumn()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Possíveis nomes de colunas de data (prioridade: data_registro primeiro)
            $possibleDateColumns = ['data_registro', 'data_retorno', 'data', 'created_at', 'date_created', 'data_criacao'];
            
            foreach ($possibleDateColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            // Se não encontrar, usar a primeira coluna que contenha 'data'
            foreach ($columns as $col) {
                if (stripos($col, 'data') !== false) {
                    return $col;
                }
            }
            
            return 'data_registro'; // fallback para data_registro
        } catch (\Exception $e) {
            return 'data_registro'; // fallback para data_registro
        }
    }

    /**
     * Get the correct filial column name from retornados table
     */
    private function getFilialColumn()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Possíveis nomes de colunas de filial
            $possibleFilialColumns = ['filial', 'branch', 'subsidiary', 'unidade'];
            
            foreach ($possibleFilialColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            return 'filial'; // fallback
        } catch (\Exception $e) {
            return 'filial'; // fallback
        }
    }

    /**
     * Get the correct destino column name from retornados table
     */
    private function getDestinoColumn()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Possíveis nomes de colunas de destino
            $possibleDestinoColumns = ['destino', 'destination', 'status', 'tipo_destino'];
            
            foreach ($possibleDestinoColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            return 'destino'; // fallback
        } catch (\Exception $e) {
            return 'destino'; // fallback
        }
    }

    /**
     * Get the correct valor column name from retornados table
     */
    private function getValorColumn()
    {
        try {
            $stmt = $this->db->query("DESCRIBE retornados");
            $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            // Possíveis nomes de colunas de valor (prioridade: valor_calculado primeiro)
            $possibleValorColumns = ['valor_calculado', 'valor_recuperado', 'valor', 'value', 'amount', 'preco'];
            
            foreach ($possibleValorColumns as $col) {
                if (in_array($col, $columns)) {
                    return $col;
                }
            }
            
            return 'valor_calculado'; // fallback para valor_calculado
        } catch (\Exception $e) {
            return 'valor_calculado'; // fallback para valor_calculado
        }
    }
    
    private function setDefaultPermissions(int $userId, string $role): void
    {
        $modules = ["toners", "amostragens", "retornados", "registros", "configuracoes"];
        
        foreach ($modules as $module) {
            if ($role === 'admin') {
                // Admin gets all permissions
                $stmt = $this->db->prepare("INSERT INTO user_permissions (user_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 1, 1, 1, 1)");
                $stmt->execute([$userId, $module]);
            } else {
                // Regular user gets view permission only
                $stmt = $this->db->prepare("INSERT INTO user_permissions (user_id, module, can_view, can_edit, can_delete, can_import, can_export) VALUES (?, ?, 1, 0, 0, 0, 0)");
                $stmt->execute([$userId, $module]);
            }
        }
    }

    /**
     * Diagnóstico de permissões de usuário
     */
    public function diagnosticoPermissoes()
    {
        // Verificar se é admin
        if (!\App\Services\PermissionService::isAdmin($_SESSION['user_id'])) {
            http_response_code(403);
            echo "<h1>Acesso Negado</h1><p>Apenas administradores podem acessar o diagnóstico.</p>";
            return;
        }

        try {
            echo "<!DOCTYPE html>
            <html lang='pt-br'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Diagnóstico de Permissões - SGQ OTI DJ</title>
                <script src='https://cdn.tailwindcss.com'></script>
            </head>
            <body class='bg-gray-100 p-8'>
                <div class='max-w-6xl mx-auto'>
                    <h1 class='text-3xl font-bold mb-6 text-gray-900'>🔍 Diagnóstico: Permissões de Usuário</h1>";

            // 1. Listar todos os usuários
            echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                    <h2 class='text-xl font-semibold mb-4'>1. Usuários do Sistema</h2>";

            $stmt = $this->db->prepare("
                SELECT u.id, u.name, u.email, u.profile_id, p.name as profile_name, p.is_admin
                FROM users u 
                LEFT JOIN profiles p ON u.profile_id = p.id 
                ORDER BY u.name
            ");
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo "<div class='overflow-x-auto'>
                    <table class='min-w-full bg-white border border-gray-200'>
                        <thead class='bg-gray-50'>
                            <tr>
                                <th class='px-4 py-2 text-left'>ID</th>
                                <th class='px-4 py-2 text-left'>Nome</th>
                                <th class='px-4 py-2 text-left'>Email</th>
                                <th class='px-4 py-2 text-left'>Perfil</th>
                                <th class='px-4 py-2 text-center'>Dashboard</th>
                                <th class='px-4 py-2 text-center'>Ações</th>
                            </tr>
                        </thead>
                        <tbody>";

            foreach ($users as $user) {
                $hasDashboard = \App\Services\PermissionService::hasPermission($user['id'], 'dashboard', 'view');
                echo "<tr class='border-t'>
                        <td class='px-4 py-2'>" . $user['id'] . "</td>
                        <td class='px-4 py-2'>" . htmlspecialchars($user['name']) . "</td>
                        <td class='px-4 py-2'>" . htmlspecialchars($user['email']) . "</td>
                        <td class='px-4 py-2'>" . htmlspecialchars($user['profile_name'] ?? 'Sem perfil') . "</td>
                        <td class='px-4 py-2 text-center'>" . ($hasDashboard ? '<span class="text-green-600">✅</span>' : '<span class="text-red-600">❌</span>') . "</td>
                        <td class='px-4 py-2 text-center'>
                            <a href='?user_id=" . $user['id'] . "' class='bg-blue-600 text-white px-2 py-1 rounded text-sm hover:bg-blue-700'>Analisar</a>
                        </td>
                      </tr>";
            }
            echo "</tbody></table></div>";
            echo "</div>";

            // 2. Análise específica de usuário
            if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
                $userId = (int)$_GET['user_id'];
                
                $stmt = $this->db->prepare("
                    SELECT u.id, u.name, u.email, u.profile_id, p.name as profile_name, p.is_admin
                    FROM users u 
                    LEFT JOIN profiles p ON u.profile_id = p.id 
                    WHERE u.id = ?
                ");
                $stmt->execute([$userId]);
                $selectedUser = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($selectedUser) {
                    echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                            <h2 class='text-xl font-semibold mb-4'>2. Análise Detalhada: " . htmlspecialchars($selectedUser['name']) . "</h2>";

                    // Informações do usuário
                    echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-6 mb-6'>
                            <div>
                                <h3 class='font-semibold mb-2'>Informações do Usuário:</h3>
                                <ul class='space-y-1'>
                                    <li><strong>ID:</strong> " . $selectedUser['id'] . "</li>
                                    <li><strong>Nome:</strong> " . htmlspecialchars($selectedUser['name']) . "</li>
                                    <li><strong>Email:</strong> " . htmlspecialchars($selectedUser['email']) . "</li>
                                    <li><strong>Perfil:</strong> " . htmlspecialchars($selectedUser['profile_name'] ?? 'Sem perfil') . "</li>
                                    <li><strong>É Admin:</strong> " . ($selectedUser['is_admin'] ? '<span class="text-green-600">✅ SIM</span>' : '<span class="text-red-600">❌ NÃO</span>') . "</li>
                                </ul>
                            </div>";

                    // Verificar permissões específicas
                    $modules = ['dashboard', 'toners_cadastro', 'homologacoes', 'pops_its_visualizacao', 'admin_usuarios'];
                    echo "<div>
                            <h3 class='font-semibold mb-2'>Permissões Principais:</h3>
                            <ul class='space-y-1'>";
                    foreach ($modules as $module) {
                        $hasPermission = \App\Services\PermissionService::hasPermission($userId, $module, 'view');
                        echo "<li><strong>" . $module . ":</strong> " . ($hasPermission ? '<span class="text-green-600">✅ TEM</span>' : '<span class="text-red-600">❌ NÃO TEM</span>') . "</li>";
                    }
                    echo "</ul></div></div>";

                    // Permissões do perfil no banco
                    if ($selectedUser['profile_id']) {
                        echo "<h3 class='font-semibold mb-2'>Permissões do Perfil no Banco de Dados:</h3>";
                        $stmt = $this->db->prepare("
                            SELECT module, can_view, can_edit, can_delete, can_import, can_export
                            FROM profile_permissions 
                            WHERE profile_id = ? 
                            ORDER BY module
                        ");
                        $stmt->execute([$selectedUser['profile_id']]);
                        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                        if ($permissions) {
                            echo "<div class='overflow-x-auto'>
                                    <table class='min-w-full bg-white border border-gray-200'>
                                        <thead class='bg-gray-50'>
                                            <tr>
                                                <th class='px-3 py-2 text-left'>Módulo</th>
                                                <th class='px-3 py-2 text-center'>View</th>
                                                <th class='px-3 py-2 text-center'>Edit</th>
                                                <th class='px-3 py-2 text-center'>Delete</th>
                                                <th class='px-3 py-2 text-center'>Import</th>
                                                <th class='px-3 py-2 text-center'>Export</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                            foreach ($permissions as $perm) {
                                echo "<tr class='border-t'>
                                        <td class='px-3 py-2 font-mono text-sm'>" . $perm['module'] . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_view'] ? '✅' : '❌') . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_edit'] ? '✅' : '❌') . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_delete'] ? '✅' : '❌') . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_import'] ? '✅' : '❌') . "</td>
                                        <td class='px-3 py-2 text-center'>" . ($perm['can_export'] ? '✅' : '❌') . "</td>
                                      </tr>";
                            }
                            echo "</tbody></table></div>";
                        } else {
                            echo "<p class='text-red-600'>❌ Este perfil não tem permissões configuradas!</p>";
                        }
                    }

                    // Botão para corrigir dashboard
                    $hasDashboard = \App\Services\PermissionService::hasPermission($userId, 'dashboard', 'view');
                    if (!$hasDashboard && $selectedUser['profile_id']) {
                        echo "<div class='mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded'>
                                <h3 class='font-semibold text-yellow-800 mb-2'>🔧 Correção Disponível</h3>
                                <p class='text-yellow-700 mb-3'>Este usuário não tem permissão de dashboard. Clique abaixo para adicionar:</p>
                                <a href='?user_id=" . $userId . "&fix_dashboard=1' class='bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'>✅ Adicionar Permissão Dashboard</a>
                              </div>";
                    }

                    echo "</div>";
                }
            }

            // 3. Correção automática
            if (isset($_GET['fix_dashboard']) && isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
                $userId = (int)$_GET['user_id'];
                
                $stmt = $this->db->prepare("SELECT profile_id FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                if ($user && $user['profile_id']) {
                    echo "<div class='bg-white rounded-lg shadow p-6 mb-6'>
                            <h2 class='text-xl font-semibold mb-4'>3. 🔧 Correção Executada</h2>";
                    
                    // Verificar se já existe
                    $checkStmt = $this->db->prepare("SELECT id FROM profile_permissions WHERE profile_id = ? AND module = 'dashboard'");
                    $checkStmt->execute([$user['profile_id']]);
                    
                    if ($checkStmt->fetch()) {
                        // Atualizar
                        $updateStmt = $this->db->prepare("UPDATE profile_permissions SET can_view = 1 WHERE profile_id = ? AND module = 'dashboard'");
                        $updateStmt->execute([$user['profile_id']]);
                        echo "<p class='text-green-600'>✅ Permissão de dashboard ATUALIZADA para visualização!</p>";
                    } else {
                        // Inserir
                        $insertStmt = $this->db->prepare("
                            INSERT INTO profile_permissions 
                            (profile_id, module, can_view, can_edit, can_delete, can_import, can_export) 
                            VALUES (?, 'dashboard', 1, 0, 0, 0, 0)
                        ");
                        $insertStmt->execute([$user['profile_id']]);
                        echo "<p class='text-green-600'>✅ Permissão de dashboard ADICIONADA para visualização!</p>";
                    }
                    
                    echo "<p class='mt-2'><a href='?user_id=" . $userId . "' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'>🔄 Verificar Novamente</a></p>";
                    echo "</div>";
                }
            }

            echo "</div></body></html>";

        } catch (\Exception $e) {
            echo "<div class='bg-red-50 border border-red-200 rounded p-4'>
                    <h3 class='text-red-800 font-semibold'>❌ Erro no Diagnóstico:</h3>
                    <p class='text-red-700'>" . htmlspecialchars($e->getMessage()) . "</p>
                  </div>";
        }
    }
    
    /**
     * Get amostragens dashboard data
     */
    public function getAmostragemsDashboardData()
    {
        header('Content-Type: application/json');
        
        try {
            // Parâmetros de filtro
            $filial = $_GET['filial'] ?? '';
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            // WHERE clause baseado nos filtros
            $where = [];
            $params = [];
            
            if (!empty($filial)) {
                $where[] = "u.filial = :filial";
                $params[':filial'] = $filial;
            }
            
            if (!empty($dataInicial)) {
                $where[] = "DATE(a.created_at) >= :data_inicial";
                $params[':data_inicial'] = $dataInicial;
            }
            
            if (!empty($dataFinal)) {
                $where[] = "DATE(a.created_at) <= :data_final";
                $params[':data_final'] = $dataFinal;
            }
            
            // CONTROLE DE VISUALIZAÇÃO: Usuários não-admin só veem amostragens onde são responsáveis
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['user_role'] ?? 'user';
            
            if ($userRole !== 'admin' && $userRole !== 'super_admin') {
                // Usuário comum: só vê amostragens onde está na lista de responsáveis
                $where[] = "(FIND_IN_SET(:user_id_responsavel, a.responsaveis) > 0 OR a.user_id = :user_id_criador)";
                $params[':user_id_responsavel'] = $userId;
                $params[':user_id_criador'] = $userId;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // 1. Cards - Somar QUANTIDADES (igual ao grid)
            $stmtQuantidades = $this->db->prepare("
                SELECT 
                    COALESCE(SUM(quantidade_recebida), 0) as total_recebida,
                    COALESCE(SUM(quantidade_testada), 0) as total_testada,
                    COALESCE(SUM(quantidade_aprovada), 0) as total_aprovada,
                    COALESCE(SUM(quantidade_reprovada), 0) as total_reprovada
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                $whereClause
            ");
            $stmtQuantidades->execute($params);
            $quantidades = $stmtQuantidades->fetch(\PDO::FETCH_ASSOC);
            
            $qtdRecebida = (int)$quantidades['total_recebida'];
            $qtdTestada = (int)$quantidades['total_testada'];
            $qtdAprovada = (int)$quantidades['total_aprovada'];
            $qtdReprovada = (int)$quantidades['total_reprovada'];
            
            // 2. Gráfico de Barras: Qtd Recebida x Qtd Testada por Mês (últimos 12 meses)
            $stmtQuantidades = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(a.created_at, '%Y-%m') as mes,
                    SUM(COALESCE(quantidade_recebida, 0)) as recebidas,
                    SUM(COALESCE(quantidade_testada, 0)) as testadas,
                    SUM(COALESCE(quantidade_reprovada, 0)) as reprovadas
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                $whereClause
                GROUP BY mes
                ORDER BY mes DESC
                LIMIT 12
            ");
            $stmtQuantidades->execute($params);
            $quantidadesData = $stmtQuantidades->fetchAll(\PDO::FETCH_ASSOC);
            $quantidadesData = array_reverse($quantidadesData); // Ordem cronológica
            
            // Arrays para os gráficos
            $quantidadesLabels = [];
            $quantidadesDatesYM = array_column($quantidadesData, 'mes'); // YYYY-MM para uso no click
            $quantidadesRecebidas = array_column($quantidadesData, 'recebidas');
            $quantidadesTestadas = array_column($quantidadesData, 'testadas');
            $quantidadesReprovadas = array_column($quantidadesData, 'reprovadas');
            
            // Converter YYYY-MM para labels legíveis (Jan/2025, Fev/2025...)
            foreach ($quantidadesDatesYM as $mesYM) {
                $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                $partes = explode('-', $mesYM);
                $mesNum = (int)$partes[1] - 1;
                $ano = substr($partes[0], 2);
                $quantidadesLabels[] = $meses[$mesNum] . '/' . $ano;
            }
            
            // 3. Gráfico de Pizza: Taxa de Aprovação/Reprovação por Fornecedor
            $stmtFornecedores = $this->db->prepare("
                SELECT 
                    forn.nome as fornecedor,
                    COUNT(*) as total,
                    SUM(CASE WHEN a.status_final IN ('Aprovado', 'Aprovado Parcialmente') THEN 1 ELSE 0 END) as aprovadas,
                    SUM(CASE WHEN a.status_final = 'Reprovado' THEN 1 ELSE 0 END) as reprovadas
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN fornecedores forn ON a.fornecedor_id = forn.id
                $whereClause
                GROUP BY forn.nome, forn.id
                HAVING COUNT(*) > 0
                ORDER BY total DESC
            ");
            $stmtFornecedores->execute($params);
            $fornecedoresData = $stmtFornecedores->fetchAll(\PDO::FETCH_ASSOC);
            
            // Calcular taxas de aprovação e reprovação por fornecedor
            $fornecedoresLabels = [];
            $fornecedoresTaxaAprovacao = [];
            $fornecedoresTaxaReprovacao = [];
            
            foreach ($fornecedoresData as $forn) {
                $total = (int)$forn['total'];
                if ($total > 0) {
                    $fornecedoresLabels[] = $forn['fornecedor'];
                    $fornecedoresTaxaAprovacao[] = round(((int)$forn['aprovadas'] / $total) * 100, 1);
                    $fornecedoresTaxaReprovacao[] = round(((int)$forn['reprovadas'] / $total) * 100, 1);
                }
            }
            
            // Buscar todas as filiais para o dropdown
            $stmtAllFiliais = $this->db->prepare("
                SELECT DISTINCT u.filial
                FROM amostragens_2 a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE u.filial IS NOT NULL AND u.filial != ''
                ORDER BY u.filial
            ");
            $stmtAllFiliais->execute();
            $allFiliais = $stmtAllFiliais->fetchAll(\PDO::FETCH_COLUMN);
            
            // Montar resposta
            $response = [
                'success' => true,
                'data' => [
                    'cards' => [
                        'qtd_recebida' => $qtdRecebida,
                        'qtd_testada' => $qtdTestada,
                        'qtd_aprovada' => $qtdAprovada,
                        'qtd_reprovada' => $qtdReprovada,
                    ],
                    'quantidades_mes' => [
                        'labels' => $quantidadesLabels,
                        'dates_ym' => $quantidadesDatesYM,
                        'recebidas' => array_map('intval', $quantidadesRecebidas),
                        'testadas' => array_map('intval', $quantidadesTestadas),
                        'reprovadas' => array_map('intval', $quantidadesReprovadas)
                    ],
                    'fornecedores_taxa' => [
                        'labels' => $fornecedoresLabels,
                        'taxa_aprovacao' => $fornecedoresTaxaAprovacao,
                        'taxa_reprovacao' => $fornecedoresTaxaReprovacao
                    ],
                    'filiais_dropdown' => $allFiliais
                ]
            ];
            
            echo json_encode($response);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar dados de amostragens: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get Não Conformidades dashboard data
     */
    public function getNaoConformidadesData()
    {
        ob_start(); // Iniciar buffer
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['user_role'] ?? 'user';
            
            // Parâmetros de filtro
            $filtroDepartamento = $_GET['departamento'] ?? '';
            $filtroStatus = $_GET['status'] ?? '';
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            // WHERE clause baseado nas permissões e filtros
            $where = [];
            $params = [];
            
            // CONTROLE DE VISUALIZAÇÃO: Usuários não-admin só veem NCs onde são responsáveis ou criadores
            if ($userRole !== 'admin' && $userRole !== 'super_admin') {
                $where[] = "(nc.usuario_responsavel_id = :user_id OR nc.usuario_criador_id = :user_id)";
                $params[':user_id'] = $userId;
            }
            
            // Filtro de status
            if (!empty($filtroStatus)) {
                $where[] = "nc.status = :status";
                $params[':status'] = $filtroStatus;
            }
            
            // Filtro de data inicial
            if (!empty($dataInicial)) {
                $where[] = "DATE(nc.created_at) >= :data_inicial";
                $params[':data_inicial'] = $dataInicial;
            }
            
            // Filtro de data final
            if (!empty($dataFinal)) {
                $where[] = "DATE(nc.created_at) <= :data_final";
                $params[':data_final'] = $dataFinal;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // 1. Cards - Contar por status
            $stmtStatus = $this->db->prepare("
                SELECT 
                    status,
                    COUNT(*) as total
                FROM nao_conformidades nc
                $whereClause
                GROUP BY status
            ");
            $stmtStatus->execute($params);
            $statusData = $stmtStatus->fetchAll(\PDO::FETCH_ASSOC);
            
            $pendentes = 0;
            $emAndamento = 0;
            $solucionadas = 0;
            
            foreach ($statusData as $row) {
                if ($row['status'] === 'pendente') $pendentes = (int)$row['total'];
                if ($row['status'] === 'em_andamento') $emAndamento = (int)$row['total'];
                if ($row['status'] === 'solucionada') $solucionadas = (int)$row['total'];
            }
            
            // 2. Verificar se a coluna departamento_id existe
            $checkColumn = $this->db->query("SHOW COLUMNS FROM nao_conformidades LIKE 'departamento_id'");
            $hasDepartamentoId = $checkColumn->rowCount() > 0;
            
            $departamentosLabels = [];
            $departamentosTotal = [];
            $departamentosPendentes = [];
            $departamentosEmAndamento = [];
            $departamentosSolucionadas = [];
            
            if ($hasDepartamentoId) {
                // Se tem departamento_id, fazer query com JOIN
                $whereDept = $where;
                $paramsDept = $params;
                
                // Filtro de departamento
                if (!empty($filtroDepartamento)) {
                    $whereDept[] = "d.nome = :departamento";
                    $paramsDept[':departamento'] = $filtroDepartamento;
                }
                
                $whereClauseDept = !empty($whereDept) ? 'WHERE ' . implode(' AND ', $whereDept) : '';
                
                $stmtDepartamentos = $this->db->prepare("
                    SELECT 
                        COALESCE(d.nome, 'Sem Departamento') as departamento,
                        COUNT(nc.id) as total_ncs,
                        SUM(CASE WHEN nc.status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                        SUM(CASE WHEN nc.status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
                        SUM(CASE WHEN nc.status = 'solucionada' THEN 1 ELSE 0 END) as solucionadas
                    FROM nao_conformidades nc
                    LEFT JOIN departamentos d ON nc.departamento_id = d.id
                    $whereClauseDept
                    GROUP BY d.id, d.nome
                    HAVING COUNT(nc.id) > 0
                    ORDER BY total_ncs DESC
                    LIMIT 10
                ");
                $stmtDepartamentos->execute($paramsDept);
                $departamentosData = $stmtDepartamentos->fetchAll(\PDO::FETCH_ASSOC);
                
                foreach ($departamentosData as $dept) {
                    $departamentosLabels[] = $dept['departamento'];
                    $departamentosTotal[] = (int)$dept['total_ncs'];
                    $departamentosPendentes[] = (int)$dept['pendentes'];
                    $departamentosEmAndamento[] = (int)$dept['em_andamento'];
                    $departamentosSolucionadas[] = (int)$dept['solucionadas'];
                }
            } else {
                // Se não tem departamento_id, mostrar apenas "Todas as NCs"
                if ($pendentes > 0 || $emAndamento > 0 || $solucionadas > 0) {
                    $departamentosLabels[] = 'Todas as NCs';
                    $departamentosTotal[] = $pendentes + $emAndamento + $solucionadas;
                    $departamentosPendentes[] = $pendentes;
                    $departamentosEmAndamento[] = $emAndamento;
                    $departamentosSolucionadas[] = $solucionadas;
                }
            }
            
            // 3. Buscar lista de departamentos para filtro (se existir a coluna)
            $departamentosDisponiveis = [];
            if ($hasDepartamentoId) {
                // MUDANÇA: Buscar TODOS os departamentos, não apenas os que têm NCs
                $stmtDeptList = $this->db->query("
                    SELECT nome 
                    FROM departamentos
                    WHERE nome IS NOT NULL
                    ORDER BY nome
                ");
                $departamentosDisponiveis = $stmtDeptList->fetchAll(\PDO::FETCH_COLUMN);
            }
            
            // DEBUG: Log para verificar o que está sendo retornado
            error_log("=== DEBUG NC Dashboard ===");
            error_log("User ID: " . $userId);
            error_log("User Role: " . $userRole);
            error_log("Pendentes: " . $pendentes);
            error_log("Em Andamento: " . $emAndamento);
            error_log("Solucionadas: " . $solucionadas);
            error_log("Departamentos disponíveis: " . count($departamentosDisponiveis));
            error_log("Tem departamento_id: " . ($hasDepartamentoId ? 'SIM' : 'NÃO'));
            error_log("========================");
            
            // Montar resposta
            $response = [
                'success' => true,
                'data' => [
                    'cards' => [
                        'pendentes' => $pendentes,
                        'em_andamento' => $emAndamento,
                        'solucionadas' => $solucionadas
                    ],
                    'departamentos' => [
                        'labels' => $departamentosLabels,
                        'total' => $departamentosTotal,
                        'pendentes' => $departamentosPendentes,
                        'em_andamento' => $departamentosEmAndamento,
                        'solucionadas' => $departamentosSolucionadas
                    ],
                    'filtros' => [
                        'departamentos_disponiveis' => $departamentosDisponiveis,
                        'tem_departamento_id' => $hasDepartamentoId
                    ]
                ]
            ];
            
            ob_clean(); // Limpar buffer antes de enviar JSON
            echo json_encode($response);
            
        } catch (\Exception $e) {
            error_log("Erro ao carregar dados de NCs: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            ob_clean(); // Limpar buffer antes de enviar JSON de erro
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar dados de não conformidades: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Dados de qualidade de fornecedores
     */
    public function fornecedoresData()
    {
        header('Content-Type: application/json');
        
        try {
            $filial = $_GET['filial'] ?? '';
            
            // Receber múltiplas origens como array
            $origens = isset($_GET['origem']) && is_array($_GET['origem']) ? $_GET['origem'] : [];
            if (isset($_GET['origem']) && !is_array($_GET['origem']) && !empty($_GET['origem'])) {
                $origens = [$_GET['origem']]; // Compatibilidade com valor único
            }
            
            $dataInicial = $_GET['data_inicial'] ?? '';
            $dataFinal = $_GET['data_final'] ?? '';
            
            error_log("🏭 Buscando dados de fornecedores - Filtros: filial={$filial}, origens=" . implode(',', $origens) . ", periodo={$dataInicial} a {$dataFinal}");
            
            // Validar datas - se não vier período, retornar dados vazios para não quebrar o dashboard
            if (empty($dataInicial) || empty($dataFinal)) {
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'cards' => [
                            'qtd_recebida' => 0,
                            'qtd_testada' => 0,
                            'qtd_aprovada' => 0,
                            'qtd_reprovada' => 0,
                        ],
                        'quantidades_mes' => [
                            'labels' => [],
                            'recebidas' => [],
                            'testadas' => [],
                        ],
                        'fornecedores_taxa' => [
                            'labels' => [],
                            'taxa_aprovacao' => [],
                            'taxa_reprovacao' => [],
                        ],
                        'filiais_dropdown' => [],
                    ],
                    'mensagem' => 'Período não informado, retornando dados vazios para o dashboard',
                ]);
                return;
            }
            
            // 1. Buscar itens comprados das amostragens 2.0
            $sqlComprados = "
                SELECT 
                    f.id as fornecedor_id,
                    f.nome as fornecedor_nome,
                    a.tipo_produto,
                    SUM(a.quantidade_recebida) as total_comprados
                FROM amostragens_2 a
                INNER JOIN fornecedores f ON a.fornecedor_id = f.id
                INNER JOIN filiais fil ON a.filial_id = fil.id
                WHERE DATE(a.created_at) BETWEEN ? AND ?
            ";
            
            $params = [$dataInicial, $dataFinal];
            
            if (!empty($filial)) {
                $sqlComprados .= " AND fil.nome = ?";
                $params[] = $filial;
            }
            
            if (!empty($origem)) {
                // Amostragens 2.0 não tem campo origem, mas podemos adicionar um filtro se necessário
                // Por enquanto, ignoramos este filtro para amostragens
            }
            
            $sqlComprados .= "
                GROUP BY f.id, f.nome, a.tipo_produto
                ORDER BY f.nome, a.tipo_produto
            ";
            
            $stmt = $this->db->prepare($sqlComprados);
            $stmt->execute($params);
            $comprados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log("📦 Total de registros de compras: " . count($comprados));
            
            // Log detalhado dos comprados
            foreach ($comprados as $idx => $c) {
                error_log("Compra {$idx}: Fornecedor {$c['fornecedor_id']} ({$c['fornecedor_nome']}), Tipo: {$c['tipo_produto']}, Qtd: {$c['total_comprados']}");
            }
            
            // 2. Buscar garantias geradas (SOMAR quantidades, não contar itens)
            $sqlGarantias = "
                SELECT 
                    f.id as fornecedor_id,
                    f.nome as fornecedor_nome,
                    gi.tipo_produto,
                    SUM(gi.quantidade) as total_garantias
                FROM garantias g
                INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
                INNER JOIN fornecedores f ON g.fornecedor_id = f.id
                WHERE DATE(g.created_at) BETWEEN ? AND ?
                AND gi.tipo_produto IS NOT NULL
            ";
            
            $paramsGarantias = [$dataInicial, $dataFinal];
            
            // Garantias não tem coluna filial na tabela
            // O filtro de filial só se aplica às amostragens
            
            // Filtro de múltiplas origens
            if (!empty($origens)) {
                $placeholders = str_repeat('?,', count($origens) - 1) . '?';
                $sqlGarantias .= " AND g.origem_garantia IN ($placeholders)";
                $paramsGarantias = array_merge($paramsGarantias, $origens);
            }
            
            $sqlGarantias .= "
                GROUP BY f.id, f.nome, gi.tipo_produto
                ORDER BY f.nome, gi.tipo_produto
            ";
            
            $stmt = $this->db->prepare($sqlGarantias);
            $stmt->execute($paramsGarantias);
            $garantias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log("⚠️ Total de registros de garantias: " . count($garantias));
            
            // Log detalhado das garantias
            foreach ($garantias as $idx => $g) {
                error_log("Garantia {$idx}: Fornecedor {$g['fornecedor_id']} ({$g['fornecedor_nome']}), Tipo: {$g['tipo_produto']}, Qtd: {$g['total_garantias']}");
            }
            
            // 3. Processar dados por fornecedor
            $fornecedoresMap = [];
            
            // Inicializar fornecedores com dados de compras
            foreach ($comprados as $row) {
                $fornecedorId = $row['fornecedor_id'];
                $tipo = $row['tipo_produto'];
                
                if (!isset($fornecedoresMap[$fornecedorId])) {
                    $fornecedoresMap[$fornecedorId] = [
                        'id' => $fornecedorId,
                        'nome' => $row['fornecedor_nome'],
                        'toner' => ['comprados' => 0, 'garantias' => 0, 'qualidade' => 100],
                        'maquina' => ['comprados' => 0, 'garantias' => 0, 'qualidade' => 100],
                        'peca' => ['comprados' => 0, 'garantias' => 0, 'qualidade' => 100],
                        'qualidade_geral' => 100
                    ];
                }
                
                $tipoKey = strtolower($tipo);
                if ($tipo === 'Máquina') $tipoKey = 'maquina';
                if ($tipo === 'Peça') $tipoKey = 'peca';
                
                $fornecedoresMap[$fornecedorId][$tipoKey]['comprados'] = (int)$row['total_comprados'];
            }
            
            // Adicionar dados de garantias
            foreach ($garantias as $row) {
                $fornecedorId = $row['fornecedor_id'];
                $tipo = $row['tipo_produto'];
                
                error_log("🔍 Processando garantia: Fornecedor {$fornecedorId}, Tipo: {$tipo}, Qtd: {$row['total_garantias']}");
                
                // Se fornecedor não existe no map, inicializar (pode ter garantias sem compras)
                if (!isset($fornecedoresMap[$fornecedorId])) {
                    error_log("⚠️ Fornecedor {$fornecedorId} tem garantias mas não tem compras - inicializando");
                    $fornecedoresMap[$fornecedorId] = [
                        'id' => $fornecedorId,
                        'nome' => $row['fornecedor_nome'],
                        'toner' => ['comprados' => 0, 'garantias' => 0, 'qualidade' => 100],
                        'maquina' => ['comprados' => 0, 'garantias' => 0, 'qualidade' => 100],
                        'peca' => ['comprados' => 0, 'garantias' => 0, 'qualidade' => 100],
                        'qualidade_geral' => 100
                    ];
                }
                
                // Normalizar tipo para lowercase
                $tipoKey = strtolower($tipo);
                if ($tipoKey === 'máquina') $tipoKey = 'maquina';
                if ($tipoKey === 'peça') $tipoKey = 'peca';
                
                error_log("✅ Mapeando {$tipo} -> {$tipoKey}");
                
                if (isset($fornecedoresMap[$fornecedorId][$tipoKey])) {
                    $fornecedoresMap[$fornecedorId][$tipoKey]['garantias'] = (int)$row['total_garantias'];
                    error_log("✅ Garantia adicionada: {$row['total_garantias']} para {$tipoKey}");
                } else {
                    error_log("❌ ERRO: tipoKey '{$tipoKey}' não existe no map do fornecedor!");
                }
            }
            
            // 4. Calcular percentuais de qualidade
            foreach ($fornecedoresMap as &$fornecedor) {
                $totalComprados = 0;
                $totalGarantias = 0;
                
                foreach (['toner', 'maquina', 'peca'] as $tipo) {
                    $comprados = $fornecedor[$tipo]['comprados'];
                    $garantias = $fornecedor[$tipo]['garantias'];
                    
                    if ($comprados > 0) {
                        // % Qualidade = ((Comprados - Garantias) / Comprados) × 100
                        $qualidade = (($comprados - $garantias) / $comprados) * 100;
                        $fornecedor[$tipo]['qualidade'] = max(0, $qualidade); // Não pode ser negativo
                    } else {
                        $fornecedor[$tipo]['qualidade'] = 100; // Sem dados = 100%
                    }
                    
                    $totalComprados += $comprados;
                    $totalGarantias += $garantias;
                }
                
                // Calcular qualidade geral do fornecedor
                if ($totalComprados > 0) {
                    $fornecedor['qualidade_geral'] = (($totalComprados - $totalGarantias) / $totalComprados) * 100;
                } else {
                    $fornecedor['qualidade_geral'] = 100;
                }
            }
            
            // 5. Ordenar por qualidade (pior para melhor)
            $fornecedoresArray = array_values($fornecedoresMap);
            usort($fornecedoresArray, function($a, $b) {
                return $a['qualidade_geral'] <=> $b['qualidade_geral'];
            });
            
            // 6. Calcular totais gerais
            $totalCompradosToner = 0;
            $totalGarantiasToner = 0;
            $totalCompradosMaquina = 0;
            $totalGarantiasMaquina = 0;
            $totalCompradosPeca = 0;
            $totalGarantiasPeca = 0;
            
            foreach ($fornecedoresArray as $f) {
                $totalCompradosToner += $f['toner']['comprados'];
                $totalGarantiasToner += $f['toner']['garantias'];
                $totalCompradosMaquina += $f['maquina']['comprados'];
                $totalGarantiasMaquina += $f['maquina']['garantias'];
                $totalCompradosPeca += $f['peca']['comprados'];
                $totalGarantiasPeca += $f['peca']['garantias'];
            }
            
            $response = [
                'success' => true,
                'data' => [
                    'fornecedores' => $fornecedoresArray,
                    'resumo' => [
                        'total_fornecedores' => count($fornecedoresArray),
                        'total_itens_comprados' => $totalCompradosToner + $totalCompradosMaquina + $totalCompradosPeca,
                        'total_garantias' => $totalGarantiasToner + $totalGarantiasMaquina + $totalGarantiasPeca,
                        'por_tipo' => [
                            'toner' => [
                                'comprados' => $totalCompradosToner,
                                'garantias' => $totalGarantiasToner
                            ],
                            'maquina' => [
                                'comprados' => $totalCompradosMaquina,
                                'garantias' => $totalGarantiasMaquina
                            ],
                            'peca' => [
                                'comprados' => $totalCompradosPeca,
                                'garantias' => $totalGarantiasPeca
                            ]
                        ]
                    ]
                ]
            ];
            
            error_log("✅ Dados processados: " . count($fornecedoresArray) . " fornecedores");
            
            echo json_encode($response);
            
        } catch (\Exception $e) {
            error_log("❌ Erro em fornecedoresData: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar dados: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Página de itens do fornecedor (aprovados ou reprovados)
     */
    public function fornecedorItens()
    {
        $fornecedor = $_GET['fornecedor'] ?? '';
        $tipo = $_GET['tipo'] ?? 'aprovados'; // 'aprovados' ou 'reprovados'
        $filial = $_GET['filial'] ?? '';
        $dataInicial = $_GET['data_inicial'] ?? '';
        $dataFinal = $_GET['data_final'] ?? '';
        $origens = isset($_GET['origem']) && is_array($_GET['origem']) ? $_GET['origem'] : [];
        
        if (empty($fornecedor)) {
            echo "Fornecedor não informado";
            return;
        }
        
        try {
            // Buscar ID do fornecedor
            $stmt = $this->db->prepare("SELECT id FROM fornecedores WHERE nome = ?");
            $stmt->execute([$fornecedor]);
            $fornecedorData = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$fornecedorData) {
                echo "Fornecedor não encontrado";
                return;
            }
            
            $fornecedorId = $fornecedorData['id'];
            $itens = [];
            
            if ($tipo === 'reprovados') {
                // 1. Buscar itens reprovados das amostragens
                $sql = "
                    SELECT 
                        a.codigo_produto as codigo,
                        CONCAT(a.nome_produto, CASE WHEN a.observacoes IS NOT NULL AND a.observacoes != '' THEN CONCAT(' - Obs: ', a.observacoes) ELSE '' END) as descricao,
                        a.tipo_produto as tipo,
                        a.quantidade_reprovada as quantidade,
                        a.created_at as data_registro,
                        fil.nome as origem,
                        COALESCE(aprovador.name, u.name) as responsavel,
                        'Amostragem' as fonte,
                        a.numero_nf as nf
                    FROM amostragens_2 a
                    INNER JOIN fornecedores f ON a.fornecedor_id = f.id
                    INNER JOIN filiais fil ON a.filial_id = fil.id
                    LEFT JOIN users u ON a.user_id = u.id
                    LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
                    WHERE f.id = ?
                    AND DATE(a.created_at) BETWEEN ? AND ?
                    AND (a.status_final IN ('reprovado', 'Reprovado') OR a.quantidade_reprovada > 0)
                ";
                
                $params = [$fornecedorId, $dataInicial, $dataFinal];
                
                if (!empty($filial)) {
                    $sql .= " AND fil.nome = ?";
                    $params[] = $filial;
                }
                
                $sql .= " ORDER BY a.created_at DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $itensAmostragens = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // 2. Buscar itens das garantias (problemas que foram direto para garantia, sem amostragem)
                $sqlGarantias = "
                    SELECT 
                        gi.codigo_produto as codigo,
                        CONCAT(
                            COALESCE(gi.nome_produto, gi.codigo_produto, 'Produto não identificado'),
                            CASE WHEN g.numero_ticket_os IS NOT NULL AND g.numero_ticket_os != '' 
                                THEN CONCAT(' - Ticket: ', g.numero_ticket_os) 
                                ELSE '' 
                            END,
                            CASE WHEN g.descricao_defeito IS NOT NULL AND g.descricao_defeito != '' 
                                THEN CONCAT(' - Defeito: ', LEFT(g.descricao_defeito, 100)) 
                                ELSE '' 
                            END
                        ) as descricao,
                        gi.tipo_produto as tipo,
                        gi.quantidade as quantidade,
                        g.created_at as data_registro,
                        COALESCE(g.origem_garantia, 'Garantia Direta') as origem,
                        'Garantia' as responsavel,
                        'Garantia' as fonte,
                        COALESCE(g.numero_nf_compras, '-') as nf
                    FROM garantias g
                    INNER JOIN garantias_itens gi ON g.id = gi.garantia_id
                    INNER JOIN fornecedores f ON g.fornecedor_id = f.id
                    WHERE f.id = ?
                    AND DATE(g.created_at) BETWEEN ? AND ?
                ";
                
                $paramsGarantias = [$fornecedorId, $dataInicial, $dataFinal];
                
                // Filtro de origens se selecionado
                if (!empty($origens)) {
                    $placeholders = str_repeat('?,', count($origens) - 1) . '?';
                    $sqlGarantias .= " AND g.origem_garantia IN ($placeholders)";
                    $paramsGarantias = array_merge($paramsGarantias, $origens);
                }
                
                $sqlGarantias .= " ORDER BY g.created_at DESC";
                
                $stmt = $this->db->prepare($sqlGarantias);
                $stmt->execute($paramsGarantias);
                $itensGarantias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // 3. Unir os resultados (amostragens + garantias)
                $itens = array_merge($itensAmostragens, $itensGarantias);
                
                // 4. Ordenar por data (mais recentes primeiro)
                usort($itens, function($a, $b) {
                    return strtotime($b['data_registro']) - strtotime($a['data_registro']);
                });
                
            } else {
                // Buscar itens aprovados das amostragens
                $sql = "
                    SELECT 
                        a.codigo_produto as codigo,
                        CONCAT(a.nome_produto, CASE WHEN a.observacoes IS NOT NULL AND a.observacoes != '' THEN CONCAT(' - Obs: ', a.observacoes) ELSE '' END) as descricao,
                        a.tipo_produto as tipo,
                        a.quantidade_aprovada as quantidade,
                        a.created_at as data_registro,
                        fil.nome as origem,
                        COALESCE(aprovador.name, u.name) as responsavel,
                        'Aprovado' as status,
                        a.numero_nf as nf
                    FROM amostragens_2 a
                    INNER JOIN fornecedores f ON a.fornecedor_id = f.id
                    INNER JOIN filiais fil ON a.filial_id = fil.id
                    LEFT JOIN users u ON a.user_id = u.id
                    LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
                    WHERE f.id = ?
                    AND DATE(a.created_at) BETWEEN ? AND ?
                    AND (a.status_final IN ('aprovado', 'Aprovado', 'aprovado_parcialmente', 'Aprovado Parcialmente') OR a.quantidade_aprovada > 0)
                ";
                
                $params = [$fornecedorId, $dataInicial, $dataFinal];
                
                if (!empty($filial)) {
                    $sql .= " AND fil.nome = ?";
                    $params[] = $filial;
                }
                
                $sql .= " ORDER BY a.created_at DESC";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $itens = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            // Renderizar página
            $titulo = $tipo === 'reprovados' ? 'Itens Reprovados' : 'Itens Aprovados';
            $corTema = $tipo === 'reprovados' ? 'red' : 'green';
            
            include __DIR__ . '/../../views/admin/fornecedor_itens.php';
            
        } catch (\Exception $e) {
            error_log("Erro em fornecedorItens: " . $e->getMessage());
            echo "Erro ao carregar dados: " . $e->getMessage();
        }
    }

    /**
     * Página de amostragens reprovadas de um mês específico
     */
    public function amostragemReprovadasMes()
    {
        $mes = $_GET['mes'] ?? ''; // Formato YYYY-MM
        
        if (empty($mes)) {
            echo "Mês não informado";
            return;
        }
        
        try {
            // Converter mês para formato legível
            $dataObj = \DateTime::createFromFormat('Y-m', $mes);
            if (!$dataObj) {
                echo "Formato de mês inválido. Use YYYY-MM";
                return;
            }
            
            // Meses em português
            $meses = [
                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
            ];
            $mesLabel = $meses[(int)$dataObj->format('m')] . '/' . $dataObj->format('Y');
            
            // Calcular primeiro e último dia do mês
            $primeiroDia = $dataObj->format('Y-m-01');
            $ultimoDia = $dataObj->format('Y-m-t');
            
            // Buscar amostragens reprovadas do mês
            $sql = "
                SELECT 
                    a.id,
                    a.codigo_produto as codigo,
                    a.nome_produto,
                    a.tipo_produto as tipo,
                    a.quantidade_reprovada as quantidade,
                    a.created_at as data_registro,
                    f.nome as fornecedor,
                    COALESCE(aprovador.name, u.name) as responsavel,
                    a.observacoes
                FROM amostragens_2 a
                INNER JOIN fornecedores f ON a.fornecedor_id = f.id
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN users aprovador ON a.aprovado_por = aprovador.id
                WHERE DATE(a.created_at) BETWEEN ? AND ?
                AND (a.status_final IN ('reprovado', 'Reprovado') OR a.quantidade_reprovada > 0)
                ORDER BY a.created_at DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$primeiroDia, $ultimoDia]);
            $itens = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Calcular total de itens reprovados
            $totalReprovado = array_sum(array_column($itens, 'quantidade'));
            
            // Renderizar página
            include __DIR__ . '/../../views/admin/amostragens_reprovadas_mes.php';
            
        } catch (\Exception $e) {
            error_log("Erro em amostragemReprovadasMes: " . $e->getMessage());
            echo "Erro ao carregar dados: " . $e->getMessage();
        }
    }

    /**
     * Get melhorias dashboard data - dados reais do módulo Melhoria Contínua 2.0
     */
    public function getMelhoriasData()
    {
        header('Content-Type: application/json');
        
        try {
            // Coletar filtros
            $departamentoId = $_GET['departamento_id'] ?? '';
            $status = $_GET['status'] ?? '';
            $idealizador = $_GET['idealizador'] ?? '';
            $dataInicio = $_GET['data_inicio'] ?? '';
            $dataFim = $_GET['data_fim'] ?? '';
            $pontuacaoMin = $_GET['pontuacao_min'] ?? '';
            $pontuacaoMax = $_GET['pontuacao_max'] ?? '';
            
            // Mapeamento de status do filtro para o banco
            $statusMap = [
                'pendente_analise' => 'Pendente análise',
                'enviado_aprovacao' => 'Enviado para Aprovação',
                'em_andamento' => 'Em andamento',
                'concluida' => 'Concluída',
                'reprovada' => 'Recusada',
                'cancelada' => 'Cancelada'
            ];
            
            // Construir WHERE clause
            $where = [];
            $params = [];
            
            if (!empty($departamentoId)) {
                $where[] = "m.departamento_id = :departamento_id";
                $params[':departamento_id'] = $departamentoId;
            }
            
            if (!empty($status) && isset($statusMap[$status])) {
                $where[] = "m.status = :status";
                $params[':status'] = $statusMap[$status];
            }
            
            if (!empty($idealizador)) {
                $where[] = "m.idealizador LIKE :idealizador";
                $params[':idealizador'] = '%' . $idealizador . '%';
            }
            
            if (!empty($dataInicio)) {
                $where[] = "DATE(m.created_at) >= :data_inicio";
                $params[':data_inicio'] = $dataInicio;
            }
            
            if (!empty($dataFim)) {
                $where[] = "DATE(m.created_at) <= :data_fim";
                $params[':data_fim'] = $dataFim;
            }
            
            if ($pontuacaoMin !== '') {
                $where[] = "COALESCE(m.pontuacao, 0) >= :pontuacao_min";
                $params[':pontuacao_min'] = (int)$pontuacaoMin;
            }
            
            if ($pontuacaoMax !== '') {
                $where[] = "COALESCE(m.pontuacao, 0) <= :pontuacao_max";
                $params[':pontuacao_max'] = (int)$pontuacaoMax;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $data = [
                'success' => true,
                'statusDistribution' => [],
                'melhoriasPorMes' => [],
                'melhoriasPorDepartamento' => [],
                'pontuacaoMedia' => 0,
                'totais' => [
                    'total' => 0,
                    'concluidas' => 0,
                    'em_andamento' => 0,
                    'pendentes' => 0
                ]
            ];

            // 1. Distribuição por Status (com filtros)
            $sql = "SELECT m.status, COUNT(*) as total FROM melhoria_continua_2 m $whereClause GROUP BY m.status ORDER BY total DESC";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $data['statusDistribution'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 2. Melhorias por Mês (últimos 12 meses, com filtros)
            $whereClauseMes = $whereClause;
            if (empty($whereClauseMes)) {
                $whereClauseMes = "WHERE m.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
            } else {
                $whereClauseMes .= " AND m.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
            }
            
            $sql = "SELECT DATE_FORMAT(m.created_at, '%Y-%m') as mes, COUNT(*) as total FROM melhoria_continua_2 m $whereClauseMes GROUP BY DATE_FORMAT(m.created_at, '%Y-%m') ORDER BY mes ASC";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $data['melhoriasPorMes'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 3. Melhorias por Departamento (Top 10, com filtros)
            $sql = "SELECT d.nome as departamento, COUNT(m.id) as total FROM melhoria_continua_2 m LEFT JOIN departamentos d ON m.departamento_id = d.id $whereClause GROUP BY d.nome ORDER BY total DESC LIMIT 10";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $data['melhoriasPorDepartamento'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 4. Pontuação Média (com filtros)
            $whereClausePont = $whereClause;
            if (empty($whereClausePont)) {
                $whereClausePont = "WHERE m.pontuacao IS NOT NULL AND m.pontuacao > 0";
            } else {
                $whereClausePont .= " AND m.pontuacao IS NOT NULL AND m.pontuacao > 0";
            }
            
            $sql = "SELECT AVG(m.pontuacao) as media FROM melhoria_continua_2 m $whereClausePont";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $data['pontuacaoMedia'] = round($result['media'] ?? 0, 2);

            // 5. Totais (com filtros)
            $sql = "SELECT COUNT(*) as total FROM melhoria_continua_2 m $whereClause";
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $data['totais']['total'] = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];

            // Contagem individual de cada status (com filtros base)
            $statusList = [
                'pendente_analise' => 'Pendente análise',
                'enviado_aprovacao' => 'Enviado para Aprovação',
                'em_andamento' => 'Em andamento',
                'concluida' => 'Concluída',
                'recusada' => 'Recusada',
                'pendente_adaptacao' => 'Pendente Adaptação'
            ];
            
            foreach ($statusList as $key => $statusValue) {
                $whereClauseStatus = $whereClause;
                if (empty($whereClauseStatus)) {
                    $whereClauseStatus = "WHERE m.status = :status_filter";
                } else {
                    $whereClauseStatus .= " AND m.status = :status_filter";
                }
                
                $sql = "SELECT COUNT(*) as total FROM melhoria_continua_2 m $whereClauseStatus";
                $stmt = $this->db->prepare($sql);
                foreach ($params as $k => $v) {
                    $stmt->bindValue($k, $v);
                }
                $stmt->bindValue(':status_filter', $statusValue);
                $stmt->execute();
                $data['totais'][$key] = (int)$stmt->fetch(\PDO::FETCH_ASSOC)['total'];
            }

            echo json_encode($data);

        } catch (\Exception $e) {
            error_log('Erro ao buscar dados de melhorias: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar dados: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Get lista de departamentos para filtros
     */
    public function getDepartamentos()
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->query("SELECT id, nome FROM departamentos ORDER BY nome ASC");
            $departamentos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'departamentos' => $departamentos
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao buscar departamentos: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Buscar permissões de abas do dashboard para o perfil do usuário
     */
    private function getDashboardTabPermissions()
    {
        $tabs = [
            'retornados' => true,
            'amostragens' => true,
            'fornecedores' => true,
            'garantias' => true,
            'melhorias' => true,
            'nao_conformidades' => true
        ];
        
        try {
            // Buscar profile_id do usuário
            $userId = $_SESSION['user_id'];
            $stmt = $this->db->prepare("SELECT profile_id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$user || !$user['profile_id']) {
                // Sem perfil, libera tudo (admin)
                return $tabs;
            }
            
            $profileId = $user['profile_id'];
            
            // Verificar se a tabela de permissões existe
            $tableExists = $this->db->query("SHOW TABLES LIKE 'dashboard_tab_permissions'")->rowCount() > 0;
            
            if (!$tableExists) {
                // Tabela não existe ainda, libera tudo
                return $tabs;
            }
            
            // Buscar permissões específicas do perfil
            $stmt = $this->db->prepare("
                SELECT tab_name, can_view 
                FROM dashboard_tab_permissions 
                WHERE profile_id = ?
            ");
            $stmt->execute([$profileId]);
            $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($permissions)) {
                // Sem permissões configuradas, libera tudo
                return $tabs;
            }
            
            // Aplicar permissões
            foreach ($permissions as $perm) {
                $tabs[$perm['tab_name']] = (bool)$perm['can_view'];
            }
            
        } catch (\Exception $e) {
            error_log("Erro ao buscar permissões de abas do dashboard: " . $e->getMessage());
            // Em caso de erro, libera tudo
        }
        
        return $tabs;
    }
    
    /**
     * Get melhorias por departamento
     * Usado no modal do dashboard quando clica em um departamento
     */
    public function getMelhoriasPorDepartamento()
    {
        header('Content-Type: application/json');
        
        try {
            $departamento = $_GET['departamento'] ?? '';
            
            if (empty($departamento)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Departamento não informado'
                ]);
                exit;
            }
            
            // Buscar melhorias do departamento
            $sql = "
                SELECT 
                    m.id,
                    m.titulo,
                    m.idealizador,
                    m.status,
                    m.pontuacao as pont_global,
                    m.created_at as data_criacao,
                    d.nome as departamento_nome
                FROM melhoria_continua_2 m
                LEFT JOIN departamentos d ON m.departamento_id = d.id
                WHERE d.nome = ?
                ORDER BY m.created_at DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$departamento]);
            $melhorias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'melhorias' => $melhorias,
                'departamento' => $departamento,
                'total' => count($melhorias)
            ]);
            
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao carregar melhorias: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}    
