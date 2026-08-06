<?php
if (PHP_SAPI === 'cli') {
    return;
}
$host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
if (in_array($host, ['talents.sportifyplus.de', 'www.talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    header('Location: https://sportifyplus.de/only-talents/home', true, 302);
    header('Cache-Control: no-store');
    exit;
}
