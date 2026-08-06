<?php
declare(strict_types=1);
$base = dirname(__DIR__);
$files = [
    'app/Support/ot_helpers_hotfix.php',
    'app/Support/helpers.php',
    'app/Support/helpers-20260629.php',
    'app/Support/helpers-20260702-ot.php',
    'bootstrap-20260629-ot.php',
    'bootstrap.php',
    'public/index-onlytalents-production.php',
    'public/index-onlytalents-redirect.php',
    'public/index-onlytalents.html',
    'public/.htaccess-onlytalents',
    'public/site-prepend-20260629-ot.php',
    'public/.user.ini-talents',
    'views/pages/onlytalents/home-20260701.php',
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
$out = $base . '/public/push-ot-talents-live-20260701.php';
$json = json_encode($payloads, JSON_UNESCAPED_SLASHES);
$body = <<<PHP
<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');
if ((\$_GET['key'] ?? '') !== 'ot-live-20260701') {
    http_response_code(403);
    echo "Forbidden\\n";
    exit;
}
\$roots = [
    dirname(__DIR__),
    dirname(__DIR__) . '/talents',
    dirname(__DIR__) . '/domains/sportifyplus.de/public_html',
    dirname(__DIR__) . '/domains/sportifyplus.de/public_html/talents',
];
\$roots = array_values(array_unique(array_filter(\$roots, 'is_dir')));
echo "marker=push-ot-talents-live-20260701\\n";
\$files = json_decode('{$json}', true, 512, JSON_THROW_ON_ERROR);
\$map = [
    'public/index-onlytalents-production.php' => 'index.php',
    'public/index-onlytalents-redirect.php' => 'index.php.redirect-backup',
    'public/index-onlytalents.html' => 'index.html',
    'public/.htaccess-onlytalents' => '.htaccess',
    'public/site-prepend-20260629-ot.php' => 'site-prepend-20260629-ot.php',
    'public/.user.ini-talents' => '.user.ini',
];
\$ok = 0;
\$fail = 0;
foreach (\$roots as \$root) {
    echo "root={\$root}\\n";
    foreach (\$files as \$rel => \$b64) {
        \$destRel = \$map[\$rel] ?? \$rel;
        \$dest = \$root . '/' . \$destRel;
        \$dir = dirname(\$dest);
        if (!is_dir(\$dir)) {
            @mkdir(\$dir, 0755, true);
        }
        \$bytes = base64_decode(\$b64, true);
        if (\$bytes === false || @file_put_contents(\$dest, \$bytes) === false) {
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
