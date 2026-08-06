<?php
declare(strict_types=1);
/**
 * RefereeX early handler - serves static HTML from disk (bypasses OPcache router freeze).
 * deploy-marker:20260705-refereex-prepend-readfile-v1
 */
if (PHP_SAPI === 'cli') {
    return;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';

$refereePaths = [
    '/refereex-ai',
    '/live-sports-hotfix-20260619.php',
];

if (!in_array($path, $refereePaths, true) && !str_starts_with($path, '/refereex-ai/')) {
    return;
}

$html = __DIR__ . '/live-sports.html';
if (!is_readable($html)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "refereex-prepend-error: missing live-sports.html\n";
    exit;
}

http_response_code(200);
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Sportify-RefereeX: prepend-readfile-20260705-v1');

if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(realpath($html) ?: $html, true);
}

readfile($html);
exit;
