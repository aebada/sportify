<?php
declare(strict_types=1);
/**
 * OAuth start diag — reports why Google auth URL fails.
 * deploy-marker: oauth-start-diag-20260720h
 */
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Sportify-OAuth-Diag: 20260720h');

ini_set('display_errors', '1');
error_reporting(E_ALL);

$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap.php') && is_file(__DIR__ . '/bootstrap.php')) {
    $root = __DIR__;
}

try {
    require $root . '/bootstrap.php';
    echo "bootstrap=ok\n";
    echo "google_configured=" . (\App\Services\SocialAuthService::isConfigured('google') ? 'yes' : 'no') . "\n";
    echo "google_visible=" . (\App\Services\SocialAuthSettings::isVisible('google') ? 'yes' : 'no') . "\n";

    $cfg = require $root . '/config/services.php';
    $g = $cfg['google'] ?? [];
    echo "client_id_len=" . strlen((string) ($g['client_id'] ?? '')) . "\n";
    echo "client_secret_len=" . strlen((string) ($g['client_secret'] ?? '')) . "\n";
    echo "redirect=" . (string) ($g['redirect'] ?? '') . "\n";
    echo "env_client_id_len=" . strlen((string) (getenv('GOOGLE_CLIENT_ID') ?: ($_ENV['GOOGLE_CLIENT_ID'] ?? ''))) . "\n";

    $url = \App\Services\GoogleOAuthStart::authUrl('login', 'player');
    echo "auth_url_ok=yes\n";
    echo "auth_url_host=" . (parse_url($url, PHP_URL_HOST) ?: '') . "\n";
    echo "auth_url_len=" . strlen($url) . "\n";
} catch (Throwable $e) {
    echo "ERROR=" . $e->getMessage() . "\n";
    echo "FILE=" . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "TRACE=\n" . $e->getTraceAsString() . "\n";
}
