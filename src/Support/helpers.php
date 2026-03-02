<?php
if (!function_exists('env')) {
    function env(string $key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $base = rtrim($_ENV['APP_URL'] ?? '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = '/'): string {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        $path = '/' . ltrim($path, '/');
        return $base ? $base . $path : $path;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if ($message === null) {
            $msg = $_SESSION['flash'][$key] ?? null;
            if (isset($_SESSION['flash'][$key])) unset($_SESSION['flash'][$key]);
            return $msg;
        }
        $_SESSION['flash'][$key] = $message;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $to): void {
        header('Location: ' . url($to));
        exit;
    }
}

if (!function_exists('view')) {
    function view(string $path, array $data = []): string {
        $basePath = dirname(__DIR__, 2);
        $viewPath = $basePath . '/views/' . $path . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$viewPath}");
        }
        
        extract($data);
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}

if (!function_exists('sendEmail')) {
    function sendEmail($to, string $subject, string $body, ?string $altBody = null, array $attachments = []): bool {
        try {
            $emailService = new \App\Services\EmailService();
            return $emailService->send($to, $subject, $body, $altBody, $attachments);
        } catch (\Exception $e) {
            error_log("Email helper error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('sendAmostragemNotification')) {
    function sendAmostragemNotification(array $amostragem, string $recipientEmail): bool {
        try {
            $emailService = new \App\Services\EmailService();
            return $emailService->sendAmostragemNotification($amostragem, $recipientEmail);
        } catch (\Exception $e) {
            error_log("Amostragem notification error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('sendRetornadoNotification')) {
    function sendRetornadoNotification(array $retornado, string $recipientEmail): bool {
        try {
            $emailService = new \App\Services\EmailService();
            return $emailService->sendRetornadoNotification($retornado, $recipientEmail);
        } catch (\Exception $e) {
            error_log("Retornado notification error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('testEmailConnection')) {
    function testEmailConnection(): array {
        try {
            $emailService = new \App\Services\EmailService();
            return $emailService->testConnection();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao testar conexão: ' . $e->getMessage()
            ];
        }
    }
}

/**
 * Verifica se o usuário atual é super admin
 * ⭐ REGRA ESPECIAL: du.claza@gmail.com SEMPRE é super_admin
 */
if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin(): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // ⭐ SUPER ADMIN HARDCODED - du.claza@gmail.com sempre tem acesso total
        if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'du.claza@gmail.com') {
            return true;
        }
        
        // Verificar role normal (compatibilidade com bases antigas)
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['super_admin', 'superadmin'], true);
    }
}

/**
 * Verifica se o usuário atual é admin (comum ou super)
 */
if (!function_exists('isAdmin')) {
    function isAdmin(): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // ⭐ SUPER ADMIN HARDCODED - du.claza@gmail.com sempre tem acesso total
        if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'du.claza@gmail.com') {
            return true;
        }
        
        // Verificar role normal (compatibilidade com bases antigas)
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'super_admin', 'superadmin'], true);
    }
}

/**
 * Retorna o role do usuário atual
 * ⭐ REGRA ESPECIAL: du.claza@gmail.com sempre retorna 'super_admin'
 */
if (!function_exists('getUserRole')) {
    function getUserRole(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        // ⭐ SUPER ADMIN HARDCODED - du.claza@gmail.com sempre é super_admin
        if (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'du.claza@gmail.com') {
            return 'super_admin';
        }
        
        return $_SESSION['user_role'] ?? 'user';
    }
}
