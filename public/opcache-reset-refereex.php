<?php
/**
 * OPcache reset + RefereeX serve probe.
 * deploy-marker: opcache-reset-refereex-v20260705
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Purge: *');
header('X-Sportify-RefereeX: opcache-reset-v20260705');

$reset = function_exists('opcache_reset') ? @opcache_reset() : null;
echo 'opcache_reset=' . var_export($reset, true) . "\n";
echo 'time=' . date('c') . "\n";
echo 'file_mtime=' . filemtime(__FILE__) . "\n";

$root = dirname(__DIR__);
foreach ([
    $root . '/public/index.php',
    $root . '/public/index-20260704-refereex-v1.php',
    $root . '/app/Controllers/RefereeXController.php',
    $root . '/config/routes-refereex-v1.php',
    $root . '/views/pages/refereex-ai/index.php',
] as $file) {
    if (function_exists('opcache_invalidate') && is_file($file)) {
        @opcache_invalidate(realpath($file) ?: $file, true);
    }
    echo 'exists ' . basename($file) . '=' . (is_file($file) ? 'yes' : 'no') . "\n";
}

if (!empty($_GET['serve'])) {
    header('Content-Type: text/html; charset=UTF-8');
    require $root . '/bootstrap-20260621-authfix.php';
    echo (new \App\Controllers\RefereeXController())->index();
}
