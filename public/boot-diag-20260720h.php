<?php
declare(strict_types=1);
/**
 * Boot diag — shows fatals from authfix bootstrap / AuthController.
 * deploy-marker: boot-diag-20260720h
 */
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Sportify-Boot-Diag: 20260720h');

ini_set('display_errors', '1');
error_reporting(E_ALL);

$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap-20260621-authfix.php')) {
    $root = __DIR__;
}

echo "root=$root\n";
echo "authfix=" . (is_file($root . '/bootstrap-20260621-authfix.php') ? 'yes' : 'no') . "\n";
echo "bootstrap=" . (is_file($root . '/bootstrap.php') ? 'yes' : 'no') . "\n";

$authSrc = (string) @file_get_contents($root . '/bootstrap-20260621-authfix.php');
echo "authfix_purge=" . (str_contains($authSrc, "header('X-LiteSpeed-Purge") ? 'YES-BAD' : 'no') . "\n";
$bootSrc = (string) @file_get_contents($root . '/bootstrap.php');
echo "bootstrap_purge=" . (str_contains($bootSrc, "header('X-LiteSpeed-Purge") ? 'YES-BAD' : 'no') . "\n";

try {
    require $root . '/bootstrap-20260621-authfix.php';
    echo "authfix_require=ok\n";
    echo "auth_check=" . (\App\Core\Auth::check() ? 'yes' : 'no') . "\n";

    if (class_exists(\App\Services\SocialAuthService::class)) {
        echo "google_configured=" . (\App\Services\SocialAuthService::isConfigured('google') ? 'yes' : 'no') . "\n";
    }
    if (class_exists(\App\Services\SocialAuthSettings::class)) {
        echo "google_visible=" . (\App\Services\SocialAuthSettings::isVisible('google') ? 'yes' : 'no') . "\n";
    }

    ob_start();
    $html = (new \App\Controllers\AuthController())->showLogin();
    $buf = (string) ob_get_clean();
    $out = $html !== null && $html !== '' ? (string) $html : $buf;
    echo "login_html_len=" . strlen($out) . "\n";
    echo "login_has_auth_brand=" . (str_contains($out, 'auth-brand') ? 'yes' : 'no') . "\n";
    echo "login_snippet=" . substr(preg_replace('/\s+/', ' ', $out) ?? '', 0, 120) . "\n";
} catch (Throwable $e) {
    echo "ERROR=" . $e->getMessage() . "\n";
    echo "FILE=" . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "TRACE=\n" . $e->getTraceAsString() . "\n";
}
