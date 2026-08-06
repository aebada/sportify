<?php
declare(strict_types=1);
header('X-Deploy-Check-Version: ot-immersive-20260707');

if (($_GET['google_oauth_probe'] ?? '') === '20260714') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-LiteSpeed-Purge: *');
    $root = dirname(__DIR__);
    foreach ([
        $root . '/bootstrap.php',
        $root . '/config/config.php',
        $root . '/app/Services/SocialAuthService.php',
        $root . '/app/Controllers/OAuthController.php',
        $root . '/app/Controllers/SocialLoginHandler20260621.php',
        $root . '/app/Models/User.php',
        $root . '/app/Models/MemberProfile.php',
        $root . '/.env',
    ] as $file) {
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
    echo 'google_marker=' . (str_contains((string) @file_get_contents($root . '/app/Services/SocialAuthService.php'), 'hopn-sportify-google-oauth-20260720') ? 'yes' : 'no') . "\n";
    echo 'google_userinfo_v3=' . (str_contains((string) @file_get_contents($root . '/app/Services/SocialAuthService.php'), 'oauth2/v3/userinfo') ? 'yes' : 'no') . "\n";
    echo 'google_redirect_uri=' . \App\Services\SocialAuthService::redirectUri('google') . "\n";
    exit;
}

if (($_GET['ot_immersive_probe'] ?? '') === '20260707') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-LiteSpeed-Purge: *');
    $root = dirname(__DIR__);
    foreach ([
        $root . '/config/routes-20260629-memberships-wellness-v1.php',
        $root . '/config/routes-20260621-memberships-v1.php',
        $root . '/app/Core/App.php',
        $root . '/app/Controllers/OnlyTalentsDiscoveryController.php',
        $root . '/views/layouts/onlytalents.php',
        $root . '/public/assets/css/onlytalents.css',
    ] as $file) {
        if (function_exists('opcache_invalidate') && is_file($file)) {
            @opcache_invalidate(realpath($file) ?: $file, true);
            @touch($file);
            echo 'invalidate=' . basename($file) . "\n";
        }
    }
    if (function_exists('opcache_reset')) {
        @opcache_reset();
        echo "reset=done\n";
    }
    require $root . '/bootstrap-20260621-authfix.php';
    $router = \App\Core\App::$router;
    if (\App\Support\OnlyTalentsContext::isActive()) {
        require $root . '/config/routes-onlytalents.php';
    } else {
        require $root . '/config/routes-20260621-memberships-v1.php';
    }
    $ref = new ReflectionClass($router);
    $prop = $ref->getProperty('routes');
    $prop->setAccessible(true);
    $routes = $prop->getValue($router);
    $match = 'no';
    foreach ($routes as $r) {
        if (($r['method'] ?? '') === 'GET' && preg_match($r['regex'], '/only-talents')) {
            $match = ($r['action'][0] ?? '') . '::' . ($r['action'][1] ?? '') . ' [' . ($r['name'] ?? '') . ']';
            break;
        }
    }
    echo "route_match=/only-talents => {$match}\n";
    echo 'layout_v5=' . (str_contains((string) file_get_contents($root . '/views/layouts/onlytalents.php'), 'ot-shorts-immersive-v5') ? 'yes' : 'no') . "\n";
    echo 'css_mtime=' . filemtime($root . '/public/assets/css/onlytalents.css') . "\n";
    exit;
}

if (($_GET['serve'] ?? '') === 'refereex' || !empty($_GET['refereex_ai'])) {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-LiteSpeed-Purge: *');
    header('X-Sportify-RefereeX: deploy-check-serve-20260707');
    $root = dirname(__DIR__);
    foreach ([
        $root . '/app/Controllers/RefereeXController.php',
        $root . '/views/pages/refereex-ai/index.php',
    ] as $file) {
        if (function_exists('opcache_invalidate') && is_file($file)) {
            @opcache_invalidate(realpath($file) ?: $file, true);
        }
    }
    require $root . '/bootstrap-20260621-authfix.php';
    echo (new \App\Controllers\RefereeXController())->index();
    exit;
}

if (($_GET['sportify_path_debug'] ?? '') === '20260629') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    $root = dirname(__DIR__);
    require $root . '/bootstrap-20260621-authfix.php';
    $req = \App\Core\App::$request;
    echo 'marker=path-debug\n';
    echo 'request_uri=' . (string)($_SERVER['REQUEST_URI'] ?? '') . "\n";
    echo 'script_name=' . (string)($_SERVER['SCRIPT_NAME'] ?? '') . "\n";
    echo 'app_path=' . $req->path . "\n";
    echo 'method=' . $req->method . "\n";
    $router = \App\Core\App::$router;
    require $root . '/config/routes-20260621-memberships-v1.php';
    if (is_file($root . '/config/routes-wellness-v1.php')) {
        require $root . '/config/routes-wellness-v1.php';
    }
    $ref = new ReflectionClass($router);
    $prop = $ref->getProperty('routes');
    $prop->setAccessible(true);
    $routes = $prop->getValue($router);
    $match = 'no';
    foreach ($routes as $r) {
        if (($r['method'] ?? '') === 'GET' && ($r['pattern'] ?? '') === '/wellness') {
            $match = preg_match($r['regex'], $req->path) ? 'yes' : 'no-regex';
        }
    }
    echo 'wellness_match=' . $match . "\n";
    exit;
}

if (($_GET['sportify_wellness_probe'] ?? '') === '20260629') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    require $root . '/bootstrap-20260621-authfix.php';
    $router = \App\Core\App::$router;
    require $root . '/config/routes-20260621-memberships-v1.php';
    if (is_file($root . '/config/routes-wellness-v1.php')) {
        require $root . '/config/routes-wellness-v1.php';
    }
    $ref = new ReflectionClass($router);
    $prop = $ref->getProperty('routes');
    $prop->setAccessible(true);
    $routes = $prop->getValue($router);
    $w = 0;
    foreach ($routes as $r) {
        if (str_contains((string) ($r['pattern'] ?? ''), 'wellness')) {
            $w++;
        }
    }
    echo "marker=wellness-probe-deploy-check\n";
    echo "root={$root}\n";
    echo "wellness_routes={$w}\n";
    echo 'wellness_controller=' . (is_file($root . '/app/Controllers/WellnessController.php') ? 'yes' : 'no') . "\n";
    echo 'htaccess_entry=' . (is_file(__DIR__ . '/.htaccess') && str_contains(file_get_contents(__DIR__ . '/.htaccess'), 'index-20260629-wellness-v1.php') ? 'wellness-v1' : 'other') . "\n";
    exit;
}

if (($_GET['ping'] ?? '') === 'hero-roles-20260621') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    $partial = $root . '/views/partials/home-hero-talent-20260621-roles.php';
    $routes = $root . '/config/routes-20260621-hero-roles-v1.php';
    $entry = $root . '/index-20260621-hero-home-v1.php';
    echo "marker=hero-roles-probe-20260621\n";
    echo 'root=' . $root . "\n";
    echo 'partial_fan=' . (is_file($partial) && str_contains((string) file_get_contents($partial), 'join_fan') ? 'yes' : 'no') . "\n";
    echo 'routes_ctrl=' . (is_file($routes) && str_contains((string) file_get_contents($routes), 'HomeController20260621Roles') ? 'yes' : 'no') . "\n";
    echo 'home_entry=' . (is_file($entry) ? 'yes' : 'no') . "\n";
    if (is_file($root . '/.htaccess') && preg_match('/RewriteRule \\^\\$ index-([^\\s]+)/', (string) file_get_contents($root . '/.htaccess'), $m)) {
        echo 'htaccess_home=' . $m[1] . "\n";
    }
    exit;
}

