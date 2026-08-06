<?php
declare(strict_types=1);
$base = dirname(__DIR__);
$files = [
    'public/assets/css/onlytalents.css',
    'views/pages/onlytalents/home-20260703.php',
    'app/Support/helpers.php',
    'app/Controllers/OnlyTalentsDiscoveryController.php',
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
$out = $base . '/public/push-ot-home-immersive-20260707.php';
$json = json_encode($payloads, JSON_UNESCAPED_SLASHES);
$body = <<<PHP
<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');
if ((\$_GET['key'] ?? '') !== 'ot-immersive-20260707') {
    http_response_code(403);
    echo "Forbidden\\n";
    exit;
}
\$root = dirname(__DIR__);
echo "marker=push-ot-home-immersive-20260707\\n";
echo "root={\$root}\\n";
\$files = json_decode('{$json}', true, 512, JSON_THROW_ON_ERROR);
\$extra = [
    'public/assets/css/onlytalents.css' => ['assets/css/onlytalents.css'],
];
\$ok = 0;
\$fail = 0;
foreach (\$files as \$rel => \$b64) {
    \$bytes = base64_decode(\$b64, true);
    if (\$bytes === false) {
        echo "decode_fail={\$rel}\\n";
        \$fail++;
        continue;
    }
    \$dests = array_merge([\$rel], \$extra[\$rel] ?? []);
    foreach (\$dests as \$destRel) {
        \$dest = \$root . '/' . \$destRel;
        \$dir = dirname(\$dest);
        if (!is_dir(\$dir)) {
            @mkdir(\$dir, 0755, true);
        }
        if (@file_put_contents(\$dest, \$bytes) === false) {
            echo "fail={\$destRel}\\n";
            \$fail++;
            continue;
        }
        echo "ok={\$destRel} bytes=" . strlen(\$bytes) . "\\n";
        \$ok++;
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(realpath(\$dest) ?: \$dest, true);
        }
    }
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
echo "summary ok={\$ok} fail={\$fail}\\n";
PHP;
file_put_contents($out, $body);
echo "wrote {$out} (" . strlen($body) . " bytes)\n";
