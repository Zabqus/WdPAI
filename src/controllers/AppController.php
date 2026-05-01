<?php



class AppController {
    protected function isGet(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'GET';
    }

    protected function isPost(): bool
    {
        return $_SERVER["REQUEST_METHOD"] === 'POST';
    }
 
    protected function render(string $template, array $variables = []): void
    {
        $templatePath = 'src/Views/' . $template . '.php';
        $path404      = 'src/Views/404.php';

        extract($variables);

        ob_start();
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            ob_end_clean();
            ErrorHandler::render(404);
            return;
        }
        echo ob_get_clean();
    }

    protected function redirect(string $path): void
    {
        header('Location: /' . ltrim($path, '/'));
        exit;
    }

    protected function requireLogin(): void
    {
        if (!Session::has('user_id')) {
            $this->redirect('login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireLogin();

        if (Session::get('user_role') !== 'admin') {
            ErrorHandler::render(403);
            exit;
        }
    }

    protected function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }
}