if (($_SERVER['HTTP_X_DEPLOY_PING'] ?? '') === 'deploy2026' || ($_GET['ping'] ?? '') === 'deploy2026') {
    header('Content-Type: text/plain; charset=UTF-8');
    if (($_GET['key'] ?? '') === 'onlytalents2026' && ($_GET['mode'] ?? '') === 'discovery') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-LiteSpeed-Cache-Control: no-cache');
    header('X-LiteSpeed-Purge: *');
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    $targets = [
        $root . '/index-20260619-home-v2.php',
        $root . '/public/index-20260619-home-v2.php',
        $root . '/views/partials/home-trust-strip.php',
        $root . '/views/pages/home.php',
        $root . '/app/Support/Svg.php',
        $root . '/bootstrap.php',
        $root . '/bootstrap-20260621-authfix.php',
    ];
    $invalidated = 0;
    if (function_exists('opcache_invalidate')) {
        foreach ($targets as $file) {
            if (is_file($file) && @opcache_invalidate(realpath($file) ?: $file, true)) {
                $invalidated++;
            }
        }
    }
    $reset = function_exists('opcache_reset') && @opcache_reset();
    $partial = $root . '/views/partials/home-trust-strip.php';
    echo "marker=trust-logos-purge-20260621\n";
    echo "root={$root}\n";
    echo "opcache_invalidate={$invalidated}\n";
    echo 'opcache_reset=' . ($reset ? 'ok' : 'skip') . "\n";
    echo 'trust_has_logos=' . (is_file($partial) && str_contains((string) file_get_contents($partial), 'trust-logos') ? 'yes' : 'no') . "\n";
    echo 'trust_mtime=' . (is_file($partial) ? date('c', filemtime($partial)) : 'missing') . "\n";
    exit;
}

if (($_GET['ping'] ?? '') === 'boot2026') {
    header('Content-Type: text/plain; charset=UTF-8');
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    register_shutdown_function(static function (): void {
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            echo 'shutdown_fatal=' . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
        }
    });
    echo 'root=' . $root . "\n";
    try {
        require $root . '/bootstrap.php';
        echo "bootstrap_ok\n";
        echo 'ot=' . (\App\Support\OnlyTalentsContext::isActive() ? 'yes' : 'no') . "\n";
        \App\Core\App::run();
    } catch (Throwable $e) {
        echo 'fail=' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
    }
    exit;
}

if (($_SERVER['HTTP_X_DEPLOY_PING'] ?? '') === 'deploy2026' || ($_GET['ping'] ?? '') === 'deploy2026') {
    header('Content-Type: text/plain; charset=UTF-8');
    if (($_GET['key'] ?? '') === 'onlytalents2026' && ($_GET['mode'] ?? '') === 'discovery') {
        $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
        require $root . '/bootstrap.php';
        $pdo = \App\Core\Database::connection();
        if (!$pdo) {
            echo "db_err\n";
            exit(1);
        }
        $schema = require $root . '/database/schema.php';
        $auto = \App\Core\Database::isSqlite() ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $json = \App\Core\Database::isSqlite() ? 'TEXT' : 'JSON';
        foreach (['talent_categories', 'discovery_profiles'] as $table) {
            $sql = str_replace(['{{AUTO}}', '{{JSON}}'], [$auto, $json], $schema[$table]);
            $pdo->exec($sql);
            echo "migrated={$table}\n";
        }
        $seed = require $root . '/database/seeds/talent_categories.php';
        $synced = 0;
        foreach ($seed as $row) {
            $slug = (string) ($row['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $stmt = $pdo->prepare(
                'INSERT INTO talent_categories (slug, name_en, name_de, name_ar, group_key, icon, sort_order, is_active)
                 VALUES (:slug, :name_en, :name_de, :name_ar, :group_key, :icon, :sort_order, 1)
                 ON DUPLICATE KEY UPDATE name_en = VALUES(name_en), name_de = VALUES(name_de), name_ar = VALUES(name_ar),
                 group_key = VALUES(group_key), icon = VALUES(icon), sort_order = VALUES(sort_order), is_active = 1'
            );
            $stmt->execute([
                'slug' => $slug,
                'name_en' => (string) ($row['name_en'] ?? $slug),
                'name_de' => (string) ($row['name_de'] ?? $row['name_en'] ?? $slug),
                'name_ar' => (string) ($row['name_ar'] ?? $row['name_en'] ?? $slug),
                'group_key' => (string) ($row['group_key'] ?? 'general'),
                'icon' => (string) ($row['icon'] ?? ''),
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ]);
            $synced++;
        }
        echo "seeded_categories={$synced}\n";
        echo "done=ok\n";
        exit;
    }
    if (($_GET['mode'] ?? '') === 'boot') {
        $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
        register_shutdown_function(static function (): void {
            $e = error_get_last();
            if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                echo 'shutdown_fatal=' . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
            }
        });
        echo 'root=' . $root . "\n";
        try {
            require $root . '/bootstrap.php';
            echo "bootstrap_ok\n";
            echo 'ot=' . (\App\Support\OnlyTalentsContext::isActive() ? 'yes' : 'no') . "\n";
        } catch (Throwable $e) {
            echo 'fail=' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
        }
        exit;
    }
    echo "pong=deploy-check-live\n";
    echo 'qs=' . ($_SERVER['QUERY_STRING'] ?? '') . "\n";
    echo 'dir=' . __DIR__ . "\n";
    exit;
}

if (($_GET['ping'] ?? '') === 'discovery2026' && ($_GET['key'] ?? '') === 'onlytalents2026') {
    header('Content-Type: text/plain; charset=UTF-8');
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    require $root . '/bootstrap.php';
    $pdo = \App\Core\Database::connection();
    if (!$pdo) {
        echo "db_err\n";
        exit(1);
    }
    $schema = require $root . '/database/schema.php';
    $auto = \App\Core\Database::isSqlite() ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $json = \App\Core\Database::isSqlite() ? 'TEXT' : 'JSON';
    foreach (['talent_categories', 'discovery_profiles'] as $table) {
        $sql = str_replace(['{{AUTO}}', '{{JSON}}'], [$auto, $json], $schema[$table]);
        $pdo->exec($sql);
        echo "migrated={$table}\n";
    }
    $seed = require $root . '/database/seeds/talent_categories.php';
    $synced = 0;
    foreach ($seed as $row) {
        $slug = (string) ($row['slug'] ?? '');
        if ($slug === '') {
            continue;
        }
        $stmt = $pdo->prepare(
            'INSERT INTO talent_categories (slug, name_en, name_de, name_ar, group_key, icon, sort_order, is_active)
             VALUES (:slug, :name_en, :name_de, :name_ar, :group_key, :icon, :sort_order, 1)
             ON DUPLICATE KEY UPDATE name_en = VALUES(name_en), name_de = VALUES(name_de), name_ar = VALUES(name_ar),
             group_key = VALUES(group_key), icon = VALUES(icon), sort_order = VALUES(sort_order), is_active = 1'
        );
        $stmt->execute([
            'slug' => $slug,
            'name_en' => (string) ($row['name_en'] ?? $slug),
            'name_de' => (string) ($row['name_de'] ?? $row['name_en'] ?? $slug),
            'name_ar' => (string) ($row['name_ar'] ?? $row['name_en'] ?? $slug),
            'group_key' => (string) ($row['group_key'] ?? 'general'),
            'icon' => (string) ($row['icon'] ?? ''),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
        ]);
        $synced++;
    }
    echo "seeded_categories={$synced}\n";
    echo "done=ok\n";
    exit;
}

