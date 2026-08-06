<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Purge: *');

$root = dirname(__DIR__);
$files = [
    $root . '/bootstrap.php',
    $root . '/config/routes.php',
    $root . '/app/Services/SocialAuthSettings.php',
    $root . '/app/Services/SocialAuthService.php',
    $root . '/app/Controllers/AdminSocialAuthController.php',
    $root . '/app/Controllers/OAuthController.php',
    $root . '/app/Controllers/SocialLoginHandler20260621.php',
    $root . '/views/partials/social-auth.php',
    $root . '/views/partials/social-auth-20260614-linkedin.php',
    $root . '/views/layouts/admin.php',
    $root . '/views/admin/social-auth/index.php',
    $root . '/lang/en/messages.php',
    $root . '/lang/de/messages.php',
    $root . '/lang/ar/messages.php',
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
echo 'social_auth_settings_class=' . (class_exists(\App\Services\SocialAuthSettings::class) ? 'yes' : 'no') . "\n";
echo 'facebook_visible=' . (\App\Services\SocialAuthSettings::isVisible('facebook') ? 'yes' : 'no') . "\n";
echo 'linkedin_visible=' . (\App\Services\SocialAuthSettings::isVisible('linkedin') ? 'yes' : 'no') . "\n";
$partial = $root . '/views/partials/social-auth-20260614-linkedin.php';
echo 'partial_marker=' . (str_contains((string) @file_get_contents($partial), 'SocialAuthSettings') ? 'yes' : 'no') . "\n";
