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
            http_response_code(404);
            include $path404;
        }
        echo ob_get_clean();
    }

    protected function redirect(string $path): void
    {
        header('Location: /' . ltrim($path, '/'));
        exit;
    }

}