<?php

namespace App\Core;

use App\Support\Rbac;

/** deploy-marker: auth-guest-redirect-v2-20260621 */
class Router
{
    /** @var array<int, array{method:string,pattern:string,regex:string,params:array,action:array,name:string}> */
    protected array $routes = [];
    /** @var array<string, string> name => raw pattern */
    protected array $names = [];

    public function get(string $pattern, array $action, string $name = ''): void
    {
        $this->add('GET', $pattern, $action, $name);
    }

    public function post(string $pattern, array $action, string $name = ''): void
    {
        $this->add('POST', $pattern, $action, $name);
    }

    protected function add(string $method, string $pattern, array $action, string $name): void
    {
        $params = [];
        $regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($m) use (&$params) {
            $params[] = $m[1];
            return '([^/]+)';
        }, $pattern);

        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'action'  => $action,
            'name'    => $name,
        ];

        if ($name !== '') {
            $this->names[$name] = $pattern;
        }
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }
            if (preg_match($route['regex'], $request->path, $matches)) {
                array_shift($matches);
                $args = array_combine($route['params'], $matches) ?: [];

                $denied = self::enforceRoutePermission($route['name'] ?? '');
                if ($denied !== null) {
                    echo $denied;
                    return;
                }

                [$class, $method] = $route['action'];
                $controller = new $class();
                echo $controller->$method(...array_values($args));
                return;
            }
        }

        $this->notFound();
    }

    protected function notFound(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if ($path === '/refereex-ai' || str_starts_with($path, '/refereex-ai/')) {
            http_response_code(200);
            header('Content-Type: text/html; charset=UTF-8');
            header('X-Sportify-RefereeX: router-fallback-20260705');
            echo (new \App\Controllers\RefereeXController())->index();
            return;
        }
        http_response_code(404);
        echo View::render('errors.404', ['title' => 'Page not found'], 'app');
    }

    /** Route-level RBAC gate (named routes only). Returns response body, empty string after redirect, or null when allowed. */
    protected static function enforceRoutePermission(string $routeName): ?string
    {
        $permission = Rbac::routePermission($routeName);
        if ($permission === null) {
            return null;
        }
        if (!Auth::check()) {
            flash_once('error', 'Please log in to continue.');
            $returnPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
            $query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
            if (is_string($query) && $query !== '') {
                $returnPath .= '?' . $query;
            }
            $returnPath = \App\Support\OnlyTalentsContext::sanitizeReturnUrl($returnPath);
            $login = self::route('login');
            if ($returnPath !== '') {
                $login .= '?next=' . rawurlencode($returnPath) . '&redirect=' . rawurlencode($returnPath);
            }
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            http_response_code(302);
            header('Location: ' . $login);
            return '';
        }
        if (!Auth::can($permission)) {
            http_response_code(403);
            return View::render('pages.errors.403', ['title' => __('errors.403.message')], 'app');
        }
        return null;
    }

    /** Generate a URL for a named route. */
    public static function route(string $name, array $params = []): string
    {
        $router = App::$router;
        if (!isset($router->names[$name])) {
            return '/';
        }
        $pattern = $router->names[$name];
        foreach ($params as $key => $value) {
            $pattern = str_replace('{' . $key . '}', rawurlencode((string) $value), $pattern);
        }
        return self::base() . ($pattern === '/' ? '/' : rtrim($pattern, '/'));
    }

    /** Base path when hosted in a subdirectory. */
    public static function base(): string
    {
        $configured = App::config('app.base_path');
        if ($configured !== null) {
            return rtrim((string) $configured, '/');
        }

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base = ($scriptDir === '/' || $scriptDir === '') ? '' : rtrim($scriptDir, '/');

        // Root .htaccess forwards /path → public/index.php; canonical URLs omit /public.
        if ($base === '/public') {
            return '';
        }

        return $base;
    }

    /** Number of registered routes (deploy diagnostics). */
    public function registeredCount(): int
    {
        return count($this->routes);
    }
}
