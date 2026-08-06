<?php
declare(strict_types=1);
$qs = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
$target = '/only-talents' . (is_string($qs) && $qs !== '' ? '?' . $qs : '');
header('Location: ' . $target, true, 301);
header('Cache-Control: no-store');
header('X-Sportify-Ot-Canonical: standalone-20260707');
exit;
