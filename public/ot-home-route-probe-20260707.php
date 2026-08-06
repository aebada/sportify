<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
$root = dirname(__DIR__);
foreach ([
    $root . '/config/routes-20260629-memberships-wellness-v1.php',
    $root . '/app/Controllers/OnlyTalentsDiscoveryController.php',
] as $file) {
    if (function_exists('opcache_invalidate') && is_file($file)) {
        @opcache_invalidate(realpath($file) ?: $file, true);
        @touch($file);
    }
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
echo 'uri=' . (string)($_SERVER['REQUEST_URI'] ?? '') . "\n";
echo 'script=' . (string)($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
require $root . '/bootstrap-20260621-authfix.php';
$req = \App\Core\App::$request;
echo 'app_path=' . $req->path . "\n";
$router = \App\Core\App::$router;
require $root . '/config/routes-20260629-memberships-wellness-v1.php';
$ref = new ReflectionClass($router);
$prop = $ref->getProperty('routes');
$prop->setAccessible(true);
$routes = $prop->getValue($router);
foreach ($routes as $r) {
    if (($r['method'] ?? '') === 'GET' && preg_match($r['regex'], $req->path)) {
        echo 'match=' . ($r['action'][0] ?? '') . '::' . ($r['action'][1] ?? '') . ' [' . ($r['name'] ?? '') . "]\n";
        break;
    }
}
$ctrl = (string) file_get_contents($root . '/app/Controllers/OnlyTalentsDiscoveryController.php');
echo 'legacy_method=' . (str_contains($ctrl, 'homeLegacyRedirect') ? 'yes' : 'no') . "\n";
echo 'home_guard=' . (str_contains($ctrl, "preg_match('#^/only-talents/home") ? 'yes' : 'no') . "\n";