if (($_GET['boot_probe'] ?? '') === '20260621' && ($_GET['key'] ?? '') === 'sportify2026') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    register_shutdown_function(static function (): void {
        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            echo 'shutdown_fatal=' . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
        }
    });
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    echo "marker=boot-probe-via-deploy-check\n";
    $steps = [
        'autoload' => static function () use ($root): void {
            require $root . '/app/Core/Autoloader.php';
            \App\Core\Autoloader::register($root . '/app');
        },
        'helpers' => static function () use ($root): void {
            require $root . '/app/Support/helpers.php';
        },
        'config' => static function () use ($root): void {
            require $root . '/config/config.php';
        },
    ];
    $config = null;
    foreach ($steps as $name => $fn) {
        echo "try={$name}\n";
        try {
            if ($name === 'config') {
                $config = require $root . '/config/config.php';
            } else {
                $fn();
            }
            echo "ok={$name}\n";
        } catch (Throwable $e) {
            echo "fail={$name} " . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
            exit;
        }
    }
    echo "try=app_boot\n";
    try {
        \App\Core\App::boot($config);
        echo "ok=app_boot\n";
    } catch (Throwable $e) {
        echo 'fail=app_boot ' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
        exit;
    }
    foreach ([
        'match_hub' => static fn () => \App\Services\MatchHubSyncService::ensureToday(),
        'referral' => static fn () => \App\Services\ReferralService::captureFromRequest(),
        'ticker' => static fn () => \App\Services\SportsFeedService::ticker(2),
        'marquee' => static fn () => \App\Services\LiveFixtureFeedService::marqueeItemsCached(3),
        'event' => static fn () => \App\Services\EventModeService::active(),
        'social_class' => static fn () => class_exists(\App\Controllers\SocialLoginHandler20260621::class),
    ] as $name => $fn) {
        echo "try={$name}\n";
        try {
            $fn();
            echo "ok={$name}\n";
        } catch (Throwable $e) {
            echo 'fail=' . $name . ' ' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
            exit;
        }
    }
    echo "try=full_bootstrap\n";
    try {
        require $root . '/bootstrap.php';
        echo "ok=full_bootstrap\n";
    } catch (Throwable $e) {
        echo 'fail=full_bootstrap ' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
    }
    exit;
}

if (($_GET['sportify_news_bootstrap'] ?? '') === '20260621') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    register_shutdown_function(static function (): void {
        $e = error_get_last();
        if ($e) {
            echo 'fatal=' . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
        }
    });
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    echo "marker=news-bootstrap-probe-20260621\n";
    echo 'root=' . $root . "\n";
    try {
        require $root . '/app/Core/Autoloader.php';
        \App\Core\Autoloader::register($root . '/app');
        echo "step=autoload\n";
        require $root . '/app/Support/helpers.php';
        echo "step=helpers\n";
        $config = require $root . '/config/config.php';
        echo "step=config\n";
        \App\Core\App::boot($config);
        echo "step=app_boot\n";
        $ticker = \App\Services\SportsFeedService::ticker(3);
        echo 'step=ticker count=' . count($ticker) . "\n";
        $news = \App\Models\NewsRepository::all();
        echo 'step=news count=' . count($news) . "\n";
        foreach ($news as $item) {
            if (str_contains((string) ($item['cover'] ?? ''), 'i.guim.co.uk')) {
                echo 'guim=' . $item['cover'] . "\n";
                break;
            }
        }
    } catch (Throwable $e) {
        echo 'ex=' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
    }
    exit;
}

if (($_GET['ot_bootstrap_probe'] ?? '') === '20260620') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    register_shutdown_function(static function (): void {
        $e = error_get_last();
        if ($e) {
            echo 'fatal=' . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
        }
    });
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    echo 'root=' . $root . "\n";
    echo 'host=' . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
    try {
        if (is_file($root . '/app/Support/ot_helpers_hotfix.php')) {
            require_once $root . '/app/Core/Autoloader.php';
            \App\Core\Autoloader::register($root . '/app');
            require_once $root . '/app/Support/ot_helpers_hotfix.php';
            echo "hotfix_loaded=yes\n";
        }
        require $root . '/bootstrap.php';
        echo "bootstrap_ok\n";
        echo 'ot=' . (\App\Support\OnlyTalentsContext::isActive() ? 'yes' : 'no') . "\n";
        $html = (new \App\Controllers\OnlyTalentsDiscoveryController())->home();
        echo 'home_len=' . strlen($html) . "\n";
    } catch (Throwable $e) {
        echo 'ex=' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
    }
    exit;
}

if (($_GET['sportify_flags_live'] ?? '') === '20260619') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    require $root . '/bootstrap.php';
    echo "marker=flags-live-probe-20260619\n";
    echo 'dir=' . __DIR__ . "\n";
    echo 'root=' . $root . "\n";
    echo 'resolver2026=' . (class_exists(\App\Support\TeamFlagResolver20260619::class) ? 'yes' : 'no') . "\n";
    echo 'audax=' . \App\Support\TeamFlagResolver20260619::resolve('Audax Italiano', 'Football', '') . "\n";
    echo 'drogheda=' . \App\Support\TeamFlagResolver20260619::resolve('Drogheda United', 'League of Ireland', '') . "\n";
    $html = (new \App\Controllers\MatchHubFlagsHotfix20260619Controller())->dispatch('/matches');
    echo 'matches_len=' . strlen($html) . "\n";
    echo 'matches_white=' . substr_count($html, '🏳️') . "\n";
    echo 'matches_cl=' . substr_count($html, '🇨🇱') . "\n";
    echo 'matches_ie=' . substr_count($html, '🇮🇪') . "\n";
    exit;
}

if (isset($_GET['sportify_flags_hotfix']) && $_GET['sportify_flags_hotfix'] === '20260619') {
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    $hotfix = __DIR__ . '/flags-hotfix-20260619.php';
    if (is_file($hotfix)) {
        require $hotfix;
    } else {
        echo "marker=flags-hotfix-missing\n";
    }
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);

if (($_GET['sportify_live_streams_html'] ?? '') === '20260619') {
    define('SPORTIFY_NO_OUTPUT_BUFFER', true);
    require $root . '/bootstrap.php';
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo (new \App\Controllers\TvScheduleController())->liveStreams();
    exit;
}


