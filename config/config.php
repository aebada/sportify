<?php
/**
 * Sportify application configuration.
 *
 * On Hostinger shared hosting, copy `.env.example` to `.env` and fill in your
 * MySQL credentials. If no `.env` is present, the app falls back to a local
 * SQLite database so it always runs out of the box.
 */

$root = dirname(__DIR__);

// --- Lightweight .env loader (no Composer dependency) --------------------
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

$driver = $get('DB_CONNECTION', 'sqlite');

return [
    'app' => [
        'name'   => $get('APP_NAME', 'Sportify'),
        'env'    => $get('APP_ENV', 'production'),
        'debug'  => filter_var($get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
        'url'    => rtrim($get('APP_URL', ''), '/'),
        // Empty string = site root URLs (/matches). Omit to auto-detect from SCRIPT_NAME.
        'base_path' => array_key_exists('APP_BASE_PATH', $env) ? $get('APP_BASE_PATH', '') : null,
        'locale' => $get('APP_LOCALE', 'en'),
        'key'    => $get('APP_KEY', 'sportify-dev-key-change-me'),
    ],
    'locales' => [
        'en' => ['name' => 'English',  'native' => 'English',  'dir' => 'ltr', 'flag' => 'GB'],
        'de' => ['name' => 'German',   'native' => 'Deutsch',  'dir' => 'ltr', 'flag' => 'DE'],
        'ar' => ['name' => 'Arabic',   'native' => 'العربية',  'dir' => 'rtl', 'flag' => 'SA'],
    ],
    'db' => [
        'driver'   => $driver,
        'host'     => $get('DB_HOST', '127.0.0.1'),
        'port'     => $get('DB_PORT', '3306'),
        'database' => $driver === 'sqlite'
            ? (($db = $get('DB_DATABASE', $root . '/storage/database.sqlite')) !== '' ? $db : $root . '/storage/database.sqlite')
            : $get('DB_DATABASE', 'sportify'),
        'username' => $get('DB_USERNAME', 'root'),
        'password' => $get('DB_PASSWORD', ''),
        'charset'  => 'utf8mb4',
    ],
    'paths' => [
        'root'    => $root,
        'views'   => $root . '/views',
        'lang'    => $root . '/lang',
        'storage' => $root . '/storage',
        'seeds'   => $root . '/database/seeds',
    ],
    'billing' => require $root . '/config/billing.php',
    'membership' => require $root . '/config/membership.php',
    'payments' => require $root . '/config/payments.php',
    'bookings' => require $root . '/config/bookings.php',
    'marketplace' => require $root . '/config/marketplace.php',
    'pro_coaches' => require $root . '/config/pro_coaches.php',
    'transfer_bureau' => require $root . '/config/transfer_bureau.php',
    'wellness' => require $root . '/config/wellness.php',
    'ai_analysis' => require $root . '/config/ai_analysis.php',
    'openai' => [
        'api_key' => $get('OPENAI_API_KEY', ''),
    ],
    // Manus AI (HOPn) — async task API: https://api.manus.ai/v2
    // Prefer MANUS_API_KEY; HOPN_MANUS_API_KEY is an alias used by some HOPn projects.
    'manus' => [
        'api_key'         => $get('MANUS_API_KEY', ''),
        'hopn_api_key'    => $get('HOPN_MANUS_API_KEY', ''),
        'base_url'        => rtrim($get('MANUS_BASE_URL', 'https://api.manus.ai/v2'), '/'),
        'agent_profile'   => $get('MANUS_AGENT_PROFILE', 'manus-1.6-lite'),
        'poll_timeout'    => (int) $get('MANUS_POLL_TIMEOUT', '45'),
        'poll_interval'   => (int) $get('MANUS_POLL_INTERVAL', '2'),
    ],
    'translation' => [
        'provider'         => $get('TRANSLATION_PROVIDER', 'gtx'),
        'google_api_key'   => $get('GOOGLE_TRANSLATE_API_KEY', ''),
        'libre_url'        => rtrim($get('LIBRETRANSLATE_URL', ''), '/'),
        'libre_api_key'    => $get('LIBRETRANSLATE_API_KEY', ''),
        'cache_ttl'        => (int) $get('TRANSLATION_CACHE_TTL', (string) (86400 * 30)),
        'timeout'          => (float) $get('TRANSLATION_TIMEOUT', '4'),
        'translate_news_on_sync' => filter_var($get('TRANSLATE_NEWS_ON_SYNC', 'false'), FILTER_VALIDATE_BOOLEAN),
    ],
    'match_hub' => require $root . '/config/match_hub.php',
    'marquee' => require $root . '/config/marquee.php',
    'event_override' => $get('SPORTIFY_EVENT', ''),
    'onlytalents' => [
        'host'           => $get('OT_HOST', 'talents.sportifyplus.de'),
        'url'            => rtrim($get('OT_URL', 'https://talents.sportifyplus.de'), '/'),
        'main_url'       => rtrim($get('OT_MAIN_URL', $get('APP_URL', 'https://sportifyplus.de')), '/'),
        'session_domain' => $get('SESSION_DOMAIN', '.sportifyplus.de'),
        'force'          => filter_var($get('OT_FORCE', 'false'), FILTER_VALIDATE_BOOLEAN),
    ],
    // Socialite-equivalent (Laravel config/services.php shape). Not a Laravel app.
    'services' => require $root . '/config/services.php',
    'social' => [
        'google' => [
            // Prefer services.google; keep social.google for existing readers.
            'client_id'     => $get('GOOGLE_CLIENT_ID', ''),
            'client_secret' => $get('GOOGLE_CLIENT_SECRET', ''),
            // Socialite pattern: GOOGLE_REDIRECT_URI or {APP_URL}/auth/google/callback.
            'redirect_uri'  => rtrim($get('GOOGLE_REDIRECT_URI', ''), '/'),
        ],
        'facebook' => [
            'app_id'     => $get('FACEBOOK_APP_ID', ''),
            'app_secret' => $get('FACEBOOK_APP_SECRET', ''),
            'scopes'     => $get('FACEBOOK_SCOPES', ''),
        ],
        'linkedin' => [
            'client_id'     => $get('LINKEDIN_CLIENT_ID', ''),
            'client_secret' => $get('LINKEDIN_CLIENT_SECRET', ''),
        ],
    ],
];
