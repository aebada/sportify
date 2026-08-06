<?php
declare(strict_types=1);
/**
 * OnlyTalents subdomain gateway — redirect to main-domain OnlyTalents routes.
 * Loaded via .user.ini auto_prepend on talents docroots.
 */
if (PHP_SAPI === 'cli') {
    return;
}

$host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

if (in_array($host, ['sportifyplus.de', 'www.sportifyplus.de'], true)
    && preg_match('#^/only-talents/home/?$#i', $path)
    && in_array($method, ['GET', 'HEAD'], true)) {
    $query = parse_url($uri, PHP_URL_QUERY);
    $qs = is_string($query) && $query !== '' ? '?' . $query : '';
    if (!headers_sent()) {
        header('Location: /only-talents' . $qs, true, 301);
        header('Cache-Control: no-store');
        header('X-Sportify-Ot-Canonical: 20260707');
    }
    exit;
}

if (!in_array($host, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    return;
}

if (!in_array($method, ['GET', 'HEAD'], true)) {
    return;
}

$path = '/' . trim($path, '/');
if ($path === '') {
    $path = '/';
}
$query = parse_url($uri, PHP_URL_QUERY);
$qs = $query !== null && $query !== '' ? '?' . $query : '';

foreach (['deploy-check', 'opcache-purge', 'ot-opcache-flush', 'ot-fatal-probe', 'ot-docroot-probe'] as $diag) {
    if (str_contains($path, $diag)) {
        return;
    }
}

if ($path === '/' || $path === '/index.php') {
    $target = 'https://sportifyplus.de/only-talents';
} elseif (in_array($path, ['/login', '/register'], true)) {
    $target = 'https://sportifyplus.de' . $path;
} else {
    $target = 'https://sportifyplus.de/only-talents' . $path;
}

if (!headers_sent()) {
    header('X-Sportify-Ot-Gateway: 20260706');
    header('Location: ' . $target . $qs, true, 302);
    header('Cache-Control: no-store');
}
exit;