if (($_GET['sportify_news_ar'] ?? '') === '20260619') {
    define('SPORTIFY_NO_OUTPUT_BUFFER', true);
    @set_time_limit(0);
    @ignore_user_abort(true);
    try {
        require $root . '/bootstrap.php';
        \App\Core\Lang::set('ar');

        $pdo = \App\Core\Database::connection();
        echo "marker=deploy-check-news-ar-v1\n";

        if ($pdo) {
            $patches = [
                'title_ar'         => 'VARCHAR(255) NULL',
                'excerpt_ar'       => 'TEXT NULL',
                'body_ar'          => 'TEXT NULL',
                'ar_translated_at' => 'DATETIME NULL',
            ];
            foreach ($patches as $col => $def) {
                if (!\App\Core\Database::columnExists('news', $col)) {
                    $pdo->exec("ALTER TABLE news ADD COLUMN {$col} {$def}");
                    echo "column_added={$col}\n";
                }
            }
        }

        $sample = 'Premier League fixtures released for next season';
        $translated = \App\Services\TranslationService::translate($sample, 'ar', 'en');
        echo 'gtx_sample=' . $translated . "\n";
        echo 'gtx_ok=' . (preg_match('/[\x{0600}-\x{06FF}]/u', $translated) ? 'yes' : 'no') . "\n";

        $limit = isset($_GET['limit']) ? max(1, min(200, (int) $_GET['limit'])) : 80;
        $force = isset($_GET['force']) && $_GET['force'] !== '0';
        $result = \App\Services\NewsTranslationService::translateAll($limit, !$force);
        echo 'translated=' . (int) ($result['translated'] ?? 0) . "\n";
        echo 'skipped=' . (int) ($result['skipped'] ?? 0) . "\n";
        echo 'errors=' . (int) ($result['errors'] ?? 0) . "\n";
        echo 'batch=' . (int) ($result['total'] ?? 0) . "\n";
        echo "news_ar_setup=done\n";
    } catch (Throwable $e) {
        http_response_code(500);
        echo "news_ar_setup=error\n";
        echo 'err=' . $e->getMessage() . "\n";
    }
    exit;
}

