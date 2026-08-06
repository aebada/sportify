<?php
declare(strict_types=1);
/**
 * RefereeX AI standalone entry - serves static HTML from disk.
 * deploy-marker:20260705-refereex-ai-php-v1
 */
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Sportify-RefereeX: refereex-ai-php-20260705-v1');

$html = __DIR__ . '/live-sports.html';
if (!is_readable($html)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "refereex-ai-error: missing live-sports.html\n";
    exit;
}

readfile($html);
