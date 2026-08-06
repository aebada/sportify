<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');
$root = __DIR__;
echo "marker=ot-routes-purge-20260707\n";
echo "root={$root}\n";
foreach ([
    $root . '/config/routes.php',
    $root . '/config/routes-20260621-memberships-v1.php',
    $root . '/app/Controllers/OnlyTalentsDiscoveryController.php',
    $root . '/app/Support/helpers.php',
] as $file) {
    if (!is_file($file)) {
        echo 'missing=' . basename($file) . "\n";
        continue;
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate(realpath($file) ?: $file, true);
    }
    echo 'ok=' . basename($file) . "\n";
}
$routes = (string) file_get_contents($root . '/config/routes-20260621-memberships-v1.php');
echo 'legacy_redirect=' . (str_contains($routes, 'homeLegacyRedirect') ? 'yes' : 'no') . "\n";
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
echo "reset=done\n";
