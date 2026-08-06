<?php

namespace App\Core;

/**
 * Application container & bootstrapper. Keeps global access to config,
 * the router and the current request without external dependencies.
 */
class App
{
    public static array $config = [];
    public static Router $router;
    public static Request $request;

    public static function boot(array $config): void
    {
        self::$config = $config;

        date_default_timezone_set('UTC');
        mb_internal_encoding('UTF-8');

        if ($config['app']['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
            ini_set('display_errors', '0');
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        Lang::boot($config);
        self::$request = new Request();
        self::$router  = new Router();
    }

    public static function config(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $value = self::$config;
        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }

    public static function run(): void
    {
        $root = self::$config['paths']['root'];
        if (\App\Support\OnlyTalentsContext::isActive()) {
            require $root . '/config/routes-onlytalents.php';
        } else {
            $routes = $root . '/config/routes.php';
            if (!is_file($routes)) {
                $routes = $root . '/config/routes-20260621-memberships-v1.php';
            }
            require $routes;
            if (is_file($root . '/config/routes-20260614-linkedin.php')) {
                require $root . '/config/routes-20260614-linkedin.php';
            }
        }
        self::$router->dispatch(self::$request);
    }
}
