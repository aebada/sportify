<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Purge: *');

$root = dirname(__DIR__);
$files = [
    $root . '/config/routes-20260629-memberships-wellness-v1.php',
    $root . '/config/routes-20260621-memberships-v1.php',
    $root . '/app/Core/App.php',
    $root . '/app/Controllers/OnlyTalentsDiscoveryController.php',
    $root . '/views/layouts/onlytalents.php',
    $root . '/public/assets/css/onlytalents.css',
];
foreach ($files as $file) {
    if (function_exists('opcache_invalidate') && is_file($file)) {
        @opcache_invalidate(realpath($file) ?: $file, true);
        @touch($file);
        echo 'invalidate=' . basename($file) . "\n";
    }
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "reset=done\n";
}

require $root . '/bootstrap-20260621-authfix.php';
$router = \App\Core\App::$router;
if (\App\Support\OnlyTalentsContext::isActive()) {
    require $root . '/config/routes-onlytalents.php';
} else {
    require $root . '/config/routes-20260621-memberships-v1.php';
}
$ref = new ReflectionClass($router);
$prop = $ref->getProperty('routes');
$prop->setAccessible(true);
$routes = $prop->getValue($router);
$match = 'no';
foreach ($routes as $r) {
    if (($r['method'] ?? '') === 'GET' && preg_match($r['regex'], '/only-talents')) {
        $match = ($r['action'][0] ?? '') . '::' . ($r['action'][1] ?? '') . ' [' . ($r['name'] ?? '') . ']';
        break;
    }
}
echo "route_match=/only-talents => {$match}\n";
echo 'layout_v5=' . (str_contains((string) file_get_contents($root . '/views/layouts/onlytalents.php'), 'ot-shorts-immersive-v5') ? 'yes' : 'no') . "\n";
echo 'css_mtime=' . filemtime($root . '/public/assets/css/onlytalents.css') . "\n";
echo 'marker=ot-immersive-probe-20260707-' . date('c') . "\n";
