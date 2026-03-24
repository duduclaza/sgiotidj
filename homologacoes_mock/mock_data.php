<?php
// MOCK DATA PARA HOMOLOGAÇÕES 2.0

// Simular sessão independente para o mock funcionar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Versão dos dados mock - incrementar para forçar reset dos dados antigos
$MOCK_DATA_VERSION = 2;

// Inicializar os dados mockados na sessão, se ainda não existirem ou se a versão mudou
if (!isset($_SESSION['mock_data_version']) || $_SESSION['mock_data_version'] < $MOCK_DATA_VERSION) {
    $_SESSION['mock_data_version'] = $MOCK_DATA_VERSION;

    $_SESSION['mock_usuarios'] = [
        ['id' => 1, 'nome' => 'Fernanda',  'setor' => 'Compras',   'perfil' => 'compras'],
        ['id' => 2, 'nome' => 'Gustavo',   'setor' => 'Logística', 'perfil' => 'logistica'],
        ['id' => 3, 'nome' => 'Rafael',    'setor' => 'TI',        'perfil' => 'responsavel'],
        ['id' => 4, 'nome' => 'Camila',    'setor' => 'TI',        'perfil' => 'responsavel'],
        ['id' => 5, 'nome' => 'Geison',    'setor' => 'TI',        'perfil' => 'responsavel'],
    ];

    $_SESSION['mock_homologacoes'] = [
        [
            'id'                    => 1,
            'codigo'                => 'HOM-2025-001',
            'titulo'                => 'Homologação Impressora HP LaserJet M404dn',
            'tipo_equipamento'      => 'Impressora',
            'descricao'             => 'Avaliação de nova impressora laser para substituição do modelo atual',
            'fornecedor'            => 'HP do Brasil Ltda',
            'modelo'                => 'LaserJet M404dn',
            'numero_serie'          => 'SN-HP-2025-00123',
            'status'                => 'concluida',
            'criado_por'            => 1,
            'responsaveis'          => [3, 4],
            'data_criacao'          => '2025-01-10',
            'data_prevista_chegada' => '2025-01-20',
            'dias_antecedencia_notif'=> 3,
            'data_recebimento'      => '2025-01-21',
            'recebido_por'          => 2,
            'local_homologacao'     => 'laboratorio',
            'data_inicio_homologacao'=> '2025-01-22',
            'data_fim_homologacao'  => '2025-01-25',
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => 'aprovado',
            'parecer_final'         => 'Equipamento aprovado. Excelente qualidade de impressão, baixo ruído, driver compatível com o parque atual.',
            'checklist_respostas'   => [
                'instalacao_driver'       => true,
                'qualidade_impressao'     => true,
                'velocidade_impressao'    => true,
                'conectividade_rede'      => true,
                'compatibilidade_sistemas'=> true,
                'nivel_ruido'             => true,
                'consumo_energia'         => true,
                'facilidade_manutencao'   => true,
                'qualidade_suprimento'    => true,
                'documentacao_tecnica'    => true,
            ],
            'observacoes_checklist' => 'Driver instalou sem erros. Impressão frente e verso automática funcionando perfeitamente.',
        ],
        [
            'id'                    => 2,
            'codigo'                => 'HOM-2025-002',
            'titulo'                => 'Homologação Notebook Dell Latitude 5540',
            'tipo_equipamento'      => 'Notebook',
            'descricao'             => 'Avaliação de notebook corporativo para renovação de frota',
            'fornecedor'            => 'Dell Computadores do Brasil',
            'modelo'                => 'Latitude 5540 i5-1335U',
            'numero_serie'          => 'SN-DELL-2025-00456',
            'status'                => 'em_homologacao',
            'criado_por'            => 1,
            'responsaveis'          => [5],
            'data_criacao'          => '2025-02-05',
            'data_prevista_chegada' => '2025-02-15',
            'dias_antecedencia_notif'=> 5,
            'data_recebimento'      => '2025-02-14',
            'recebido_por'          => 2,
            'local_homologacao'     => 'laboratorio',
            'data_inicio_homologacao'=> '2025-02-15',
            'data_fim_homologacao'  => null,
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => null,
            'parecer_final'         => null,
            'checklist_respostas'   => [
                'instalacao_so'           => true,
                'desempenho_processador'  => true,
                'memoria_ram'             => true,
                'armazenamento'           => false,
                'conectividade_wifi'      => true,
                'conectividade_bluetooth' => null,
                'bateria'                 => null,
                'tela_qualidade'          => null,
                'teclado_touchpad'        => null,
                'portas_conectores'       => null,
            ],
            'observacoes_checklist' => 'Em andamento. SSD apresentou lentidão nos testes iniciais.',
        ],
        [
            'id'                    => 3,
            'codigo'                => 'HOM-2025-003',
            'titulo'                => 'Homologação Toner Compatível para Canon imageRUNNER',
            'tipo_equipamento'      => 'Suprimento de Impressora',
            'descricao'             => 'Teste de toner compatível de terceiro para redução de custos',
            'fornecedor'            => 'SupriMax Distribuidora',
            'modelo'                => 'Toner NPG-59 Compatível',
            'numero_serie'          => 'LOT-2025-NPG59-001',
            'status'                => 'em_homologacao',
            'criado_por'            => 1,
            'responsaveis'          => [3, 5],
            'data_criacao'          => '2025-02-20',
            'data_prevista_chegada' => '2025-03-01',
            'dias_antecedencia_notif'=> 2,
            'data_recebimento'      => '2025-03-01',
            'recebido_por'          => 2,
            'local_homologacao'     => 'cliente',
            'data_inicio_homologacao'=> '2025-03-03',
            'data_fim_homologacao'  => null,
            'data_instalacao_cliente'=> '2025-03-03',
            'nome_cliente'          => 'Construtora Horizonte S.A.',
            'resultado'             => null,
            'parecer_final'         => null,
            'checklist_respostas'   => [
                'encaixe_cartucho'        => true,
                'qualidade_impressao'     => true,
                'rendimento_paginas'      => null,
                'manchas_vazamentos'      => null,
                'compatibilidade_maquina' => true,
                'nivel_toner_correto'     => null,
            ],
            'observacoes_checklist' => 'Instalação sem problemas. Aguardando teste de rendimento (500 páginas).',
        ],
        [
            'id'                    => 4,
            'codigo'                => 'HOM-2025-004',
            'titulo'                => 'Homologação Fusor para Lexmark MS821',
            'tipo_equipamento'      => 'Peça de Impressora',
            'descricao'             => 'Validação de peça fusor genérica para manutenção preventiva',
            'fornecedor'            => 'TecPeças Importações',
            'modelo'                => 'Fusor 40X9929 Compatível',
            'numero_serie'          => 'LOT-FUS-2025-0089',
            'status'                => 'item_recebido',
            'criado_por'            => 1,
            'responsaveis'          => [4],
            'data_criacao'          => '2025-03-01',
            'data_prevista_chegada' => '2025-03-10',
            'dias_antecedencia_notif'=> 3,
            'data_recebimento'      => '2025-03-09',
            'recebido_por'          => 2,
            'local_homologacao'     => null,
            'data_inicio_homologacao'=> null,
            'data_fim_homologacao'  => null,
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => null,
            'parecer_final'         => null,
            'checklist_respostas'   => [],
            'observacoes_checklist' => null,
        ],
        [
            'id'                    => 5,
            'codigo'                => 'HOM-2025-005',
            'titulo'                => 'Homologação Notebook Lenovo ThinkPad E14',
            'tipo_equipamento'      => 'Notebook',
            'descricao'             => 'Avaliação de notebook para área financeira',
            'fornecedor'            => 'Lenovo do Brasil',
            'modelo'                => 'ThinkPad E14 Gen 5 AMD',
            'numero_serie'          => 'SN-LNV-2025-00789',
            'status'                => 'aguardando_chegada',
            'criado_por'            => 1,
            'responsaveis'          => [3, 4, 5],
            'data_criacao'          => '2025-03-15',
            'data_prevista_chegada' => date('Y-m-d', strtotime('+2 days')),
            'dias_antecedencia_notif'=> 4,
            'data_recebimento'      => null,
            'recebido_por'          => null,
            'local_homologacao'     => null,
            'data_inicio_homologacao'=> null,
            'data_fim_homologacao'  => null,
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => null,
            'parecer_final'         => null,
            'checklist_respostas'   => [],
            'observacoes_checklist' => null,
        ],
    ];

    $_SESSION['mock_checklists_por_tipo'] = [
        'Impressora' => [
            'instalacao_driver' => 'Instalação de driver',
            'qualidade_impressao' => 'Qualidade de impressão (preto e colorido)',
            'velocidade_impressao' => 'Velocidade de impressão (ppm)',
            'conectividade_rede' => 'Conectividade de rede (TCP/IP, Wi-Fi)',
            'compatibilidade_sistemas' => 'Compatibilidade com sistemas operacionais',
            'nivel_ruido' => 'Nível de ruído',
            'consumo_energia' => 'Consumo de energia',
            'facilidade_manutencao' => 'Facilidade de manutenção',
            'qualidade_suprimento' => 'Qualidade do suprimento original',
            'documentacao_tecnica' => 'Documentação técnica disponível',
        ],
        'Notebook' => [
            'instalacao_so' => 'Instalação do SO corporativo',
            'desempenho_processador' => 'Desempenho do processador (benchmark)',
            'memoria_ram' => 'Memória RAM (capacidade e desempenho)',
            'armazenamento' => 'Armazenamento (velocidade SSD/HD)',
            'conectividade_wifi' => 'Conectividade Wi-Fi (2.4GHz e 5GHz)',
            'conectividade_bluetooth' => 'Conectividade Bluetooth',
            'bateria' => 'Bateria (autonomia em horas)',
            'tela_qualidade' => 'Tela (qualidade e resolução)',
            'teclado_touchpad' => 'Teclado e touchpad',
            'portas_conectores' => 'Portas e conectores (USB, HDMI, etc.)',
        ],
        'Suprimento de Impressora' => [
            'encaixe_cartucho' => 'Encaixe do cartucho/toner na máquina',
            'qualidade_impressao' => 'Qualidade de impressão',
            'rendimento_paginas' => 'Rendimento de páginas (conforme especificação)',
            'manchas_vazamentos' => 'Manchas ou vazamentos',
            'compatibilidade_maquina' => 'Compatibilidade com a máquina alvo',
            'nivel_toner_correto' => 'Indicador de nível correto no painel',
        ],
        'Peça de Impressora' => [
            'encaixe_fixacao' => 'Encaixe e fixação da peça',
            'funcionamento_apos_instalacao' => 'Funcionamento após instalação',
            'qualidade_apos_troca' => 'Qualidade de impressão após troca',
            'temperatura_operacao' => 'Temperatura de operação (se aplicável)',
            'durabilidade_ciclos' => 'Durabilidade (ciclos de teste)',
            'compatibilidade_firmware' => 'Compatibilidade com firmware atual',
        ]
    ];

    // Reset do usuário logado para o padrão ao atualizar versão
    $_SESSION['usuario_logado_id'] = 1;
}

