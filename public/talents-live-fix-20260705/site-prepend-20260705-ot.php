<?php
declare(strict_types=1);
if (PHP_SAPI === 'cli') {
    return;
}
$host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (!in_array($host, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    return;
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim($path, '/');
if ($path === '/' || $path === '/index.php') {
    header('Location: https://sportifyplus.de/only-talents/home', true, 302);
    header('Cache-Control: no-store');
    exit;
}
$otRoot = '/home/u234903558/domains/sportifyplus.de/public_html/talents';
$hotfix = $otRoot . '/app/Support/ot_helpers_hotfix.php';
if (is_file($hotfix)) {
    require_once $hotfix;
}
