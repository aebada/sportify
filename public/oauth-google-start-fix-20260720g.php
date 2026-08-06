<?php
declare(strict_types=1);
/**
 * Google OAuth start — fresh file to bypass broken OPcache of prior start scripts.
 * deploy-marker: oauth-google-start-fix-20260720g
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-Sportify-OAuth-Start: 20260720g');

$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap.php') && is_file(__DIR__ . '/bootstrap.php')) {
    $root = __DIR__;
}

require $root . '/bootstrap.php';

use App\Core\Auth;
use App\Services\SocialAuthService;
use App\Services\SocialAuthSettings;

if (Auth::check()) {
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

if (!SocialAuthSettings::isVisible('google') || !SocialAuthService::isConfigured('google')) {
    header('Location: /login?error=google', true, 302);
    exit;
}

try {
    $url = SocialAuthService::googleAuthUrl($from, $role);
} catch (Throwable $e) {
    header('Location: /login?error=google', true, 302);
    exit;
}

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
