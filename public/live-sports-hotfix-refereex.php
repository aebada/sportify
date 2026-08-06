<?php
/**
 * RefereeX AI via uncached PHP shell - bypasses hCDN static freeze.
 * deploy-marker:20260705-refereex-hotfix-v1
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Sportify-RefereeX: hotfix-20260705-v1');

$html = __DIR__ . '/live-sports.html';
if (!is_readable($html)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "refereex-hotfix-error: missing live-sports.html\n";
    exit;
}

if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(realpath($html) ?: $html, true);
}

readfile($html);
