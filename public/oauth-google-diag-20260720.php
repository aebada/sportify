<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "\nFATAL=" . $e['message'] . ' @ ' . $e['file'] . ':' . $e['line'] . "\n";
    }
});

$root = dirname(__DIR__);
echo "step=boot\n";
require $root . '/bootstrap.php';
echo "step=booted\n";

$gid = (string) \App\Core\App::config('social.google.client_id', '');
echo 'google_client_id_prefix=' . substr($gid, 0, 20) . "\n";
echo 'google_redirect_uri=' . \App\Services\SocialAuthService::redirectUri('google') . "\n";
echo 'google_redirect_uri_env=' . (string) \App\Core\App::config('social.google.redirect_uri', '') . "\n";
echo 'is_configured=' . (\App\Services\SocialAuthService::isConfigured('google') ? 'yes' : 'no') . "\n";
echo 'controller_prevent=' . (method_exists(\App\Core\Controller::class, 'preventRedirectCaching') ? 'yes' : 'no') . "\n";

try {
    $url = \App\Services\SocialAuthService::googleAuthUrl('login', 'player');
    parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
    echo "auth_url_ok=yes\n";
    echo 'redirect_uri=' . ($q['redirect_uri'] ?? '') . "\n";
    echo 'client_id_prefix=' . substr((string) ($q['client_id'] ?? ''), 0, 20) . "\n";
    echo 'scope=' . ($q['scope'] ?? '') . "\n";
} catch (Throwable $e) {
    echo 'auth_url_ok=no err=' . $e->getMessage() . "\n";
}

try {
    $_GET['from'] = 'login';
    $ctrl = new \App\Controllers\OAuthController();
    echo "oauth_new=ok\n";
    $body = $ctrl->google();
    echo 'oauth_body_len=' . strlen($body) . "\n";
    foreach (headers_list() as $h) {
        if (stripos($h, 'Location:') === 0) {
            echo 'location=' . trim(substr($h, 9)) . "\n";
        }
    }
} catch (Throwable $e) {
    echo 'oauth_err=' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n";
}
echo "done\n";
