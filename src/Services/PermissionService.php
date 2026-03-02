<?php

namespace App\Services;

use App\Config\Database;

class PermissionService
{
    private static $db = null;
    private static $userPermissions = [];
    
    private static function getDb()
    {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }
    
    /**
     * Check if user has permission for a specific module and action
     */
    public static function hasPermission(int $userId, string $module, string $action): bool
    {
        // Master User (GOD MODE) tem acesso total sempre
        if (\App\Services\MasterUserService::isMasterUserId($userId)) {
            return true;
        }
        
        // Super Admin users have all permissions (not customizable)
        if (self::isSuperAdmin($userId)) {
            return true;
        }

        // Liberação global da visualização de POPs e ITs para todos os usuários
        // (ações de edição/aprovação continuam controladas por permissões específicas)
        if ($module === 'pops_its_visualizacao' && $action === 'view') {
            return true;
        }
        
        // Load user permissions if not already loaded
        if (!isset(self::$userPermissions[$userId])) {
            self::loadUserPermissions($userId);
        }
        
        $permissions = self::$userPermissions[$userId] ?? [];

        // Compatibilidade: perfis antigos podem ter apenas permissões granulares de POPs e ITs
        // e não a chave de visualização principal. Nesse caso, liberar acesso ao módulo principal.
        if ($module === 'pops_its_visualizacao' && $action === 'view') {
            $hasPopsMain = isset($permissions['pops_its']) && ($permissions['pops_its']['view'] ?? false);
            $hasAnyPopsGranular =
                (isset($permissions['pops_its_cadastro_titulos']) && ($permissions['pops_its_cadastro_titulos']['view'] ?? false)) ||
                (isset($permissions['pops_its_meus_registros']) && ($permissions['pops_its_meus_registros']['view'] ?? false)) ||
                (isset($permissions['pops_its_pendente_aprovacao']) && ($permissions['pops_its_pendente_aprovacao']['view'] ?? false));

            if ($hasPopsMain || $hasAnyPopsGranular) {
                return true;
            }
        }
        
        // Check if user has permission for this module and action
        return isset($permissions[$module]) && ($permissions[$module][$action] ?? false);
    }
    
    /**
     * Check if user is admin (regular admin - customizable permissions)
     */
    public static function isAdmin(int $userId): bool
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM users u 
            JOIN profiles p ON u.profile_id = p.id 
            WHERE u.id = ? AND p.name = 'Administrador'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if user is super admin (unrestricted access - not customizable)
     * ⭐ Super Admin tem ACESSO TOTAL a tudo
     */
    public static function isSuperAdmin(int $userId): bool
    {
        $db = self::getDb();
        
        // ⭐ Verificar por email hardcoded (du.claza@gmail.com sempre é super admin)
        $stmt = $db->prepare("SELECT email, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($user) {
            // Email hardcoded sempre é super admin
            if ($user['email'] === 'du.claza@gmail.com') {
                return true;
            }
            
            // Verificar role direto (compatibilidade com bases antigas)
            if (in_array((string)$user['role'], ['super_admin', 'superadmin'], true)) {
                return true;
            }
        }
        
        // Fallback: verificar pelo perfil
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM users u 
            JOIN profiles p ON u.profile_id = p.id 
            WHERE u.id = ? AND p.name = 'Super Administrador'
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if user has admin privileges (either admin or super admin)
     */
    public static function hasAdminPrivileges(int $userId): bool
    {
        return self::isAdmin($userId) || self::isSuperAdmin($userId);
    }
    
    /**
     * Load user permissions from database
     */
    private static function loadUserPermissions(int $userId): void
    {
        $db = self::getDb();
        
        $stmt = $db->prepare("
            SELECT pp.module, pp.can_view, pp.can_edit, pp.can_delete, pp.can_import, pp.can_export
            FROM users u
            LEFT JOIN profiles p ON u.profile_id = p.id
            LEFT JOIN profile_permissions pp ON p.id = pp.profile_id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $userPermissions = [];
        foreach ($permissions as $perm) {
            if ($perm['module']) {
                $userPermissions[$perm['module']] = [
                    'view' => $perm['can_view'] == 1,
                    'edit' => $perm['can_edit'] == 1,
                    'delete' => $perm['can_delete'] == 1,
                    'import' => $perm['can_import'] == 1,
                    'export' => $perm['can_export'] == 1,
                ];
            }
        }
        
        self::$userPermissions[$userId] = $userPermissions;
    }
    
    /**
     * Get all permissions for a user
     */
    public static function getUserPermissions(int $userId): array
    {
        if (!isset(self::$userPermissions[$userId])) {
            self::loadUserPermissions($userId);
        }
        
        return self::$userPermissions[$userId] ?? [];
    }
    
    /**
     * Get user profile information
     */
    public static function getUserProfile(int $userId): ?array
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.description, p.is_admin, p.is_default
            FROM users u
            LEFT JOIN profiles p ON u.profile_id = p.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Check multiple permissions at once
     */
    public static function hasAnyPermission(int $userId, array $moduleActions): bool
    {
        foreach ($moduleActions as $module => $actions) {
            if (is_array($actions)) {
                foreach ($actions as $action) {
                    if (self::hasPermission($userId, $module, $action)) {
                        return true;
                    }
                }
            } else {
                if (self::hasPermission($userId, $module, $actions)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Require permission or throw exception
     */
    public static function requirePermission(int $userId, string $module, string $action): void
    {
        if (!self::hasPermission($userId, $module, $action)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Acesso negado - permissão insuficiente']);
                exit;
            } else {
                // Redirect to unauthorized page or show error
                http_response_code(403);
                echo '<h1>Acesso Negado</h1><p>Você não tem permissão para acessar esta funcionalidade.</p>';
                exit;
            }
        }
    }
    
    /**
     * Clear cached permissions (useful after profile changes)
     */
    public static function clearUserPermissions(int $userId): void
    {
        unset(self::$userPermissions[$userId]);
    }
    
    /**
     * Clear all cached permissions
     */
    public static function clearAllPermissions(): void
    {
        self::$userPermissions = [];
    }
}
