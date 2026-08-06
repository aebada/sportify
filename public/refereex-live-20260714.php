<?php
/**
 * RefereeX AI static serve — new filename bypasses frozen OPcache (2026-07-14).
 * deploy-marker: refereex-live-20260714
 */
declare(strict_types=1);

if (PHP_SAPI === 'cli') {
    exit(0);
}

foreach (glob(__DIR__ . '/refereex-ai*.html') ?: [] as $file) {
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate(realpath($file) ?: $file, true);
    }
}
if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(__FILE__, true);
}

$candidates = [
    __DIR__ . '/live-sports.html',
    __DIR__ . '/refereex-ai.html',
    __DIR__ . '/refereex-ai-static.html',
];

foreach ($candidates as $html) {
    if (is_readable($html)) {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-LiteSpeed-Purge: *');
        header('X-Sportify-RefereeX: live-20260714');
        readfile($html);
        exit;
    }
}

http_response_code(503);
header('Content-Type: text/plain; charset=UTF-8');
header('X-Sportify-RefereeX: live-20260714-missing');
echo "refereex-live-20260714: html not found\n";
