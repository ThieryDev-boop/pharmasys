<?php
// app/core/Controller.php

class Controller {

    protected function render(string $view, array $data = []): void {
        extract($data);
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("Vue introuvable : " . htmlspecialchars($viewFile));
        }
        require_once $viewFile;
    }

    protected function renderLayout(string $layout, string $view, array $data = []): void {
        extract($data);
        $viewFile   = APP_PATH . '/views/' . $view . '.php';
        $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("Vue introuvable : " . htmlspecialchars($viewFile));
        }
        if (!file_exists($layoutFile)) {
            http_response_code(500);
            die("Layout introuvable : " . htmlspecialchars($layoutFile));
        }
        ob_start();
        require_once $viewFile;
        $content = ob_get_clean();
        require_once $layoutFile;
    }

    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    protected function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Methode non autorisee.');
        }
    }

    protected function input(string $key, string $default = ''): string {
        return trim(htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8'));
    }

    protected function inputInt(string $key, int $default = 0): int {
        return (int)($_POST[$key] ?? $default);
    }

    protected function inputFloat(string $key, float $default = 0.0): float {
        return (float)($_POST[$key] ?? $default);
    }

    // Transactions PDO via un model temporaire
    protected function beginTransaction(): void {
        (new Model())->beginTransaction();
    }

    protected function commit(): void {
        (new Model())->commit();
    }

    protected function rollback(): void {
        (new Model())->rollback();
    }
}