<?php
/**
 * Shared bootstrap used by the web front controller and CLI scripts.
 * deploy: 2026-06-30 oauth-session-fix-v1
 */

define('SPORTIFY_START', microtime(true));

$__sportifyBootstrapPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$__sportifyBootstrapHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (in_array($__sportifyBootstrapHost, ['sportifyplus.de', 'www.sportifyplus.de'], true)
    && preg_match('#^/only-talents/home/?$#i', $__sportifyBootstrapPath)
    && in_array(strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')), ['GET', 'HEAD'], true)) {
    $__sportifyBootstrapQs = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    header('Location: /only-talents' . (is_string($__sportifyBootstrapQs) && $__sportifyBootstrapQs !== '' ? '?' . $__sportifyBootstrapQs : ''), true, 301);
    header('Cache-Control: no-store');
    header('X-Sportify-Ot-Canonical: bootstrap-20260707');
    exit;
}

$__sportifyRefereexPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($__sportifyRefereexPath === '/refereex-ai' || str_starts_with($__sportifyRefereexPath, '/refereex-ai/')) {
    header('Location: https://aebada.github.io/sportify-refereex/', true, 302);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-Sportify-RefereeX: bootstrap-ghpages-redirect-20260715');
    exit;
}

$__sportifyHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (in_array($__sportifyHost, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)
    && function_exists('opcache_reset')) {
    @opcache_reset();
}
if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(__FILE__, true);
}
if (in_array($__sportifyHost, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)
    && function_exists('opcache_invalidate')) {
    foreach ([
        __DIR__ . '/app/Support/OnlyTalentsContext.php',
        __DIR__ . '/app/Support/helpers.php',
        __DIR__ . '/app/Controllers/OnlyTalentsDiscoveryController.php',
        __DIR__ . '/config/routes-onlytalents.php',
    ] as $__otInvalidate) {
        if (is_file($__otInvalidate)) {
            @opcache_invalidate(realpath($__otInvalidate) ?: $__otInvalidate, true);
        }
    }
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('X-LiteSpeed-Cache-Control: no-cache');
// Never send X-LiteSpeed-Purge on web requests — Hostinger can abort the
// response as an empty HTTP 500 (home, login, OAuth, and redirects).

$bootstrapLight = PHP_SAPI !== 'cli' && (static function (): bool {
    $path = strtolower(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    foreach (['deploy-check', 'opcache-purge', 'oauth-env-diag', 'only-talents-diag', 'only-talents-setup', 'seed-ot-shorts', 'ot-prune-batch'] as $needle) {
        if (str_contains($path, $needle)) {
            return true;
        }
    }
    return false;
})();

if ($bootstrapLight && function_exists('opcache_invalidate')) {
    $invalidate = [
        __DIR__ . '/bootstrap.php',
        __DIR__ . '/config/config.php',
        __DIR__ . '/config/routes.php',
        __DIR__ . '/app/Support/Media.php',
        __DIR__ . '/app/Support/helpers.php',
        __DIR__ . '/app/Support/Svg.php',
        __DIR__ . '/app/Models/NewsRepository.php',
        __DIR__ . '/app/Services/TranslationService.php',
        __DIR__ . '/app/Services/ContentLocalizer.php',
        __DIR__ . '/app/Services/NewsTranslationService.php',
        __DIR__ . '/app/Services/TranslationGtx20260619.php',
        __DIR__ . '/app/Core/Lang.php',
        __DIR__ . '/app/Services/SportsFeedService.php',
        __DIR__ . '/app/Controllers/HomeController.php',
        __DIR__ . '/views/pages/home.php',
        __DIR__ . '/views/partials/home-hero-talent.php',
        __DIR__ . '/views/partials/home-event-strip.php',
        __DIR__ . '/views/partials/home-trust-strip.php',
        __DIR__ . '/views/partials/home-how-it-works.php',
        __DIR__ . '/app/Models/PlayerRepository.php',
        __DIR__ . '/app/Models/HighlightRepository.php',
        __DIR__ . '/views/partials/player-card.php',
        __DIR__ . '/views/partials/player-portrait.php',
        __DIR__ . '/views/partials/player-avatar.php',
        __DIR__ . '/views/partials/ai-rec-card.php',
        __DIR__ . '/views/partials/home-ai-clubs.php',
        __DIR__ . '/views/partials/home-shorts.php',
        __DIR__ . '/app/Services/ShortsFeedService.php',
        __DIR__ . '/app/Models/ShortsFeedRepository.php',
        __DIR__ . '/views/partials/home-news-transfers.php',
        __DIR__ . '/views/partials/home-audiences.php',
        __DIR__ . '/views/partials/home-fitpass.php',
        __DIR__ . '/views/partials/home-final-cta.php',
        __DIR__ . '/lang/en/messages.php',
        __DIR__ . '/lang/de/messages.php',
        __DIR__ . '/lang/ar/messages.php',
        __DIR__ . '/public/index-20260614-homefix-v5.php',
        __DIR__ . '/public/index.php',
        __DIR__ . '/views/pages/transfers.php',
        __DIR__ . '/views/pages/news.php',
        __DIR__ . '/views/pages/videos.php',
        __DIR__ . '/app/Core/View.php',
        __DIR__ . '/app/Controllers/PageController.php',
        __DIR__ . '/app/Controllers/TeamFanChatController.php',
        __DIR__ . '/app/Models/ChatGroupRepository.php',
        __DIR__ . '/config/routes-20260614-linkedin.php',
        __DIR__ . '/views/pages/teams/show.php',
        __DIR__ . '/views/pages/teams/chat/index.php',
        __DIR__ . '/views/pages/teams/chat/room.php',
        __DIR__ . '/views/partials/chat-message.php',
        __DIR__ . '/public/assets/css/app.css',
        __DIR__ . '/public/deploy-check.php',
        __DIR__ . '/public/seed-ot-shorts-2026.php',
        __DIR__ . '/app/Services/OtShortsValidationService.php',
        __DIR__ . '/app/Services/OtShortsRefreshService.php',
        __DIR__ . '/app/Services/OtShortsBulkSeedService.php',
        __DIR__ . '/app/Services/OtLandingShortsService.php',
        __DIR__ . '/app/Models/TalentShortRepository.php',
        __DIR__ . '/app/Support/TeamFlagResolver.php',
        __DIR__ . '/app/Models/TeamRepository.php',
        __DIR__ . '/app/Models/FixtureRepository.php',
        __DIR__ . '/app/Services/RankingsFeedService.php',
        __DIR__ . '/app/Services/EventModeService.php',
        __DIR__ . '/views/partials/match-card.php',
        __DIR__ . '/views/partials/header-brand.php',
        __DIR__ . '/lang/en/messages.php',
        __DIR__ . '/lang/de/messages.php',
        __DIR__ . '/lang/ar/messages.php',
    ];
    foreach ($invalidate as $file) {
        if (is_file($file)) {
            $resolved = realpath($file) ?: $file;
            @opcache_invalidate($resolved, true);
        }
    }
}

$root = __DIR__;

require $root . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register($root . '/app');

if (PHP_SAPI !== 'cli' && function_exists('opcache_invalidate')) {
    foreach ([
        $root . '/app/Core/Controller.php',
        $root . '/app/Core/Router.php',
        $root . '/app/Support/helpers.php',
        $root . '/app/Controllers/OAuthController.php',
        $root . '/app/Controllers/FacebookOAuthController.php',
        $root . '/app/Services/SocialAuthService.php',
        $root . '/app/Support/TeamFlagResolver.php',
        $root . '/app/Models/TeamRepository.php',
        $root . '/app/Models/FixtureRepository.php',
        $root . '/app/Services/RankingsFeedService.php',
        $root . '/app/Services/EventModeService.php',
        $root . '/views/partials/match-card.php',
        $root . '/views/partials/header-brand.php',
        $root . '/lang/en/messages.php',
        $root . '/lang/de/messages.php',
        $root . '/lang/ar/messages.php',
    ] as $flagFile) {
        if (is_file($flagFile)) {
            @opcache_invalidate(realpath($flagFile) ?: $flagFile, true);
        }
    }
}

if (in_array($__sportifyHost, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)
    && is_file(__DIR__ . '/app/Support/ot_helpers_hotfix.php')) {
    require __DIR__ . '/app/Support/ot_helpers_hotfix.php';
}

require $root . '/app/Support/helpers.php';

$config = require $root . '/config/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $cookieParams = [
        'path'     => '/',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    $sessionDomain = trim((string) ($config['onlytalents']['session_domain'] ?? ''));
    $hostNorm = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    if ($sessionDomain !== '' && str_contains($sessionDomain, '.') && $hostNorm !== '') {
        $baseHost = ltrim($sessionDomain, '.');
        if ($hostNorm === $baseHost || str_ends_with($hostNorm, '.' . $baseHost)) {
            $cookieParams['domain'] = $sessionDomain;
        }
    }
    session_set_cookie_params($cookieParams);
    session_start();
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isAuthFlow = preg_match('#^/(auth/(google|facebook|linkedin)(/callback)?|login|register)(/|$|\?)#', $requestPath) === 1;
if ($isAuthFlow || preg_match('#^/auth/(google|facebook|linkedin)(/callback)?(/|$)#', $requestPath)) {
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }
    if (function_exists('opcache_invalidate')) {
        foreach ([
            $root . '/app/Core/Router.php',
            $root . '/app/Core/Controller.php',
            $root . '/app/Core/Auth.php',
            $root . '/app/Support/helpers.php',
            $root . '/app/Support/Rbac.php',
            $root . '/app/Support/OnlyTalentsContext.php',
            $root . '/config/roles.php',
            $root . '/app/Controllers/AuthController.php',
            $root . '/app/Controllers/OAuthController.php',
            $root . '/app/Controllers/FacebookOAuthController.php',
            $root . '/app/Controllers/SocialLoginHandler20260621.php',
            $root . '/app/Services/SocialAuthService.php',
            $root . '/app/Services/SocialAuthSettings.php',
            $root . '/views/auth/login.php',
            $root . '/views/auth/register.php',
            $root . '/views/partials/social-auth.php',
            $root . '/views/partials/social-auth-20260614-linkedin.php',
            $root . '/views/partials/social-auth-20260720-visibility.php',
        ] as $authFile) {
            if (is_file($authFile)) {
                @opcache_invalidate(realpath($authFile) ?: $authFile, true);
            }
        }
    }
}

if (!empty($_GET['lang']) && isset($config['locales'][$_GET['lang']])) {
    $_SESSION['locale'] = $_GET['lang'];
    $localeCookie = [
        'expires'  => time() + 86400 * 365,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    $sessionDomain = trim((string) ($config['onlytalents']['session_domain'] ?? ''));
    if ($sessionDomain !== '' && str_contains($sessionDomain, '.')) {
        $localeCookie['domain'] = $sessionDomain;
    }
    setcookie('sportify_locale', $_GET['lang'], $localeCookie);
}

\App\Core\App::boot($config);

// OAuth: Google start/return — Hostinger returns empty 500 for legacy /auth/google/callback handler paths.
if (PHP_SAPI !== 'cli') {
    try {
        $oauthPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $isGoogleReturn = (bool) preg_match('#^/auth/google/callback/?$#', $oauthPath)
            || (
                str_ends_with($oauthPath, '/oauth-env-diag.php')
                && (isset($_GET['code']) || isset($_GET['error']))
            );
        if ($isGoogleReturn) {
            header('X-Sportify-OAuth-Return: bootstrap-20260720');
            if (is_file(__DIR__ . '/app/Services/GoogleOAuthStart.php')) {
                require_once __DIR__ . '/app/Services/GoogleOAuthStart.php';
            }
            if (is_file(__DIR__ . '/config/routes-20260621-memberships-v1.php')) {
                require __DIR__ . '/config/routes-20260621-memberships-v1.php';
            }
            echo (new \App\Controllers\GoogleLoginController())->returned();
            exit;
        }
        if (preg_match('#^/auth/(facebook|linkedin)/callback/?$#', $oauthPath, $oauthMatch)) {
            $provider = $oauthMatch[1];
            if (class_exists(\App\Controllers\SocialLoginHandler20260621::class)) {
                \App\Controllers\SocialLoginHandler20260621::handleCallback($provider);
                exit;
            }
        }
    } catch (\Throwable $e) {
        if (!empty($config['app']['debug'])) {
            error_log('[Sportify bootstrap oauth early hook] ' . $e->getMessage());
        }
    }
}

$isOtSubdomain = \App\Support\OnlyTalentsContext::isActive();

\App\Core\View::share('onlyTalentsMode', $isOtSubdomain);

if (!$bootstrapLight && !$isOtSubdomain && \App\Core\Database::available() && \App\Core\Database::tableExists('fixtures')) {
    try {
        \App\Services\MatchHubSyncService::ensureToday();
    } catch (\Throwable $e) {
        // Never take down the site for background fixture sync.
    }
}

try {
    \App\Services\ReferralService::captureFromRequest();
} catch (\Throwable $e) {
}

// Share globals available to every view.
$authUser = \App\Core\Auth::user();
\App\Core\View::share('auth', $authUser);
\App\Core\View::share('appName', \App\Core\App::config('app.name'));
$feedTicker = [];
$feedSources = [];
$scoresTicker = [];
$activeEvent = null;
$worldCupPhase = null;
if (!$isOtSubdomain) {
    try {
        $feedTicker = $bootstrapLight ? [] : \App\Services\SportsFeedService::ticker(12);
        $feedSources = \App\Services\SportsFeedService::sources();
        $scoresTicker = $bootstrapLight ? [] : \App\Services\LiveFixtureFeedService::marqueeItemsCached(10);
        $activeEvent = \App\Services\EventModeService::active();
        $worldCupPhase = \App\Services\EventModeService::phase();
    } catch (\Throwable $e) {
    }
}
\App\Core\View::share('feedTicker', $feedTicker);
\App\Core\View::share('feedSources', $feedSources);
\App\Core\View::share('scoresTicker', $scoresTicker);
\App\Core\View::share('activeEvent', $activeEvent);
\App\Core\View::share('worldCupPhase', $worldCupPhase);
        if ($authUser && \App\Core\Database::available()) {
    try {
        $mp = \App\Models\MemberProfile::findByUserId((int) $authUser['id']);
        if ($mp) {
            \App\Core\View::share('memberProfile', $mp);
            \App\Core\View::share('unreadMessages', \App\Models\DirectMessageRepository::unreadCount((int) $authUser['id'])
                + \App\Models\MessageRepository::unreadCount((int) $authUser['id']));
        }
        \App\Core\View::share('subscription', \App\Models\SubscriptionRepository::activeForUser((int) $authUser['id']));
        \App\Core\View::share('userMembership', \App\Services\MembershipService::membershipForUser((int) $authUser['id'], $authUser['role'] ?? null));
    } catch (\Throwable $e) {
    }
}

if (PHP_SAPI !== 'cli' && !defined('SPORTIFY_NO_OUTPUT_BUFFER')) {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = '/' . trim($path, '/');
    if ($path === '' || $path === '/index.php') {
        $path = '/';
    }
    if (!empty($_GET['refereex']) || $path === '/refereex-ai' || str_starts_with($path, '/refereex-ai/')
        || $path === '/refereex-ai.php' || str_ends_with($path, '/refereex-ai.php')) {
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('X-Sportify-RefereeX: bootstrap-early-v20260707');
        echo (new \App\Controllers\RefereeXController())->index();
        exit;
    }
    if (in_array($path, ['/live-streams', '/live-sports', '/watch-live-today'], true)) {
        echo (new \App\Controllers\TvScheduleController())->liveStreams();
        exit;
    }
    if (str_contains($path, 'logo.svg')) {
        (new \App\Controllers\PageController())->logoSvg();
        exit;
    }
    if (str_contains($path, 'logo.png') && !str_contains($path, 'logo-icon.png')) {
        (new \App\Controllers\PageController())->logoPng();
        exit;
    }
    if (str_contains($path, 'logo-icon.png')) {
        (new \App\Controllers\PageController())->logoIconPng();
        exit;
    }
    if (str_contains($path, 'favicon.png')) {
        (new \App\Controllers\PageController())->faviconPng();
        exit;
    }

    ob_start(static function (string $html): string {
        $html = str_replace(
            ['/assets/img/logo.svg', '/assets/img/favicon.svg'],
            ['/assets/img/logo.png', '/assets/img/favicon.png'],
            $html
        );
        // google-btn-v1 runtime — survives stale lang/views on Hostinger PHP disk
        if (str_contains($html, 'auth.social.continue_google')) {
            $html = str_replace('auth.social.continue_google', 'Continue with Google', $html);
        }
        if (str_contains($html, 'google-btn') && !str_contains($html, 'google-btn-v1')) {
            $css = '<style>/* google-btn-v1 runtime */.google-btn{position:relative;width:100%;display:inline-flex;align-items:center;justify-content:center;gap:.75rem;padding:.875rem 1.25rem;border:1px solid #d0d7de;border-radius:10px;background:#fff;color:#1f2328;font-size:.9375rem;font-weight:600;font-family:inherit;text-decoration:none;box-sizing:border-box;cursor:pointer;transition:border-color .2s,box-shadow .2s}.google-btn:hover:not(.google-btn--soon):not([aria-disabled="true"]){border-color:#dadce0;box-shadow:0 1px 3px rgba(60,64,67,.2)}.google-btn--soon{cursor:not-allowed;opacity:.92}.google-btn__icon{flex-shrink:0;display:block}</style>';
            if (preg_match('/<\/head>/i', $html)) {
                $html = preg_replace('/<\/head>/i', $css . '</head>', $html, 1);
            } else {
                $html = $css . $html;
            }
        }
        return $html;
    });
}

if (PHP_SAPI !== 'cli') {
    $penaltyPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($penaltyPath === '/play/penalties' || $penaltyPath === '/play/penalties/') {
        \App\Core\App::boot($config);
        echo (new \App\Controllers\PlayController())->penaltiesRemoved();
        exit;
    }

    $livePath = '/' . trim($penaltyPath, '/');
    if (preg_match('#^/matches/\d+#', $livePath) && function_exists('opcache_invalidate')) {
        foreach ([
            __DIR__ . '/app/Services/MatchWatchService.php',
            __DIR__ . '/app/Services/LiveHd7SyncService.php',
            __DIR__ . '/app/Controllers/MatchController.php',
            __DIR__ . '/views/pages/matches/show.php',
            __DIR__ . '/views/pages/matches/show-20260621-watch-v1.php',
            __DIR__ . '/views/partials/match-watch.php',
            __DIR__ . '/views/partials/match-watch-20260621-v1.php',
            __DIR__ . '/config/live_streams.php',
            __DIR__ . '/lang/en/messages.php',
            __DIR__ . '/lang/de/messages.php',
            __DIR__ . '/lang/ar/messages.php',
        ] as $file) {
            if (is_file($file)) {
                @opcache_invalidate(realpath($file) ?: $file, true);
            }
        }
    }
    if (in_array($livePath, ['/live-streams', '/live-sports'], true) && function_exists('opcache_invalidate')) {
        foreach ([
            __DIR__ . '/app/Services/LiveHd7SyncService.php',
            __DIR__ . '/app/Controllers/TvScheduleController.php',
            __DIR__ . '/views/partials/livehd7-matches.php',
            __DIR__ . '/views/pages/live-streams.php',
            __DIR__ . '/lang/en/messages.php',
            __DIR__ . '/lang/de/messages.php',
        ] as $file) {
            if (is_file($file)) {
                @opcache_invalidate(realpath($file) ?: $file, true);
            }
        }
    }
}

if (PHP_SAPI !== 'cli') {
    try {
        if (function_exists('sportify_redirect_guests_to_login')) {
            sportify_redirect_guests_to_login();
        }
    } catch (\Throwable $e) {
        if (!empty($config['app']['debug'])) {
            error_log('[Sportify bootstrap auth hook] ' . $e->getMessage());
        }
    }
}

if (PHP_SAPI !== 'cli' && is_file(__DIR__ . '/public/site-prepend-20260621.php')) {
    require __DIR__ . '/public/site-prepend-20260621.php';
}


if (PHP_SAPI !== 'cli' && ($_GET['sportify_wellness_migrate'] ?? '') === '20260629' && ($_GET['key'] ?? '') === 'sportify2026') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    $pdo = \App\Core\Database::connection();
    if (!$pdo) {
        echo "db_err\n";
        exit(1);
    }
    $schema = require __DIR__ . '/database/schema.php';
    $auto = \App\Core\Database::isSqlite() ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $json = \App\Core\Database::isSqlite() ? 'TEXT' : 'JSON';
    foreach (['wellness_user_profiles', 'wellness_calendar_connections', 'wellness_recommendations', 'wellness_gdpr_consent'] as $table) {
        if (!isset($schema[$table])) {
            echo "skip={$table}\n";
            continue;
        }
        $sql = str_replace(['{{AUTO}}', '{{JSON}}'], [$auto, $json], $schema[$table]);
        $pdo->exec($sql);
        echo "migrated={$table}\n";
    }
    echo "done\n";
    exit;
}

if (PHP_SAPI !== 'cli') {
    $__rxPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (!empty($_GET['refereex']) || $__rxPath === '/refereex-ai' || str_starts_with($__rxPath, '/refereex-ai/')
        || $__rxPath === '/refereex-ai.php' || str_ends_with($__rxPath, '/refereex-ai.php')) {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Sportify-RefereeX: bootstrap-fallback-20260707');
        echo (new \App\Controllers\RefereeXController())->index();
        exit;
    }
}

if (PHP_SAPI !== 'cli') {
    $__wellnessPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($__wellnessPath === '/wellness' || str_starts_with($__wellnessPath, '/wellness/')) {
        $router = \App\Core\App::$router;
        require __DIR__ . '/config/routes-20260629-memberships-wellness-v1.php';
        if (is_file(__DIR__ . '/config/routes-wellness-v1.php')) {
            require __DIR__ . '/config/routes-wellness-v1.php';
        }
        $router->dispatch(\App\Core\App::$request);
        exit;
    }
}

return $config;
