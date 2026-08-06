<?php
/**
 * Sportify front controller — hero role CTAs (2026-06-21).
 */
$root = dirname(__DIR__);
$__otHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (in_array($__otHost, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
    if (function_exists('opcache_invalidate')) {
        foreach ([
            $root . '/app/Support/OnlyTalentsContext.php',
            $root . '/app/Support/helpers.php',
            $root . '/bootstrap-20260621-authfix.php',
            $root . '/config/routes-onlytalents.php',
            $root . '/app/Controllers/OnlyTalentsDiscoveryController.php',
        ] as $__otFile) {
            if (is_file($__otFile)) {
                @opcache_invalidate(realpath($__otFile) ?: $__otFile, true);
            }
        }
    }
}
if (function_exists('opcache_invalidate')) {
    foreach ([
        $root . '/app/Core/Router.php',
        $root . '/app/Core/Controller.php',
        $root . '/app/Support/Rbac.php',
        $root . '/config/roles.php',
        $root . '/app/Controllers/AuthController.php',
        $root . '/app/Controllers/AdminController.php',
        $root . '/app/Controllers/AdminMembershipController.php',
        $root . '/config/routes-20260621-memberships-v1.php',
        $root . '/app/Controllers/WellnessController.php',
    ] as $authOpcacheFile) {
        if (is_file($authOpcacheFile)) {
            @opcache_invalidate(realpath($authOpcacheFile) ?: $authOpcacheFile, true);
        }
    }
}
if (in_array($__otHost, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)
    && is_file($root . '/app/Support/ot_helpers_hotfix.php')) {
    require_once $root . '/app/Core/Autoloader.php';
    \App\Core\Autoloader::register($root . '/app');
    require_once $root . '/app/Support/ot_helpers_hotfix.php';
}

require $root . '/bootstrap-20260621-authfix.php';

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
if ($path === '/wellness-migrate-20260629.php' || str_ends_with($path, '/wellness-migrate-20260629.php')) {
    require __DIR__ . '/wellness-migrate-20260629.php';
    return;
}
if ($path === '/opcache-reset-20260621.php' || str_ends_with($path, '/opcache-reset-20260621.php')) {
    require __DIR__ . '/opcache-reset-20260621.php';
    return;
}
if (!empty($_GET['sportify_deploy_check'])
    || $path === '/deploy-check.php' || str_ends_with($path, '/deploy-check.php')) {
    require __DIR__ . '/deploy-check.php';
    return;
}
if ($path === '/rbac-probe-20260621.php' || str_ends_with($path, '/rbac-probe-20260621.php')) {
    require __DIR__ . '/rbac-probe-20260621.php';
    return;
}

if (preg_match('#^/matches/(\d+)$#', $path, $matchRoute)) {
    foreach ([
        $root . '/app/Controllers/MatchController.php',
        $root . '/app/Services/MatchWatchService.php',
        $root . '/app/Services/LiveHd7SyncService.php',
        $root . '/views/pages/matches/show-20260621-watch-v1.php',
        $root . '/views/partials/match-watch-20260621-v1.php',
        $root . '/config/live_streams.php',
    ] as $file) {
        if (function_exists('opcache_invalidate') && is_file($file)) {
            @opcache_invalidate(realpath($file) ?: $file, true);
        }
    }
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Sportify-Match-Watch: 20260621-v1');
    echo (new \App\Controllers\MatchWatchLive20260621Controller())->show($matchRoute[1]);
    return;
}

$router = \App\Core\App::$router;
if (\App\Support\OnlyTalentsContext::isActive()) {
    require $root . '/config/routes-onlytalents.php';
} else {
    require $root . '/config/routes-20260621-memberships-v1.php';
    if (is_file($root . '/config/routes-wellness-v1.php')) {
        require $root . '/config/routes-wellness-v1.php';
    }
}
$router->dispatch(\App\Core\App::$request);
