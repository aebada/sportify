<?php
/** OT canonical URL guard — fresh entry bypasses frozen OPcache (2026-07-07). */
$__otMainHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
$__otMainPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (in_array($__otMainHost, ['sportifyplus.de', 'www.sportifyplus.de'], true)
    && preg_match('#^/only-talents/home/?$#i', $__otMainPath)
    && in_array(strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')), ['GET', 'HEAD'], true)) {
    $__otMainQs = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    header('Location: /only-talents' . (is_string($__otMainQs) && $__otMainQs !== '' ? '?' . $__otMainQs : ''), true, 301);
    header('Cache-Control: no-store');
    header('X-Sportify-Ot-Canonical: fresh-entry-v2-20260707');
    exit;
}
require __DIR__ . '/index-20260707-ot-immersive-v1.php';
