<?php
/**
 * RefereeX static serve via deploy-check path (2026-07-14).
 * deploy-marker: deploy-check-refereex-static-20260714
 */
declare(strict_types=1);

if (($_GET['serve'] ?? '') !== 'refereex' && empty($_GET['refereex_ai']) && empty($_GET['refereex'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "deploy-check-refereex-static-20260714\n";
    echo "use ?serve=refereex or ?refereex=1\n";
    exit;
}

if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(__FILE__, true);
}

$root = dirname(__DIR__);
$candidates = [
    $root . '/live-sports.html',
    $root . '/public/live-sports.html',
    $root . '/public_html/public/live-sports.html',
    $root . '/public_html/live-sports.html',
    dirname($root) . '/live-sports.html',
];

foreach ($candidates as $html) {
    if (is_readable($html)) {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-LiteSpeed-Purge: *');
        header('X-Sportify-RefereeX: deploy-check-static-20260714');
        readfile($html);
        exit;
    }
}

http_response_code(503);
header('Content-Type: text/plain; charset=UTF-8');
echo "refereex html not found\nroot={$root}\n";
