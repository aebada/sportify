<?php
/**
 * RefereeX AI page serve — overwrites known-good hotfix path (2026-07-14).
 * deploy-marker: refereex-hotfix-20260714
 */
declare(strict_types=1);

if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(__FILE__, true);
}

$files = [
    __DIR__ . '/live-sports.html',
    __DIR__ . '/refereex-ai.html',
    __DIR__ . '/refereex-ai-static.html',
];

foreach ($files as $html) {
    if (is_readable($html)) {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-LiteSpeed-Purge: *');
        header('X-Sportify-RefereeX: hotfix-20260714');
        readfile($html);
        exit;
    }
}

http_response_code(503);
header('Content-Type: text/plain; charset=UTF-8');
echo "refereex-hotfix-20260714: html missing\n";
