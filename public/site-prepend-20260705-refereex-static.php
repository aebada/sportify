<?php
declare(strict_types=1);
/**
 * RefereeX static landing — readfile bypass (no router/OPcache).
 * deploy-marker: refereex-static-prepend-v2
 */
if (PHP_SAPI === 'cli') {
    return;
}

$__p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__rxPaths = ['/refereex-ai', '/live-sports.html', '/refereex-ai-static.html', '/refereex-ai.html'];
if (in_array($__p, $__rxPaths, true) || str_starts_with($__p, '/refereex-ai/')) {
    $__candidates = [
        __DIR__ . '/public/refereex-ai-static.html',
        __DIR__ . '/public/live-sports.html',
        __DIR__ . '/refereex-ai-static.html',
        __DIR__ . '/live-sports.html',
        '/home/u234903558/domains/sportifyplus.de/public_html/public/refereex-ai-static.html',
        '/home/u234903558/domains/sportifyplus.de/public_html/public/live-sports.html',
        '/home/u234903558/domains/sportifyplus.de/public_html/refereex-ai-static.html',
        '/home/u234903558/domains/sportifyplus.de/public_html/live-sports.html',
    ];
    foreach ($__candidates as $__html) {
        if (is_readable($__html)) {
            http_response_code(200);
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('X-Sportify-RefereeX: static-prepend-v2');
            readfile($__html);
            exit;
        }
    }
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    header('X-Sportify-RefereeX: static-prepend-missing');
    echo "refereex-static-prepend: html not found\n";
    exit;
}

// Legacy output-buffer prepend for other routes (trust logos, flags)
$__sportifyOtHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (in_array($__sportifyOtHost, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    return;
}

if (!function_exists('trust_logos_markup') && is_file(__DIR__ . '/site-prepend-20260705-refereex-live.php')) {
    // Load only ob_start portion from legacy file if needed — skip refereex block
}

ob_start(static function (string $buffer): string {
    if (!str_contains($buffer, '</body>')) {
        return $buffer;
    }
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if (preg_match('#/(matches|fixtures)(/|$|\?)#', $uri)) {
        $tag = '<script src="/assets/js/flag-fix-20260619.js?v=20260619" defer></script>';
        if (!str_contains($buffer, 'flag-fix-20260619.js')) {
            $buffer = str_replace('</body>', $tag . '</body>', $buffer);
        }
    }
    return $buffer;
});
