<?php
/** Talents subdomain homepage — redirect before main front controller (2026-07-05). */
declare(strict_types=1);
$host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (!in_array($host, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    return;
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim($path, '/');
if ($path === '') {
    $path = '/';
}
if (in_array($path, ['/', '/index.php'], true) && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    header('Location: https://sportifyplus.de/only-talents/home', true, 302);
    header('Cache-Control: no-store');
    exit;
}
