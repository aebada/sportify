<?php
declare(strict_types=1);
/**
 * Site-wide prepend — RefereeX early handler (2026-07-07).
 * deploy-marker: site-prepend-refereex-20260707
 */
if (PHP_SAPI === 'cli') {
    return;
}

if (!headers_sent()) {
    header('X-Sportify-Ot-Prepend: 20260707-refereex');
    header('X-Sportify-Auth-Brand-Prepend: 20260720-v1');
}

$__p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__rxRefereex = !empty($_GET['refereex'])
    || $__p === '/refereex-ai'
    || str_starts_with($__p, '/refereex-ai/')
    || $__p === '/refereex-ai.php'
    || str_ends_with($__p, '/refereex-ai.php')
    || str_contains($__p, 'refereex-serve-20260707');

if ($__rxRefereex) {
    header('Location: https://aebada.github.io/sportify-refereex/', true, 302);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-Sportify-RefereeX: prepend-ghpages-redirect-20260715');
    exit;
}

// Delegate to existing site prepend for trust logos / OT subdomain handling.
$__legacyPrepend = __DIR__ . '/site-prepend-20260705-refereex-live.php';
if (is_file($__legacyPrepend)) {
    require $__legacyPrepend;
    return;
}
