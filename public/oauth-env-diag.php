<?php
declare(strict_types=1);
/**
 * OAuth env diag + optional Google return (?code= / ?error= / mode=return).
 * deploy-marker: oauth-env-diag-20260720f
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap.php') && is_file(__DIR__ . '/bootstrap.php')) {
    $root = __DIR__;
}

$isReturn = isset($_GET['code']) || isset($_GET['error']) || ((string) ($_GET['mode'] ?? '') === 'return');

if ($isReturn) {
    require __DIR__ . '/oauth-google-callback-20260720.php';
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');
foreach ([
    __DIR__ . '/oauth-google-callback-20260720.php',
    __DIR__ . '/oauth-google-cb-fix-20260720e.php',
    $root . '/app/Controllers/OAuthController.php',
    $root . '/app/Controllers/SocialLoginHandler20260621.php',
    $root . '/app/Services/SocialAuthService.php',
    $root . '/app/Services/GoogleAuthService.php',
    $root . '/app/Services/GoogleOAuthStart.php',
    $root . '/config/services.php',
    $root . '/bootstrap.php',
] as $file) {
    if (is_file($file)) {
        @touch($file);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(realpath($file) ?: $file, true);
        }
        echo 'invalidate=' . basename($file) . "\n";
    } else {
        echo 'missing=' . basename($file) . "\n";
    }
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "reset=done\n";
}

require $root . '/bootstrap.php';
echo 'root=' . $root . "\n";
echo 'script=' . __FILE__ . "\n";
echo 'stack=custom-php-socialite-equivalent\n';
echo 'google_auth_service=' . (class_exists(\App\Services\GoogleAuthService::class) ? 'yes' : 'no') . "\n";
echo 'callback_marker=' . (str_contains((string) @file_get_contents(__DIR__ . '/oauth-google-callback-20260720.php'), 'oauth-google-callback-20260720f') ? 'yes' : 'no') . "\n";
echo 'redirect_uri=' . \App\Services\SocialAuthService::redirectUri('google') . "\n";
echo 'is_configured=' . (\App\Services\SocialAuthService::isConfigured('google') ? 'yes' : 'no') . "\n";
try {
    $url = \App\Services\SocialAuthService::googleAuthUrl('login', 'player');
    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
    echo "auth_url_ok=yes\n";
    echo 'auth_redirect_uri=' . ($q['redirect_uri'] ?? '') . "\n";
} catch (Throwable $e) {
    echo 'auth_url_err=' . $e->getMessage() . "\n";
}
echo "done\n";
