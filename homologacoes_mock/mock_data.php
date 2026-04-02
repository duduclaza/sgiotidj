<?php
// MOCK DATA PARA HOMOLOGAÇÕES 2.0

// Simular sessão independente para o mock funcionar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Versão dos dados mock - incrementar para forçar reset dos dados antigos
$MOCK_DATA_VERSION = 12;

// Inicializar os dados mockados na sessão, se ainda não existirem ou se a versão mudou
if (!isset($_SESSION['mock_data_version']) || $_SESSION['mock_data_version'] < $MOCK_DATA_VERSION) {
    $_SESSION['mock_data_version'] = $MOCK_DATA_VERSION;

    $_SESSION['mock_usuarios'] = [
        ['id' => 1, 'nome' => 'Fernanda',  'setor' => 'Compras',   'perfil' => 'compras'],
        ['id' => 2, 'nome' => 'Gustavo',   'setor' => 'Logística', 'perfil' => 'logistica'],
        ['id' => 3, 'nome' => 'Rafael',    'setor' => 'Qualidade', 'perfil' => 'qualidade'],
        ['id' => 4, 'nome' => 'Camila',    'setor' => 'TI',        'perfil' => 'tecnico'],
        ['id' => 5, 'nome' => 'Geison',    'setor' => 'TI',        'perfil' => 'tecnico'],
        ['id' => 6, 'nome' => 'Admin User', 'setor' => 'Gestão',    'perfil' => 'admin'],
        ['id' => 7, 'nome' => 'Super User', 'setor' => 'Diretoria', 'perfil' => 'super_admin'],
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
            'quantidade'            => 1,
            'tipo_aquisicao'        => 'comprado',
            'data_vencimento'       => '2025-02-15',
            'dias_vencimento_notif' => 5,
            'setor_responsavel'     => 'tecnico',
            'dados_comercial'       => [],
            'observacoes_logistica' => 'Item recebido na doca B, embalagem intacta.',
            'foto_carga'            => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=400&h=300',
            'status'                => 'concluida',
            'criado_por'            => 1,
            'tipo_homologacao'      => 'primeira',
            'produto_original_id'   => null,
            'homologacao_anterior_id' => null,
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
            'quantidade'            => 1,
            'tipo_aquisicao'        => 'comprado',
            'data_vencimento'       => '2025-03-01',
            'dias_vencimento_notif' => 5,
            'setor_responsavel'     => 'tecnico',
            'dados_comercial'       => [],
            'observacoes_logistica' => 'Recebido via transportadora Jadlog.',
            'foto_carga'            => 'https://images.unsplash.com/photo-1566576721346-d4a3b4eaad5b?auto=format&fit=crop&w=400&h=300',
            'status'                => 'concluida',
            'criado_por'            => 1,
            'tipo_homologacao'      => 'primeira',
            'produto_original_id'   => null,
            'homologacao_anterior_id' => null,
            'responsaveis'          => [5],
            'data_criacao'          => '2025-02-05',
            'data_prevista_chegada' => '2025-02-15',
            'dias_antecedencia_notif'=> 5,
            'data_recebimento'      => '2025-02-14',
            'recebido_por'          => 2,
            'local_homologacao'     => 'laboratorio',
            'data_inicio_homologacao'=> '2025-02-15',
            'data_fim_homologacao'  => '2025-02-18',
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => 'reprovado',
            'parecer_final'         => 'Equipamento reprovado. Apresentou lentidão extrema e travamentos com o software de telemetria base.',
            'checklist_respostas'   => [
                'instalacao_so'           => true,
                'desempenho_processador'  => false,
                'memoria_ram'             => true,
                'armazenamento'           => false,
                'conectividade_wifi'      => true,
                'conectividade_bluetooth' => true,
                'bateria'                 => false,
                'tela_qualidade'          => true,
                'teclado_touchpad'        => true,
                'portas_conectores'       => true,
            ],
            'observacoes_checklist' => 'Bateria durou apenas 2h nos testes e o SSD apresentou erro de leitura/escrita constante.',
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
            'quantidade'            => 5,
            'tipo_aquisicao'        => 'emprestado',
            'data_vencimento'       => '2025-04-10',
            'dias_vencimento_notif' => 10,
            'setor_responsavel'     => 'qualidade',
            'dados_comercial'       => [],
            'observacoes_logistica' => 'Lote com 5 caixas conforme NF.',
            'foto_carga'            => 'https://images.unsplash.com/photo-1553413077-190dd305871c?auto=format&fit=crop&w=400&h=300',
            'status'                => 'concluida',
            'criado_por'            => 1,
            'tipo_homologacao'      => 'rehomologacao',
            'produto_original_id'   => 1,
            'homologacao_anterior_id' => 1,
            'responsaveis'          => [3, 5],
            'data_criacao'          => '2025-02-20',
            'data_prevista_chegada' => '2025-03-01',
            'dias_antecedencia_notif'=> 2,
            'data_recebimento'      => '2025-03-01',
            'recebido_por'          => 2,
            'local_homologacao'     => 'cliente',
            'data_inicio_homologacao'=> '2025-03-03',
            'data_fim_homologacao'  => '2025-03-12',
            'data_instalacao_cliente'=> '2025-03-03',
            'nome_cliente'          => 'Construtora Horizonte S.A.',
            'resultado'             => 'aprovado_restricoes',
            'parecer_final'         => 'Equipamento aprovado com restrições. Embora apresente bom custo-benefício e excelente instalação, o rendimento final ficou 15% abaixo do especificado. Pode ser usado apenas para áreas não de linha de frente.',
            'checklist_respostas'   => [
                'encaixe_cartucho'        => true,
                'qualidade_impressao'     => true,
                'rendimento_paginas'      => false,
                'manchas_vazamentos'      => true,
                'compatibilidade_maquina' => true,
                'nivel_toner_correto'     => true,
            ],
            'observacoes_checklist' => 'Instalação sem problemas. Rendimento final marcou apenas 425 páginas de 500 informadas.',
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
            'quantidade'            => 2,
            'tipo_aquisicao'        => 'comprado',
            'data_vencimento'       => '2025-03-30',
            'dias_vencimento_notif' => 5,
            'setor_responsavel'     => 'tecnico',
            'dados_comercial'       => [],
            'observacoes_logistica' => 'Aguardando retirada pelo Rafael da TI.',
            'foto_carga'            => 'https://images.unsplash.com/photo-1590639880812-1f44a30e461a?auto=format&fit=crop&w=400&h=300',
            'status'                => 'item_recebido',
            'criado_por'            => 1,
            'tipo_homologacao'      => 'primeira',
            'produto_original_id'   => null,
            'homologacao_anterior_id' => null,
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
            'quantidade'            => 1,
            'tipo_aquisicao'        => 'comprado',
            'data_vencimento'       => date('Y-m-d', strtotime('+7 days')),
            'dias_vencimento_notif' => 5,
            'setor_responsavel'     => 'comercial',
            'dados_comercial'       => [
                'vendedor_nome' => 'Marcos Vendedor',
                'vendedor_email' => 'marcos@comercial.com',
                'supervisor_email' => 'joao.supervisor@comercial.com'
            ],
            'observacoes_logistica' => '',
            'foto_carga'            => '',
            'status'                => 'aguardando_chegada',
            'criado_por'            => 1,
            'tipo_homologacao'      => 'primeira',
            'produto_original_id'   => null,
            'homologacao_anterior_id' => null,
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
        [
            'id'                    => 6,
            'codigo'                => 'HOM-2025-001-R1',
            'titulo'                => 'Rehomologação: Impressora HP LaserJet M404dn (Revisão Anual)',
            'tipo_equipamento'      => 'Impressora',
            'descricao'             => 'Revisão anual de conformidade e testes de durabilidade após 1 ano de operação',
            'fornecedor'            => 'HP do Brasil Ltda',
            'modelo'                => 'LaserJet M404dn',
            'numero_serie'          => 'SN-HP-2025-00123',
            'quantidade'            => 1,
            'tipo_aquisicao'        => 'comprado',
            'data_vencimento'       => '2026-02-15',
            'dias_vencimento_notif' => 5,
            'setor_responsavel'     => 'qualidade',
            'dados_comercial'       => [],
            'observacoes_logistica' => 'Item ainda em operação. Será coletado na segunda-feira para revisão completa.',
            'foto_carga'            => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=400&h=300',
            'status'                => 'concluida',
            'criado_por'            => 3,
            'tipo_homologacao'      => 'rehomologacao',
            'produto_original_id'   => 1,
            'homologacao_anterior_id' => 1,
            'responsaveis'          => [3, 5],
            'data_criacao'          => '2025-12-01',
            'data_prevista_chegada' => '2025-12-08',
            'dias_antecedencia_notif'=> 3,
            'data_recebimento'      => '2025-12-08',
            'recebido_por'          => 2,
            'local_homologacao'     => 'laboratorio',
            'data_inicio_homologacao'=> '2025-12-09',
            'data_fim_homologacao'  => '2025-12-15',
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => 'aprovado',
            'parecer_final'         => 'Equipamento aprovado. Mantém total compatibilidade após 1 ano. Consumo dentro dos limites. Recomendação: substituir fusível preventivamente em 6 meses.',
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
            'observacoes_checklist' => 'Contagem de cópias: 847.392. Desempenho dentro do esperado. Sem problemas mecânicos detectados.',
        ],
        [
            'id'                    => 7,
            'codigo'                => 'HOM-2025-001-R2',
            'titulo'                => 'Rehomologação: Impressora HP LaserJet M404dn (Após Revisão Preventiva)',
            'tipo_equipamento'      => 'Impressora',
            'descricao'             => 'Teste pós-manutenção preventiva realizada conforme recomendação da revisão anterior',
            'fornecedor'            => 'HP do Brasil Ltda',
            'modelo'                => 'LaserJet M404dn',
            'numero_serie'          => 'SN-HP-2025-00123',
            'quantidade'            => 1,
            'tipo_aquisicao'        => 'comprado',
            'data_vencimento'       => '2026-08-01',
            'dias_vencimento_notif' => 5,
            'setor_responsavel'     => 'tecnico',
            'dados_comercial'       => [],
            'observacoes_logistica' => 'Retornando de revisão preventiva (fusível e correia) realizada pela assistência técnica.',
            'foto_carga'            => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=400&h=300',
            'status'                => 'concluida',
            'criado_por'            => 5,
            'tipo_homologacao'      => 'rehomologacao',
            'produto_original_id'   => 1,
            'homologacao_anterior_id' => 6,
            'responsaveis'          => [4, 5],
            'data_criacao'          => '2026-06-01',
            'data_prevista_chegada' => '2026-06-05',
            'dias_antecedencia_notif'=> 2,
            'data_recebimento'      => '2026-06-04',
            'recebido_por'          => 2,
            'local_homologacao'     => 'laboratorio',
            'data_inicio_homologacao'=> '2026-06-05',
            'data_fim_homologacao'  => '2026-06-08',
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => 'aprovado',
            'parecer_final'=> 'Equipamento aprovado após revisão preventiva. Todos os componentes substituídos funcionando perfeitamente. Qualidade de impressão excelente. Recomendação: continuar em operação por mais 6 meses antes da próxima revisão.',
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
            'observacoes_checklist' => 'Testes funcionais realizados por 4 horas. Sem erros ou travamentos. Fusível e correia novos confirmam funcionamento ótimo.',
        ],
        [
            'id'                    => 8,
            'codigo'                => 'HOM-2025-004-R1',
            'titulo'                => 'Rehomologação: Fusor para Lexmark MS821 (Validação Pós-Instalação)',
            'tipo_equipamento'      => 'Peça de Impressora',
            'descricao'             => 'Validação após instalação em máquina produtiva para verificar desempenho real',
            'fornecedor'            => 'TecPeças Importações',
            'modelo'                => 'Fusor 40X9929 Compatível',
            'numero_serie'          => 'LOT-FUS-2025-0089',
            'quantidade'            => 1,
            'tipo_aquisicao'        => 'comprado',
            'data_vencimento'       => '2025-09-30',
            'dias_vencimento_notif' => 5,
            'setor_responsavel'     => 'tecnico',
            'dados_comercial'       => [],
            'observacoes_logistica' => 'Peça retirada da máquina após 3 meses de uso para teste pós-instalação.',
            'foto_carga'            => 'https://images.unsplash.com/photo-1590639880812-1f44a30e461a?auto=format&fit=crop&w=400&h=300',
            'status'                => 'concluida',
            'criado_por'            => 4,
            'tipo_homologacao'      => 'rehomologacao',
            'produto_original_id'   => 4,
            'homologacao_anterior_id' => 4,
            'responsaveis'          => [4],
            'data_criacao'          => '2025-06-15',
            'data_prevista_chegada' => '2025-06-18',
            'dias_antecedencia_notif'=> 3,
            'data_recebimento'      => '2025-06-18',
            'recebido_por'          => 2,
            'local_homologacao'     => 'laboratorio',
            'data_inicio_homologacao'=> '2025-06-19',
            'data_fim_homologacao'  => '2025-06-22',
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => 'aprovado',
            'parecer_final'         => 'Equipamento aprovado para uso contínuo. Funcionou perfeitamente após 3 meses em operação. Sem sinais de desgaste prematuro. Compatibilidade confirmada.',
            'checklist_respostas'   => [
                'encaixe_fixacao'         => true,
                'funcionamento_apos_instalacao' => true,
                'qualidade_apos_troca'   => true,
                'temperatura_operacao'   => true,
                'durabilidade_ciclos'    => true,
                'compatibilidade_firmware'=> true,
            ],
            'observacoes_checklist' => 'Ciclos de teste completados. 125.000 páginas impressas sem problemas. Peça aprovada para continuidade de uso.',
        ],
        [
            'id'                    => 9,
            'codigo'                => 'HOM-2025-009',
            'titulo'                => 'Homologação de Novos Toners Premium - Lexmark',
            'tipo_equipamento'      => 'Suprimento de Impressora',
            'descricao'             => 'Avaliação de qualidade e rendimento de novos toners premium para linha Lexmark',
            'fornecedor'            => 'Premium Supplies Ltda',
            'modelo'                => 'Lexmark MS/MX 310/410/510/610',
            'numero_serie'          => 'LOT-LEX-2025-P09',
            'quantidade'            => 10,
            'tipo_aquisicao'        => 'comprado',
            'data_vencimento'       => date('Y-m-d', strtotime('+5 days')),
            'dias_vencimento_notif' => 5,
            'setor_responsavel'     => 'qualidade',
            'dados_comercial'       => [],
            'observacoes_logistica' => 'Material entregue diretamente na sala da qualidade.',
            'foto_carga'            => 'https://images.unsplash.com/photo-1553413077-190dd305871c?auto=format&fit=crop&w=400&h=300',
            'status'                => 'em_homologacao',
            'criado_por'            => 1,
            'tipo_homologacao'      => 'primeira',
            'produto_original_id'   => null,
            'homologacao_anterior_id' => null,
            'responsaveis'          => [3], // Rafael
            'data_criacao'          => date('Y-m-d', strtotime('-5 days')),
            'data_prevista_chegada' => date('Y-m-d', strtotime('-4 days')),
            'dias_antecedencia_notif'=> 3,
            'data_recebimento'      => date('Y-m-d', strtotime('-3 days')),
            'recebido_por'          => 2,
            'local_homologacao'     => 'laboratorio',
            'data_inicio_homologacao'=> date('Y-m-d', strtotime('-1 days')),
            'data_fim_homologacao'  => null,
            'data_instalacao_cliente'=> null,
            'nome_cliente'          => null,
            'resultado'             => null,
            'parecer_final'         => null,
            'checklist_respostas'   => [], // Vazio para o Rafael preencher
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
    
    // Definir tipo_homologacao baseado no perfil do usuário
    if (!isset($dados['tipo_homologacao'])) {
        $usuario = getUserById($_SESSION['usuario_logado_id']);
        if ($usuario && strtolower($usuario['perfil']) === 'compras') {
            $dados['tipo_homologacao'] = 'primeira';
        } else {
            $dados['tipo_homologacao'] = 'rehomologacao';
        }
    }
    
    // Se for primeira homologação
    if ($dados['tipo_homologacao'] === 'primeira') {
        $dados['produto_original_id'] = null;
        $dados['homologacao_anterior_id'] = null;
    } else {
        // Se for rehomologação, OBRIGATÓRIO ter homologacao_anterior_id
        if (!isset($dados['homologacao_anterior_id']) || !$dados['homologacao_anterior_id']) {
            throw new Exception("Rehomologação requer homologacao_anterior_id");
        }
        // produto_original_id deve ser preenchido dinamicamente
        if (!isset($dados['produto_original_id'])) {
            // Buscar qual é a primeira homologação da cadeia
            $anterior = null;
            $homologacoes = $_SESSION['mock_homologacoes'];
            foreach ($homologacoes as $h) {
                if ($h['id'] == $dados['homologacao_anterior_id']) {
                    $anterior = $h;
                    break;
                }
            }
            if ($anterior) {
                $dados['produto_original_id'] = ($anterior['tipo_homologacao'] === 'primeira') 
                    ? $anterior['id'] 
                    : $anterior['produto_original_id'];
            }
        }
    }
    
    // Fill empty arrays/variables
    $dados['data_recebimento']      = $dados['data_recebimento'] ?? null;
    $dados['recebido_por']          = $dados['recebido_por'] ?? null;
    $dados['local_homologacao']     = $dados['local_homologacao'] ?? null;
    $dados['data_inicio_homologacao']= $dados['data_inicio_homologacao'] ?? null;
    $dados['data_fim_homologacao']  = $dados['data_fim_homologacao'] ?? null;
    $dados['data_instalacao_cliente']= $dados['data_instalacao_cliente'] ?? null;
    $dados['nome_cliente']          = $dados['nome_cliente'] ?? null;
    $dados['resultado']             = $dados['resultado'] ?? null;
    $dados['parecer_final']         = $dados['parecer_final'] ?? null;
    $dados['observacoes_checklist'] = $dados['observacoes_checklist'] ?? null;
    
    $_SESSION['mock_homologacoes'][] = $dados;
    return $dados['id'];
}

function excluirHomologacaoMock($id) {
    foreach ($_SESSION['mock_homologacoes'] as $key => $h) {
        if ($h['id'] == $id) {
            unset($_SESSION['mock_homologacoes'][$key]);
            $_SESSION['mock_homologacoes'] = array_values($_SESSION['mock_homologacoes']); // Reindex
            return true;
        }
    }
    return false;
}
?>
