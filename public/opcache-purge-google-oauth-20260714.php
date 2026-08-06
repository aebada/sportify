<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Purge: *');

$root = dirname(__DIR__);
$files = [
    $root . '/bootstrap.php',
    $root . '/config/config.php',
    $root . '/app/Services/SocialAuthService.php',
    $root . '/app/Controllers/OAuthController.php',
    $root . '/app/Controllers/SocialLoginHandler20260621.php',
    $root . '/app/Models/User.php',
    $root . '/app/Models/MemberProfile.php',
    $root . '/public/oauth-env-diag.php',
];

foreach ($files as $file) {
    if (is_file($file)) {
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
$gid = (string) \App\Core\App::config('social.google.client_id', '');
echo 'google_id_prefix=' . substr($gid, 0, 12) . "\n";
echo 'google_marker=' . (str_contains((string) @file_get_contents($root . '/app/Services/SocialAuthService.php'), 'aipass-google-oauth-20260714') ? 'yes' : 'no') . "\n";
echo 'google_redirect_uri=' . \App\Services\SocialAuthService::redirectUri('google') . "\n";
