<?php
$host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
if (in_array($host, ['talents.sportifyplus.de', 'www.talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    header('Location: https://sportifyplus.de/only-talents/home', true, 302);
    header('Cache-Control: no-store');
    exit;
}
/**
 * Sportify front controller — hero roles + wellness (2026-06-21 bundle).
 */
require __DIR__ . '/index-20260621-hero-roles-v1.php';
