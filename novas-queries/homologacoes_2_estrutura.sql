CREATE TABLE IF NOT EXISTS modules (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homologacoes_2_tipos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL UNIQUE,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homologacoes_2_checklists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    tipo_id INT UNSIGNED NULL,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_h2_checklist_tipo (tipo_id),
    CONSTRAINT fk_h2_checklist_tipo FOREIGN KEY (tipo_id) REFERENCES homologacoes_2_tipos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homologacoes_2_checklist_itens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    checklist_id INT UNSIGNED NOT NULL,
    chave VARCHAR(120) NOT NULL,
    label VARCHAR(255) NOT NULL,
    ordem INT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_h2_checklist_item (checklist_id, chave),
    CONSTRAINT fk_h2_checklist_item_checklist FOREIGN KEY (checklist_id) REFERENCES homologacoes_2_checklists(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homologacoes_2 (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    titulo VARCHAR(255) NOT NULL,
    tipo_id INT UNSIGNED NOT NULL,
    descricao TEXT NOT NULL,
    fornecedor_id INT UNSIGNED NULL,
    fornecedor_nome VARCHAR(255) NOT NULL,
    modelo VARCHAR(255) NOT NULL,
    numero_serie VARCHAR(255) NULL,
    quantidade INT UNSIGNED NOT NULL DEFAULT 1,
    tipo_aquisicao ENUM('comprado', 'emprestado') NOT NULL DEFAULT 'comprado',
    tipo_homologacao ENUM('primeira', 'rehomologacao') NOT NULL DEFAULT 'primeira',
    homologacao_anterior_id INT UNSIGNED NULL,
    produto_original_id INT UNSIGNED NULL,
    data_prevista_chegada DATE NULL,
    dias_antecedencia_notif INT UNSIGNED NOT NULL DEFAULT 3,
    data_vencimento DATE NULL,
    dias_vencimento_notif INT UNSIGNED NOT NULL DEFAULT 5,
    setor_responsavel ENUM('tecnico', 'qualidade', 'comercial') NOT NULL DEFAULT 'tecnico',
    vendedor_nome VARCHAR(120) NULL,
    vendedor_email VARCHAR(190) NULL,
    supervisor_email VARCHAR(190) NULL,
    notificar_envolvidos TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('aguardando_chegada', 'item_recebido', 'em_homologacao', 'concluida', 'cancelada') NOT NULL DEFAULT 'aguardando_chegada',
    criado_por INT UNSIGNED NOT NULL,
    recebido_por INT UNSIGNED NULL,
    local_homologacao ENUM('laboratorio', 'cliente') NULL,
    cliente_id INT UNSIGNED NULL,
    nome_cliente VARCHAR(255) NULL,
    data_instalacao_cliente DATE NULL,
    data_criacao DATE NOT NULL,
    data_recebimento DATE NULL,
    data_inicio_homologacao DATE NULL,
    data_fim_homologacao DATE NULL,
    observacoes_logistica TEXT NULL,
    foto_carga VARCHAR(500) NULL,
    resultado ENUM('aprovado', 'reprovado', 'aprovado_restricoes', 'pendente') NULL,
    parecer_final LONGTEXT NULL,
    observacoes_checklist LONGTEXT NULL,
    public_token VARCHAR(64) NOT NULL UNIQUE,
    deleted_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_h2_status (status),
    KEY idx_h2_tipo (tipo_id),
    KEY idx_h2_criado_por (criado_por),
    KEY idx_h2_produto_original (produto_original_id),
    CONSTRAINT fk_h2_tipo FOREIGN KEY (tipo_id) REFERENCES homologacoes_2_tipos(id),
    CONSTRAINT fk_h2_homologacao_anterior FOREIGN KEY (homologacao_anterior_id) REFERENCES homologacoes_2(id) ON DELETE SET NULL,
    CONSTRAINT fk_h2_produto_original FOREIGN KEY (produto_original_id) REFERENCES homologacoes_2(id) ON DELETE SET NULL,
    -- FKs com tabelas legadas (users/fornecedores/clientes) foram removidas
    -- para evitar erro 150 em ambientes onde o tipo/engine dessas tabelas varia.
    -- A integridade desses IDs continua validada pela camada PHP do modulo.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homologacoes_2_responsaveis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_h2_responsavel (homologacao_id, user_id),
    CONSTRAINT fk_h2_responsavel_homologacao FOREIGN KEY (homologacao_id) REFERENCES homologacoes_2(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homologacoes_2_respostas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT UNSIGNED NOT NULL,
    checklist_item_id INT UNSIGNED NOT NULL,
    resultado ENUM('1', '0', 'pendente') NOT NULL DEFAULT 'pendente',
    avaliador_nome VARCHAR(255) NULL,
    origem ENUM('interno', 'publico') NOT NULL DEFAULT 'interno',
    respondido_por INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_h2_resposta (homologacao_id, checklist_item_id),
    CONSTRAINT fk_h2_resposta_homologacao FOREIGN KEY (homologacao_id) REFERENCES homologacoes_2(id) ON DELETE CASCADE,
    CONSTRAINT fk_h2_resposta_item FOREIGN KEY (checklist_item_id) REFERENCES homologacoes_2_checklist_itens(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homologacoes_2_anexos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT UNSIGNED NOT NULL,
    tipo ENUM('logistica', 'laudo') NOT NULL DEFAULT 'laudo',
    caminho VARCHAR(500) NULL,
    arquivo_blob MEDIUMBLOB NULL,
    nome_original VARCHAR(255) NOT NULL,
    mime_type ENUM('image/png', 'image/jpeg', 'application/pdf') NULL,
    tamanho_bytes INT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_h2_anexos_tipo (tipo),
    CONSTRAINT fk_h2_anexo_homologacao FOREIGN KEY (homologacao_id) REFERENCES homologacoes_2(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS homologacoes_2_historico (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    homologacao_id INT UNSIGNED NOT NULL,
    acao VARCHAR(80) NOT NULL,
    status_anterior VARCHAR(50) NULL,
    status_novo VARCHAR(50) NULL,
    descricao TEXT NOT NULL,
    created_by INT UNSIGNED NULL,
    created_by_name VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_h2_hist_homologacao (homologacao_id),
    KEY idx_h2_hist_acao (acao),
    CONSTRAINT fk_h2_hist_homologacao FOREIGN KEY (homologacao_id) REFERENCES homologacoes_2(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO homologacoes_2_tipos (nome, ativo) VALUES
('Impressora', 1),
('Notebook', 1),
('Suprimento de Impressora', 1),
('Peça de Impressora', 1)
ON DUPLICATE KEY UPDATE ativo = VALUES(ativo);

INSERT INTO homologacoes_2_checklists (titulo, tipo_id, created_by)
SELECT 'Checklist de Impressora', t.id, NULL
FROM homologacoes_2_tipos t
WHERE t.nome = 'Impressora'
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'instalacao_driver', 'Instalação de driver', 1 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'qualidade_impressao', 'Qualidade de impressão (preto e colorido)', 2 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'velocidade_impressao', 'Velocidade de impressão (ppm)', 3 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'conectividade_rede', 'Conectividade de rede (TCP/IP, Wi-Fi)', 4 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Impressora';

INSERT INTO homologacoes_2_checklists (titulo, tipo_id, created_by)
SELECT 'Checklist de Notebook', t.id, NULL
FROM homologacoes_2_tipos t
WHERE t.nome = 'Notebook'
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'instalacao_so', 'Instalação do SO corporativo', 1 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Notebook';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'desempenho_processador', 'Desempenho do processador', 2 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Notebook';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'memoria_ram', 'Memória RAM', 3 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Notebook';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'armazenamento', 'Armazenamento', 4 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Notebook';

INSERT INTO homologacoes_2_checklists (titulo, tipo_id, created_by)
SELECT 'Checklist de Suprimento de Impressora', t.id, NULL
FROM homologacoes_2_tipos t
WHERE t.nome = 'Suprimento de Impressora'
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'encaixe_cartucho', 'Encaixe do cartucho/toner na máquina', 1 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Suprimento de Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'qualidade_impressao', 'Qualidade de impressão', 2 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Suprimento de Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'rendimento_paginas', 'Rendimento de páginas', 3 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Suprimento de Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'compatibilidade_maquina', 'Compatibilidade com a máquina alvo', 4 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Suprimento de Impressora';

INSERT INTO homologacoes_2_checklists (titulo, tipo_id, created_by)
SELECT 'Checklist de Peça de Impressora', t.id, NULL
FROM homologacoes_2_tipos t
WHERE t.nome = 'Peça de Impressora'
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'encaixe_fixacao', 'Encaixe e fixação da peça', 1 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Peça de Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'funcionamento_apos_instalacao', 'Funcionamento após instalação', 2 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Peça de Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'qualidade_apos_troca', 'Qualidade de impressão após troca', 3 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Peça de Impressora';
INSERT IGNORE INTO homologacoes_2_checklist_itens (checklist_id, chave, label, ordem)
SELECT c.id, 'compatibilidade_firmware', 'Compatibilidade com firmware atual', 4 FROM homologacoes_2_checklists c INNER JOIN homologacoes_2_tipos t ON t.id = c.tipo_id WHERE t.nome = 'Peça de Impressora';

INSERT INTO modules (`key`, `name`, `description`, `active`)
VALUES ('homologacoes_2', 'Homologações 2.0', 'Fluxo completo de homologações com logística, fila técnica, checklist público e laudo final.', 1)
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `active` = VALUES(`active`);

INSERT INTO profile_permissions (profile_id, module, can_view, can_edit, can_delete, can_import, can_export)
SELECT p.id, 'homologacoes_2', 1, 1, 1, 0, 1
FROM profiles p
WHERE p.name IN ('Super Administrador', 'Administrador')
ON DUPLICATE KEY UPDATE
    can_view = VALUES(can_view),
    can_edit = VALUES(can_edit),
    can_delete = VALUES(can_delete),
    can_import = VALUES(can_import),
    can_export = VALUES(can_export);
