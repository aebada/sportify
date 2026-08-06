<?php
/**
 * RefereeX AI serve — replaces livehd7 hotfix path (2026-07-14).
 * deploy-marker: refereex-livehd7-20260714
 */
declare(strict_types=1);

if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(__FILE__, true);
}

$html = __DIR__ . '/live-sports.html';
if (!is_readable($html)) {
    $html = __DIR__ . '/public/live-sports.html';
}
if (!is_readable($html)) {
    $html = __DIR__ . '/public_html/public/live-sports.html';
}

if (is_readable($html)) {
    http_response_code(200);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-LiteSpeed-Purge: *');
    header('X-Sportify-RefereeX: livehd7-readfile-20260714');
    readfile($html);
    exit;
}

http_response_code(503);
header('Content-Type: text/plain; charset=UTF-8');
echo "refereex-livehd7-20260714: html missing\n";
