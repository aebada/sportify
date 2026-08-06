<?php
declare(strict_types=1);
/**
 * One-shot deploy: wire Shortlist/Contact on AI recommendation cards.
 * Upload to public_html/public/, visit ?key=ai-rec-20260705, then delete.
 */
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');

if (($_GET['key'] ?? '') !== 'ai-rec-20260705') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

$rel = 'views/partials/ai-rec-card.php';
$roots = [
    dirname(__DIR__),
    dirname(__DIR__) . '/domains/sportifyplus.de/public_html',
    dirname(__DIR__) . '/public_html',
];

$source = dirname(__DIR__) . '/' . $rel;
if (!is_file($source)) {
    echo "missing_local={$rel}\n";
    exit(1);
}

$content = (string) file_get_contents($source);
$ok = 0;
$fail = 0;

foreach (array_unique(array_filter($roots, 'is_dir')) as $root) {
    $dest = $root . '/' . $rel;
    $dir = dirname($dest);
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        echo "fail_mkdir={$dir}\n";
        $fail++;
        continue;
    }
    if (@file_put_contents($dest, $content) !== false) {
        echo "ok={$dest}\n";
        $ok++;
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($dest, true);
        }
    } else {
        echo "fail={$dest}\n";
        $fail++;
    }
}

echo "done ok={$ok} fail={$fail}\n";
