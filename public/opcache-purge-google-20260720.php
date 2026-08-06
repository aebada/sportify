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

$files = [
    $root . '/app/Core/Controller.php',
    $root . '/app/Controllers/OAuthController.php',
    $root . '/app/Controllers/SocialLoginHandler20260621.php',
    $root . '/app/Services/SocialAuthService.php',
    $root . '/app/Services/GoogleAuthService.php',
    $root . '/app/Models/MemberProfile.php',
];

echo 'root=' . $root . "\n";
echo 'cwd=' . (getcwd() ?: '') . "\n";

foreach ($files as $file) {
    $exists = is_file($file);
    echo 'file=' . basename($file)
        . ' exists=' . ($exists ? 'yes' : 'NO')
        . ' size=' . ($exists ? (string) filesize($file) : '0')
        . ' mtime=' . ($exists ? date('c', (int) filemtime($file)) : '-')
        . "\n";
    if ($exists) {
        @touch($file);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(realpath($file) ?: $file, true);
        }
        echo 'invalidate=' . basename($file) . "\n";
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "reset=done\n";
}

require $root . '/bootstrap.php';

echo 'controller_prevent=' . (method_exists(\App\Core\Controller::class, 'preventRedirectCaching') ? 'yes' : 'no') . "\n";
echo 'google_auth_service=' . (class_exists(\App\Services\GoogleAuthService::class) ? 'yes' : 'no') . "\n";
echo 'google_marker=' . (str_contains((string) @file_get_contents($root . '/app/Services/SocialAuthService.php'), 'hopn-sportify-google-oauth-20260720') ? 'yes' : 'no') . "\n";
echo 'member_find_google=' . (method_exists(\App\Models\MemberProfile::class, 'findUserIdByGoogleId') ? 'yes' : 'no') . "\n";
echo 'is_configured=' . (\App\Services\SocialAuthService::isConfigured('google') ? 'yes' : 'no') . "\n";
echo 'redirect_uri=' . \App\Services\SocialAuthService::redirectUri('google') . "\n";

try {
    $url = \App\Services\SocialAuthService::googleAuthUrl('login', 'player');
    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
    echo "auth_url_ok=yes\n";
    echo 'auth_url_host=' . (parse_url($url, PHP_URL_HOST) ?: '') . "\n";
    echo 'client_id_prefix=' . substr((string) ($q['client_id'] ?? ''), 0, 20) . "\n";
    echo 'scope=' . ($q['scope'] ?? '') . "\n";
    echo 'prompt=' . ($q['prompt'] ?? '') . "\n";
} catch (Throwable $e) {
    echo "auth_url_ok=no\n";
    echo 'auth_url_error=' . $e->getMessage() . "\n";
}

try {
    $ctrl = new \App\Controllers\OAuthController();
    echo 'oauth_controller=yes\n';
    echo 'oauth_has_google=' . (method_exists($ctrl, 'google') ? 'yes' : 'no') . "\n";
} catch (Throwable $e) {
    echo "oauth_controller=no\n";
    echo 'oauth_controller_error=' . $e->getMessage() . "\n";
}
