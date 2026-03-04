-- =====================================================
-- Módulo: Precificação de Coleta de Descartes
-- Tabela principal
-- =====================================================

CREATE TABLE IF NOT EXISTS precificacao_coleta_descartes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    data_coleta DATE NOT NULL,
    valor_coleta DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_by INT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_data_coleta (data_coleta),
    INDEX idx_created_by (created_by),
    INDEX idx_updated_by (updated_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opcional: exemplo de seed
-- INSERT INTO precificacao_coleta_descartes (data_coleta, valor_coleta, created_by)
-- VALUES
-- ('2026-03-01', 1200.00, 1),
-- ('2026-03-10', 980.50, 1),
-- ('2026-03-18', 1430.90, 1);
