<?php
/**
 * OnlyTalents subdomain front controller — production docroot (talents/).
 * deploy as talents/index.php — uses $root = __DIR__ (not dirname(__DIR__)).
 */
register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
        }
        echo 'fatal=' . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
    }
});

$root = __DIR__;

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

require_once $root . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register($root . '/app');
if (is_file($root . '/app/Support/ot_helpers_hotfix.php')) {
    require_once $root . '/app/Support/ot_helpers_hotfix.php';
}

require $root . '/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($path, $scriptDir)) {
    $path = substr($path, strlen($scriptDir));
}
$path = '/' . trim($path, '/');
if ($path === '/public' || str_starts_with($path, '/public/')) {
    $path = $path === '/public' ? '/' : '/' . trim(substr($path, 7), '/');
}
if ($path === '/set-lang.php' || str_ends_with($path, '/set-lang.php')) {
    require __DIR__ . '/set-lang.php';
    return;
}
if ($path === '/opcache-purge.php' || str_ends_with($path, '/opcache-purge.php')) {
    require __DIR__ . '/opcache-purge.php';
    return;
}
if (!empty($_GET['sportify_deploy_check'])
    || $path === '/deploy-check.php' || str_ends_with($path, '/deploy-check.php')) {
    require __DIR__ . '/deploy-check.php';
    return;
}

try {
    \App\Core\App::run();
} catch (\Throwable $e) {
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8', true, 500);
    }
    echo 'error=' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
}
