/** Early OnlyTalents subdomain homepage fix (2026-07-05). */
$__otHostBag = strtolower(implode(' ', array_filter([
    (string) ($_SERVER['HTTP_HOST'] ?? ''),
    (string) ($_SERVER['SERVER_NAME'] ?? ''),
    (string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''),
])));
if (str_contains($__otHostBag, 'talents.sportifyplus.de') || str_contains($__otHostBag, 'onlytalents.sportifyplus.de')) {
    $__otPathEarly = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (in_array($__otPathEarly, ['/', '/index.php', '/public/index.php', ''], true)) {
        header('Location: https://sportifyplus.de/only-talents/home', true, 302);
        header('Cache-Control: no-store');
        exit;
    }
}
/**
 * Sportify front controller — RefereeX PHP routing (2026-07-05).
 * deploy-marker: index-refereex-php-v20260705
 */
$root = dirname(__DIR__);
$__rxUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($__rxUri === '/refereex-boot-probe' || $__rxUri === '/refereex-boot-probe/') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    header('X-Sportify-Probe: refereex-php-' . date('c'));
    echo "probe_ok\nmtime=" . filemtime(__FILE__) . "\nuri=$__rxUri\nscript=" . ($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
    exit;
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
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
        $root . '/app/Controllers/RefereeXController.php',
        $root . '/config/routes-refereex-v1.php',
        $root . '/views/pages/refereex-ai/index.php',
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
if ($path === '/deploy-check-ot-immersive-v5.php' || str_ends_with($path, '/deploy-check-ot-immersive-v5.php')) {
    require __DIR__ . '/deploy-check-ot-immersive-v5.php';
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
if ($path === '/opcache-reset-refereex.php' || str_ends_with($path, '/opcache-reset-refereex.php')) {
    require __DIR__ . '/opcache-reset-refereex.php';
    return;
}
if (!empty($_GET['sportify_deploy_check'])
    || $path === '/deploy-check.php' || str_ends_with($path, '/deploy-check.php')) {
    require __DIR__ . '/deploy-check.php';
    return;
}

if ($path === '/refereex-ai' || str_starts_with($path, '/refereex-ai/')
    || $path === '/refereex-ai.php' || str_ends_with($path, '/refereex-ai.php')) {
    foreach ([
        $root . '/app/Controllers/RefereeXController.php',
        $root . '/views/pages/refereex-ai/index.php',
        $root . '/public/assets/css/refereex-ai.css',
        $root . '/public/assets/js/refereex-ai.js',
        $root . '/config/routes-refereex-v1.php',
    ] as $file) {
        if (function_exists('opcache_invalidate') && is_file($file)) {
            @opcache_invalidate(realpath($file) ?: $file, true);
        }
    }
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-Sportify-RefereeX: index-php-v20260705');
    echo (new \App\Controllers\RefereeXController())->index();
    return;
}
if ($path === '/refereex-diag-20260702.php' || str_ends_with($path, '/refereex-diag-20260702.php')) {
    require __DIR__ . '/refereex-diag-20260702.php';
    return;
}
if ($path === '/push-refereex-ai-20260702.php' || str_ends_with($path, '/push-refereex-ai-20260702.php')) {
    require __DIR__ . '/push-refereex-ai-20260702.php';
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
    if (is_file($root . '/config/routes-refereex-v1.php')) {
        require $root . '/config/routes-refereex-v1.php';
    }
}
$router->dispatch(\App\Core\App::$request);
