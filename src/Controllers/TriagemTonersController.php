<?php

namespace App\Controllers;

use App\Config\Database;
use App\Services\PermissionService;
use PDO;

class TriagemTonersController
{
    private PDO $db;

    private function calcularImpactoFinanceiro(array $toner, float $percentualCalculado, string $destino): array
    {
        $capacidade = (float)($toner['capacidade_folhas'] ?? 0);
        $custoFolha = (float)($toner['custo_por_folha'] ?? 0);
        $folhasEquivalentes = ($percentualCalculado > 0 && $capacidade > 0)
            ? (($percentualCalculado / 100) * $capacidade)
            : 0;

        $valorBase = ($folhasEquivalentes > 0 && $custoFolha > 0)
            ? round($folhasEquivalentes * $custoFolha, 2)
            : 0.00;

        $valor = 0.00;
        if ($destino === 'Estoque') {
            $valor = abs($valorBase);
        } elseif ($destino === 'Descarte') {
            $valor = -abs($valorBase);
        }

        return [
            'valor' => round($valor, 2),
            'folhas_equivalentes' => (int)round($folhasEquivalentes),
        ];
    }

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTablesExist();
    }

    private function ensureTablesExist(): void
    {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS triagem_toners (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    toner_id INT NOT NULL,
                    toner_modelo VARCHAR(255) NOT NULL,
                    cliente_id INT NULL,
                    cliente_nome VARCHAR(255) NULL,
                    fornecedor_id INT NULL,
                    fornecedor_nome VARCHAR(255) NULL,
                    filial_registro VARCHAR(150) NULL,
                    colaborador_registro VARCHAR(255) NULL,
                    codigo_requisicao VARCHAR(100) NULL,
                    defeito_id INT NULL,
                    defeito_nome VARCHAR(255) NULL,
                    modo ENUM('peso','percentual') NOT NULL DEFAULT 'peso',
                    peso_retornado DECIMAL(10,2) NULL,
                    percentual_informado DECIMAL(5,2) NULL,
                    gramatura_restante DECIMAL(10,2) NULL,
                    percentual_calculado DECIMAL(5,2) NOT NULL,
                    parecer TEXT NULL,
                    destino ENUM('Descarte','Garantia','Uso Interno','Estoque') NOT NULL,
                    valor_recuperado DECIMAL(10,2) NULL DEFAULT 0.00,
                    observacoes TEXT NULL,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_by INT NULL,
                    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_toner_id (toner_id),
                    INDEX idx_cliente_id (cliente_id),
                    INDEX idx_fornecedor_id (fornecedor_id),
                    INDEX idx_defeito_id (defeito_id),
                    INDEX idx_destino (destino),
                    INDEX idx_created_at (created_at),
                    INDEX idx_created_by (created_by)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Compatibilidade para bases antigas (antes de cliente_id/cliente_nome)
            $colClienteId = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'cliente_id'")->fetch();
            if (!$colClienteId) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN cliente_id INT NULL AFTER toner_modelo");
                $this->db->exec("ALTER TABLE triagem_toners ADD INDEX idx_cliente_id (cliente_id)");
            }
            $colClienteNome = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'cliente_nome'")->fetch();
            if (!$colClienteNome) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN cliente_nome VARCHAR(255) NULL AFTER cliente_id");
            }
            $colFornecedorId = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'fornecedor_id'")->fetch();
            if (!$colFornecedorId) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN fornecedor_id INT NULL AFTER cliente_nome");
                $this->db->exec("ALTER TABLE triagem_toners ADD INDEX idx_fornecedor_id (fornecedor_id)");
            }
            $colFornecedorNome = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'fornecedor_nome'")->fetch();
            if (!$colFornecedorNome) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN fornecedor_nome VARCHAR(255) NULL AFTER fornecedor_id");
            }
            $colFilialRegistro = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'filial_registro'")->fetch();
            if (!$colFilialRegistro) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN filial_registro VARCHAR(150) NULL AFTER cliente_nome");
            }
            $colColaboradorRegistro = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'colaborador_registro'")->fetch();
            if (!$colColaboradorRegistro) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN colaborador_registro VARCHAR(255) NULL AFTER filial_registro");
            }
            $colCodigoRequisicao = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'codigo_requisicao'")->fetch();
            if (!$colCodigoRequisicao) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN codigo_requisicao VARCHAR(100) NULL AFTER cliente_nome");
            }
            $colDefeitoId = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'defeito_id'")->fetch();
            if (!$colDefeitoId) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN defeito_id INT NULL AFTER codigo_requisicao");
                $this->db->exec("ALTER TABLE triagem_toners ADD INDEX idx_defeito_id (defeito_id)");
            }
            $colDefeitoNome = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'defeito_nome'")->fetch();
            if (!$colDefeitoNome) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN defeito_nome VARCHAR(255) NULL AFTER defeito_id");
            }
            $colValorRecuperado = $this->db->query("SHOW COLUMNS FROM triagem_toners LIKE 'valor_recuperado'")->fetch();
            if (!$colValorRecuperado) {
                $this->db->exec("ALTER TABLE triagem_toners ADD COLUMN valor_recuperado DECIMAL(10,2) NULL DEFAULT 0.00 AFTER destino");
            }
            $this->db->exec("UPDATE triagem_toners SET valor_recuperado = 0.00 WHERE valor_recuperado IS NULL");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS triagem_toners_parametros (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    percentual_min DECIMAL(5,2) NOT NULL,
                    percentual_max DECIMAL(5,2) NOT NULL,
                    parecer TEXT NOT NULL,
                    ordem INT NOT NULL DEFAULT 0,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Inserir parâmetros padrão se a tabela estiver vazia
            $count = $this->db->query("SELECT COUNT(*) FROM triagem_toners_parametros")->fetchColumn();
            if ($count == 0) {
                $this->db->exec("
                    INSERT INTO triagem_toners_parametros (percentual_min, percentual_max, parecer, ordem, created_by) VALUES
                    (0,   5,   'Descartar o toner.', 1, 0),
                    (6,   40,  'Teste o toner: se tiver com boa qualidade, use internamente ou em clientes próximos. Se estiver com má qualidade, descarte.', 2, 0),
                    (41,  80,  'Teste o toner: se tiver com boa qualidade, use internamente ou em clientes próximos. Se estiver com má qualidade, solicite garantia para o fornecedor.', 3, 0),
                    (81,  100, 'Teste o toner: se tiver com boa qualidade, envie para a logística como novo. Se estiver com má qualidade, solicite garantia para o fornecedor.', 4, 0)
                ");
            }
        } catch (\Exception $e) {
            error_log('Erro ao criar tabelas de triagem: ' . $e->getMessage());
        }
    }

    // Baixar modelo de importação
    public function downloadTemplate(): void
    {
        try {
            if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'import')) {
                http_response_code(403);
                echo 'Sem permissão para baixar modelo.';
                return;
            }

            $filename = 'modelo_importacao_triagem_toners_' . date('Ymd') . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, [
                'Código Cliente',
                'Código de Requisição',
                'Defeito (nome simples)',
                'Modelo Toner',
                'Modo (peso/percentual)',
                'Peso Retornado (g)',
                '% Toner (%)',
                'Destino (Descarte/Garantia/Uso Interno/Estoque)',
                'Valor Recuperado (R$) [calculado automaticamente]',
                'Observações',
                'Data Registro (DD/MM/AAAA ou DD/MM/AAAA HH:MM)',
                'Filial (opcional)',
                'Colaborador (opcional)',
            ], ';');

            fputcsv($output, [
                '000123',
                'REQ-2026-0001',
                'Risco no cilindro',
                'HP CF280A',
                'peso',
                '320.5',
                '',
                'Estoque',
                '',
                'Lote de teste de importação',
                date('d/m/Y H:i'),
                '',
                '',
            ], ';');

            fclose($output);
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Erro ao gerar modelo: ' . $e->getMessage();
        }
    }

    // Importar triagens em lote via Excel/CSV
    public function importar(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'import')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para importar.']);
            return;
        }

        try {
            if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Arquivo não enviado ou com erro.']);
                return;
            }

            $file = $_FILES['arquivo'];
            if ($file['size'] > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 10MB.']);
                return;
            }

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ['csv', 'xls', 'xlsx'])) {
                echo json_encode(['success' => false, 'message' => 'Formato inválido. Use CSV, XLS ou XLSX.']);
                return;
            }

            $rows = $this->readSpreadsheetRows($file['tmp_name'], $extension);
            if (empty($rows) || count($rows) <= 1) {
                echo json_encode(['success' => false, 'message' => 'Planilha vazia ou inválida.']);
                return;
            }

            $header = array_map(static fn($v) => strtolower(trim((string)$v)), $rows[0] ?? []);
            $hasDefeitoColumn = false;
            $hasValorRecuperadoColumn = false;
            $hasDataColumn = false;
            $filialColumnIndex = null;
            $colaboradorColumnIndex = null;
            foreach ($header as $idx => $h) {
                if (strpos($h, 'defeito') !== false) {
                    $hasDefeitoColumn = true;
                }
                if (strpos($h, 'valor recuperado') !== false) {
                    $hasValorRecuperadoColumn = true;
                }
                if (strpos($h, 'data') !== false) {
                    $hasDataColumn = true;
                }
                if ($filialColumnIndex === null && strpos($h, 'filial') !== false) {
                    $filialColumnIndex = $idx;
                }
                if ($colaboradorColumnIndex === null && (strpos($h, 'colaborador') !== false || strpos($h, 'colab') !== false)) {
                    $colaboradorColumnIndex = $idx;
                }
            }

            $imported = 0;
            $errors = [];
            $importedDetails = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue; // cabeçalho
                }

                $line = $index + 1;
                $row = array_map(static fn($v) => trim((string)$v), $row);

                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    $codigoCliente = $row[0] ?? '';
                    $codigoRequisicao = $row[1] ?? null;

                    // Compatibilidade: layout antigo (sem coluna Defeito) e novo layout (com Defeito)
                    if ($hasDefeitoColumn) {
                        $defeitoNomeRaw = $row[2] ?? '';
                        $modeloToner   = $row[3] ?? '';
                        $modoRaw       = strtolower($row[4] ?? 'peso');
                        $pesoRet       = $row[5] ?? '';
                        $pctRaw        = $row[6] ?? '';
                        $destinoRaw    = $row[7] ?? '';
                        if ($hasValorRecuperadoColumn) {
                            $observacoes = $row[9] ?? null;
                            $dataRegistroRaw = $hasDataColumn ? ($row[10] ?? null) : null;
                        } else {
                            $observacoes = $row[8] ?? null;
                            $dataRegistroRaw = $hasDataColumn ? ($row[9] ?? null) : null;
                        }
                    } else {
                        $defeitoNomeRaw = '';
                        $modeloToner   = $row[2] ?? '';
                        $modoRaw       = strtolower($row[3] ?? 'peso');
                        $pesoRet       = $row[4] ?? '';
                        $pctRaw        = $row[5] ?? '';
                        $destinoRaw    = $row[6] ?? '';
                        if ($hasValorRecuperadoColumn) {
                            $observacoes = $row[8] ?? null;
                            $dataRegistroRaw = $hasDataColumn ? ($row[9] ?? null) : null;
                        } else {
                            $observacoes = $row[7] ?? null;
                            $dataRegistroRaw = $hasDataColumn ? ($row[8] ?? null) : null;
                        }
                    }

                    $createdAtImport = null;
                    $dataRegistroRaw = trim((string)($dataRegistroRaw ?? ''));
                    if ($dataRegistroRaw !== '') {
                        $dt = \DateTime::createFromFormat('d/m/Y H:i', $dataRegistroRaw)
                            ?: \DateTime::createFromFormat('d/m/Y', $dataRegistroRaw)
                            ?: \DateTime::createFromFormat('Y-m-d H:i:s', $dataRegistroRaw)
                            ?: \DateTime::createFromFormat('Y-m-d', $dataRegistroRaw);
                        if ($dt) {
                            // Se vier só data, completa com 00:00:00
                            if (strlen($dataRegistroRaw) === 10 && strpos($dataRegistroRaw, ':') === false) {
                                $dt->setTime(0, 0, 0);
                            }
                            $createdAtImport = $dt->format('Y-m-d H:i:s');
                        }
                    }

                    $filialRegistroImport = null;
                    if ($filialColumnIndex !== null) {
                        $filialRaw = trim((string)($row[$filialColumnIndex] ?? ''));
                        if ($filialRaw !== '') {
                            $filialRegistroImport = $filialRaw;
                        }
                    }

                    $colaboradorRegistroImport = null;
                    if ($colaboradorColumnIndex !== null) {
                        $colaboradorRaw = trim((string)($row[$colaboradorColumnIndex] ?? ''));
                        if ($colaboradorRaw !== '') {
                            $colaboradorRegistroImport = $colaboradorRaw;
                        }
                    }

                    // Legado: ignorar linhas sem chaves mínimas em vez de quebrar importação
                    if ($codigoCliente === '' || $modeloToner === '') {
                        continue;
                    }

                    $cliente = $this->findClienteByCodigoOrNome($codigoCliente);
                    if (!$cliente) {
                        $errors[] = "Linha {$line}: Cliente '{$codigoCliente}' não encontrado.";
                        continue;
                    }

                    $tonerStmt = $this->db->prepare("SELECT id, modelo, peso_vazio, peso_cheio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE LOWER(modelo) = LOWER(?) LIMIT 1");
                    $tonerStmt->execute([$modeloToner]);
                    $toner = $tonerStmt->fetch(PDO::FETCH_ASSOC);
                    if (!$toner) {
                        $errors[] = "Linha {$line}: Toner '{$modeloToner}' não encontrado.";
                        continue;
                    }

                    $pesoRetNum = ($pesoRet === '') ? null : (float)str_replace(',', '.', $pesoRet);
                    $pctNum     = ($pctRaw === '')  ? null : (float)str_replace(',', '.', $pctRaw);

                    // Legado: aceitar lacunas de modo/peso/percentual com fallback automático
                    $modoRawNorm = strtolower(trim((string)$modoRaw));
                    if (in_array($modoRawNorm, ['percentual', 'pct', '%']) && $pctNum !== null) {
                        $modo = 'percentual';
                    } elseif ($modoRawNorm === 'peso' && $pesoRetNum !== null) {
                        $modo = 'peso';
                    } elseif ($pctNum !== null) {
                        $modo = 'percentual';
                    } elseif ($pesoRetNum !== null) {
                        $modo = 'peso';
                    } else {
                        $modo = 'percentual';
                        $pctNum = 0.0;
                    }

                    if ($pesoRetNum !== null && $pesoRetNum < 0) {
                        $pesoRetNum = 0.0;
                    }
                    if ($pctNum !== null && $pctNum < 0) {
                        $pctNum = 0.0;
                    }

                    $gramaturaToner = (float)($toner['gramatura'] ?: ((float)$toner['peso_cheio'] - (float)$toner['peso_vazio']));
                    $gramaturaRestante = null;
                    $percentualCalculado = 0;

                    if ($modo === 'peso') {
                        $gramaturaRestante = max(0, $pesoRetNum - (float)$toner['peso_vazio']);
                        $percentualCalculado = $gramaturaToner > 0
                            ? min(100, max(0, round(($gramaturaRestante / $gramaturaToner) * 100, 2)))
                            : 0;
                    } else {
                        $percentualCalculado = min(100, max(0, round((float)$pctNum, 2)));
                        if ($gramaturaToner > 0) {
                            $gramaturaRestante = round(($percentualCalculado / 100) * $gramaturaToner, 2);
                        }
                    }

                    $destino = $this->normalizeDestino($destinoRaw);
                    if ($destino === null) {
                        $destino = 'Descarte';
                    }

                    $defeitoId = null;
                    $defeitoNome = null;
                    if ($defeitoNomeRaw !== '') {
                        $defeitoStmt = $this->db->prepare("SELECT id, nome_defeito FROM cadastro_defeitos WHERE LOWER(nome_defeito) = LOWER(?) LIMIT 1");
                        $defeitoStmt->execute([$defeitoNomeRaw]);
                        $defeito = $defeitoStmt->fetch(PDO::FETCH_ASSOC);
                        if (!$defeito) {
                            $errors[] = "Linha {$line}: Defeito '{$defeitoNomeRaw}' não encontrado no cadastro geral.";
                            continue;
                        }
                        $defeitoId = (int)$defeito['id'];
                        $defeitoNome = $defeito['nome_defeito'];
                    }

                    $parecer = $this->getParecer($percentualCalculado);

                    $impactoFinanceiro = $this->calcularImpactoFinanceiro($toner, $percentualCalculado, $destino);
                    $valorRecuperado = $impactoFinanceiro['valor'];

                    $insert = $this->db->prepare("
                        INSERT INTO triagem_toners
                            (toner_id, toner_modelo, cliente_id, cliente_nome, filial_registro, colaborador_registro, codigo_requisicao,
                             defeito_id, defeito_nome, modo,
                             peso_retornado, percentual_informado, gramatura_restante,
                             percentual_calculado, parecer, destino, valor_recuperado,
                             observacoes, created_by, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $percentualParaSalvar = $modo === 'percentual' ? $pctNum : $percentualCalculado;

                    $insert->execute([
                        $toner['id'],
                        $toner['modelo'],
                        $cliente['id'],
                        $cliente['nome'],
                        $filialRegistroImport,
                        $colaboradorRegistroImport,
                        $codigoRequisicao !== '' ? $codigoRequisicao : null,
                        $defeitoId,
                        $defeitoNome,
                        $modo,
                        $modo === 'peso' ? $pesoRetNum : null,
                        $percentualParaSalvar,
                        $gramaturaRestante,
                        $percentualCalculado,
                        $parecer,
                        $destino,
                        $valorRecuperado,
                        $observacoes,
                        $_SESSION['user_id'],
                        $createdAtImport,
                    ]);

                    // Sincronizar devolutiva
                    $this->syncDevolutiva($codigoRequisicao !== '' ? $codigoRequisicao : null, $destino, $parecer, $_SESSION['user_id']);

                    $importedDetails[] = sprintf(
                        'Linha %d: Cliente %s | Toner %s | Filial %s | Colaborador %s',
                        $line,
                        $cliente['nome'],
                        $toner['modelo'],
                        $filialRegistroImport ?? 'Não informado',
                        $colaboradorRegistroImport ?? 'Não informado'
                    );

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Linha {$line}: " . $e->getMessage();
                }
            }

            $success = $imported > 0 || empty($errors);
            $message = "Importação concluída: {$imported} registro(s) importado(s).";
            if ($imported === 0 && !empty($errors)) {
                $preview = implode(' | ', array_slice($errors, 0, 3));
                $message = 'Nenhum registro foi importado. ' . ($preview !== '' ? $preview : 'Verifique os erros da planilha.');
            }

            echo json_encode([
                'success' => $success,
                'imported' => $imported,
                'imported_details' => $importedDetails,
                'errors' => $errors,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro na importação: ' . $e->getMessage()]);
        }
    }


    /**
     * Endpoint API para buscar toners com defeito dado um código de requisição
     */
    public function getDefeitosPorCodigo(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'view')) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            return;
        }

        try {
            $codigoRequisicao = trim($_GET['codigo_requisicao'] ?? '');

            if (empty($codigoRequisicao)) {
                echo json_encode(['success' => true, 'data' => []]);
                return;
            }

            $stmt = $this->db->prepare("
                SELECT id, numero_pedido, descricao AS defeito_relatado, toner_id, modelo_toner AS toner_modelo, cliente_id, cliente_nome
                FROM toners_defeitos 
                WHERE numero_pedido = ?
            ");
            $stmt->execute([$codigoRequisicao]);
            $defeitosLocalizados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $defeitosLocalizados]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    // Página principal
    public function index(): void
    {
        if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'view')) {
            http_response_code(403);
            include __DIR__ . '/../../views/errors/403.php';
            return;
        }

        $userRole = $_SESSION['user_role'] ?? '';
        $isAdmin  = in_array($userRole, ['admin', 'super_admin']);

        try {
            $stmt = $this->db->query("SELECT id, modelo, peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha, preco_toner FROM toners WHERE peso_cheio IS NOT NULL AND peso_vazio IS NOT NULL ORDER BY modelo");
            $toners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $toners = [];
        }

        try {
            $stmt = $this->db->query("SELECT id, codigo, nome FROM clientes ORDER BY nome ASC");
            $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $clientes = [];
        }

        try {
            $stmt = $this->db->query("SELECT id, nome_defeito FROM cadastro_defeitos ORDER BY nome_defeito ASC");
            $defeitos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $defeitos = [];
        }

        try {
            $stmt = $this->db->query("SELECT id, nome FROM fornecedores ORDER BY nome ASC");
            $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $fornecedores = [];
        }

        $parametros = $this->getParametros();

        $title    = 'Triagem de Toners - SGQ OTI DJ';
        $viewFile = __DIR__ . '/../../views/pages/toners/triagem.php';
        include __DIR__ . '/../../views/layouts/main.php';
    }

    // API: Listar registros de triagem
    public function list(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'view')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            return;
        }

        try {
            $page     = max(1, (int)($_GET['page'] ?? 1));
            $perPage  = max(1, min(100, (int)($_GET['per_page'] ?? 15)));
            $offset   = ($page - 1) * $perPage;

            $where  = "WHERE 1=1";
            $params = [];

            if (!empty($_GET['toner_modelo'])) {
                $where .= " AND t.toner_modelo LIKE ?";
                $params[] = '%' . trim((string)$_GET['toner_modelo']) . '%';
            }
            if (!empty($_GET['modelo'])) {
                $where .= " AND t.toner_modelo LIKE ?";
                $params[] = '%' . trim((string)$_GET['modelo']) . '%';
            }
            if (!empty($_GET['cliente'])) {
                $where .= " AND (t.cliente_nome LIKE ? OR CAST(t.cliente_id AS CHAR) LIKE ?)";
                $clienteFiltro = '%' . trim((string)$_GET['cliente']) . '%';
                $params[] = $clienteFiltro;
                $params[] = $clienteFiltro;
            }
            if (!empty($_GET['codigo_requisicao'])) {
                $where .= " AND t.codigo_requisicao LIKE ?";
                $params[] = '%' . trim((string)$_GET['codigo_requisicao']) . '%';
            }
            if (!empty($_GET['colaborador'])) {
                $where .= " AND t.colaborador_registro LIKE ?";
                $params[] = '%' . trim((string)$_GET['colaborador']) . '%';
            }
            if (!empty($_GET['defeito'])) {
                $where .= " AND t.defeito_nome LIKE ?";
                $params[] = '%' . trim((string)$_GET['defeito']) . '%';
            }
            if (!empty($_GET['fornecedor'])) {
                $where .= " AND t.fornecedor_nome LIKE ?";
                $params[] = '%' . trim((string)$_GET['fornecedor']) . '%';
            }
            if (!empty($_GET['modo'])) {
                $where .= " AND t.modo = ?";
                $params[] = trim((string)$_GET['modo']);
            }
            if (!empty($_GET['filial'])) {
                $where .= " AND COALESCE(t.filial_registro, '') LIKE ?";
                $params[] = '%' . trim((string)$_GET['filial']) . '%';
            }
            if (!empty($_GET['destino'])) {
                $where .= " AND t.destino = ?";
                $params[] = $_GET['destino'];
            }
            if (isset($_GET['percentual_min']) && $_GET['percentual_min'] !== '') {
                $where .= " AND COALESCE(t.percentual_calculado, 0) >= ?";
                $params[] = (float)$_GET['percentual_min'];
            }
            if (isset($_GET['percentual_max']) && $_GET['percentual_max'] !== '') {
                $where .= " AND COALESCE(t.percentual_calculado, 0) <= ?";
                $params[] = (float)$_GET['percentual_max'];
            }
            if (!empty($_GET['data_inicio'])) {
                $where .= " AND DATE(t.created_at) >= ?";
                $params[] = $_GET['data_inicio'];
            }
            if (!empty($_GET['data_fim'])) {
                $where .= " AND DATE(t.created_at) <= ?";
                $params[] = $_GET['data_fim'];
            }

            if (!empty($_GET['search'])) {
                $rawSearch = trim((string)$_GET['search']);
                $tokens = preg_split('/\s+/', $rawSearch) ?: [];
                $tokens = array_values(array_filter($tokens, static fn($t) => $t !== ''));

                foreach ($tokens as $token) {
                    $search = '%' . $token . '%';
                    $where .= " AND (
                        CAST(t.id AS CHAR) LIKE ?
                        OR CAST(t.cliente_id AS CHAR) LIKE ?
                        OR t.cliente_nome LIKE ?
                        OR t.codigo_requisicao LIKE ?
                        OR t.filial_registro LIKE ?
                        OR t.colaborador_registro LIKE ?
                        OR t.defeito_nome LIKE ?
                        OR t.fornecedor_nome LIKE ?
                        OR t.toner_modelo LIKE ?
                        OR t.modo LIKE ?
                        OR CAST(t.peso_retornado AS CHAR) LIKE ?
                        OR CAST(t.percentual_informado AS CHAR) LIKE ?
                        OR CAST(t.percentual_calculado AS CHAR) LIKE ?
                        OR t.parecer LIKE ?
                        OR t.destino LIKE ?
                        OR CAST(t.valor_recuperado AS CHAR) LIKE ?
                        OR CAST(t.created_at AS CHAR) LIKE ?
                        OR EXISTS (
                            SELECT 1
                            FROM users u_search
                            WHERE u_search.id = t.created_by
                              AND u_search.name LIKE ?
                        )
                    )";

                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                }
            }

            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM triagem_toners t $where");
            $countStmt->execute($params);
            $total      = (int)$countStmt->fetchColumn();
            $totalPages = (int)ceil($total / $perPage);

            $stmt = $this->db->prepare("
                SELECT t.*,
                       CASE
                           WHEN t.destino = 'Descarte' THEN ROUND(
                               -1 * (
                                   CASE
                                       WHEN COALESCE(t.valor_recuperado, 0) <> 0 THEN ABS(t.valor_recuperado)
                                       ELSE ((COALESCE(t.percentual_calculado, 0) / 100) * COALESCE(tt.capacidade_folhas, 0) * COALESCE(tt.custo_por_folha, 0))
                                   END
                               ),
                               2
                           )
                           WHEN t.destino = 'Estoque' THEN ROUND(
                               CASE
                                   WHEN COALESCE(t.valor_recuperado, 0) <> 0 THEN ABS(t.valor_recuperado)
                                   ELSE ((COALESCE(t.percentual_calculado, 0) / 100) * COALESCE(tt.capacidade_folhas, 0) * COALESCE(tt.custo_por_folha, 0))
                               END,
                               2
                           )
                           ELSE ROUND(COALESCE(t.valor_recuperado, 0), 2)
                       END AS valor_recuperado,
                       CASE
                           WHEN t.destino IN ('Descarte', 'Estoque')
                               THEN ROUND((COALESCE(t.percentual_calculado, 0) / 100) * COALESCE(tt.capacidade_folhas, 0), 0)
                           ELSE 0
                       END AS folhas_equivalentes,
                       COALESCE(t.colaborador_registro, u.name) AS colaborador_registro_nome,
                       COALESCE(t.filial_registro, '') AS filial_registro_nome,
                       u.name  AS criado_por_nome,
                       uu.name AS atualizado_por_nome
                FROM triagem_toners t
                LEFT JOIN toners tt ON tt.id = t.toner_id
                LEFT JOIN users u  ON u.id  = t.created_by
                LEFT JOIN users uu ON uu.id = t.updated_by
                $where
                ORDER BY t.created_at DESC
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute($params);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data'    => $registros,
                'pagination' => [
                    'page'        => $page,
                    'per_page'    => $perPage,
                    'total'       => $total,
                    'total_pages' => $totalPages,
                ],
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // API: Calcular % e parecer (chamado via AJAX antes de salvar)
    public function calcular(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        try {
            $toner_id   = (int)($_POST['toner_id'] ?? 0);
            $modo       = $_POST['modo'] ?? 'peso';
            $peso_ret   = isset($_POST['peso_retornado'])   ? (float)$_POST['peso_retornado']   : null;
            $pct_inf    = isset($_POST['percentual'])       ? (float)$_POST['percentual']       : null;

            if (!$toner_id) {
                echo json_encode(['success' => false, 'message' => 'Selecione um toner.']);
                return;
            }

            $stmt = $this->db->prepare("SELECT peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha, preco_toner FROM toners WHERE id = ?");
            $stmt->execute([$toner_id]);
            $toner = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$toner) {
                echo json_encode(['success' => false, 'message' => 'Toner não encontrado.']);
                return;
            }

            $gramatura_restante  = null;
            $percentual_calculado = null;

            if ($modo === 'peso') {
                if ($peso_ret === null || $peso_ret < 0) {
                    echo json_encode(['success' => false, 'message' => 'Informe o peso retornado.']);
                    return;
                }
                $gramatura_toner    = (float)($toner['gramatura'] ?: ($toner['peso_cheio'] - $toner['peso_vazio']));
                $gramatura_restante = max(0, $peso_ret - (float)$toner['peso_vazio']);
                $percentual_calculado = $gramatura_toner > 0
                    ? min(100, max(0, round(($gramatura_restante / $gramatura_toner) * 100, 2)))
                    : 0;
            } else {
                if ($pct_inf === null || $pct_inf < 0) {
                    echo json_encode(['success' => false, 'message' => 'Informe o percentual.']);
                    return;
                }
                $percentual_calculado = min(100, max(0, round($pct_inf, 2)));
                $gramatura_toner      = (float)($toner['gramatura'] ?: ($toner['peso_cheio'] - $toner['peso_vazio']));
                if ($gramatura_toner > 0) {
                    $gramatura_restante = round(($percentual_calculado / 100) * $gramatura_toner, 2);
                }
            }

            $parecer = $this->getParecer($percentual_calculado);

            $impactoEstoque = $this->calcularImpactoFinanceiro($toner, $percentual_calculado, 'Estoque');
            $impactoDescarte = $this->calcularImpactoFinanceiro($toner, $percentual_calculado, 'Descarte');

            echo json_encode([
                'success'              => true,
                'percentual_calculado' => $percentual_calculado,
                'gramatura_restante'   => $gramatura_restante,
                'parecer'              => $parecer,
                'valor_estoque'        => $impactoEstoque['valor'],
                'valor_descarte'       => $impactoDescarte['valor'],
                'folhas_equivalentes'  => $impactoEstoque['folhas_equivalentes'],
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Salvar registro de triagem
    public function store(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para criar.']);
            return;
        }

        try {
            $toner_id    = (int)($_POST['toner_id'] ?? 0);
            $cliente_id  = (int)($_POST['cliente_id'] ?? 0);
            $modo        = $_POST['modo'] ?? 'peso';
            $peso_ret    = isset($_POST['peso_retornado']) && $_POST['peso_retornado'] !== '' ? (float)$_POST['peso_retornado'] : null;
            $pct_inf     = isset($_POST['percentual'])     && $_POST['percentual'] !== ''     ? (float)$_POST['percentual']     : null;
            $destino     = $_POST['destino']     ?? '';
            $fornecedorId = isset($_POST['fornecedor_id']) && $_POST['fornecedor_id'] !== '' ? (int)$_POST['fornecedor_id'] : null;
            $codigoRequisicao = trim($_POST['codigo_requisicao'] ?? '');
            $defeitoId   = isset($_POST['defeito_id']) && $_POST['defeito_id'] !== '' ? (int)$_POST['defeito_id'] : null;
            $observacoes = $_POST['observacoes'] ?? '';
            $filialRegistro = $_SESSION['user_filial'] ?? '';
            $colaboradorRegistro = $_SESSION['user_name'] ?? '';
            
            $tonersDefeitosIds = $_POST['toners_defeitos_ids'] ?? [];
            if (!is_array($tonersDefeitosIds)) {
                $tonersDefeitosIds = $tonersDefeitosIds ? [$tonersDefeitosIds] : [];
            }

            if (!$toner_id || !$cliente_id || !$destino) {
                echo json_encode(['success' => false, 'message' => 'Preencha cliente, toner e destino.']);
                return;
            }

            if ($destino === 'Garantia' && !$fornecedorId) {
                echo json_encode(['success' => false, 'message' => 'Para Garantia, é obrigatório selecionar o Fornecedor.']);
                return;
            }

            $stmtCliente = $this->db->prepare("SELECT id, nome FROM clientes WHERE id = ? LIMIT 1");
            $stmtCliente->execute([$cliente_id]);
            $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                echo json_encode(['success' => false, 'message' => 'Cliente não encontrado.']);
                return;
            }

            $fornecedorNome = null;
            if ($fornecedorId) {
                $stmtForn = $this->db->prepare("SELECT nome FROM fornecedores WHERE id = ?");
                $stmtForn->execute([$fornecedorId]);
                $f = $stmtForn->fetch(PDO::FETCH_ASSOC);
                if ($f) {
                    $fornecedorNome = $f['nome'];
                }
            }

            $defeitoNome = null;
            if ($defeitoId) {
                $stmtDef = $this->db->prepare("SELECT nome_defeito FROM cadastro_defeitos WHERE id = ?");
                $stmtDef->execute([$defeitoId]);
                $defeito = $stmtDef->fetch(PDO::FETCH_ASSOC);
                if (!$defeito) {
                    echo json_encode(['success' => false, 'message' => 'Defeito selecionado não encontrado.']);
                    return;
                }
                $defeitoNome = $defeito['nome_defeito'];
            }

            $stmt = $this->db->prepare("SELECT modelo, peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE id = ?");
            $stmt->execute([$toner_id]);
            $toner = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$toner) {
                echo json_encode(['success' => false, 'message' => 'Toner não encontrado.']);
                return;
            }

            // Block validation: if a requisition code exists, and there are defects matching this code,
            // ensure the selected toner model belongs to that requisition code.
            if (!empty($codigoRequisicao)) {
                $stmtChkDef = $this->db->prepare("SELECT id FROM toners_defeitos WHERE numero_pedido = ? LIMIT 1");
                $stmtChkDef->execute([$codigoRequisicao]);
                if ($stmtChkDef->fetch()) {
                    // Validar se o modelo do toner é permitido para este número de pedido
                    $stmtMod = $this->db->prepare("SELECT id FROM toners_defeitos WHERE numero_pedido = ? AND modelo_toner = ? LIMIT 1");
                    $stmtMod->execute([$codigoRequisicao, $toner['modelo']]);
                    if (!$stmtMod->fetch()) {
                        echo json_encode(['success' => false, 'message' => "O modelo de toner selecionado ('{$toner['modelo']}') não está vinculado a este pedido de defeito ($codigoRequisicao)."]);
                        return;
                    }
                }
            }

            $gramatura_restante   = null;
            $percentual_calculado = 0;
            $gramatura_toner      = (float)($toner['gramatura'] ?: ($toner['peso_cheio'] - $toner['peso_vazio']));

            if ($modo === 'peso' && $peso_ret !== null) {
                $gramatura_restante   = max(0, $peso_ret - (float)$toner['peso_vazio']);
                $percentual_calculado = $gramatura_toner > 0
                    ? min(100, max(0, round(($gramatura_restante / $gramatura_toner) * 100, 2))) : 0;
            } elseif ($modo === 'percentual' && $pct_inf !== null) {
                $percentual_calculado = min(100, max(0, round($pct_inf, 2)));
                if ($gramatura_toner > 0) {
                    $gramatura_restante = round(($percentual_calculado / 100) * $gramatura_toner, 2);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Informe o peso ou o percentual.']);
                return;
            }

            $parecer = $this->getParecer($percentual_calculado);

            $impactoFinanceiro = $this->calcularImpactoFinanceiro($toner, $percentual_calculado, $destino);
            $valor_recuperado = $impactoFinanceiro['valor'];

            $insert = $this->db->prepare("
                INSERT INTO triagem_toners
                    (toner_id, toner_modelo, cliente_id, cliente_nome, fornecedor_id, fornecedor_nome, filial_registro, colaborador_registro, codigo_requisicao, defeito_id, defeito_nome,
                     modo, peso_retornado, percentual_informado,
                     gramatura_restante, percentual_calculado, parecer, destino,
                     valor_recuperado, observacoes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $percentualParaSalvar = $modo === 'percentual' ? $pct_inf : $percentual_calculado;
            $insert->execute([
                $toner_id,
                $toner['modelo'],
                $cliente_id,
                $cliente['nome'],
                $fornecedorId,
                $fornecedorNome,
                $filialRegistro !== '' ? $filialRegistro : null,
                $colaboradorRegistro !== '' ? $colaboradorRegistro : null,
                $codigoRequisicao !== '' ? $codigoRequisicao : null,
                $defeitoId,
                $defeitoNome,
                $modo,
                $peso_ret,
                $percentualParaSalvar,
                $gramatura_restante,
                $percentual_calculado,
                $parecer,
                $destino,
                $valor_recuperado,
                $observacoes,
                $_SESSION['user_id'],
            ]);

            $this->syncDevolutiva($codigoRequisicao !== '' ? $codigoRequisicao : null, $destino, $parecer, $_SESSION['user_id'], $defeitoNome, $tonersDefeitosIds, $observacoes);

            echo json_encode(['success' => true, 'message' => 'Triagem registrada com sucesso!', 'id' => $this->db->lastInsertId()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Atualizar registro
    public function update(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para editar.']);
            return;
        }

        try {
            $id          = (int)($_POST['id'] ?? 0);
            $toner_id    = (int)($_POST['toner_id'] ?? 0);
            $cliente_id  = (int)($_POST['cliente_id'] ?? 0);
            $modo        = $_POST['modo'] ?? 'peso';
            $peso_ret    = isset($_POST['peso_retornado']) && $_POST['peso_retornado'] !== '' ? (float)$_POST['peso_retornado'] : null;
            $pct_inf     = isset($_POST['percentual'])     && $_POST['percentual'] !== ''     ? (float)$_POST['percentual']     : null;
            $destino     = $_POST['destino']     ?? '';
            $fornecedorId = isset($_POST['fornecedor_id']) && $_POST['fornecedor_id'] !== '' ? (int)$_POST['fornecedor_id'] : null;
            $codigoRequisicao = trim($_POST['codigo_requisicao'] ?? '');
            $defeitoId   = isset($_POST['defeito_id']) && $_POST['defeito_id'] !== '' ? (int)$_POST['defeito_id'] : null;
            $observacoes = $_POST['observacoes'] ?? null;
            $filialRegistro = trim((string)($_SESSION['user_filial'] ?? ''));
            $colaboradorRegistro = trim((string)($_SESSION['user_name'] ?? ''));
            
            $tonersDefeitosIds = $_POST['toners_defeitos_ids'] ?? [];
            if (!is_array($tonersDefeitosIds)) {
                $tonersDefeitosIds = $tonersDefeitosIds ? [$tonersDefeitosIds] : [];
            }

            if (!$id || !$toner_id || !$cliente_id || !$destino) {
                echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
                return;
            }

            $fornecedorNome = null;
            if ($destino === 'Garantia') {
                if (!$fornecedorId) {
                    echo json_encode(['success' => false, 'message' => 'Selecione o fornecedor para destino Garantia.']);
                    return;
                }
                $stmtFornecedor = $this->db->prepare("SELECT id, nome FROM fornecedores WHERE id = ? LIMIT 1");
                $stmtFornecedor->execute([$fornecedorId]);
                $fornecedor = $stmtFornecedor->fetch(PDO::FETCH_ASSOC);
                if (!$fornecedor) {
                    echo json_encode(['success' => false, 'message' => 'Fornecedor selecionado não encontrado.']);
                    return;
                }
                $fornecedorId = (int)$fornecedor['id'];
                $fornecedorNome = $fornecedor['nome'];
            } else {
                $fornecedorId = null;
            }

            $stmtCliente = $this->db->prepare("SELECT id, nome FROM clientes WHERE id = ?");
            $stmtCliente->execute([$cliente_id]);
            $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
            if (!$cliente) {
                echo json_encode(['success' => false, 'message' => 'Cliente não encontrado.']);
                return;
            }

            $defeitoNome = null;
            if ($defeitoId) {
                $stmtDefeito = $this->db->prepare("SELECT id, nome_defeito FROM cadastro_defeitos WHERE id = ? LIMIT 1");
                $stmtDefeito->execute([$defeitoId]);
                $defeito = $stmtDefeito->fetch(PDO::FETCH_ASSOC);
                if (!$defeito) {
                    echo json_encode(['success' => false, 'message' => 'Defeito selecionado não encontrado.']);
                    return;
                }
                $defeitoNome = $defeito['nome_defeito'];
            }

            $stmt = $this->db->prepare("SELECT modelo, peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE id = ?");
            $stmt->execute([$toner_id]);
            $toner = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$toner) {
                echo json_encode(['success' => false, 'message' => 'Toner não encontrado.']);
                return;
            }

            // Block validation: se existe código de requisição, checar modelo correspondente
            if (!empty($codigoRequisicao)) {
                $stmtChkDef = $this->db->prepare("SELECT id FROM toners_defeitos WHERE numero_pedido = ? LIMIT 1");
                $stmtChkDef->execute([$codigoRequisicao]);
                if ($stmtChkDef->fetch()) {
                    $stmtMod = $this->db->prepare("SELECT id FROM toners_defeitos WHERE numero_pedido = ? AND modelo_toner = ? LIMIT 1");
                    $stmtMod->execute([$codigoRequisicao, $toner['modelo']]);
                    if (!$stmtMod->fetch()) {
                        echo json_encode(['success' => false, 'message' => "O modelo de toner selecionado ('{$toner['modelo']}') não está vinculado a este pedido de defeito ($codigoRequisicao)."]);
                        return;
                    }
                }
            }

            $gramatura_toner      = (float)($toner['gramatura'] ?: ($toner['peso_cheio'] - $toner['peso_vazio']));
            $gramatura_restante   = null;
            $percentual_calculado = 0;

            if ($modo === 'peso' && $peso_ret !== null) {
                $gramatura_restante   = max(0, $peso_ret - (float)$toner['peso_vazio']);
                $percentual_calculado = $gramatura_toner > 0
                    ? min(100, max(0, round(($gramatura_restante / $gramatura_toner) * 100, 2))) : 0;
            } elseif ($modo === 'percentual' && $pct_inf !== null) {
                $percentual_calculado = min(100, max(0, round($pct_inf, 2)));
                if ($gramatura_toner > 0) {
                    $gramatura_restante = round(($percentual_calculado / 100) * $gramatura_toner, 2);
                }
            }

            $parecer = $this->getParecer($percentual_calculado);

            $impactoFinanceiro = $this->calcularImpactoFinanceiro($toner, $percentual_calculado, $destino);
            $valor_recuperado = $impactoFinanceiro['valor'];

            $upd = $this->db->prepare("
                UPDATE triagem_toners SET
                    toner_id = ?, toner_modelo = ?, cliente_id = ?, cliente_nome = ?, codigo_requisicao = ?,
                    fornecedor_id = ?, fornecedor_nome = ?,
                    filial_registro = COALESCE(filial_registro, ?), colaborador_registro = COALESCE(colaborador_registro, ?),
                    defeito_id = ?, defeito_nome = ?, modo = ?,
                    peso_retornado = ?, percentual_informado = ?,
                    gramatura_restante = ?, percentual_calculado = ?,
                    parecer = ?, destino = ?, valor_recuperado = ?,
                    observacoes = ?, updated_by = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $percentualParaSalvar = $modo === 'percentual' ? $pct_inf : $percentual_calculado;
            $upd->execute([
                $toner_id, $toner['modelo'], $cliente_id, $cliente['nome'], $codigoRequisicao !== '' ? $codigoRequisicao : null,
                $fornecedorId, $fornecedorNome,
                $filialRegistro !== '' ? $filialRegistro : null,
                $colaboradorRegistro !== '' ? $colaboradorRegistro : null,
                $defeitoId, $defeitoNome, $modo,
                $peso_ret, $percentualParaSalvar,
                $gramatura_restante, $percentual_calculado,
                $parecer, $destino, $valor_recuperado,
                $observacoes, $_SESSION['user_id'], $id,
            ]);

            // Sync since we update triage
            $this->syncDevolutiva($codigoRequisicao !== '' ? $codigoRequisicao : null, $destino, $parecer, $_SESSION['user_id'], $defeitoNome, $tonersDefeitosIds, $observacoes);

            echo json_encode(['success' => true, 'message' => 'Registro atualizado com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Duplicar registro
    public function duplicate(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para duplicar.']);
            return;
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            $novoClienteId = isset($_POST['cliente_id']) && $_POST['cliente_id'] !== '' ? (int)$_POST['cliente_id'] : null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                return;
            }

            $stmt = $this->db->prepare("SELECT * FROM triagem_toners WHERE id = ?");
            $stmt->execute([$id]);
            $original = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$original) {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado.']);
                return;
            }

            $observacoes = $original['observacoes'] ?? '';
            $prefixoDuplicado = "[Duplicado do registro #{$id}]";
            $observacoes = trim($prefixoDuplicado . ' ' . $observacoes);

            $clienteIdFinal = (int)($original['cliente_id'] ?? 0);
            $clienteNomeFinal = $original['cliente_nome'] ?? null;
            if ($novoClienteId !== null) {
                $stmtCliente = $this->db->prepare("SELECT id, nome FROM clientes WHERE id = ? LIMIT 1");
                $stmtCliente->execute([$novoClienteId]);
                $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);
                if (!$cliente) {
                    echo json_encode(['success' => false, 'message' => 'Cliente selecionado para duplicação não foi encontrado.']);
                    return;
                }
                $clienteIdFinal = (int)$cliente['id'];
                $clienteNomeFinal = $cliente['nome'];
            }

            $insert = $this->db->prepare("
                INSERT INTO triagem_toners (
                    toner_id, toner_modelo, cliente_id, cliente_nome, fornecedor_id, fornecedor_nome, filial_registro, colaborador_registro, codigo_requisicao,
                    defeito_id, defeito_nome, modo,
                    peso_retornado, percentual_informado, gramatura_restante,
                    percentual_calculado, parecer, destino, valor_recuperado,
                    observacoes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $insert->execute([
                $original['toner_id'],
                $original['toner_modelo'],
                $clienteIdFinal,
                $clienteNomeFinal,
                $original['fornecedor_id'] ?? null,
                $original['fornecedor_nome'] ?? null,
                $original['filial_registro'] ?? ($_SESSION['user_filial'] ?? null),
                $original['colaborador_registro'] ?? ($_SESSION['user_name'] ?? null),
                $original['codigo_requisicao'] ?? null,
                $original['defeito_id'] ?? null,
                $original['defeito_nome'] ?? null,
                $original['modo'],
                $original['peso_retornado'],
                $original['percentual_informado'],
                $original['gramatura_restante'],
                $original['percentual_calculado'],
                $original['parecer'],
                $original['valor_recuperado'],
                $observacoes,
                $_SESSION['user_id'],
            ]);

            $this->syncDevolutiva($original['codigo_requisicao'] ?? null, $original['destino'], $original['parecer'], $_SESSION['user_id'], $original['defeito_nome'] ?? null, [], $observacoes);

            echo json_encode([
                'success' => true,
                'message' => 'Registro duplicado com sucesso!',
                'id' => $this->db->lastInsertId(),
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Excluir registro
    public function delete(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        if (!PermissionService::hasPermission($_SESSION['user_id'], 'triagem_toners', 'delete')) {
            echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir.']);
            return;
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                return;
            }

            // Fetch the triagem first to get the codigo_requisicao
            $stmt = $this->db->prepare("SELECT codigo_requisicao FROM triagem_toners WHERE id = ?");
            $stmt->execute([$id]);
            $triagem = $stmt->fetch(PDO::FETCH_ASSOC);

            // Exclui a triagem
            $this->db->prepare("DELETE FROM triagem_toners WHERE id = ?")->execute([$id]);

            // Clear Devolutiva from matching toners com defeito
            if ($triagem && !empty($triagem['codigo_requisicao'])) {
                 $this->clearDevolutivaByCodigo($triagem['codigo_requisicao']);
            }

            echo json_encode(['success' => true, 'message' => 'Registro excluído com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function clearDevolutivaByCodigo(string $codigoRequisicao): void 
    {
        try {
            $upd = $this->db->prepare("
                UPDATE toners_defeitos SET 
                    devolutiva_descricao = NULL, 
                    devolutiva_resultado = NULL,
                    devolutiva_at = NULL,
                    devolutiva_uid = NULL,
                    devolutiva_foto1 = NULL,
                    devolutiva_foto2 = NULL,
                    devolutiva_foto3 = NULL
                WHERE numero_pedido = ?
            ");
            $upd->execute([$codigoRequisicao]);
        } catch (\Exception $e) {
            error_log('Erro ao limpar devolutiva do toner com defeito: ' . $e->getMessage());
        }
    }

    // ===== PARÂMETROS =====

    public function getParametrosApi(): void
    {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $this->getParametros()]);
    }

    public function saveParametros(): void
    {
        ob_clean();
        header('Content-Type: application/json');

        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, ['admin', 'super_admin'])) {
            echo json_encode(['success' => false, 'message' => 'Apenas admin pode alterar parâmetros.']);
            return;
        }

        try {
            $parametros = json_decode(file_get_contents('php://input'), true);
            if (!is_array($parametros) || empty($parametros)) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
                return;
            }

            $this->db->exec("DELETE FROM triagem_toners_parametros");
            $stmt = $this->db->prepare("
                INSERT INTO triagem_toners_parametros (percentual_min, percentual_max, parecer, ordem, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($parametros as $i => $p) {
                $stmt->execute([
                    (float)$p['percentual_min'],
                    (float)$p['percentual_max'],
                    trim($p['parecer']),
                    $i + 1,
                    $_SESSION['user_id'],
                ]);
            }

            echo json_encode(['success' => true, 'message' => 'Parâmetros salvos com sucesso!']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ===== HELPERS =====

    private function getParametros(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM triagem_toners_parametros ORDER BY ordem ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getParecer(float $percentual): string
    {
        $parametros = $this->getParametros();
        foreach ($parametros as $p) {
            if ($percentual >= (float)$p['percentual_min'] && $percentual <= (float)$p['percentual_max']) {
                return $p['parecer'];
            }
        }
        return 'Sem parecer definido para este percentual. Verifique os parâmetros de triagem.';
    }

    private function normalizeDestino(string $destino): ?string
    {
        $d = mb_strtolower(trim($destino));
        return match ($d) {
            'descarte' => 'Descarte',
            'garantia' => 'Garantia',
            'uso interno', 'uso_interno' => 'Uso Interno',
            'estoque' => 'Estoque',
            default => null,
        };
    }

    private function findClienteByCodigoOrNome(string $codigoOuNome): ?array
    {
        $valor = trim($codigoOuNome);

        $stmt = $this->db->prepare("SELECT id, nome FROM clientes WHERE codigo = ? LIMIT 1");
        $stmt->execute([$valor]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cliente) {
            return $cliente;
        }

        $stmt = $this->db->prepare("SELECT id, nome FROM clientes WHERE LOWER(nome) = LOWER(?) LIMIT 1");
        $stmt->execute([$valor]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        return $cliente ?: null;
    }

    private function readSpreadsheetRows(string $filePath, string $extension): array
    {
        if ($extension === 'csv') {
            $rows = [];
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                return [];
            }

            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                rewind($handle);
            }

            while (($line = fgetcsv($handle, 0, ';')) !== false) {
                if (count($line) === 1 && str_contains((string)$line[0], ',')) {
                    $line = str_getcsv($line[0], ',');
                }
                $rows[] = $line;
            }
            fclose($handle);
            return $rows;
        }

        // XLS/XLSX com PhpSpreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $rows;
    }

    /**
     * Sincroniza o resultado da triagem com a devolutiva do Toner com Defeito correspondente
     */
    private function syncDevolutiva(?string $codigoRequisicao, ?string $destino, string $parecer, int $userId, ?string $defeitoNome = null, array $tonersDefeitosIds = [], ?string $observacoes = null): void
    {
        if (empty($codigoRequisicao)) {
            return;
        }

        try {
            $destinoTexto = $destino ?: 'Não informado';
            // User requested that the description matches the triage observations
            $descricao = !empty($observacoes) ? trim($observacoes) : "Preenchido automaticamente via Triagem. Destino: {$destinoTexto}. Parecer: {$parecer}";
            
            // Allow exact defect name from Triage if set, else fallback
            $resultado = $defeitoNome ?: 'DEFEITO_PROCEDENTE'; 
            
            if (in_array($destinoTexto, ['Uso Interno', 'Estoque'])) {
                $resultado = 'TONER_SEM_DEFEITO'; 
            } elseif (($destinoTexto === 'Descarte' || $destinoTexto === 'Garantia') && !$defeitoNome) {
                // Keep default procedence identifier if no select was made and destino relates to defective scenario
                $resultado = 'DEFEITO_PROCEDENTE';
            }

            if (!empty($tonersDefeitosIds)) {
                // Update only specific IDs
                $inQuery = implode(',', array_fill(0, count($tonersDefeitosIds), '?'));
                $sql = "UPDATE toners_defeitos SET 
                            devolutiva_descricao = ?, 
                            devolutiva_resultado = ?,
                            devolutiva_at = NOW(),
                            devolutiva_uid = ?
                        WHERE id IN ($inQuery) AND numero_pedido = ?";
                $params = array_merge([$descricao, $resultado, $userId], array_values($tonersDefeitosIds), [$codigoRequisicao]);
                $upd = $this->db->prepare($sql);
                $upd->execute($params);
            } else {
                // Update all matching by numero_pedido (legacy behavior, or if they skipped selecting)
                $upd = $this->db->prepare("
                    UPDATE toners_defeitos SET 
                        devolutiva_descricao = ?, 
                        devolutiva_resultado = ?,
                        devolutiva_at = NOW(),
                        devolutiva_uid = ?
                    WHERE numero_pedido = ?
                ");
                $upd->execute([
                    $descricao,
                    $resultado,
                    $userId,
                    $codigoRequisicao
                ]);
            }
        } catch (\Exception $e) {
            error_log('Erro ao sincronizar devolutiva do toner com defeito: ' . $e->getMessage() . ' - Stack Trace: ' . $e->getTraceAsString());
        }
    }
}
