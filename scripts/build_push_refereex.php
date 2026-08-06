<?php
declare(strict_types=1);
$base = dirname(__DIR__);
$files = [
    'app/Controllers/RefereeXController.php',
    'config/routes-refereex-v1.php',
    'public/refereex-ai.php',
    'public/.htaccess',
    'public/index-20260621-hero-roles-v1.php',
    'views/pages/refereex-ai/index.php',
    'views/partials/home-refereex.php',
    'public/assets/css/refereex-ai.css',
    'public/assets/js/refereex-ai.js',
];
$payloads = [];
foreach ($files as $rel) {
    $path = $base . '/' . $rel;
    if (!is_file($path)) {
        fwrite(STDERR, "missing {$rel}\n");
        exit(1);
    }
    $payloads[$rel] = base64_encode((string) file_get_contents($path));
}
$out = $base . '/public/push-refereex-ai-20260702.php';
$body = <<<'PHP'
<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');
if (($_GET['key'] ?? '') !== 'refereex-fix-20260702') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}
$root = dirname(__DIR__);
echo "marker=push-refereex-ai-20260702\n";
echo "root={$root}\n";
$files = %PAYLOADS%;
$ok = 0;
$fail = 0;
foreach ($files as $rel => $b64) {
    $dest = $root . '/' . $rel;
    $dir = dirname($dest);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $bytes = base64_decode($b64, true);
    if ($bytes === false || @file_put_contents($dest, $bytes) === false) {
        echo "fail={$rel}\n";
        $fail++;
        continue;
    }
    echo "ok={$rel} bytes=" . strlen($bytes) . "\n";
    $ok++;
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate(realpath($dest) ?: $dest, true);
    }
}
$mirrors = [
    'public/assets/css/refereex-ai.css' => 'assets/css/refereex-ai.css',
    'public/assets/js/refereex-ai.js' => 'assets/js/refereex-ai.js',
    'public/refereex-ai.php' => 'refereex-ai.php',
];
foreach ($mirrors as $srcRel => $destRel) {
    $src = $root . '/' . $srcRel;
    $dest = $root . '/' . $destRel;
    if (!is_file($src)) {
        continue;
    }
    $dir = dirname($dest);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    @copy($src, $dest);
    echo "mirror={$destRel}\n";
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
echo "summary ok={$ok} fail={$fail}\n";
PHP;
$body = str_replace('%PAYLOADS%', var_export($payloads, true), $body);
file_put_contents($out, $body);
echo 'Wrote ' . $out . ' (' . number_format(filesize($out)) . " bytes)\n";
