<?php
declare(strict_types=1);
/**
 * Google OAuth start (Socialite-equivalent).
 * deploy-marker: oauth-google-start-20260720i
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Sportify-OAuth-Start: 20260720i');

$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap.php') && is_file(__DIR__ . '/bootstrap.php')) {
    $root = __DIR__;
}

$fail = static function (string $msg = ''): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        if ($msg !== '') {
            $_SESSION['_flash']['error'] = $msg;
        }
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

register_shutdown_function(static function () use ($fail): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        @error_log('sportify google start fatal: ' . $e['message'] . ' @ ' . $e['file'] . ':' . $e['line']);
        if (!headers_sent()) {
            $fail('Google sign-in failed. Please try again.');
        }
    }
});

try {
    require $root . '/bootstrap.php';

    if (class_exists(\App\Core\Auth::class) && \App\Core\Auth::check()) {
        header('Location: /', true, 302);
        exit;
    }

    $from = isset($_GET['from']) ? (string) $_GET['from'] : 'login';
    $role = isset($_GET['role']) ? (string) $_GET['role'] : 'player';
    if ($from === 'register') {
        $_SESSION['oauth_registering'] = true;
        $_SESSION['oauth_pending_role'] = $role;
    } else {
        unset($_SESSION['oauth_registering'], $_SESSION['oauth_pending_role']);
    }

    if (!\App\Services\SocialAuthSettings::isVisible('google')
        || !\App\Services\SocialAuthService::isConfigured('google')) {
        $fail('Google login is not configured.');
    }

    $url = \App\Services\GoogleOAuthStart::authUrl($from, $role);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header('Location: ' . $url, true, 302);
    $safe = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
        . '<meta http-equiv="refresh" content="0;url=' . $safe . '">'
        . '<title>Redirecting to Google…</title></head><body>'
        . '<p><a href="' . $safe . '">Continue with Google</a></p></body></html>';
    exit;
} catch (Throwable $e) {
    @error_log('sportify google start: ' . $e->getMessage());
    $fail($e->getMessage());
}
