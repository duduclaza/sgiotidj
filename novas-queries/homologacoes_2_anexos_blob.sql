ALTER TABLE homologacoes_2_anexos
    MODIFY caminho VARCHAR(500) NULL,
    ADD COLUMN arquivo_blob MEDIUMBLOB NULL AFTER caminho;
