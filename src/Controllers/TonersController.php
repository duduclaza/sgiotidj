<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class TonersController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function cadastro(): void
    {
        try {
            // Get ALL toners (sem paginaÃ§Ã£o)
            $stmt = $this->db->query('
                SELECT * FROM toners 
                ORDER BY modelo
            ');
            $toners = $stmt->fetchAll();
            
        } catch (\PDOException $e) {
            $toners = [];
        }
        
        $this->render('toners/cadastro', [
            'title' => 'Cadastro de Toners', 
            'toners' => $toners
        ]);
    }

    public function retornados(): void
    {
        try {
            // Get all toners for modelo dropdown
            $stmt = $this->db->query('SELECT modelo FROM toners ORDER BY modelo');
            $toners = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Get all filiais for filial dropdown
            $stmt = $this->db->query('SELECT nome FROM filiais ORDER BY nome');
            $filiais = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Get pagination parameters
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 100;
            $offset = ($page - 1) * $perPage;
            
            // Get total count for pagination
            $countStmt = $this->db->query('SELECT COUNT(*) FROM retornados');
            $totalRecords = $countStmt->fetchColumn();
            $totalPages = ceil($totalRecords / $perPage);
            
            // Get paginated retornados for grid
            $stmt = $this->db->prepare('
                SELECT id, modelo, codigo_cliente, usuario, filial, destino, 
                       data_registro, modelo_cadastrado, valor_calculado, observacao, quantidade
                FROM retornados 
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset
            ');
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $retornados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->render('toners/retornados', [
                'title' => 'Registro de Retornados',
                'toners' => $toners,
                'filiais' => $filiais,
                'retornados' => $retornados,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $totalRecords,
                    'per_page' => $perPage,
                    'has_prev' => $page > 1,
                    'has_next' => $page < $totalPages
                ]
            ]);
        } catch (\PDOException $e) {
            $this->render('toners/retornados', [
                'title' => 'Registro de Retornados',
                'toners' => [],
                'filiais' => [],
                'retornados' => [],
                'pagination' => [
                    'current_page' => 1,
                    'total_pages' => 1,
                    'total_records' => 0,
                    'per_page' => 100,
                    'has_prev' => false,
                    'has_next' => false
                ],
                'error' => 'Erro ao carregar dados: ' . $e->getMessage()
            ]);
        }
    }

    public function getTonerData(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        
        // Se nÃ£o hÃ¡ parÃ¢metro modelo, retorna todos os toners para dropdown
        $modelo = $_GET['modelo'] ?? '';
        
        if (empty($modelo)) {
            try {
                // Tentar conectar ao banco
                if (!$this->db) {
                    echo json_encode(['error' => 'ConexÃ£o com banco nÃ£o disponÃ­vel']);
                    return;
                }
                
                $stmt = $this->db->query('SELECT id, modelo, gramatura, peso_cheio, peso_vazio, preco_toner as valor, capacidade_folhas as rendimento FROM toners ORDER BY modelo');
                $toners = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Log para debug
                error_log('API /api/toner: Retornando ' . count($toners) . ' toners');
                
                echo json_encode($toners);
                return;
            } catch (\PDOException $e) {
                error_log('Erro ao buscar toners: ' . $e->getMessage());
                
                // Retornar dados mock para teste local
                $mockToners = [
                    ['id' => 1, 'modelo' => 'HP CF280A', 'gramatura' => 300, 'peso_cheio' => 350, 'peso_vazio' => 50, 'valor' => 89.90, 'rendimento' => 2700],
                    ['id' => 2, 'modelo' => 'HP CE285A', 'gramatura' => 280, 'peso_cheio' => 330, 'peso_vazio' => 50, 'valor' => 79.90, 'rendimento' => 1600],
                    ['id' => 3, 'modelo' => 'Canon 728', 'gramatura' => 250, 'peso_cheio' => 300, 'peso_vazio' => 50, 'valor' => 69.90, 'rendimento' => 2100]
                ];
                
                error_log('API /api/toner: Usando dados mock - ' . count($mockToners) . ' toners');
                echo json_encode($mockToners);
                return;
            }
        }

        // Se hÃ¡ parÃ¢metro modelo, retorna dados especÃ­ficos do toner
        try {
            $stmt = $this->db->prepare('SELECT gramatura, peso_vazio, preco_toner as preco FROM toners WHERE modelo = ?');
            $stmt->execute([$modelo]);
            $toner = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($toner) {
                echo json_encode([
                    'success' => true,
                    'toner' => [
                        'gramatura' => (float)$toner['gramatura'],
                        'peso_vazio' => (float)$toner['peso_vazio'],
                        'preco' => (float)$toner['preco']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Toner nÃ£o encontrado']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Erro ao buscar dados do toner']);
        }
    }

    public function getParameters(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->query('SELECT nome, faixa_min as percentual_min, faixa_max as percentual_max, orientacao FROM parametros_retornados ORDER BY faixa_min');
            $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'parameters' => $parameters
            ]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Erro ao buscar parÃ¢metros']);
        }
    }

    public function storeRetornado(): void
    {
        header('Content-Type: application/json');
        
        try {
            // Log dos dados recebidos para debug
            error_log('POST data received: ' . print_r($_POST, true));
            
            $modelo = trim($_POST['modelo'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $filial = trim($_POST['filial'] ?? '');
            $codigo_cliente = trim($_POST['codigo_cliente'] ?? '');
            $modo = trim($_POST['modo'] ?? '');
            $peso_retornado = $_POST['peso_retornado'] ?? null;
            $percentual_chip = $_POST['percentual_chip'] ?? null;
            $destino = trim($_POST['destino'] ?? '');
            $observacao = trim($_POST['observacao'] ?? '');
            $data_registro = $_POST['data_registro'] ?? date('Y-m-d');

            // Debug dos campos
            error_log("ValidaÃ§Ã£o - modelo: '$modelo', usuario: '$usuario', filial: '$filial', codigo_cliente: '$codigo_cliente', modo: '$modo', destino: '$destino'");

            // Validate required fields
            if (empty($modelo) || empty($usuario) || empty($filial) || empty($codigo_cliente) || empty($modo) || empty($destino)) {
                $missing = [];
                if (empty($modelo)) $missing[] = 'modelo';
                if (empty($usuario)) $missing[] = 'usuario';
                if (empty($filial)) $missing[] = 'filial';
                if (empty($codigo_cliente)) $missing[] = 'codigo_cliente';
                if (empty($modo)) $missing[] = 'modo';
                if (empty($destino)) $missing[] = 'destino';
                
                error_log('Campos faltando: ' . implode(', ', $missing));
                echo json_encode(['success' => false, 'message' => 'Campos obrigatÃ³rios faltando: ' . implode(', ', $missing)]);
                return;
            }

            // Check if modelo exists in toners table (prefer ID if available, fallback to name)
            $modelo_id = $_POST['modelo_id'] ?? null;
            
            if ($modelo_id) {
                // Use ID if provided
                $stmt = $this->db->prepare('SELECT peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE id = :modelo_id');
                $stmt->execute([':modelo_id' => $modelo_id]);
                error_log('Buscando modelo por ID: ' . $modelo_id);
            } else {
                // Fallback to search by name
                $stmt = $this->db->prepare('SELECT peso_cheio, peso_vazio, gramatura, capacidade_folhas, custo_por_folha FROM toners WHERE modelo = :modelo');
                $stmt->execute([':modelo' => $modelo]);
                error_log('Buscando modelo por nome: ' . $modelo);
            }
            
            $tonerData = $stmt->fetch(PDO::FETCH_ASSOC);
            $modelo_cadastrado = $tonerData ? 1 : 0;
            
            // Log para debug
            error_log('Verificando modelo cadastrado: ' . $modelo . ' (ID: ' . ($modelo_id ?: 'N/A') . ') - Encontrado: ' . ($modelo_cadastrado ? 'SIM' : 'NÃƒO'));
            $gramatura_existente = null;
            $percentual_restante = null;
            $valor_calculado = 0.00;

            // Calculate based on mode
            if ($modo === 'peso' && $peso_retornado > 0 && $tonerData) {
                $gramatura_existente = max(0, $peso_retornado - $tonerData['peso_vazio']);
                $percentual_restante = $tonerData['gramatura'] > 0 ? 
                    min(100, max(0, ($gramatura_existente / $tonerData['gramatura']) * 100)) : 0;
                    
                error_log('CÃ¡lculo por peso: Peso=' . $peso_retornado . 'g, Vazio=' . $tonerData['peso_vazio'] . 'g, Gramatura=' . $gramatura_existente . 'g, Percentual=' . $percentual_restante . '%');
            } elseif ($modo === 'chip' && $percentual_chip >= 0) {
                $percentual_restante = max(0, min(100, $percentual_chip));
                if ($tonerData && $tonerData['gramatura'] > 0) {
                    $gramatura_existente = ($percentual_restante / 100) * $tonerData['gramatura'];
                }
                
                error_log('CÃ¡lculo por chip: Percentual=' . $percentual_restante . '%, Gramatura=' . ($gramatura_existente ?? 'N/A') . 'g');
            }

            // Calculate value if destino is estoque
            if ($destino === 'estoque' && $percentual_restante > 0 && $tonerData) {
                $capacidade_folhas = $tonerData['capacidade_folhas'] ?? 0;
                $custo_por_folha = $tonerData['custo_por_folha'] ?? 0;
                
                if ($capacidade_folhas > 0 && $custo_por_folha > 0) {
                    $folhas_restantes = ($percentual_restante / 100) * $capacidade_folhas;
                    $valor_calculado = $folhas_restantes * $custo_por_folha;
                    
                    error_log('CÃ¡lculo de valor para estoque: ' . 
                        'Percentual: ' . $percentual_restante . '% | ' .
                        'Capacidade: ' . $capacidade_folhas . ' folhas | ' .
                        'Custo por folha: R$ ' . $custo_por_folha . ' | ' .
                        'Folhas restantes: ' . $folhas_restantes . ' | ' .
                        'Valor calculado: R$ ' . $valor_calculado
                    );
                } else {
                    error_log('NÃ£o foi possÃ­vel calcular valor - dados faltando: ' .
                        'Capacidade: ' . $capacidade_folhas . ' | ' .
                        'Custo: ' . $custo_por_folha
                    );
                }
            } else {
                error_log('CÃ¡lculo de valor nÃ£o executado - CondiÃ§Ãµes: ' .
                    'Destino: ' . $destino . ' | ' .
                    'Percentual: ' . $percentual_restante . ' | ' .
                    'TonerData: ' . ($tonerData ? 'OK' : 'NULL')
                );
            }

            // Get quantidade from POST
            $quantidade = max(1, (int)($_POST['quantidade'] ?? 1));

            // Multiplicar valor calculado pela quantidade
            $valor_total = $valor_calculado * $quantidade;

            error_log('Valor final: UnitÃ¡rio R$ ' . number_format($valor_calculado, 2, ',', '.') . 
                      ' x ' . $quantidade . ' = R$ ' . number_format($valor_total, 2, ',', '.'));

            // Insert into database
            $stmt = $this->db->prepare('
                INSERT INTO retornados (modelo, modelo_cadastrado, usuario, filial, codigo_cliente, modo, 
                                      peso_retornado, percentual_chip, gramatura_existente, percentual_restante, 
                                      destino, valor_calculado, observacao, data_registro, quantidade) 
                VALUES (:modelo, :modelo_cadastrado, :usuario, :filial, :codigo_cliente, :modo, 
                        :peso_retornado, :percentual_chip, :gramatura_existente, :percentual_restante, 
                        :destino, :valor_calculado, :observacao, :data_registro, :quantidade)
            ');
            
            $stmt->execute([
                ':modelo' => $modelo,
                ':modelo_cadastrado' => (int)$modelo_cadastrado,
                ':usuario' => $usuario,
                ':filial' => $filial,
                ':codigo_cliente' => $codigo_cliente,
                ':modo' => $modo,
                ':peso_retornado' => $peso_retornado ?: null,
                ':percentual_chip' => $percentual_chip ?: null,
                ':gramatura_existente' => $gramatura_existente ?: null,
                ':percentual_restante' => $percentual_restante ?: null,
                ':quantidade' => $quantidade,
                ':destino' => $destino,
                ':valor_calculado' => $valor_total,
                ':observacao' => $observacao,
                ':data_registro' => $data_registro
            ]);

            error_log('Retornado inserido - Destino: ' . $destino . ' | Quantidade: ' . $quantidade . ' | Valor Total: R$ ' . number_format($valor_total, 2, ',', '.'));

            echo json_encode([
                'success' => true,
                'message' => 'Retornado registrado com sucesso!',
                'data' => [
                    'percentual_restante' => $percentual_restante,
                    'valor_calculado' => $valor_calculado,
                    'modelo_cadastrado' => $modelo_cadastrado
                ]
            ]);

        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar: ' . $e->getMessage()]);
        }
    }

    public function store(): void
    {
        $modelo = trim($_POST['modelo'] ?? '');
        $peso_cheio = !empty($_POST['peso_cheio']) ? (float)$_POST['peso_cheio'] : null;
        $peso_vazio = !empty($_POST['peso_vazio']) ? (float)$_POST['peso_vazio'] : null;
        $capacidade_folhas = (int)($_POST['capacidade_folhas'] ?? 0);
        $preco_toner = (float)($_POST['preco_toner'] ?? 0);
        $cor = $_POST['cor'] ?? '';
        $tipo = $_POST['tipo'] ?? '';

        // Validar campos obrigatÃ³rios (pesos sÃ£o opcionais)
        if ($modelo === '' || $capacidade_folhas <= 0 || $preco_toner <= 0 || $cor === '' || $tipo === '') {
            flash('error', 'Campos obrigatÃ³rios: Modelo, Capacidade de Folhas, PreÃ§o, Cor e Tipo.');
            redirect('/toners/cadastro');
            return;
        }

        // Se um dos pesos foi informado, ambos devem ser informados
        if (($peso_cheio !== null && $peso_vazio === null) || ($peso_cheio === null && $peso_vazio !== null)) {
            flash('error', 'Se informar peso, ambos Peso Cheio e Peso Vazio devem ser preenchidos.');
            redirect('/toners/cadastro');
            return;
        }

        // Se ambos os pesos foram informados, validar que peso cheio > peso vazio
        if ($peso_cheio !== null && $peso_vazio !== null && $peso_cheio <= $peso_vazio) {
            flash('error', 'O peso cheio deve ser maior que o peso vazio.');
            redirect('/toners/cadastro');
            return;
        }

        // Calcular campos derivados
        $gramatura = null;
        $gramatura_por_folha = null;
        $custo_por_folha = null;

        if ($peso_cheio !== null && $peso_vazio !== null) {
            $gramatura = $peso_cheio - $peso_vazio;
        }

        if ($gramatura !== null && $capacidade_folhas > 0) {
            $gramatura_por_folha = $gramatura / $capacidade_folhas;
        }

        if ($preco_toner !== null && $capacidade_folhas > 0) {
            $custo_por_folha = $preco_toner / $capacidade_folhas;
        }

        try {
            // Colunas gramatura, gramatura_por_folha e custo_por_folha sÃ£o GENERATED no banco
            $stmt = $this->db->prepare('INSERT INTO toners (modelo, peso_cheio, peso_vazio, capacidade_folhas, preco_toner, cor, tipo) VALUES (:modelo, :peso_cheio, :peso_vazio, :capacidade_folhas, :preco_toner, :cor, :tipo)');
            $stmt->execute([
                ':modelo' => $modelo,
                ':peso_cheio' => $peso_cheio,
                ':peso_vazio' => $peso_vazio,
                ':capacidade_folhas' => $capacidade_folhas,
                ':preco_toner' => $preco_toner,
                ':cor' => $cor,
                ':tipo' => $tipo
            ]);
            
            // Fallback: se as colunas NÃƒO forem generated, atualizar computados aqui
            try {
                $newId = (int)$this->db->lastInsertId();
                if ($newId > 0) {
                    $upd = $this->db->prepare('UPDATE toners 
                        SET gramatura = (CASE WHEN :peso_cheio_ins IS NOT NULL AND :peso_vazio_ins IS NOT NULL THEN :peso_cheio_ins - :peso_vazio_ins ELSE gramatura END),
                            gramatura_por_folha = (CASE WHEN :cap_ins > 0 AND :peso_cheio_ins IS NOT NULL AND :peso_vazio_ins IS NOT NULL THEN (:peso_cheio_ins - :peso_vazio_ins) / :cap_ins ELSE gramatura_por_folha END),
                            custo_por_folha = (CASE WHEN :cap_ins2 > 0 AND :preco_ins IS NOT NULL THEN :preco_ins / :cap_ins2 ELSE custo_por_folha END)
                        WHERE id = :id');
                    $upd->execute([
                        ':peso_cheio_ins' => $peso_cheio,
                        ':peso_vazio_ins' => $peso_vazio,
                        ':cap_ins' => $capacidade_folhas,
                        ':cap_ins2' => $capacidade_folhas,
                        ':preco_ins' => $preco_toner,
                        ':id' => $newId
                    ]);
                }
            } catch (\PDOException $ie) {
                // Ignorar: provavelmente as colunas sÃ£o generated
            }
            
            // Update existing retornados records that have this model as "nÃ£o cadastrado"
            $updateStmt = $this->db->prepare('UPDATE retornados SET modelo_cadastrado = 1 WHERE modelo = :modelo AND modelo_cadastrado = 0');
            $updateStmt->execute([':modelo' => $modelo]);
            
            flash('success', 'Toner cadastrado com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao cadastrar toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $modelo = trim($_POST['modelo'] ?? '');
        $peso_cheio = !empty($_POST['peso_cheio']) ? (float)$_POST['peso_cheio'] : null;
        $peso_vazio = !empty($_POST['peso_vazio']) ? (float)$_POST['peso_vazio'] : null;
        $capacidade_folhas = (int)($_POST['capacidade_folhas'] ?? 0);
        $preco_toner = (float)($_POST['preco_toner'] ?? 0);
        $cor = $_POST['cor'] ?? '';
        $tipo = $_POST['tipo'] ?? '';

        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                  || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
        }

        // Validar campos obrigatÃ³rios (pesos sÃ£o opcionais)
        if ($id <= 0 || $modelo === '' || $capacidade_folhas <= 0 || $preco_toner <= 0 || $cor === '' || $tipo === '') {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Dados invÃ¡lidos. Campos obrigatÃ³rios: Modelo, Capacidade de Folhas, PreÃ§o, Cor e Tipo.']);
                return;
            }
            flash('error', 'Dados invÃ¡lidos. Campos obrigatÃ³rios: Modelo, Capacidade de Folhas, PreÃ§o, Cor e Tipo.');
            redirect('/toners/cadastro');
            return;
        }

        // Se um dos pesos foi informado, ambos devem ser informados
        if (($peso_cheio !== null && $peso_vazio === null) || ($peso_cheio === null && $peso_vazio !== null)) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Se informar peso, ambos Peso Cheio e Peso Vazio devem ser preenchidos.']);
                return;
            }
            flash('error', 'Se informar peso, ambos Peso Cheio e Peso Vazio devem ser preenchidos.');
            redirect('/toners/cadastro');
            return;
        }

        // Se ambos os pesos foram informados, validar que peso cheio > peso vazio
        if ($peso_cheio !== null && $peso_vazio !== null && $peso_cheio <= $peso_vazio) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'O peso cheio deve ser maior que o peso vazio.']);
                return;
            }
            flash('error', 'O peso cheio deve ser maior que o peso vazio.');
            redirect('/toners/cadastro');
            return;
        }

        // Calcular campos derivados
        $gramatura = null;
        $gramatura_por_folha = null;
        $custo_por_folha = null;

        if ($peso_cheio !== null && $peso_vazio !== null) {
            $gramatura = $peso_cheio - $peso_vazio;
        }

        if ($gramatura !== null && $capacidade_folhas > 0) {
            $gramatura_por_folha = $gramatura / $capacidade_folhas;
        }

        if ($preco_toner !== null && $capacidade_folhas > 0) {
            $custo_por_folha = $preco_toner / $capacidade_folhas;
        }

        try {
            // Colunas gramatura, gramatura_por_folha e custo_por_folha sÃ£o GENERATED no banco
            $stmt = $this->db->prepare('UPDATE toners SET modelo = :modelo, peso_cheio = :peso_cheio, peso_vazio = :peso_vazio, capacidade_folhas = :capacidade_folhas, preco_toner = :preco_toner, cor = :cor, tipo = :tipo WHERE id = :id');
            $stmt->execute([
                ':modelo' => $modelo,
                ':peso_cheio' => $peso_cheio,
                ':peso_vazio' => $peso_vazio,
                ':capacidade_folhas' => $capacidade_folhas,
                ':preco_toner' => $preco_toner,
                ':cor' => $cor,
                ':tipo' => $tipo,
                ':id' => $id
            ]);

            // Fallback: atualizar campos computados caso nÃ£o sejam generated
            try {
                $upd = $this->db->prepare('UPDATE toners 
                    SET gramatura = (CASE WHEN :peso_cheio_up IS NOT NULL AND :peso_vazio_up IS NOT NULL THEN :peso_cheio_up - :peso_vazio_up ELSE gramatura END),
                        gramatura_por_folha = (CASE WHEN :cap_up > 0 AND :peso_cheio_up IS NOT NULL AND :peso_vazio_up IS NOT NULL THEN (:peso_cheio_up - :peso_vazio_up) / :cap_up ELSE gramatura_por_folha END),
                        custo_por_folha = (CASE WHEN :cap_up2 > 0 AND :preco_up IS NOT NULL THEN :preco_up / :cap_up2 ELSE custo_por_folha END)
                    WHERE id = :id_up');
                $upd->execute([
                    ':peso_cheio_up' => $peso_cheio,
                    ':peso_vazio_up' => $peso_vazio,
                    ':cap_up' => $capacidade_folhas,
                    ':cap_up2' => $capacidade_folhas,
                    ':preco_up' => $preco_toner,
                    ':id_up' => $id
                ]);
            } catch (\PDOException $ie) {
                // Ignorar: provavelmente as colunas sÃ£o generated
            }

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Toner atualizado com sucesso.']);
                return;
            }
            flash('success', 'Toner atualizado com sucesso.');
        } catch (\PDOException $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar toner: ' . $e->getMessage()]);
                return;
            }
            flash('error', 'Erro ao atualizar toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'ID invÃ¡lido.');
            redirect('/toners/cadastro');
            return;
        }

        try {
            $stmt = $this->db->prepare('DELETE FROM toners WHERE id = :id');
            $stmt->execute([':id' => $id]);
            flash('success', 'Toner excluÃ­do com sucesso.');
        } catch (\PDOException $e) {
            flash('error', 'Erro ao excluir toner: ' . $e->getMessage());
        }

        redirect('/toners/cadastro');
    }

    // ExclusÃ£o via AJAX (DELETE /toners/{id}) com retorno JSON
    public function deleteAjax(): void
    {
        header('Content-Type: application/json');

        // Obter ID da URL
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));
        $id = end($parts);

        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID invÃ¡lido']);
            return;
        }

        try {
            // Verificar existÃªncia
            $check = $this->db->prepare('SELECT id FROM toners WHERE id = :id');
            $check->execute([':id' => $id]);
            if (!$check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Toner nÃ£o encontrado']);
                return;
            }

            // Excluir
            $stmt = $this->db->prepare('DELETE FROM toners WHERE id = :id');
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Toner excluÃ­do com sucesso']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir toner: ' . $e->getMessage()]);
        }
    }

    // Baixar template CSV/Excel para importaÃ§Ã£o
    public function downloadTemplate(): void
    {
        try {
            // Criar CSV com template
            $filename = 'template_toners_' . date('Ymd') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Abrir output como arquivo
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8 (para Excel reconhecer acentos)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // CabeÃ§alhos (campos do formulÃ¡rio)
            $headers = [
                'Modelo',
                'Peso Cheio (g)',
                'Peso Vazio (g)',
                'Capacidade de Folhas',
                'PreÃ§o do Toner (R$)',
                'Cor',
                'Tipo'
            ];
            fputcsv($output, $headers, ';');

            // Linha de exemplo
            $exemplo = [
                'HP CF280A',
                '850.50',
                '120.30',
                '2700',
                '89.90',
                'Black',
                'Original'
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

    public function import(): void
    {
        header('Content-Type: application/json');
        
        try {
            // Log para debug
            error_log('Import method called. FILES: ' . print_r($_FILES, true));
            error_log('POST data: ' . print_r($_POST, true));
            
            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                $error = $_FILES['excel_file']['error'] ?? 'Arquivo nÃ£o enviado';
                echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo. CÃ³digo: ' . $error]);
                return;
            }

            $uploadedFile = $_FILES['excel_file']['tmp_name'];
            $originalFileName = $_FILES['excel_file']['name'];
            
            // Check file type by MIME type and extension
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $uploadedFile);
            finfo_close($finfo);
            
            $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
            
            // Accept CSV files (converted from Excel) and Excel files
            $validExtensions = ['xlsx', 'xls', 'csv'];
            $validMimeTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'application/vnd.ms-excel', // .xls
                'text/csv', // .csv
                'text/plain', // sometimes CSV is detected as plain text
                'application/csv'
            ];
            
            if (!in_array($fileExtension, $validExtensions) && !in_array($mimeType, $validMimeTypes)) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Formato de arquivo invÃ¡lido. ExtensÃ£o: $fileExtension, MIME: $mimeType. Use .xlsx, .xls ou .csv"
                ]);
                return;
            }

            // Read Excel data (simulate reading - in real implementation you'd use a library like PhpSpreadsheet)
            $excelData = $this->readExcelFile($uploadedFile);
            
            if (empty($excelData)) {
                echo json_encode(['success' => false, 'message' => 'Arquivo vazio ou formato invÃ¡lido. Verifique se o arquivo contÃ©m dados.']);
                return;
            }
            
            // Log the data structure for debugging
            error_log("Excel data structure: " . json_encode([
                'total_rows' => count($excelData),
                'first_row' => $excelData[0] ?? 'empty',
                'second_row' => $excelData[1] ?? 'empty'
            ]));

            $totalRows = count($excelData);
            $imported = 0;
            $errors = [];

            foreach ($excelData as $index => $row) {
                try {
                    // Skip header row
                    if ($index === 0) continue;
                    
                    // Skip empty rows
                    if (empty(array_filter($row))) continue;

                    $modelo = trim($row[0] ?? '');
                    
                    // Parse numbers with better handling
                    $peso_cheio_str = trim($row[1] ?? '0');
                    $peso_vazio_str = trim($row[2] ?? '0');
                    $capacidade_folhas_str = trim($row[3] ?? '0');
                    $preco_toner_str = trim($row[4] ?? '0');
                    
                    // Convert comma to dot for decimal numbers
                    $peso_cheio = (float)str_replace(',', '.', $peso_cheio_str);
                    $peso_vazio = (float)str_replace(',', '.', $peso_vazio_str);
                    $capacidade_folhas = (int)$capacidade_folhas_str;
                    $preco_toner = (float)str_replace(',', '.', $preco_toner_str);
                    
                    $cor = trim($row[5] ?? '');
                    $tipo = trim($row[6] ?? '');

                    // Log row data for debugging
                    error_log("Processing row " . ($index + 1) . ": " . json_encode($row));

                    // Validate required fields
                    if (empty($modelo) || $peso_cheio <= 0 || $peso_vazio <= 0 || $capacidade_folhas <= 0 || $preco_toner <= 0 || empty($cor) || empty($tipo)) {
                        $errors[] = "Linha " . ($index + 1) . ": Dados incompletos ou invÃ¡lidos - Modelo: '$modelo', Peso Cheio: $peso_cheio, Peso Vazio: $peso_vazio, Cap: $capacidade_folhas, PreÃ§o: $preco_toner, Cor: '$cor', Tipo: '$tipo'";
                        continue;
                    }

                    if ($peso_cheio <= $peso_vazio) {
                        $errors[] = "Linha " . ($index + 1) . ": Peso cheio deve ser maior que peso vazio";
                        continue;
                    }

                    // Validate enum values
                    if (!in_array($cor, ['Yellow', 'Magenta', 'Cyan', 'Black'])) {
                        $errors[] = "Linha " . ($index + 1) . ": Cor invÃ¡lida (use: Yellow, Magenta, Cyan, Black)";
                        continue;
                    }

                    if (!in_array($tipo, ['Original', 'Compativel', 'Remanufaturado'])) {
                        $errors[] = "Linha " . ($index + 1) . ": Tipo invÃ¡lido (use: Original, Compativel, Remanufaturado)";
                        continue;
                    }

                    // Colunas gramatura, gramatura_por_folha e custo_por_folha sÃ£o GENERATED no banco
                    $stmt = $this->db->prepare('INSERT INTO toners (modelo, peso_cheio, peso_vazio, capacidade_folhas, preco_toner, cor, tipo) VALUES (:modelo, :peso_cheio, :peso_vazio, :capacidade_folhas, :preco_toner, :cor, :tipo)');
                    $stmt->execute([
                        ':modelo' => $modelo,
                        ':peso_cheio' => $peso_cheio,
                        ':peso_vazio' => $peso_vazio,
                        ':capacidade_folhas' => $capacidade_folhas,
                        ':preco_toner' => $preco_toner,
                        ':cor' => $cor,
                        ':tipo' => $tipo
                    ]);

                    $imported++;

                } catch (\PDOException $e) {
                    $errors[] = "Linha " . ($index + 1) . ": Erro no banco - " . $e->getMessage();
                }
            }

            $message = "ImportaÃ§Ã£o concluÃ­da! $imported registros importados";
            if (!empty($errors)) {
                $message .= ". Erros encontrados: " . implode('; ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " (e mais " . (count($errors) - 3) . " erros)";
                }
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'total' => $totalRows - 1, // Exclude header
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    private function readExcelFile(string $filePath): array
    {
        $data = [];
        
        // Try to read as CSV first (most common case from our frontend conversion)
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Try different delimiters - prioritize comma since frontend uses it
            $delimiters = [',', ';', "\t"];
            $firstLine = fgets($handle);
            rewind($handle);
            
            // Detect delimiter by counting occurrences
            $delimiter = ','; // Default to comma (used by frontend)
            $maxCount = 0;
            
            foreach ($delimiters as $del) {
                $count = substr_count($firstLine, $del);
                if ($count > $maxCount) {
                    $maxCount = $count;
                    $delimiter = $del;
                }
            }
            
            // Log for debugging
            error_log("Detected delimiter: '$delimiter' in first line: " . trim($firstLine));
            
            // Read with detected delimiter
            while (($row = fgetcsv($handle, 2000, $delimiter)) !== FALSE) {
                // Clean up the row data and handle empty cells
                $cleanRow = array_map(function($cell) {
                    return trim($cell ?? '');
                }, $row);
                
                // Ensure we have at least 7 columns (pad with empty strings if needed)
                while (count($cleanRow) < 7) {
                    $cleanRow[] = '';
                }
                
                $data[] = $cleanRow;
            }
            fclose($handle);
        }
        
        // Log the parsed data for debugging
        error_log("Parsed CSV data: " . json_encode(array_slice($data, 0, 3))); // First 3 rows
        
        return $data;
    }

    public function deleteRetornado(): void
    {
        header('Content-Type: application/json');
        
        // Get ID from URL path
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($path, '/'));
        $id = end($pathParts);
        
        if (!$id || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => 'ID invÃ¡lido']);
            return;
        }
        
        try {
            // Check if record exists
            $stmt = $this->db->prepare('SELECT id FROM retornados WHERE id = :id');
            $stmt->execute([':id' => $id]);
            
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Registro nÃ£o encontrado']);
                return;
            }
            
            // Delete the record
            $stmt = $this->db->prepare('DELETE FROM retornados WHERE id = :id');
            $stmt->execute([':id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Registro excluÃ­do com sucesso']);
            
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir registro: ' . $e->getMessage()]);
        }
    }

    public function exportRetornados(): void
    {
        try {
            // Get filter parameters
            $dateFrom = $_GET['date_from'] ?? null;
            $dateTo = $_GET['date_to'] ?? null;
            $search = $_GET['search'] ?? null;

            // Build query with filters
            $sql = 'SELECT * FROM retornados WHERE 1=1';
            $params = [];

            if ($dateFrom) {
                $sql .= ' AND DATE(data_registro) >= :date_from';
                $params[':date_from'] = $dateFrom;
            }

            if ($dateTo) {
                $sql .= ' AND DATE(data_registro) <= :date_to';
                $params[':date_to'] = $dateTo;
            }

            if ($search) {
                $sql .= ' AND (modelo LIKE :search OR codigo_cliente LIKE :search OR usuario LIKE :search)';
                $params[':search'] = '%' . $search . '%';
            }

            $sql .= ' ORDER BY data_registro DESC';

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $retornados = $stmt->fetchAll();

            if (empty($retornados)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nenhum registro encontrado para exportar']);
                return;
            }

            // Generate filename with current date
            $filename = 'retornados_' . date('Y-m-d_H-i-s') . '.csv';

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8 (helps with Excel encoding)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers
            $headers = [
                'Modelo',
                'Código Cliente',
                'Usuário',
                'Filial',
                'Modo',
                'Peso Retornado (g)',
                'Percentual Chip (%)',
                'Quantidade',
                'Destino',
                'Valor Calculado (R$)',
                'Observação',
                'Data'
            ];
            fputcsv($output, $headers, ';');

            // Add data rows
            foreach ($retornados as $retornado) {
                $dataExport = $retornado['data_registro'] ?? null;
                if (empty($dataExport) && !empty($retornado['created_at'])) {
                    $dataExport = $retornado['created_at'];
                }

                $row = [
                    $retornado['modelo'] ?? '',
                    $retornado['codigo_cliente'] ?? '',
                    $retornado['usuario'] ?? '',
                    $retornado['filial'] ?? '',
                    $retornado['modo'] ?? '',
                    !empty($retornado['peso_retornado']) ? number_format((float)$retornado['peso_retornado'], 2, ',', '.') : '',
                    !empty($retornado['percentual_chip']) ? number_format((float)$retornado['percentual_chip'], 2, ',', '.') : '',
                    $retornado['quantidade'] ?? '1',
                    !empty($retornado['destino']) ? ucfirst(str_replace('_', ' ', (string)$retornado['destino'])) : '',
                    !empty($retornado['valor_calculado']) ? 'R$ ' . number_format((float)$retornado['valor_calculado'], 2, ',', '.') : '',
                    $retornado['observacao'] ?? '',
                    $dataExport ? date('d/m/Y H:i', strtotime((string)$dataExport)) : ''
                ];

                fputcsv($output, $row, ';');
            }

            fclose($output);
            exit;

        } catch (\PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    public function importRetornados(): void
    {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
                return;
            }

            $uploadedFile = $_FILES['import_file']['tmp_name'];
            $data = $this->readCSVFile($uploadedFile);
            
            if (empty($data)) {
                echo json_encode(['success' => false, 'message' => 'Arquivo vazio ou formato invÃ¡lido']);
                return;
            }

            $imported = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                if ($index === 0) continue; // Skip header

                try {
                    // Validate and insert data
                    $stmt = $this->db->prepare('
                        INSERT INTO retornados (usuario, filial, codigo_cliente, modelo, modo, peso_retornado, percentual_chip, destino, observacao) 
                        VALUES (:usuario, :filial, :codigo_cliente, :modelo, :modo, :peso_retornado, :percentual_chip, :destino, :observacao)
                    ');
                    
                    $stmt->execute([
                        ':usuario' => $row[2] ?? 'Importado',
                        ':filial' => $row[3] ?? 'JundiaÃ­',
                        ':codigo_cliente' => $row[1] ?? '',
                        ':modelo' => $row[0] ?? '',
                        ':modo' => 'peso', // Default
                        ':peso_retornado' => !empty($row[5]) ? (float)str_replace(',', '.', $row[5]) : null,
                        ':percentual_chip' => !empty($row[6]) ? (float)str_replace(',', '.', $row[6]) : null,
                        ':destino' => strtolower(str_replace(' ', '_', $row[7] ?? 'descarte')),
                        ':observacao' => $row[9] ?? ''
                    ]);

                    $imported++;
                } catch (\PDOException $e) {
                    $errors[] = "Linha " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "ImportaÃ§Ã£o concluÃ­da! $imported registros importados",
                'imported' => $imported,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    private function readCSVFile(string $filePath): array
    {
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $data[] = array_map('trim', $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public function importRow(): void
    {
        header('Content-Type: application/json');
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Dados invÃ¡lidos']);
            return;
        }
        
        try {
            // Debug: Log received data (only in debug mode)
            if (filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN)) {
                error_log('Import data received: ' . json_encode($input));
            }
            
            // Skip empty rows (all fields empty or just empty strings)
            $hasData = false;
            foreach ($input as $key => $value) {
                if (!empty(trim($value))) {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) {
                echo json_encode(['success' => true, 'message' => 'Linha vazia ignorada']);
                return;
            }
            
            // Validate only essential fields - allow empty values for historical records
            if (empty(trim($input['modelo'] ?? ''))) {
                echo json_encode(['success' => false, 'message' => "Campo modelo Ã© obrigatÃ³rio. Dados recebidos: " . json_encode($input)]);
                return;
            }
            
            // Validate and normalize destino field
            $destino = trim($input['destino'] ?? '');
            if (empty($destino)) {
                echo json_encode(['success' => false, 'message' => "Campo destino Ã© obrigatÃ³rio. Valor recebido: '" . ($input['destino'] ?? 'null') . "'"]);
                return;
            }
            
            // Normalize destino values to match database format
            $destinoNormalized = strtolower($destino);
            $destinoMap = [
                'uso interno' => 'uso_interno',
                'uso_interno' => 'uso_interno',
                'descarte' => 'descarte',
                'estoque' => 'estoque',
                'garantia' => 'garantia'
            ];
            
            if (!isset($destinoMap[$destinoNormalized])) {
                echo json_encode(['success' => false, 'message' => "Destino invÃ¡lido: '$destino'. Use: descarte, estoque, uso interno ou garantia"]);
                return;
            }
            
            $destino = $destinoMap[$destinoNormalized];
            
            // Check if modelo exists in toners table
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM toners WHERE modelo = :modelo');
            $stmt->execute([':modelo' => $input['modelo']]);
            $modeloCadastrado = $stmt->fetchColumn() > 0;
            
            // Parse and validate date - prioritize DD/MM/YYYY format
            $dataRegistro = $input['data_registro'] ?? date('Y-m-d');
            
            // First try DD/MM/YYYY format (Brazilian standard)
            $date = \DateTime::createFromFormat('d/m/Y', $dataRegistro);
            if ($date) {
                $dataRegistro = $date->format('Y-m-d');
            } else {
                // Try YYYY-MM-DD format as fallback
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataRegistro)) {
                    // If neither format works, use current date
                    $dataRegistro = date('Y-m-d');
                }
            }
            
            // Insert record - modo is required by table schema
            $stmt = $this->db->prepare('
                INSERT INTO retornados (modelo, modelo_cadastrado, usuario, filial, codigo_cliente, 
                                      destino, valor_calculado, data_registro, modo, peso_retornado, 
                                      percentual_chip, gramatura_existente, percentual_restante) 
                VALUES (:modelo, :modelo_cadastrado, :usuario, :filial, :codigo_cliente, 
                        :destino, :valor_calculado, :data_registro, :modo, :peso_retornado, 
                        :percentual_chip, :gramatura_existente, :percentual_restante)
            ');
            
            $result = $stmt->execute([
                ':modelo' => $input['modelo'],
                ':modelo_cadastrado' => $modeloCadastrado ? 1 : 0,
                ':usuario' => $input['usuario'] ?: 'N/A',
                ':filial' => $input['filial'] ?: 'N/A',
                ':codigo_cliente' => $input['codigo_cliente'] ?: 'N/A',
                ':destino' => $input['destino'],
                ':valor_calculado' => $input['valor_calculado'] ?? 0,
                ':data_registro' => $dataRegistro,
                ':modo' => 'peso',
                ':peso_retornado' => null,
                ':percentual_chip' => null,
                ':gramatura_existente' => null,
                ':percentual_restante' => null
            ]);
            
            if (!$result) {
                echo json_encode(['success' => false, 'message' => 'Erro ao inserir registro no banco de dados']);
                return;
            }
            
            $message = 'Registro importado com sucesso';
            if (!$modeloCadastrado) {
                $message .= ' (modelo nÃ£o cadastrado)';
            }
            
            echo json_encode(['success' => true, 'message' => $message]);
            
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao importar: ' . $e->getMessage()]);
        }
    }

    public function exportExcel(): void
    {
        try {
            // Get all toners for export
            $stmt = $this->db->query('
                SELECT 
                    modelo,
                    peso_cheio,
                    peso_vazio,
                    gramatura,
                    capacidade_folhas,
                    preco_toner,
                    gramatura_por_folha,
                    custo_por_folha,
                    cor,
                    tipo,
                    created_at,
                    updated_at
                FROM toners 
                ORDER BY modelo
            ');
            $toners = $stmt->fetchAll();

            if (empty($toners)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nenhum toner encontrado para exportar']);
                return;
            }

            // Generate filename with current date
            $filename = 'toners_cadastro_' . date('Y-m-d_H-i-s') . '.csv';

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8 (helps with Excel encoding)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers (in Portuguese)
            $headers = [
                'Modelo',
                'Peso Cheio (g)',
                'Peso Vazio (g)', 
                'Gramatura (g)',
                'Capacidade Folhas',
                'PreÃ§o Toner (R$)',
                'Gramatura por Folha (g)',
                'Custo por Folha (R$)',
                'Cor',
                'Tipo',
                'Data Cadastro',
                'Ãšltima AtualizaÃ§Ã£o'
            ];

            fputcsv($output, $headers, ';'); // Using semicolon for better Excel compatibility

            // Add data rows
            foreach ($toners as $toner) {
                $row = [
                    $toner['modelo'],
                    number_format($toner['peso_cheio'], 2, ',', '.'),
                    number_format($toner['peso_vazio'], 2, ',', '.'),
                    number_format($toner['gramatura'], 2, ',', '.'),
                    number_format($toner['capacidade_folhas'], 0, ',', '.'),
                    'R$ ' . number_format($toner['preco_toner'], 2, ',', '.'),
                    number_format($toner['gramatura_por_folha'], 4, ',', '.'),
                    'R$ ' . number_format($toner['custo_por_folha'], 4, ',', '.'),
                    $toner['cor'],
                    $toner['tipo'],
                    date('d/m/Y H:i', strtotime($toner['created_at'])),
                    date('d/m/Y H:i', strtotime($toner['updated_at']))
                ];

                fputcsv($output, $row, ';');
            }

            fclose($output);
            exit;

        } catch (\PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    public function exportExcelAdvanced(): void
    {
        try {
            // Get all toners with additional statistics
            $stmt = $this->db->query('
                SELECT 
                    t.*,
                    COALESCE(r.total_retornados, 0) as total_retornados,
                    COALESCE(r.valor_total_recuperado, 0) as valor_total_recuperado
                FROM toners t
                LEFT JOIN (
                    SELECT 
                        modelo,
                        SUM(quantidade) as total_retornados,
                        SUM(valor_calculado) as valor_total_recuperado
                    FROM retornados 
                    WHERE modelo_cadastrado = 1
                    GROUP BY modelo
                ) r ON t.modelo = r.modelo
                ORDER BY t.modelo
            ');
            $toners = $stmt->fetchAll();

            if (empty($toners)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Nenhum toner encontrado para exportar']);
                return;
            }

            // Generate filename with current date
            $filename = 'toners_relatorio_completo_' . date('Y-m-d_H-i-s') . '.csv';

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers (Extended)
            $headers = [
                'Modelo',
                'Peso Cheio (g)',
                'Peso Vazio (g)', 
                'Gramatura (g)',
                'Capacidade Folhas',
                'PreÃ§o Toner (R$)',
                'Gramatura por Folha (g)',
                'Custo por Folha (R$)',
                'Cor',
                'Tipo',
                'Total Retornados',
                'Valor Total Recuperado (R$)',
                'Data Cadastro',
                'Ãšltima AtualizaÃ§Ã£o'
            ];

            fputcsv($output, $headers, ';');

            // Add summary row
            $totalToners = count($toners);
            $totalRetornados = array_sum(array_column($toners, 'total_retornados'));
            $valorTotalRecuperado = array_sum(array_column($toners, 'valor_total_recuperado'));

            $summaryRow = [
                "RESUMO - {$totalToners} Toners Cadastrados",
                '', '', '', '', '', '', '',
                "Total Retornados: {$totalRetornados}",
                "Valor Total: R$ " . number_format($valorTotalRecuperado, 2, ',', '.'),
                '', '', '', ''
            ];
            fputcsv($output, $summaryRow, ';');
            fputcsv($output, [''], ';'); // Empty row

            // Add data rows
            foreach ($toners as $toner) {
                $row = [
                    $toner['modelo'],
                    number_format($toner['peso_cheio'], 2, ',', '.'),
                    number_format($toner['peso_vazio'], 2, ',', '.'),
                    number_format($toner['gramatura'], 2, ',', '.'),
                    number_format($toner['capacidade_folhas'], 0, ',', '.'),
                    'R$ ' . number_format($toner['preco_toner'], 2, ',', '.'),
                    number_format($toner['gramatura_por_folha'], 4, ',', '.'),
                    'R$ ' . number_format($toner['custo_por_folha'], 4, ',', '.'),
                    $toner['cor'],
                    $toner['tipo'],
                    $toner['total_retornados'],
                    'R$ ' . number_format($toner['valor_total_recuperado'], 2, ',', '.'),
                    date('d/m/Y H:i', strtotime($toner['created_at'])),
                    date('d/m/Y H:i', strtotime($toner['updated_at']))
                ];

                fputcsv($output, $row, ';');
            }

            fclose($output);
            exit;

        } catch (\PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../../views/pages/' . $view . '.php';
        $layout = __DIR__ . '/../../views/layouts/main.php';
        include $layout;
    }
    
    /**
     * API: Lista todos os toners para seleÃ§Ã£o em dropdowns
     * Usado em: Amostragens 2.0, Garantias
     */
    public function apiListToners(): void
    {
        header('Content-Type: application/json');
        
        try {
            // Mesma query usada em Amostragens 2.0
            $stmt = $this->db->prepare('
                SELECT 
                    id, 
                    modelo as codigo, 
                    modelo as nome 
                FROM toners 
                ORDER BY modelo
            ');
            $stmt->execute();
            $toners = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Adicionar campo 'modelo' tambÃ©m para compatibilidade
            foreach ($toners as &$toner) {
                $toner['modelo'] = $toner['codigo'];
            }
            
            echo json_encode($toners);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Erro ao buscar toners',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Busca dados de um retornado especÃ­fico para ediÃ§Ã£o
     */
    public function getRetornado($id): void
    {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare('
                SELECT r.id, r.modelo, r.codigo_cliente, r.usuario, r.filial, r.destino, 
                       r.data_registro, r.modelo_cadastrado, r.valor_calculado, r.observacao, 
                       r.quantidade, r.modo, r.peso_retornado, r.percentual_chip,
                       r.gramatura_existente, r.percentual_restante,
                       t.id as modelo_id
                FROM retornados r
                LEFT JOIN toners t ON t.modelo = r.modelo
                WHERE r.id = ?
            ');
            $stmt->execute([$id]);
            $retornado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($retornado) {
                echo json_encode(['success' => true, 'data' => $retornado]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Registro nÃ£o encontrado']);
            }
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar registro: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Atualiza um registro de retornado existente
     */
    public function updateRetornado(): void
    {
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID invÃ¡lido']);
                return;
            }
            
            // Validar campos obrigatÃ³rios
            $modelo = trim($_POST['modelo'] ?? '');
            $codigo_cliente = trim($_POST['codigo_cliente'] ?? '');
            $destino = trim($_POST['destino'] ?? '');
            $quantidade = max(1, (int)($_POST['quantidade'] ?? 1));
            $observacao = trim($_POST['observacao'] ?? '');
            $data_registro = $_POST['data_registro'] ?? date('Y-m-d');
            
            if (empty($modelo) || empty($codigo_cliente) || empty($destino)) {
                echo json_encode(['success' => false, 'message' => 'Campos obrigatÃ³rios: modelo, cÃ³digo cliente e destino']);
                return;
            }
            
            // Verificar se modelo existe
            $modelo_id = $_POST['modelo_id'] ?? null;
            if ($modelo_id) {
                $stmt = $this->db->prepare('SELECT capacidade_folhas, custo_por_folha FROM toners WHERE id = ?');
                $stmt->execute([$modelo_id]);
            } else {
                $stmt = $this->db->prepare('SELECT capacidade_folhas, custo_por_folha FROM toners WHERE modelo = ?');
                $stmt->execute([$modelo]);
            }
            $tonerData = $stmt->fetch(PDO::FETCH_ASSOC);
            $modelo_cadastrado = $tonerData ? 1 : 0;
            
            // Recalcular valor se destino Ã© estoque
            $valor_calculado = 0.00;
            $percentual_restante = (float)($_POST['percentual_restante'] ?? 0);
            
            if ($destino === 'estoque' && $percentual_restante > 0 && $tonerData) {
                $capacidade_folhas = $tonerData['capacidade_folhas'] ?? 0;
                $custo_por_folha = $tonerData['custo_por_folha'] ?? 0;
                
                if ($capacidade_folhas > 0 && $custo_por_folha > 0) {
                    $folhas_restantes = ($percentual_restante / 100) * $capacidade_folhas;
                    $valor_calculado = $folhas_restantes * $custo_por_folha * $quantidade;
                }
            }
            
            // Atualizar registro
            $stmt = $this->db->prepare('
                UPDATE retornados SET 
                    modelo = :modelo,
                    modelo_cadastrado = :modelo_cadastrado,
                    codigo_cliente = :codigo_cliente,
                    destino = :destino,
                    quantidade = :quantidade,
                    observacao = :observacao,
                    data_registro = :data_registro,
                    valor_calculado = :valor_calculado
                WHERE id = :id
            ');
            
            $stmt->execute([
                ':modelo' => $modelo,
                ':modelo_cadastrado' => $modelo_cadastrado,
                ':codigo_cliente' => $codigo_cliente,
                ':destino' => $destino,
                ':quantidade' => $quantidade,
                ':observacao' => $observacao,
                ':data_registro' => $data_registro,
                ':valor_calculado' => $valor_calculado,
                ':id' => $id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Registro atualizado com sucesso!']);
            
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }

    // =========================================================
    // MÃ“DULO: TONERS COM DEFEITO
    // =========================================================

    /**
     * PÃ¡gina principal do mÃ³dulo Toners com Defeito
     */
    public function defeitos(): void
    {
        // Verificar permissoes do modulo
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $canEdit = \App\Services\PermissionService::hasPermission($userId, 'toners_defeitos', 'edit');
        $canDelete = \App\Services\PermissionService::hasPermission($userId, 'toners_defeitos', 'delete');

        try {
            // Todos os toners para o listbox (id + modelo)
            $stmt = $this->db->query('SELECT id, modelo FROM toners ORDER BY modelo ASC');
            $toners_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $toners_lista = [];
        }

        try {
            // Todos os clientes para o listbox (id + codigo + nome)
            $stmt = $this->db->query('SELECT id, codigo, nome FROM clientes ORDER BY nome ASC');
            $clientes_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $clientes_lista = [];
        }

        try {
            // HistÃ³rico de defeitos registrados
            $stmt = $this->db->query("
                SELECT
                    td.id,
                    td.modelo_toner,
                    td.numero_pedido,
                    td.cliente_nome,
                    td.descricao,
                    td.quantidade,
                    td.foto1_nome,
                    td.foto2_nome,
                    td.foto3_nome,
                    td.devolutiva_descricao,
                    td.devolutiva_resultado,
                    td.devolutiva_at,
                    td.devolutiva_uid,
                    td.created_at,
                    u.name AS registrado_por_nome,
                    ud.name AS devolutiva_por_nome
                FROM toners_defeitos td
                LEFT JOIN users u ON u.id = td.registrado_por
                LEFT JOIN users ud ON ud.id = td.devolutiva_uid
                ORDER BY td.created_at DESC
                LIMIT 200
            ");
            $defeitos_historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $defeitos_historico = [];
        }

        // Departamentos para o campo de notificacoes (apenas setores relevantes)
        try {
            $stmt = $this->db->query("
                SELECT id, nome FROM departamentos 
                WHERE nome IN ('Qualidade', 'Log\u00edstica', 'Logistica', '\u00c1rea T\u00e9cnica', 'Area Tecnica', 'Atendimento')
                ORDER BY FIELD(nome, 'Qualidade', 'Log\u00edstica', 'Logistica', '\u00c1rea T\u00e9cnica', 'Area Tecnica', 'Atendimento')
            ");
            $departamentos_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $departamentos_lista = [];
        }


        $this->render('toners/defeitos', [
            'title'              => 'Toners com Defeito',
            'toners_lista'       => $toners_lista,
            'clientes_lista'     => $clientes_lista,
            'defeitos_historico' => $defeitos_historico,
            'departamentos_lista' => $departamentos_lista,
            'canEdit' => $canEdit,
            'canDelete' => $canDelete,
        ]);
    }

    /**
     * Registrar um novo Toner com Defeito (POST JSON)
     */
    public function storeDefeito(): void
    {
        // Verificar permissao de edicao
        \App\Services\PermissionService::requirePermission((int)($_SESSION['user_id'] ?? 0), 'toners_defeitos', 'edit');

        header('Content-Type: application/json');

        try {
            $userId = $_SESSION['user_id'] ?? null;

            // ---- Campos de texto ----
            $toner_id      = !empty($_POST['toner_id'])   ? (int)$_POST['toner_id']   : null;
            $modelo_toner  = trim($_POST['modelo_toner']  ?? '');
            $numero_pedido = trim($_POST['numero_pedido'] ?? '');
            $cliente_id    = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
            $cliente_nome  = trim($_POST['cliente_nome']  ?? '');
            $descricao     = trim($_POST['descricao']     ?? '');
            $quantidade    = max(1, (int)($_POST['quantidade'] ?? 1));

            // ---- ValidaÃ§Ã£o ----
            $erros = [];
            if (empty($modelo_toner))  $erros[] = 'Modelo do Toner';
            if (empty($numero_pedido)) $erros[] = 'NÃºmero do Pedido';
            if (empty($cliente_nome))  $erros[] = 'Cliente';
            if (empty($descricao))     $erros[] = 'DescriÃ§Ã£o do Defeito';

            if (!empty($erros)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Campos obrigatÃ³rios nÃ£o preenchidos: ' . implode(', ', $erros),
                ]);
                return;
            }

            // ---- Processar fotos (atÃ© 3 arquivos MEDIUMBLOB ~16MB cada) ----
            $fotos = [];
            for ($i = 1; $i <= 3; $i++) {
                $key = 'foto' . $i;
                if (
                    isset($_FILES[$key]) &&
                    $_FILES[$key]['error'] === UPLOAD_ERR_OK &&
                    $_FILES[$key]['size'] > 0
                ) {
                    $mime = mime_content_type($_FILES[$key]['tmp_name']);
                    if (!str_starts_with($mime, 'image/')) {
                        echo json_encode([
                            'success' => false,
                            'message' => "Foto {$i}: apenas imagens sÃ£o permitidas (recebido: {$mime})",
                        ]);
                        return;
                    }
                    $fotos[$i] = [
                        'dados' => file_get_contents($_FILES[$key]['tmp_name']),
                        'nome'  => basename($_FILES[$key]['name']),
                        'tipo'  => $mime,
                    ];
                } else {
                    $fotos[$i] = null;
                }
            }

            // ---- Inserir no banco ----
            $stmt = $this->db->prepare("
                INSERT INTO toners_defeitos
                    (toner_id, modelo_toner, numero_pedido, cliente_id, cliente_nome, descricao,
                     foto1, foto1_nome, foto1_tipo,
                     foto2, foto2_nome, foto2_tipo,
                     foto3, foto3_nome, foto3_tipo,
                     registrado_por, created_at)
                VALUES
                    (:toner_id, :modelo_toner, :numero_pedido, :cliente_id, :cliente_nome, :descricao,
                     :foto1, :foto1_nome, :foto1_tipo,
                     :foto2, :foto2_nome, :foto2_tipo,
                     :foto3, :foto3_nome, :foto3_tipo,
                     :registrado_por, NOW())
            ");

            $stmt->bindValue(':toner_id',     $toner_id,                     PDO::PARAM_INT);
            $stmt->bindValue(':modelo_toner', $modelo_toner);
            $stmt->bindValue(':numero_pedido',$numero_pedido);
            $stmt->bindValue(':cliente_id',   $cliente_id,                    PDO::PARAM_INT);
            $stmt->bindValue(':cliente_nome', $cliente_nome);
            $stmt->bindValue(':descricao',    $descricao);
            // Foto 1
            $stmt->bindValue(':foto1',        $fotos[1] ? $fotos[1]['dados'] : null, $fotos[1] ? PDO::PARAM_LOB : PDO::PARAM_NULL);
            $stmt->bindValue(':foto1_nome',   $fotos[1] ? $fotos[1]['nome']  : null);
            $stmt->bindValue(':foto1_tipo',   $fotos[1] ? $fotos[1]['tipo']  : null);
            // Foto 2
            $stmt->bindValue(':foto2',        $fotos[2] ? $fotos[2]['dados'] : null, $fotos[2] ? PDO::PARAM_LOB : PDO::PARAM_NULL);
            $stmt->bindValue(':foto2_nome',   $fotos[2] ? $fotos[2]['nome']  : null);
            $stmt->bindValue(':foto2_tipo',   $fotos[2] ? $fotos[2]['tipo']  : null);
            // Foto 3
            $stmt->bindValue(':foto3',        $fotos[3] ? $fotos[3]['dados'] : null, $fotos[3] ? PDO::PARAM_LOB : PDO::PARAM_NULL);
            $stmt->bindValue(':foto3_nome',   $fotos[3] ? $fotos[3]['nome']  : null);
            $stmt->bindValue(':foto3_tipo',   $fotos[3] ? $fotos[3]['tipo']  : null);
            $stmt->bindValue(':registrado_por', $userId, PDO::PARAM_INT);

            $stmt->execute();
            $novoId = (int)$this->db->lastInsertId();

            // ---- Notificar todos os admins e super_admins ----
            try {
                $admStmt = $this->db->query("
                    SELECT id FROM users
                    WHERE role IN ('admin', 'super_admin', 'superadmin')
                      AND id != " . (int)$userId . "
                ");
                $admins = $admStmt->fetchAll(PDO::FETCH_COLUMN);

                $titulo = 'âš ï¸ Toner com Defeito Registrado';
                $mensagem = "O toner \"{$modelo_toner}\" (Pedido #{$numero_pedido} â€“ Cliente: {$cliente_nome}) foi registrado com defeito.";

                foreach ($admins as $adminId) {
                    NotificationsController::create(
                        $adminId,
                        $titulo,
                        $mensagem,
                        'warning',
                        'toner_defeito',
                        $novoId
                    );
                }
            } catch (\Exception $notifEx) {
                error_log('Erro ao enviar notificacoes de defeito (admins): ' . $notifEx->getMessage());
            }

            // ---- Notificar usuarios dos setores selecionados (in-app + email) ----
            $setoresSelecionados = $_POST['notificar_setores'] ?? [];
            if (!empty($setoresSelecionados) && is_array($setoresSelecionados)) {
                try {
                    $placeholders = implode(',', array_fill(0, count($setoresSelecionados), '?'));
                    $params = $setoresSelecionados;
                    $params[] = (int)$userId;

                    $usersStmt = $this->db->prepare("
                        SELECT id, email FROM users
                        WHERE setor IN ($placeholders)
                          AND id != ?
                          AND status = 'active'
                    ");
                    $usersStmt->execute($params);
                    $usersDoSetor = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

                    $tituloNotif = 'Toner com Defeito Registrado';
                    $msgNotif = "O toner \"{$modelo_toner}\" (Pedido #{$numero_pedido} - Cliente: {$cliente_nome}) foi registrado com defeito.";
                    $emailsParaNotificar = [];

                    foreach ($usersDoSetor as $u) {
                        NotificationsController::create(
                            $u['id'],
                            $tituloNotif,
                            $msgNotif,
                            'warning',
                            'toner_defeito',
                            $novoId
                        );

                        if (!empty($u['email']) && filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
                            $emailsParaNotificar[] = $u['email'];
                        }
                    }

                    if (!empty($emailsParaNotificar)) {
                        try {
                            $emailService = new \App\Services\EmailService();
                            $appUrl = $_ENV['APP_URL'] ?? 'https://djbr.sgqoti.com.br';
                            $registradoPor = $_SESSION['user_name'] ?? 'Usuario';
                            $setoresTexto = implode(', ', $setoresSelecionados);

                            $htmlBody = "
<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Toner com Defeito</title></head>
<body style='font-family:Arial,sans-serif;line-height:1.6;color:#333;max-width:600px;margin:0 auto;padding:20px;'>
<div style='background:linear-gradient(135deg,#DC2626,#991B1B);padding:30px;text-align:center;border-radius:10px 10px 0 0;'>
<h1 style='color:white;margin:0;font-size:28px;'>Toner com Defeito</h1>
<p style='color:#f0f0f0;margin:5px 0 0 0;'>SGQ OTI DJ</p></div>
<div style='background:white;padding:30px;border:1px solid #e0e0e0;border-top:none;'>
<p style='color:#374151;font-size:15px;margin:0 0 20px 0;'>Um novo toner com defeito foi registrado e o seu setor foi notificado.</p>
<table style='width:100%;border-collapse:collapse;margin:0 0 20px 0;'>
<tr><td style='padding:10px 12px;border:1px solid #E5E7EB;background:#F9FAFB;font-weight:600;width:140px;'>Modelo</td><td style='padding:10px 12px;border:1px solid #E5E7EB;'>" . htmlspecialchars($modelo_toner) . "</td></tr>
<tr><td style='padding:10px 12px;border:1px solid #E5E7EB;background:#F9FAFB;font-weight:600;'>Pedido</td><td style='padding:10px 12px;border:1px solid #E5E7EB;'>" . htmlspecialchars($numero_pedido) . "</td></tr>
<tr><td style='padding:10px 12px;border:1px solid #E5E7EB;background:#F9FAFB;font-weight:600;'>Cliente</td><td style='padding:10px 12px;border:1px solid #E5E7EB;'>" . htmlspecialchars($cliente_nome) . "</td></tr>
<tr><td style='padding:10px 12px;border:1px solid #E5E7EB;background:#F9FAFB;font-weight:600;'>Qtd</td><td style='padding:10px 12px;border:1px solid #E5E7EB;'>$quantidade</td></tr>
<tr><td style='padding:10px 12px;border:1px solid #E5E7EB;background:#F9FAFB;font-weight:600;'>Descricao</td><td style='padding:10px 12px;border:1px solid #E5E7EB;'>" . nl2br(htmlspecialchars($descricao)) . "</td></tr>
<tr><td style='padding:10px 12px;border:1px solid #E5E7EB;background:#F9FAFB;font-weight:600;'>Por</td><td style='padding:10px 12px;border:1px solid #E5E7EB;'>" . htmlspecialchars($registradoPor) . "</td></tr>
<tr><td style='padding:10px 12px;border:1px solid #E5E7EB;background:#F9FAFB;font-weight:600;'>Setores</td><td style='padding:10px 12px;border:1px solid #E5E7EB;'>" . htmlspecialchars($setoresTexto) . "</td></tr>
</table>
<div style='text-align:center;margin:30px 0;'><a href='$appUrl/toners/defeitos' style='background:#DC2626;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:500;'>Ver no Sistema</a></div>
</div>
<div style='background:#f8f9fa;padding:20px;text-align:center;border-radius:0 0 10px 10px;border:1px solid #e0e0e0;border-top:none;'>
<p style='margin:0;color:#666;font-size:12px;'>SGQ OTI DJ - Email automatico.</p></div></body></html>";

                            $altBody = "SGQ - Toner com Defeito\nModelo: {$modelo_toner}\nPedido: #{$numero_pedido}\nCliente: {$cliente_nome}\nDescricao: {$descricao}\nPor: {$registradoPor}\nAcesse: {$appUrl}/toners/defeitos";

                            $emailService->send($emailsParaNotificar, 'SGQ - Toner com Defeito Registrado', $htmlBody, $altBody);
                        } catch (\Exception $emailEx) {
                            error_log('Erro ao enviar emails de defeito para setores: ' . $emailEx->getMessage());
                        }
                    }
                } catch (\Exception $setorEx) {
                    error_log('Erro ao notificar setores: ' . $setorEx->getMessage());
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Toner com defeito registrado com sucesso!',
                'id'      => $novoId,
            ]);

        } catch (\PDOException $e) {
            error_log('storeDefeito PDO error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('storeDefeito error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    /**
     * Serve foto de evidÃªncia armazenada como BLOB
     * GET /toners/defeitos/{id}/foto/{n}  (n = 1, 2 ou 3)
     */
    public function downloadFotoDefeito(): void
    {
        // Extrair parÃ¢metros da URL /toners/defeitos/{id}/foto/{n}
        $path   = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $parts  = explode('/', trim($path, '/'));
        // Esperado: ['toners', 'defeitos', '{id}', 'foto', '{n}']
        $id = isset($parts[2]) ? (int)$parts[2] : 0;
        $n  = isset($parts[4]) ? (int)$parts[4] : 1;

        if ($id <= 0 || !in_array($n, [1, 2, 3])) {
            http_response_code(400);
            echo 'ParÃ¢metros invÃ¡lidos';
            return;
        }

        try {
            $col = "foto{$n}";
            $stmt = $this->db->prepare(
                "SELECT {$col}, foto{$n}_nome AS nome, foto{$n}_tipo AS tipo FROM toners_defeitos WHERE id = ?"
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || empty($row[$col])) {
                http_response_code(404);
                echo 'Foto nÃ£o encontrada';
                return;
            }

            $mime = $row['tipo'] ?: 'image/jpeg';
            $nome = $row['nome'] ?: "foto{$n}.jpg";

            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="' . $nome . '"');
            header('Cache-Control: max-age=3600');
            echo $row[$col];

        } catch (\PDOException $e) {
            http_response_code(500);
            echo 'Erro ao recuperar imagem';
        }
    }

    /**
     * Excluir registro de defeito
     * POST /toners/defeitos/delete
     */
    public function deleteDefeito(): void
    {
        // Verificar permissao de exclusao
        \App\Services\PermissionService::requirePermission((int)($_SESSION['user_id'] ?? 0), 'toners_defeitos', 'delete');

        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = (int)($input['id'] ?? $_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                return;
            }
            
            $stmt = $this->db->prepare('DELETE FROM toners_defeitos WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Registro excluído com sucesso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Registro não encontrado ou já excluído.']);
            }
            
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir: ' . $e->getMessage()]);
        }
    }

    /**
     * Store Devolutiva (Feedback from Qualidade)
     * POST /toners/defeitos/devolutiva/store
     */
    public function storeDevolutiva(): void
    {
        header('Content-Type: application/json');
        
        $setor = $_SESSION['user_setor'] ?? '';
        $role  = $_SESSION['user_role'] ?? '';
        
        // Check permissions
        $isQualidade = stripos($setor, 'Qualidade') !== false;
        $isAdmin     = in_array($role, ['admin', 'super_admin']);
        
        if (!$isQualidade && !$isAdmin) {
             echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas Qualidade pode inserir.']);
             return;
        }

        try {
            $id = (int)($_POST['defeito_id'] ?? 0);
            $descricao = trim($_POST['devolutiva_descricao'] ?? '');
            $resultado_raw = trim($_POST['devolutiva_resultado'] ?? '');
            $allowedResultados = ['DEFEITO_PROCEDENTE', 'TONER_SEM_DEFEITO'];
            $resultado = in_array($resultado_raw, $allowedResultados) ? $resultado_raw : null;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                return;
            }
            if (empty($descricao)) {
                echo json_encode(['success' => false, 'message' => 'Descrição obrigatória.']);
                return;
            }
            
            // Check if exists
            $stmt = $this->db->prepare("SELECT id FROM toners_defeitos WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Defeito não encontrado.']);
                return;
            }

            // Upload Fotos
            $fotos = [];
            for ($i = 1; $i <= 3; $i++) {
                if (isset($_FILES["devolutiva_foto$i"]) && $_FILES["devolutiva_foto$i"]['error'] === UPLOAD_ERR_OK) {
                    $fotos[$i] = file_get_contents($_FILES["devolutiva_foto$i"]['tmp_name']);
                } else {
                    $fotos[$i] = null;
                }
            }

            // Update Query Dinâmica
            $sql = "UPDATE toners_defeitos SET 
                    devolutiva_descricao = ?, 
                    devolutiva_resultado = ?,
                    devolutiva_at = NOW(),
                    devolutiva_uid = ?";
            
            $params = [$descricao, $resultado, $_SESSION['user_id'] ?? 0];
            
            for ($i = 1; $i <= 3; $i++) {
                if ($fotos[$i] !== null) {
                    $sql .= ", devolutiva_foto$i = ?";
                    $params[] = $fotos[$i];
                }
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Devolutiva salva com sucesso!']);
            
        } catch (\PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar: ' . $e->getMessage()]);
        }
    }

    /**
     * Download Devolutiva Photo
     * GET /toners/defeitos/{id}/devolutiva-foto/{n}
     */
    public function downloadFotoDevolutiva($id, $n): void
    {
        $n = (int)$n;
        if ($n < 1 || $n > 3) exit('Foto inválida');

        try {
            $stmt = $this->db->prepare("SELECT devolutiva_foto{$n} AS foto FROM toners_defeitos WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row || empty($row['foto'])) {
                http_response_code(404);
                exit('Foto não encontrada');
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($row['foto']);
            header("Content-Type: $mime");
            header("Content-Length: " . strlen($row['foto']));
            echo $row['foto'];
        } catch (\PDOException $e) {
            http_response_code(500);
            exit('Erro interno');
        }
    }
}