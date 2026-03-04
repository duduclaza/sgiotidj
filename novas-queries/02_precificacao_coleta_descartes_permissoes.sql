-- =====================================================
-- Módulo: Precificação de Coleta de Descartes
-- Permissões por perfil
-- =====================================================

-- 1) Garante que todos os perfis tenham o módulo cadastrado em profile_permissions
--    (inicia SEM permissão para você liberar manualmente depois)
INSERT INTO profile_permissions (
    profile_id,
    module,
    can_view,
    can_edit,
    can_delete,
    can_import,
    can_export
)
SELECT
    p.id,
    'precificacao_coleta_descartes',
    0,
    0,
    0,
    0,
    0
FROM profiles p
LEFT JOIN profile_permissions pp
    ON pp.profile_id = p.id
   AND pp.module = 'precificacao_coleta_descartes'
WHERE pp.id IS NULL;

-- 2) Libera acesso total para perfis administrativos (Administrador e Super Administrador)
UPDATE profile_permissions pp
JOIN profiles p ON p.id = pp.profile_id
SET
    pp.can_view = 1,
    pp.can_edit = 1,
    pp.can_delete = 1,
    pp.can_import = 0,
    pp.can_export = 1
WHERE pp.module = 'precificacao_coleta_descartes'
  AND LOWER(p.name) IN ('administrador', 'super administrador');

-- 3) (Opcional) Verificação rápida
-- SELECT p.name, pp.module, pp.can_view, pp.can_edit, pp.can_delete, pp.can_import, pp.can_export
-- FROM profile_permissions pp
-- JOIN profiles p ON p.id = pp.profile_id
-- WHERE pp.module = 'precificacao_coleta_descartes'
-- ORDER BY p.name;
