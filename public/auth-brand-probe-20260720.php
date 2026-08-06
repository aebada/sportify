<?php
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Sportify-Probe: auth-brand-20260720');
$roots = [
    __DIR__,
    dirname(__DIR__),
];
foreach ($roots as $root) {
    echo "root_candidate=$root\n";
    $ctl = $root . '/app/Controllers/AuthController.php';
    if (!is_file($ctl)) {
        $ctl = $root . '/../app/Controllers/AuthController.php';
    }
    echo "ctl=$ctl exists=" . (is_file($ctl) ? 'yes' : 'no') . "\n";
    if (is_file($ctl)) {
        $src = file_get_contents($ctl);
        echo "ctl_bytes=" . strlen($src) . "\n";
        echo "ctl_mtime=" . date('c', filemtime($ctl)) . "\n";
        echo "has_brand_fn=" . (str_contains($src, 'ensureAuthBrandInHtml') ? 'yes' : 'no') . "\n";
        echo "has_auth_v2=" . (str_contains($src, "'auth-v2'") ? 'yes' : 'no') . "\n";
    }
    $layout = $root . '/views/layouts/auth.php';
    if (!is_file($layout)) {
        $layout = dirname($root) . '/views/layouts/auth.php';
    }
    echo "layout=$layout exists=" . (is_file($layout) ? 'yes' : 'no') . "\n";
    if (is_file($layout)) {
        echo "layout_marker_new=" . (str_contains((string)file_get_contents($layout), '20260720-auth-brand-topleft') ? 'yes' : 'no') . "\n";
        echo "layout_marker_old=" . (str_contains((string)file_get_contents($layout), '20260614-logo-v5') ? 'yes' : 'no') . "\n";
    }
    echo "----\n";
}
echo "cwd=" . getcwd() . "\n";
echo "script=" . (__FILE__) . "\n";
echo "docroot=" . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo "auto_prepend=" . (ini_get('auto_prepend_file') ?: '(none)') . "\n";
echo "opcache.enable=" . (string) ini_get('opcache.enable') . "\n";
if (function_exists('opcache_get_status')) {
    $s = @opcache_get_status(false);
    echo "opcache_enabled_runtime=" . (!empty($s['opcache_enabled']) ? 'yes' : 'no') . "\n";
}
