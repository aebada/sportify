<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Purge: *');

$root = dirname(__DIR__);
register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo 'shutdown_fatal=' . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
    }
});

foreach ([
    $root . '/app/Core/Controller.php',
    $root . '/app/Services/SocialAuthService.php',
    $root . '/app/Services/GoogleAuthService.php',
    $root . '/app/Controllers/OAuthController.php',
] as $file) {
    if (is_file($file) && function_exists('opcache_invalidate')) {
        @opcache_invalidate(realpath($file) ?: $file, true);
    }
}

require $root . '/bootstrap.php';

echo 'controller_prevent_redirect=' . (method_exists(\App\Core\Controller::class, 'preventRedirectCaching') ? 'yes' : 'no') . "\n";
echo 'google_auth_service=' . (class_exists(\App\Services\GoogleAuthService::class) ? 'yes' : 'no') . "\n";
echo 'google_marker=' . (str_contains((string) @file_get_contents($root . '/app/Services/SocialAuthService.php'), 'hopn-sportify-google-oauth-20260720') ? 'yes' : 'no') . "\n";
echo 'google_userinfo_v3=' . (str_contains((string) @file_get_contents($root . '/app/Services/SocialAuthService.php'), 'oauth2/v3/userinfo') ? 'yes' : 'no') . "\n";
echo 'google_redirect_uri=' . \App\Services\SocialAuthService::redirectUri('google') . "\n";
echo 'is_configured=' . (\App\Services\SocialAuthService::isConfigured('google') ? 'yes' : 'no') . "\n";

try {
    $url = \App\Services\SocialAuthService::googleAuthUrl('login', 'player');
    echo 'auth_url_ok=yes' . "\n";
    echo 'auth_url_host=' . (parse_url($url, PHP_URL_HOST) ?: '') . "\n";
    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
    echo 'scope=' . ($q['scope'] ?? '') . "\n";
    echo 'prompt=' . ($q['prompt'] ?? '') . "\n";
    echo 'redirect_uri=' . ($q['redirect_uri'] ?? '') . "\n";
} catch (Throwable $e) {
    echo 'auth_url_ok=no' . "\n";
    echo 'auth_url_error=' . $e->getMessage() . "\n";
}

try {
    $ctrl = new \App\Controllers\OAuthController();
    $ref = new ReflectionMethod($ctrl, 'google');
    echo 'oauth_controller_google=yes' . "\n";
} catch (Throwable $e) {
    echo 'oauth_controller_google=no' . "\n";
    echo 'oauth_controller_error=' . $e->getMessage() . "\n";
}
