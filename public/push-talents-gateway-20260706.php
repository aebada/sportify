<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');

if (($_GET['key'] ?? '') !== 'talents-gateway-20260706') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

$prepend = <<<'PHP'
<?php
declare(strict_types=1);
if (PHP_SAPI === 'cli') {
    return;
}
$host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (!in_array($host, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    return;
}
$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
if (!in_array($method, ['GET', 'HEAD'], true)) {
    return;
}
$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$path = parse_url($uri, PHP_URL_PATH) ?: '/';
$path = '/' . trim($path, '/');
if ($path === '') {
    $path = '/';
}
$query = parse_url($uri, PHP_URL_QUERY);
$qs = $query !== null && $query !== '' ? '?' . $query : '';
foreach (['deploy-check', 'opcache-purge', 'ot-opcache-flush', 'push-talents-gateway'] as $diag) {
    if (str_contains($path, $diag)) {
        return;
    }
}
if ($path === '/' || $path === '/index.php') {
    $target = 'https://sportifyplus.de/only-talents/home';
} elseif (in_array($path, ['/login', '/register'], true)) {
    $target = 'https://sportifyplus.de' . $path;
} else {
    $target = 'https://sportifyplus.de/only-talents' . $path;
}
header('X-Sportify-Ot-Gateway: 20260706');
header('Location: ' . $target . $qs, true, 302);
header('Cache-Control: no-store');
exit;
PHP;

$indexPhp = <<<'PHP'
<?php
header('X-Sportify-Ot-Gateway: index-redirect-20260706');
header('Location: https://sportifyplus.de/only-talents/home', true, 302);
header('Cache-Control: no-store');
exit;
PHP;

$indexHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="refresh" content="0;url=https://sportifyplus.de/only-talents/home">
  <title>OnlyTalents</title>
</head>
<body>
  <p><a href="https://sportifyplus.de/only-talents/home">Continue to OnlyTalents</a></p>
</body>
</html>
HTML;

$htaccess = <<<'HTA'
DirectoryIndex index.html index.php
Options -Indexes

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /

RewriteRule ^index\.html$ - [L]
RewriteRule ^(deploy-check|opcache-purge|push-talents-gateway)\.php$ - [L]

RewriteRule ^$ https://sportifyplus.de/only-talents/home [R=302,L]
RewriteRule ^(login|register)(/.*)?$ https://sportifyplus.de/$1 [R=302,L,QSA]
RewriteRule ^(discover|categories|locations|videos|upload|communities|profile)(/.*)?$ https://sportifyplus.de/only-talents/$1$2 [R=302,L,QSA]
RewriteRule ^(.*)$ https://sportifyplus.de/only-talents/$1 [R=302,L,QSA]
</IfModule>
HTA;

$userIniMain = <<<'INI'
auto_prepend_file="/home/u234903558/domains/sportifyplus.de/public_html/site-prepend-20260705-ot.php"
opcache.validate_timestamps=1
opcache.revalidate_freq=0
INI;

$userIniSub = <<<'INI'
auto_prepend_file="/home/u234903558/domains/talents.sportifyplus.de/public_html/site-prepend-20260705-ot.php"
opcache.validate_timestamps=1
opcache.revalidate_freq=0
INI;

$roots = [
    __DIR__,
    dirname(__DIR__),
    dirname(__DIR__) . '/talents',
    dirname(__DIR__) . '/domains/sportifyplus.de/public_html',
    dirname(__DIR__) . '/domains/sportifyplus.de/public_html/talents',
    dirname(__DIR__) . '/domains/talents.sportifyplus.de/public_html',
];
$roots = array_values(array_unique(array_filter($roots, 'is_dir')));

echo "marker=push-talents-gateway-20260706\n";
$ok = 0;
$fail = 0;

foreach ($roots as $root) {
    echo "root={$root}\n";
    $isSub = str_contains($root, 'talents.sportifyplus.de/public_html');
    $isTalentsFolder = str_ends_with($root, '/talents') || $root === dirname(__DIR__) . '/talents';
    $isMain = str_ends_with($root, 'sportifyplus.de/public_html') && !$isTalentsFolder;

    $writes = [];
    if ($isSub || $isTalentsFolder) {
        $prependPath = $root . '/site-prepend-20260705-ot.php';
        $userIni = "auto_prepend_file=\"{$prependPath}\"\nopcache.validate_timestamps=1\nopcache.revalidate_freq=0\n";
        $writes = [
            'site-prepend-20260705-ot.php' => $prepend,
            'index.php' => $indexPhp,
            'index.html' => $indexHtml,
            '.htaccess' => $htaccess,
            '.user.ini' => $userIni,
        ];
    }
    if ($isMain) {
        $writes['site-prepend-20260705-ot.php'] = $prepend;
        $writes['.user.ini'] = $userIniMain;
    }

    foreach ($writes as $rel => $content) {
        $dest = $root . '/' . $rel;
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (@file_put_contents($dest, $content) === false) {
            echo "fail={$rel}\n";
            $fail++;
            continue;
        }
        echo "ok={$rel} bytes=" . strlen($content) . "\n";
        $ok++;
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(realpath($dest) ?: $dest, true);
        }
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
}
echo "summary ok={$ok} fail={$fail}\n";
