<?php
/**
 * RefereeX AI — standalone PHP entry (bypasses stale router/OPcache).
 * deploy-marker: refereex-ai-php-v20260705
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-LiteSpeed-Purge: *');
header('X-Sportify-RefereeX: refereex-ai-php-v20260705');

$root = dirname(__DIR__);
foreach ([
    $root . '/app/Controllers/RefereeXController.php',
    $root . '/views/pages/refereex-ai/index.php',
    $root . '/config/routes-refereex-v1.php',
] as $file) {
    if (function_exists('opcache_invalidate') && is_file($file)) {
        @opcache_invalidate(realpath($file) ?: $file, true);
    }
}

require $root . '/bootstrap-20260621-authfix.php';
echo (new \App\Controllers\RefereeXController())->index();
