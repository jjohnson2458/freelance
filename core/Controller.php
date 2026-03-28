<?php

namespace Core;

class Controller
{
    protected function view(string $name, array $data = []): void
    {
        extract($data);
        $viewPath = BASE_PATH . '/app/Views/' . str_replace('.', '/', $name) . '.php';
        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "View not found: {$name}";
            return;
        }
        require $viewPath;
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }

    protected function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    protected function getFlash(string $key): ?string
    {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
}
