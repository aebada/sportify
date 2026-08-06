<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Purge: *');

$root = dirname(__DIR__);
echo "marker=opcache-purge-auth-brand-20260720\n";
echo 'root=' . $root . "\n";
echo 'cwd=' . (getcwd() ?: '') . "\n";
echo 'docroot=' . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";

$files = [
    $root . '/app/Controllers/AuthController.php',
    $root . '/views/layouts/auth.php',
    $root . '/views/layouts/auth-v2.php',
    $root . '/public/login-brand-wrap-20260720.php',
    $root . '/login-brand-wrap-20260720.php',
    $root . '/site-prepend-20260720-auth-brand.php',
    $root . '/public/site-prepend-20260720-auth-brand.php',
    $root . '/public/index-20260720-auth-brand-v1.php',
    $root . '/public/index.php',
    $root . '/bootstrap.php',
    $root . '/bootstrap-20260621-authfix.php',
];

foreach ($files as $file) {
    $exists = is_file($file);
    echo 'file=' . str_replace($root . '/', '', $file)
        . ' exists=' . ($exists ? 'yes' : 'NO')
        . ' size=' . ($exists ? (string) filesize($file) : '0')
        . ' mtime=' . ($exists ? date('c', (int) filemtime($file)) : '-')
        . "\n";
    if ($exists) {
        @touch($file);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(realpath($file) ?: $file, true);
            echo 'invalidate=' . basename($file) . "\n";
        }
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "reset=done\n";
} else {
    echo "reset=unavailable\n";
}

$authCtl = $root . '/app/Controllers/AuthController.php';
$authLayout = $root . '/views/layouts/auth.php';
$authCtlSrc = is_file($authCtl) ? (string) file_get_contents($authCtl) : '';
$authLayoutSrc = is_file($authLayout) ? (string) file_get_contents($authLayout) : '';
echo 'auth_ctl_brand_fn=' . (str_contains($authCtlSrc, 'ensureAuthBrandInHtml') ? 'yes' : 'no') . "\n";
echo 'auth_ctl_marker=' . (str_contains($authCtlSrc, '20260720-auth-brand-topleft-v1') ? 'yes' : 'no') . "\n";
echo 'auth_layout_marker=' . (str_contains($authLayoutSrc, '20260720-auth-brand-topleft-v1') ? 'yes' : 'no') . "\n";
echo 'auth_layout_old=' . (str_contains($authLayoutSrc, '20260614-logo-v5') ? 'yes' : 'no') . "\n";
echo "done\n";