if (($_GET['setup_only_talents'] ?? '') === 'onlytalents2026') {
    define('SPORTIFY_NO_OUTPUT_BUFFER', true);
    register_shutdown_function(static function (): void {
        $e = error_get_last();
        if ($e) {
            echo 'fatal=' . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
        }
    });
    try {
        $bootFile = $root . '/bootstrap.php';
        echo 'bootstrap_ot_guard=' . (is_file($bootFile) && str_contains((string) file_get_contents($bootFile), 'isOtSubdomain') ? 'yes' : 'no') . "\n";
        require $bootFile;
        echo "marker=deploy-check-setup-v4\n";
        $pdo = \App\Core\Database::connection();
        if (!$pdo) {
            echo "only_talents_setup=error\ndb_err=unavailable\n";
            exit;
        }
        $action = strtolower(trim((string) ($_GET['action'] ?? 'seed')));
        $delete = isset($_GET['delete']) && $_GET['delete'] !== '0';

        if (in_array($action, ['prune', 'refresh', 'prune_refresh'], true)) {
            @set_time_limit(0);
            @ignore_user_abort(true);
        }

        if ($action === 'discovery') {
            $schema = require $root . '/database/schema.php';
            $auto = \App\Core\Database::isSqlite() ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
            $json = \App\Core\Database::isSqlite() ? 'TEXT' : 'JSON';
            foreach (['talent_categories', 'discovery_profiles'] as $table) {
                if (!isset($schema[$table])) {
                    echo "skip={$table}\n";
                    continue;
                }
                $sql = str_replace(['{{AUTO}}', '{{JSON}}'], [$auto, $json], $schema[$table]);
                $pdo->exec($sql);
                echo "migrated={$table}\n";
            }
            $seed = require $root . '/database/seeds/talent_categories.php';
            $synced = 0;
            foreach ($seed as $row) {
                $slug = (string) ($row['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }
                $stmt = $pdo->prepare(
                    'INSERT INTO talent_categories (slug, name_en, name_de, name_ar, group_key, icon, sort_order, is_active)
                     VALUES (:slug, :name_en, :name_de, :name_ar, :group_key, :icon, :sort_order, 1)
                     ON DUPLICATE KEY UPDATE name_en = VALUES(name_en), name_de = VALUES(name_de), name_ar = VALUES(name_ar),
                     group_key = VALUES(group_key), icon = VALUES(icon), sort_order = VALUES(sort_order), is_active = 1'
                );
                $stmt->execute([
                    'slug' => $slug,
                    'name_en' => (string) ($row['name_en'] ?? $slug),
                    'name_de' => (string) ($row['name_de'] ?? $row['name_en'] ?? $slug),
                    'name_ar' => (string) ($row['name_ar'] ?? $row['name_en'] ?? $slug),
                    'group_key' => (string) ($row['group_key'] ?? 'general'),
                    'icon' => (string) ($row['icon'] ?? ''),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                ]);
                $synced++;
            }
            echo 'seeded_categories=' . $synced . "\n";
            echo "only_talents_setup=done\n";
            exit;
        }

        if ($action === 'prune_refresh') {
            $prune = \App\Services\OtShortsValidationService::prune($delete);
            $refresh = \App\Services\OtShortsRefreshService::refresh();
            echo "only_talents_action=prune_refresh\n";
            echo "marker=deploy-check-setup-v5-daily\n";
            echo 'prune_checked=' . (int) ($prune['checked'] ?? 0) . "\n";
            echo 'prune_unavailable=' . (int) ($prune['unavailable'] ?? 0) . "\n";
            echo 'prune_marked=' . (int) ($prune['marked'] ?? 0) . "\n";
            echo 'prune_deleted=' . (int) ($prune['deleted'] ?? 0) . "\n";
            echo 'refresh_added=' . (int) ($refresh['added'] ?? 0) . "\n";
            echo 'refresh_bumped=' . (int) ($refresh['bumped'] ?? 0) . "\n";
            echo 'refresh_skipped=' . (int) ($refresh['skipped'] ?? 0) . "\n";
            echo 'refresh_pruned=' . (int) ($refresh['pruned'] ?? 0) . "\n";
            echo 'shorts_total=' . (int) ($refresh['total'] ?? 0) . "\n";
            echo 'available_total=' . (int) ($refresh['available_total'] ?? 0) . "\n";
            echo 'synced_at=' . (\App\Services\TalentShortsService::lastSynced() ?? '') . "\n";
            echo "only_talents_setup=done\n";
            exit;
        }

        if ($action === 'prune') {
            $limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : null;
            $prune = \App\Services\OtShortsValidationService::prune($delete, $limit);
            echo "only_talents_action=prune\n";
            echo 'prune_checked=' . (int) ($prune['checked'] ?? 0) . "\n";
            echo 'prune_unavailable=' . (int) ($prune['unavailable'] ?? 0) . "\n";
            echo 'prune_marked=' . (int) ($prune['marked'] ?? 0) . "\n";
            echo 'prune_deleted=' . (int) ($prune['deleted'] ?? 0) . "\n";
            echo 'available_total=' . (int) ($prune['available_total'] ?? 0) . "\n";
            echo "only_talents_setup=done\n";
            exit;
        }

        if ($action === 'refresh') {
            $refresh = \App\Services\OtShortsRefreshService::refresh();
            echo "only_talents_action=refresh\n";
            echo "marker=deploy-check-setup-v5-daily\n";
            echo 'refresh_added=' . (int) ($refresh['added'] ?? 0) . "\n";
            echo 'refresh_bumped=' . (int) ($refresh['bumped'] ?? 0) . "\n";
            echo 'refresh_skipped=' . (int) ($refresh['skipped'] ?? 0) . "\n";
            echo 'refresh_pruned=' . (int) ($refresh['pruned'] ?? 0) . "\n";
            echo 'shorts_total=' . (int) ($refresh['total'] ?? 0) . "\n";
            echo 'available_total=' . (int) ($refresh['available_total'] ?? 0) . "\n";
            echo 'synced_at=' . (\App\Services\TalentShortsService::lastSynced() ?? '') . "\n";
            echo "only_talents_setup=done\n";
            exit;
        }

        $before = (int) $pdo->query('SELECT COUNT(*) FROM talent_shorts')->fetchColumn();
        echo "only_talents_setup=start\n";
        echo "shorts_before={$before}\n";
        if ($before < 500) {
            $runner = require $root . '/database/seeds/ot_bulk_runner_web.php';
            $bulk = $runner($pdo, $root);
            if (!empty($bulk['skipped'])) {
                echo 'bulk_skipped=' . ($bulk['reason'] ?? 'count') . "\n";
                echo 'bulk_existing=' . (int) ($bulk['existing'] ?? $before) . "\n";
            } else {
                echo 'bulk_inserted=' . (int) ($bulk['inserted'] ?? 0) . "\n";
                echo 'bulk_total=' . (int) ($bulk['total'] ?? $before) . "\n";
            }
        }
        echo "only_talents_setup=done\n";
    } catch (Throwable $e) {
        http_response_code(500);
        echo "only_talents_setup=error\n";
        echo 'err=' . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
    }
    exit;
}

$header = $root . '/views/partials/header.php';
$headerBrand = $root . '/views/partials/header-brand.php';
$layout = $root . '/views/layouts/app.php';
$authLayout = $root . '/views/layouts/auth.php';
$socialAuth = $root . '/views/partials/social-auth.php';
$authController = $root . '/app/Controllers/AuthController.php';
$bootstrap = $root . '/bootstrap.php';

echo 'time=' . gmdate('c') . "\n";
echo 'deploy=AUTH-FIX-20260621-v3' . "\n";
echo 'header_brand_logo=' . (is_file($headerBrand) && str_contains((string) file_get_contents($headerBrand), 'logo.png') ? 'png' : 'svg') . "\n";
echo 'logo_png_exists=' . (is_file($root . '/public/assets/img/logo.png') ? 'yes' : 'no') . "\n";
echo 'header_mtime=' . (is_file($header) ? date('c', filemtime($header)) : 'missing') . "\n";
echo 'header_brand_mtime=' . (is_file($headerBrand) ? date('c', filemtime($headerBrand)) : 'missing') . "\n";
echo 'header_brand_talent_hub=' . (is_file($headerBrand) && str_contains((string) file_get_contents($headerBrand), 'nav-talent-hub') ? 'yes' : 'no') . "\n";
echo 'perf_layout=' . (is_file($layout) && str_contains((string) file_get_contents($layout), 'deploy-marker:20260615-perf-v1') ? 'yes' : 'no') . "\n";
echo 'app_min_css=' . (is_file($root . '/public/assets/css/app.min.css') ? 'yes' : 'no') . "\n";
echo 'app_min_js=' . (is_file($root . '/public/assets/js/app.min.js') ? 'yes' : 'no') . "\n";
echo 'logo_webp=' . (is_file($root . '/public/assets/img/logo.webp') ? 'yes' : 'no') . "\n";
echo 'header_has_img=' . (is_file($header) && str_contains((string) file_get_contents($header), 'brand-logo-img') ? 'yes' : 'no') . "\n";
echo 'header_brand_has_img=' . (is_file($headerBrand) && str_contains((string) file_get_contents($headerBrand), 'brand-logo-img') ? 'yes' : 'no') . "\n";
echo 'auth_layout_inline_script=' . (is_file($authLayout) && str_contains((string) file_get_contents($authLayout), 'socialToast') ? 'yes' : 'no') . "\n";
$authCtlSrc = is_file($authController) ? (string) file_get_contents($authController) : '';
echo 'auth_ctl_bytes=' . strlen($authCtlSrc) . "\n";
echo 'auth_ctl_mtime=' . (is_file($authController) ? date('c', filemtime($authController)) : 'missing') . "\n";
echo 'auth_ctl_brand_fn=' . (str_contains($authCtlSrc, 'ensureAuthBrandInHtml') ? 'yes' : 'no') . "\n";
echo 'auth_ctl_auth_v2=' . (str_contains($authCtlSrc, "'auth-v2'") ? 'yes' : 'no') . "\n";
echo 'auth_layout_v2=' . (is_file($root . '/views/layouts/auth-v2.php') ? 'yes' : 'no') . "\n";
echo 'auth_layout_brand_marker=' . (is_file($authLayout) && str_contains((string) file_get_contents($authLayout), '20260720-auth-brand-topleft') ? 'yes' : 'no') . "\n";
if (class_exists(\App\Controllers\AuthController::class)) {
    try {
        $ref = new ReflectionClass(\App\Controllers\AuthController::class);
        echo 'auth_ctl_loaded=' . $ref->getFileName() . "\n";
        echo 'auth_ctl_has_brand_method=' . ($ref->hasMethod('ensureAuthBrandInHtml') ? 'yes' : 'no') . "\n";
    } catch (Throwable $e) {
        echo 'auth_ctl_reflect_err=' . $e->getMessage() . "\n";
    }
}

echo 'social_auth_oauth=' . (is_file($socialAuth) && str_contains((string) file_get_contents($socialAuth), 'data-auth-url') ? 'yes' : 'no') . "\n";
$socialAuthLinkedin = $root . '/views/partials/social-auth-20260614-linkedin.php';
echo 'social_auth_visibility_marker=' . (is_file($socialAuthLinkedin) && str_contains((string) file_get_contents($socialAuthLinkedin), 'SocialAuthSettings') ? 'yes' : 'no') . "\n";
echo 'social_auth_settings_class=' . (class_exists(\App\Services\SocialAuthSettings::class) ? 'yes' : 'no') . "\n";
if (class_exists(\App\Services\SocialAuthSettings::class)) {
    echo 'social_auth_facebook_visible=' . (\App\Services\SocialAuthSettings::isVisible('facebook') ? 'yes' : 'no') . "\n";
    echo 'social_auth_linkedin_visible=' . (\App\Services\SocialAuthSettings::isVisible('linkedin') ? 'yes' : 'no') . "\n";
    $partialHtml = \App\Core\View::partial('partials.social-auth-20260720-visibility', ['oauthContext' => 'login', 'dbReady' => true]);
    echo 'social_auth_partial_has_facebook=' . (str_contains($partialHtml, 'data-social="facebook"') ? 'yes' : 'no') . "\n";
    echo 'social_auth_partial_has_linkedin=' . (str_contains($partialHtml, 'data-social="linkedin"') ? 'yes' : 'no') . "\n";
    echo 'social_auth_partial_has_google=' . (str_contains($partialHtml, 'data-social="google"') ? 'yes' : 'no') . "\n";
}
$socialService = $root . '/app/Services/SocialAuthService.php';
echo 'social_linkedin_flow=' . (is_file($socialService) && str_contains((string) file_get_contents($socialService), 'handleLinkedInCallback') ? 'yes' : 'no') . "\n";
echo 'social_state_role=' . (is_file($socialService) && str_contains((string) file_get_contents($socialService), 'applyStateMeta') ? 'yes' : 'no') . "\n";
$routerCore = $root . '/app/Core/Router.php';
echo 'auth_flash_once=' . (is_file($routerCore) && str_contains((string) file_get_contents($routerCore), 'flash_once') ? 'yes' : 'no') . "\n";
echo 'auth_db_guard=' . (is_file($authController) && str_contains((string) file_get_contents($authController), 'authStorageReady') ? 'yes' : 'no') . "\n";
$fixturesCalendar = $root . '/views/pages/fixtures/calendar.php';
$fixturesController = $root . '/app/Controllers/FixturesController.php';
$liveFeed = $root . '/app/Services/LiveFixtureFeedService.php';
echo 'fixtures_calendar_mtime=' . (is_file($fixturesCalendar) ? date('c', filemtime($fixturesCalendar)) : 'missing') . "\n";
echo 'fixtures_calendar_v2=' . (is_file($fixturesCalendar) && str_contains((string) file_get_contents($fixturesCalendar), 'fixtures-calendar-v2') ? 'yes' : 'no') . "\n";
echo 'fixtures_calendar_v3=' . (is_file($fixturesCalendar) && str_contains((string) file_get_contents($fixturesCalendar), 'fixtures-calendar-v3') ? 'yes' : 'no') . "\n";
echo 'fixtures_calendar_v4=' . (is_file($fixturesCalendar) && str_contains((string) file_get_contents($fixturesCalendar), 'fixtures-calendar-v4-kooora') ? 'yes' : 'no') . "\n";
echo 'fixtures_month_cal_partial=' . (is_file($root . '/views/partials/fixtures-month-calendar.php') ? 'yes' : 'no') . "\n";
echo 'fixtures_calendar_routes=' . (is_file($root . '/config/routes.php') && str_contains((string) file_get_contents($root . '/config/routes.php'), 'fixtures.calendar') ? 'yes' : 'no') . "\n";
echo 'fixtures_controller_v2=' . (is_file($fixturesController) && str_contains((string) file_get_contents($fixturesController), 'LiveFixtureFeedService') ? 'yes' : 'no') . "\n";
echo 'live_fixture_feed=' . (is_file($liveFeed) ? date('c', filemtime($liveFeed)) : 'missing') . "\n";
echo 'live_feed_reconcile=' . (is_file($liveFeed) && str_contains((string) file_get_contents($liveFeed), 'reconcileStaleLiveStatus') ? 'yes' : 'no') . "\n";
echo 'live_feed_filter=' . (is_file($liveFeed) && str_contains((string) file_get_contents($liveFeed), 'filterRealFeedFixtures') ? 'yes' : 'no') . "\n";
$fixtureRepo = $root . '/app/Models/FixtureRepository.php';
echo 'fixture_repo_filter=' . (is_file($fixtureRepo) && str_contains((string) file_get_contents($fixtureRepo), 'filterProductionRows') ? 'yes' : 'no') . "\n";
echo 'fixture_calendar_prod=' . (is_file($root . '/app/Services/FixtureCalendarService.php') && str_contains((string) file_get_contents($root . '/app/Services/FixtureCalendarService.php'), 'productionCalendarPayload') ? 'yes' : 'no') . "\n";
echo 'environment_guard=' . (is_file($root . '/app/Support/Environment.php') ? 'yes' : 'no') . "\n";
$envFile = $root . '/.env';
echo 'app_env=' . (function (): string {
    if (is_file(dirname(__DIR__) . '/.env')) {
        $raw = file_get_contents(dirname(__DIR__) . '/.env');
        if (preg_match('/^APP_ENV=(.+)$/m', (string) $raw, $m)) {
            return trim($m[1]);
        }
    }
    return 'unknown';
})() . "\n";
echo 'bootstrap_ensure_today=' . (is_file($bootstrap) && str_contains((string) file_get_contents($bootstrap), 'MatchHubSyncService::ensureToday') ? 'yes' : 'no') . "\n";
echo 'bootstrap_marquee=' . (is_file($bootstrap) && str_contains((string) file_get_contents($bootstrap), 'LiveFixtureFeedService::marqueeItems') ? 'yes' : 'no') . "\n";
$scoresTicker = $root . '/views/partials/scores-ticker.php';
echo 'scores_ticker_live=' . (is_file($scoresTicker) && str_contains((string) file_get_contents($scoresTicker), 'data-scores-ticker') ? 'yes' : 'no') . "\n";
echo 'scores_ticker_updated=' . (is_file($scoresTicker) && str_contains((string) file_get_contents($scoresTicker), 'data-scores-updated') ? 'yes' : 'no') . "\n";
echo 'marquee_cache_ttl=' . (is_file($liveFeed) && str_contains((string) file_get_contents($liveFeed), 'CACHE_TTL_MARQUEE = 300') ? '300' : 'other') . "\n";
$apiController = $root . '/app/Controllers/ApiController.php';
echo 'api_ticker_v2=' . (is_file($apiController) && str_contains((string) file_get_contents($apiController), 'synced_at') ? 'yes' : 'no') . "\n";
$indexPhp = $root . '/public/index.php';
$routes = $root . '/config/routes.php';
echo 'index_entry=' . (is_file($indexPhp) && str_contains((string) file_get_contents($indexPhp), 'index-20260614-player-photos-v1') ? 'player-photos-v1' : (is_file($indexPhp) && str_contains((string) file_get_contents($indexPhp), 'index-20260614-ticker-v1') ? 'ticker-v1' : (str_contains((string) @file_get_contents($indexPhp), 'index-20260614-homefix-v5') ? 'homefix-v5' : 'other'))) . "\n";
echo 'api_scores_route=' . (is_file($routes) && str_contains((string) file_get_contents($routes), 'api.scores_ticker') ? 'yes' : 'no') . "\n";
$rankingsView = $root . '/views/pages/rankings/index.php';
$rankingsService = $root . '/app/Services/RankingsFeedService.php';
echo 'rankings_view_v2=' . (is_file($rankingsView) && str_contains((string) file_get_contents($rankingsView), 'rankings-page') ? 'yes' : 'no') . "\n";
echo 'rankings_service=' . (is_file($rankingsService) ? date('c', filemtime($rankingsService)) : 'missing') . "\n";
$teamRepo = $root . '/app/Models/TeamRepository.php';
$hubSeed = $root . '/app/Services/HubSeedService.php';
echo 'team_repo_hubseed=' . (is_file($teamRepo) && str_contains((string) file_get_contents($teamRepo), 'HubSeedService') ? 'yes' : 'no') . "\n";
echo 'hubseed_national=' . (is_file($hubSeed) && str_contains((string) file_get_contents($hubSeed), 'nationalTeam') ? 'yes' : 'no') . "\n";
echo 'team_repo_mtime=' . (is_file($teamRepo) ? date('c', filemtime($teamRepo)) : 'missing') . "\n";
echo 'session_cookie_path=' . (is_file($bootstrap) && str_contains((string) file_get_contents($bootstrap), "'path'     => '/'") ? 'yes' : 'no') . "\n";
$tvView = $root . '/views/pages/tv/index.php';
$tvService = $root . '/app/Services/TvBroadcastFeedService.php';
$tvController = $root . '/app/Controllers/TvScheduleController.php';
echo 'tv_view_v2=' . (is_file($tvView) && str_contains((string) file_get_contents($tvView), 'tv-broadcast-grid') ? 'yes' : 'no') . "\n";
echo 'tv_service=' . (is_file($tvService) ? date('c', filemtime($tvService)) : 'missing') . "\n";
echo 'tv_controller_v2=' . (is_file($tvController) && str_contains((string) file_get_contents($tvController), 'TvBroadcastFeedService') ? 'yes' : 'no') . "\n";
$homePage = $root . '/views/pages/home.php';
echo 'root=' . $root . "\n";
echo 'home_page_mtime=' . (is_file($homePage) ? date('c', filemtime($homePage)) : 'missing') . "\n";
if (is_file($homePage) && preg_match('/deploy-marker:([^\s]+)/', (string) file_get_contents($homePage), $mHome)) {
    echo 'home_page_marker=' . $mHome[1] . "\n";
} else {
    echo "home_page_marker=missing\n";
}
echo 'home_has_partial=' . (is_file($homePage) && str_contains((string) file_get_contents($homePage), 'home-hero-talent') ? 'yes' : 'no') . "\n";
$trustStrip = $root . '/views/partials/home-trust-strip.php';
echo 'trust_strip_mtime=' . (is_file($trustStrip) ? date('c', filemtime($trustStrip)) : 'missing') . "\n";
echo 'trust_strip_logos=' . (is_file($trustStrip) && str_contains((string) file_get_contents($trustStrip), 'trust-logos') ? 'yes' : 'no') . "\n";
$routes = $root . '/config/routes.php';
$homeHero = $root . '/views/pages/home-20260614-talent-v4.php';
$homeV2 = $root . '/app/Controllers/HomeV2Controller.php';
echo 'routes_homev2=' . (is_file($routes) && str_contains((string) file_get_contents($routes), 'HomeV2Controller') ? 'yes' : 'no') . "\n";
echo 'homev2_controller=' . (is_file($homeV2) ? 'yes' : 'no') . "\n";
if (is_file($homeHero) && preg_match('/deploy-marker:([^\s]+)/', (string) file_get_contents($homeHero), $m)) {
    echo 'home_hero_marker=' . $m[1] . "\n";
} else {
    echo "home_hero_marker=missing\n";
}
$media = $root . '/app/Support/Media.php';
$helpers = $root . '/app/Support/helpers.php';
$playerAvatar = $root . '/views/partials/player-avatar.php';
$homeAiClubs = $root . '/views/partials/home-ai-clubs.php';
echo 'player_photos_media=' . (is_file($media) && str_contains((string) file_get_contents($media), 'photo-1574629810360-7efbbe195018') ? 'v2' : 'old') . "\n";
echo 'player_avatar_helper=' . (is_file($helpers) && str_contains((string) file_get_contents($helpers), 'function player_avatar') ? 'yes' : 'no') . "\n";
echo 'player_avatar_partial=' . (is_file($playerAvatar) ? 'yes' : 'no') . "\n";
echo 'home_ai_clubs_v2=' . (is_file($homeAiClubs) && str_contains((string) file_get_contents($homeAiClubs), 'player-photos-v2') ? 'yes' : 'no') . "\n";
echo 'home_ai_clubs_mtime=' . (is_file($homeAiClubs) ? date('c', filemtime($homeAiClubs)) : 'missing') . "\n";
$feedView = $root . '/views/pages/feed.php';
$feedController = $root . '/app/Controllers/FeedController.php';
$postRepo = $root . '/app/Models/PostRepository.php';
$postComposer = $root . '/views/partials/post-composer.php';
echo 'feed_view_v2=' . (is_file($feedView) && str_contains((string) file_get_contents($feedView), 'feed-tabs') ? 'yes' : 'no') . "\n";
echo 'feed_controller_v2=' . (is_file($feedController) && str_contains((string) file_get_contents($feedController), "tab === 'mine'") ? 'yes' : 'no') . "\n";
echo 'post_repo_v2=' . (is_file($postRepo) && str_contains((string) file_get_contents($postRepo), 'video_embed') ? 'yes' : 'no') . "\n";
echo 'post_composer_v2=' . (is_file($postComposer) && str_contains((string) file_get_contents($postComposer), 'data-post-composer') ? 'yes' : 'no') . "\n";
echo 'posts_table=' . (function (): string {
    try {
        require dirname(__DIR__) . '/bootstrap.php';
        return \App\Core\Database::tableExists('posts') ? 'yes' : 'no';
    } catch (Throwable $e) {
        return 'error';
    }
})() . "\n";
echo 'posts_count=' . (function (): string {
    try {
        require dirname(__DIR__) . '/bootstrap.php';
        if (!\App\Core\Database::tableExists('posts')) {
            return '0';
        }
        return (string) \App\Models\PostRepository::count();
    } catch (Throwable $e) {
        return 'error';
    }
})() . "\n";
echo 'uploads_writable=' . (is_writable($root . '/public/uploads') ? 'yes' : 'no') . "\n";
$feedView = $root . '/views/pages/feed.php';
$feedController = $root . '/app/Controllers/FeedController.php';
echo 'feed_linkedin_ui=' . (is_file($feedView) && str_contains((string) file_get_contents($feedView), 'feed-tabs') ? 'yes' : 'no') . "\n";
echo 'feed_my_posts=' . (is_file($feedController) && str_contains((string) file_get_contents($feedController), "tab === 'mine'") ? 'yes' : 'no') . "\n";
echo 'community_feed_seed=' . (is_file($root . '/database/seeds/community_feed.php') ? 'yes' : 'no') . "\n";
$fanChatCtrl = $root . '/app/Controllers/TeamFanChatController.php';
$fanChatRepo = $root . '/app/Models/ChatGroupRepository.php';
$fanChatShow = $root . '/views/pages/teams/show.php';
$fanChatHub = $root . '/views/pages/teams/chat/index.php';
$fanChatRoom = $root . '/views/pages/teams/chat/room.php';
$fanChatMsg = $root . '/views/partials/chat-message.php';
$fanChatSeed = $root . '/database/seeds/chat_groups.php';
echo 'fan_chat_controller=' . (is_file($fanChatCtrl) && str_contains((string) file_get_contents($fanChatCtrl), 'ROOM_TYPES') ? 'yes' : 'no') . "\n";
echo 'fan_chat_repo=' . (is_file($fanChatRepo) && str_contains((string) file_get_contents($fanChatRepo), 'fanRoomsForTeam') ? 'yes' : 'no') . "\n";
echo 'fan_chat_show_section=' . (is_file($fanChatShow) && str_contains((string) file_get_contents($fanChatShow), 'fan-chat-section') ? 'yes' : 'no') . "\n";
echo 'fan_chat_hub_view=' . (is_file($fanChatHub) && str_contains((string) file_get_contents($fanChatHub), 'fan-chat-hub') ? 'yes' : 'no') . "\n";
echo 'fan_chat_room_view=' . (is_file($fanChatRoom) && str_contains((string) file_get_contents($fanChatRoom), 'fan-chat-room') ? 'yes' : 'no') . "\n";
echo 'fan_chat_message_partial=' . (is_file($fanChatMsg) ? 'yes' : 'no') . "\n";
echo 'fan_chat_routes=' . (is_file($routes) && str_contains((string) file_get_contents($routes), 'TeamFanChatController') ? 'yes' : 'no') . "\n";
echo 'fan_chat_seed=' . (is_file($fanChatSeed) ? 'yes' : 'no') . "\n";
echo 'fan_chat_css=' . (is_file($root . '/public/assets/css/app.css') && str_contains((string) file_get_contents($root . '/public/assets/css/app.css'), 'fan-chat-section') ? 'yes' : 'no') . "\n";

$socialService = $root . '/app/Services/SocialAuthService.php';
$socialRaw = is_file($socialService) ? (string) file_get_contents($socialService) : '';
echo 'facebook_scopes_fix=' . (str_contains($socialRaw, 'facebookScopes') ? 'yes' : 'no') . "\n";
echo 'facebook_old_scope=' . (str_contains($socialRaw, "'scope'         => 'email,public_profile'") ? 'yes' : 'no') . "\n";
echo 'facebook_deploy_marker=' . (preg_match('/deploy-marker:\s*(\S+)/', $socialRaw, $mFb) ? $mFb[1] : 'missing') . "\n";
echo 'social_service_mtime=' . (is_file($socialService) ? date('c', filemtime($socialService)) : 'missing') . "\n";
try {
    require_once $root . '/bootstrap.php';
    echo 'facebook_scopes_config=' . (string) \App\Core\App::config('social.facebook.scopes', '') . "\n";
    echo 'facebook_scopes_effective=' . \App\Services\SocialAuthService::facebookScopes() . "\n";
} catch (Throwable $e) {
    echo 'facebook_scopes_error=' . $e->getMessage() . "\n";
}
$matchCard = $root . '/views/partials/match-card.php';
$matchRaw = is_file($matchCard) ? (string) file_get_contents($matchCard) : '';
echo 'match_card_mtime=' . (is_file($matchCard) ? date('c', filemtime($matchCard)) : 'missing') . "\n";
echo 'match_card_v2=' . (str_contains($matchRaw, 'match-card-v2') ? 'yes' : 'no') . "\n";
echo 'match_card_flags=' . (str_contains($matchRaw, 'match-card__flag') ? 'yes' : 'no') . "\n";
echo 'team_repo_flags=' . (is_file($root . '/app/Models/TeamRepository.php') && str_contains((string) file_get_contents($root . '/app/Models/TeamRepository.php'), 'flagForTeam') ? 'yes' : 'no') . "\n";
echo 'team_flag_resolver=' . (is_file($root . '/app/Support/TeamFlagResolver.php') ? 'yes' : 'no') . "\n";
echo 'team_repo_resolver=' . (is_file($root . '/app/Models/TeamRepository.php') && str_contains((string) file_get_contents($root . '/app/Models/TeamRepository.php'), 'TeamFlagResolver') ? 'yes' : 'no') . "\n";
try {
    require_once $root . '/bootstrap.php';
    echo 'flag_us=' . \App\Models\TeamRepository::flagForTeam('United States', 'FIFA World Cup 2026', 'world-cup-2026') . "\n";
    echo 'flag_sct=' . \App\Models\TeamRepository::flagForTeam('Scotland', 'FIFA World Cup 2026', 'world-cup-2026') . "\n";
    echo 'flag_club_cl=' . \App\Models\TeamRepository::flagForTeam('Audax Italiano', 'FIFA World Cup 2026', 'world-cup-2026') . "\n";
    echo 'flag_club_ie=' . \App\Models\TeamRepository::flagForTeam('Drogheda United', 'Football', '') . "\n";
} catch (Throwable $e) {
    echo 'flag_test_error=' . $e->getMessage() . "\n";
}
$liveStreamsConfig = $root . '/config/live_streams.php';
$liveStreamsView = $root . '/views/pages/live-streams.php';
$liveStreamsRoutes = $root . '/config/routes-20260619-live-streams-v1.php';
$liveStreamsDirect = $root . '/public/live-streams-20260619-v1.php';
$htaccess = $root . '/public/.htaccess';
echo 'live_streams_config=' . (is_file($liveStreamsConfig) ? 'yes' : 'no') . "\n";
echo 'live_streams_view=' . (is_file($liveStreamsView) ? 'yes' : 'no') . "\n";
echo 'live_streams_routes=' . (is_file($liveStreamsRoutes) && str_contains((string) file_get_contents($liveStreamsRoutes), '/live-sports') ? 'yes' : 'no') . "\n";
echo 'live_streams_direct=' . (is_file($liveStreamsDirect) ? 'yes' : 'no') . "\n";
echo 'htaccess_live_streams=' . (is_file($htaccess) && str_contains((string) file_get_contents($htaccess), 'live-streams-20260619-v1.php') ? 'yes' : 'no') . "\n";
echo 'tv_live_streams_method=' . (is_file($tvController) && str_contains((string) file_get_contents($tvController), 'liveStreams') ? 'yes' : 'no') . "\n";
if (is_file($liveStreamsConfig)) {
    $cfg = require $liveStreamsConfig;
    $streamCount = 0;
    foreach ($cfg['categories'] ?? [] as $cat) {
        $streamCount += count($cat['streams'] ?? []);
    }
    echo 'live_streams_count=' . $streamCount . "\n";
    echo 'live_streams_world_cup=' . (str_contains((string) file_get_contents($liveStreamsConfig), 'tinyurl.com/3u3t883c') ? 'yes' : 'no') . "\n";
}

$matchDetail = $root . '/app/Services/MatchDetailService.php';
echo 'match_detail_mtime=' . (is_file($matchDetail) ? gmdate('c', filemtime($matchDetail)) : 'missing') . "\n";
echo 'match_detail_seed_load=' . (is_file($matchDetail)
    && str_contains((string) file_get_contents($matchDetail), "Seed::load('kooora')")
    ? 'yes' : 'no') . "\n";
try {
    if (!class_exists(\App\Controllers\MatchController::class, false)) {
        require_once $root . '/bootstrap.php';
    }
    $fixture = \App\Models\FixtureRepository::find(64);
    echo 'fixture_64=' . ($fixture ? 'yes' : 'no') . "\n";
    if ($fixture) {
        \App\Services\MatchDetailService::lineups($fixture);
        echo "match_detail_lineups=ok\n";
        $html = (new \App\Controllers\MatchController())->show('64');
        echo 'match_show_len=' . strlen($html) . "\n";
        echo 'match_show_ok=' . (strlen($html) > 1000 ? 'yes' : 'no') . "\n";
    }
} catch (Throwable $e) {
    echo 'match_detail_error=' . $e->getMessage() . "\n";
    echo 'match_detail_file=' . $e->getFile() . ':' . $e->getLine() . "\n";
}

$matchWatch = $root . '/app/Services/MatchWatchService.php';
$matchShow = $root . '/views/pages/matches/show.php';
echo 'match_watch_mtime=' . (is_file($matchWatch) ? gmdate('c', filemtime($matchWatch)) : 'missing') . "\n";
echo 'match_show_watch_v2=' . (is_file($matchShow) && str_contains((string) file_get_contents($matchShow), 'primary_free_stream') ? 'yes' : 'no') . "\n";
try {
    $probeFixture = [
        'home_team' => 'Tunisia',
        'away_team' => 'Japan',
        'competition_slug' => 'world-cup-2026',
        'kickoff_at' => '2026-06-21 04:00:00',
        'status' => 'live',
    ];
    $watchProbe = \App\Services\MatchWatchService::forFixture($probeFixture);
    echo 'match_watch_free=' . (!empty($watchProbe['has_free_stream']) ? 'yes' : 'no') . "\n";
    echo 'match_watch_primary=' . (string) ($watchProbe['primary_free_stream']['url'] ?? 'none') . "\n";
    $fixture144 = \App\Models\FixtureRepository::find(144);
    if ($fixture144) {
        $watch144 = \App\Services\MatchWatchService::forFixture($fixture144);
        echo 'match144_free=' . (!empty($watch144['has_free_stream']) ? 'yes' : 'no') . "\n";
        echo 'match144_primary=' . (string) ($watch144['primary_free_stream']['url'] ?? 'none') . "\n";
        $html144 = (new \App\Controllers\MatchController())->show('144');
        echo 'match144_has_tinyurl=' . (str_contains($html144, 'tinyurl.com') ? 'yes' : 'no') . "\n";
        echo 'match144_watch_tab_only=' . (str_contains($html144, 'tab=watch') && !str_contains($html144, 'tinyurl.com') ? 'yes' : 'no') . "\n";
    }
} catch (Throwable $e) {
    echo 'match_watch_probe_error=' . $e->getMessage() . "\n";
}
