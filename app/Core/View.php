<?php

namespace App\Core;

/**
 * Minimal Blade-like view engine. Views are plain PHP files under /views.
 * A view renders into a $content buffer that is injected into a layout.
 */
class View
{
    protected static array $shared = [];

    public static function share(string $key, $value): void
    {
        self::$shared[$key] = $value;
    }

    /**
     * Render a view (dot notation) optionally wrapped in a layout.
     */
    public static function render(string $view, array $data = [], ?string $layout = 'app'): string
    {
        $viewPath = self::path($view);
        if (!is_file($viewPath)) {
            throw new \RuntimeException("View not found: {$view} ({$viewPath})");
        }

        $data = array_merge(self::$shared, $data);

        $content = self::capture($viewPath, $data);

        if ($layout === null) {
            return $content;
        }

        $layoutPath = self::path('layouts.' . $layout);
        $data['content'] = $content;
        return self::capture($layoutPath, $data);
    }

    /** Render a partial and return its HTML (for use inside views). */
    public static function partial(string $view, array $data = []): string
    {
        $data = array_merge(self::$shared, $data);
        return self::capture(self::path($view), $data);
    }

    protected static function capture(string $path, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }

    protected static function path(string $view): string
    {
        $base = App::config('paths.views');
        return $base . '/' . str_replace('.', '/', $view) . '.php';
    }
}
