<?php
/**
 * RefereeX AI — fresh PHP entry (OPcache bypass via never-cached filename).
 * deploy-marker: refereex-serve-20260707
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-LiteSpeed-Purge: *');
header('X-Sportify-RefereeX: serve-20260707');

$root = dirname(__DIR__);
foreach ([
    $root . '/app/Controllers/RefereeXController.php',
    $root . '/views/pages/refereex-ai/index.php',
    $root . '/public/assets/css/refereex-ai.css',
    $root . '/public/assets/js/refereex-ai.js',
    $root . '/config/routes-refereex-v1.php',
    $root . '/lang/en/refereex-messages.php',
    $root . '/lang/de/refereex-messages.php',
    $root . '/lang/ar/refereex-messages.php',
] as $file) {
    if (function_exists('opcache_invalidate') && is_file($file)) {
        @opcache_invalidate(realpath($file) ?: $file, true);
    }
}

require $root . '/bootstrap-20260621-authfix.php';
echo (new \App\Controllers\RefereeXController())->index();
