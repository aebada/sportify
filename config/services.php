<?php
/**
 * Socialite-equivalent services config (custom PHP MVC — not Laravel).
 *
 * Mirrors Laravel's config/services.php google block:
 *   client_id / client_secret / redirect from GOOGLE_* env vars.
 */
$root = dirname(__DIR__);
$env = [];
$envFile = $root . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $env[trim($key)] = trim(trim($value), "\"'");
    }
}

$get = static function (string $key, $default = null) use ($env) {
    if (array_key_exists($key, $env)) {
        return $env[$key];
    }
    $fromServer = getenv($key);
    return $fromServer !== false ? $fromServer : $default;
};

$appUrl = rtrim((string) $get('APP_URL', 'https://sportifyplus.de'), '/');
$redirect = rtrim((string) $get('GOOGLE_REDIRECT_URI', ''), '/');
if ($redirect === '') {
    $redirect = $appUrl . '/auth/google/callback';
}

return [
    'google' => [
        'client_id'     => (string) $get('GOOGLE_CLIENT_ID', ''),
        'client_secret' => (string) $get('GOOGLE_CLIENT_SECRET', ''),
        'redirect'      => $redirect,
    ],
];
