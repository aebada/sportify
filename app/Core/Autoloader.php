<?php

namespace App\Core;

/**
 * PSR-4-ish autoloader mapping the "App\" namespace to the /app directory.
 * Avoids any Composer dependency so the project runs on bare shared hosting.
 */
class Autoloader
{
    public static function register(string $appDir): void
    {
        spl_autoload_register(function (string $class) use ($appDir) {
            $prefix = 'App\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }
            $relative = substr($class, strlen($prefix));
            $path = $appDir . '/' . str_replace('\\', '/', $relative) . '.php';
            if (is_file($path)) {
                require $path;
            }
        });
    }
}
