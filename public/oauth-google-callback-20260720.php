<?php
declare(strict_types=1);
/**
 * Google OAuth callback (dedicated entry — Hostinger-safe).
 * Loads routes before finish() so route('login') does not fatal into an empty 500.
 * deploy-marker: oauth-google-callback-20260720f
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap.php') && is_file(__DIR__ . '/bootstrap.php')) {
    $root = __DIR__;
}

$failRedirect = static function (string $msg = ''): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['_flash']['error'] = $msg !== '' ? $msg : 'Google sign-in failed. Please try again.';
        session_write_close();
    }
    if (!headers_sent()) {
        header('Location: /login?error=google', true, 302);
    }
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
        . '<meta http-equiv="refresh" content="0;url=/login?error=google">'
        . '<title>Redirecting…</title></head><body>'
        . '<p><a href="/login?error=google">Continue to login</a></p></body></html>';
    exit;
};

register_shutdown_function(static function () use ($failRedirect): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        @error_log('sportify google callback fatal: ' . $e['message'] . ' @ ' . $e['file'] . ':' . $e['line']);
        if (!headers_sent()) {
            $failRedirect('Google sign-in failed. Please try again.');
        }
    }
});

try {
    foreach ([
        $root . '/app/Services/GoogleAuthService.php',
        $root . '/app/Services/GoogleOAuthStart.php',
        $root . '/app/Services/SocialAuthService.php',
        $root . '/app/Controllers/OAuthController.php',
        $root . '/app/Controllers/SocialLoginHandler20260621.php',
        $root . '/app/Models/User.php',
        $root . '/app/Models/MemberProfile.php',
    ] as $file) {
        if (is_file($file) && function_exists('opcache_invalidate')) {
            @opcache_invalidate(realpath($file) ?: $file, true);
        }
    }

    require $root . '/bootstrap.php';

    $routeFiles = [
        $root . '/config/routes-20260629-memberships-wellness-v1.php',
        $root . '/config/routes-20260621-memberships-v1.php',
        $root . '/config/routes.php',
    ];
    foreach ($routeFiles as $routes) {
        if (is_file($routes)) {
            require $routes;
            break;
        }
    }

    if (class_exists(\App\Controllers\SocialLoginHandler20260621::class)) {
        \App\Controllers\SocialLoginHandler20260621::handleCallback('google');
        exit;
    }

    if (class_exists(\App\Controllers\OAuthController::class)) {
        echo (new \App\Controllers\OAuthController())->googleCallback();
        exit;
    }

    $failRedirect('Google sign-in is temporarily unavailable.');
} catch (Throwable $e) {
    @error_log('sportify google callback: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    $failRedirect($e->getMessage());
}
