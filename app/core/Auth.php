<?php
// app/core/Auth.php

class Auth {

    public static function startSecureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
    }

    public static function login(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id']       = $user['id_utilisateur'];
        $_SESSION['user_nom']      = $user['nom'] . ' ' . $user['prenom'];
        $_SESSION['user_role']     = $user['role'];
        $_SESSION['login_time']    = time();
        $_SESSION['last_activity'] = time();
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function isAuthenticated(): bool {
        if (!isset($_SESSION['user_id'])) return false;
        // Timeout session : 8 heures
        if (time() - $_SESSION['last_activity'] > 28800) {
            self::logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function hasRole(string ...$roles): bool {
        return in_array($_SESSION['user_role'] ?? '', $roles, true);
    }

    public static function requireAuth(): void {
        if (!self::isAuthenticated()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void {
        self::requireAuth();
        if (!self::hasRole(...$roles)) {
            http_response_code(403);
            die('Acces interdit.');
        }
    }

    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(string $token): bool {
        return isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function userId(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    public static function userNom(): string {
        return $_SESSION['user_nom'] ?? '';
    }

    public static function userRole(): string {
        return $_SESSION['user_role'] ?? '';
    }
}
