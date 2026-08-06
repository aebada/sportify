<?php
/**
 * RefereeX AI — fresh PHP entry (never-cached filename for OPcache bypass).
 * deploy-marker: refereex-live-20260705
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-LiteSpeed-Purge: *');
header('X-Sportify-RefereeX: refereex-live-20260705');

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

$root = dirname(__DIR__);
foreach ([
    $root . '/public/index.php',
    $root . '/public/index-20260704-refereex-v1.php',
    $root . '/app/Controllers/RefereeXController.php',
    $root . '/config/routes-refereex-v1.php',
    $root . '/views/pages/refereex-ai/index.php',
    $root . '/bootstrap-20260621-authfix.php',
] as $file) {
    if (function_exists('opcache_invalidate') && is_file($file)) {
        @opcache_invalidate(realpath($file) ?: $file, true);
    }
}

require $root . '/bootstrap-20260621-authfix.php';
echo (new \App\Controllers\RefereeXController())->index();
