<?php
namespace App\Core;

class View
{
    private string $viewPath;

    public function __construct()
    {
        $this->viewPath = BASE_PATH . '/app/Views';
    }

    /**
     * Render a view inside the default layout.
     *
     * @param string $view    Dot-or-slash path relative to Views/  (e.g. 'checkin/index')
     * @param array  $data    Variables passed to the view
     * @param string $layout  Layout file under Views/layouts/
     */
    public function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile   = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = $this->viewPath . '/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        // Extract data into local scope for the view
        extract($data, EXTR_SKIP);

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render inside layout (layout uses $content)
        if ($layout && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render a view with no layout (partials, AJAX fragments, JSON responses).
     */
    public function partial(string $view, array $data = []): void
    {
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
        extract($data, EXTR_SKIP);
        require $viewFile;
    }

    /** Escape a value for safe HTML output */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