// Inicializar um usuário logado padrão, se não houver um definido (Simulação)
if (!isset($_SESSION['usuario_logado_id'])) {
    $_SESSION['usuario_logado_id'] = 1; // Default: Fernanda (Compras)
}

function getMockData() {
    return [
        'usuarios' => $_SESSION['mock_usuarios'],
        'homologacoes' => $_SESSION['mock_homologacoes'],
        'checklists' => $_SESSION['mock_checklists_por_tipo']
    ];
}

function atualizarHomologacaoMock($id, $dados) {
    foreach ($_SESSION['mock_homologacoes'] as $key => $h) {
        if ($h['id'] == $id) {
            $_SESSION['mock_homologacoes'][$key] = array_merge($h, $dados);
            return true;
        }
    }
    return false;
}

function criarHomologacaoMock($dados) {
    $novo_id = empty($_SESSION['mock_homologacoes']) ? 1 : max(array_column($_SESSION['mock_homologacoes'], 'id')) + 1;
    $ano = date('Y');
    $dados['id'] = $novo_id;
    $dados['codigo'] = "HOM-{$ano}-" . str_pad($novo_id, 3, '0', STR_PAD_LEFT);
    $dados['data_criacao'] = date('Y-m-d');
    $dados['status'] = 'aguardando_chegada';
    $dados['criado_por'] = $_SESSION['usuario_logado_id'];
    $dados['checklist_respostas'] = [];
    
    // Fill empty arrays/variables
    $dados['data_recebimento']      = null;
    $dados['recebido_por']          = null;
    $dados['local_homologacao']     = null;
    $dados['data_inicio_homologacao']= null;
    $dados['data_fim_homologacao']  = null;
    $dados['data_instalacao_cliente']= null;
    $dados['nome_cliente']          = null;
    $dados['resultado']             = null;
    $dados['parecer_final']         = null;
    $dados['observacoes_checklist'] = null;
    
    $_SESSION['mock_homologacoes'][] = $dados;
    return $dados['id'];
}
?>
