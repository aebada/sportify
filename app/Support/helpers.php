<?php

use App\Core\App;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Lang;
use App\Core\Router;

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return Lang::get($key, $replace);
    }
}

if (!function_exists('tr_content')) {
    /** Translate dynamic English content when locale is Arabic. */
    function tr_content(string $text): string
    {
        return \App\Services\ContentLocalizer::text($text);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        return Router::route($name, $params);
    }
}

if (!function_exists('home_url')) {
    /** Canonical landing page URL (always `/` at site root). */
    function home_url(): string
    {
        return url('/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return Router::base() . '/' . ltrim($path, '/');
    }
}

if (!function_exists('brand_logo_img')) {
    /**
     * Header/footer logo — official PNG (green S + stadium mark + SPORTIFY wordmark).
     */
    function brand_logo_img(int $height = 42, int $width = 168, string $class = 'brand-logo brand-logo-img', string $extra = ''): string
    {
        $png = asset('img/logo.png');
        $webpFile = App::config('paths.root') . '/public/assets/img/logo.webp';
        $webp = is_file($webpFile) ? asset('img/logo.webp') : '';
        $attrs = sprintf(
            'alt="Sportify" class="%s" width="%d" height="%d" decoding="async" fetchpriority="high"',
            e($class),
            $width,
            $height
        );
        if ($extra !== '') {
            $attrs .= ' ' . trim($extra);
        }
        if ($webp !== '') {
            return '<picture>'
                . '<source type="image/webp" srcset="' . e($webp) . '">'
                . '<img src="' . e($png) . '" ' . $attrs . '>'
                . '</picture>';
        }
        return '<img src="' . e($png) . '" ' . $attrs . '>';
    }
}

if (!function_exists('perf_preload_link')) {
    /** Emit a preload link for an LCP or hero image (supports responsive srcset). */
    function perf_preload_link(
        string $href,
        string $as = 'image',
        bool $high = true,
        ?string $imagesrcset = null,
        ?string $imagesizes = null
    ): string {
        if ($href === '') {
            return '';
        }
        $type = '';
        if ($as === 'image') {
            if (str_contains($href, '.webp') || ($imagesrcset && str_contains($imagesrcset, 'fm=webp'))) {
                $type = ' type="image/webp"';
            } elseif (str_contains($href, '.png')) {
                $type = ' type="image/png"';
            }
        }
        $prio = ($as === 'image' && $high) ? ' fetchpriority="high"' : '';
        $cors = str_starts_with($href, 'http') ? ' crossorigin' : '';
        $srcset = ($imagesrcset !== null && $imagesrcset !== '')
            ? ' imagesrcset="' . e($imagesrcset) . '"'
            : '';
        $sizes = ($imagesizes !== null && $imagesizes !== '')
            ? ' imagesizes="' . e($imagesizes) . '"'
            : '';
        return '<link rel="preload" href="' . e($href) . '" as="' . e($as) . '"' . $type . $cors . $srcset . $sizes . $prio . '>';
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        if ($path === 'img/logo.svg' || $path === 'img/logo.png') {
            $path = 'img/logo.png';
        }
        if ($path === 'img/favicon.svg' || $path === 'img/favicon.png') {
            $path = 'img/favicon.png';
        }

        $root = App::config('paths.root');
        $rel = ltrim($path, '/');
        $file = $root . '/public/assets/' . $rel;

        if (preg_match('/\.(css|js)$/', $rel) && !str_contains($rel, '.min.')) {
            $minRel = preg_replace('/\.(css|js)$/', '.min.$1', $rel);
            $minFile = $root . '/public/assets/' . $minRel;
            if (is_file($minFile) && filemtime($minFile) >= @filemtime($file)) {
                $rel = $minRel;
                $file = $minFile;
            }
        }

        $url = Router::base() . '/assets/' . $rel;
        if (is_file($file)) {
            $url .= '?v=' . filemtime($file);
        } elseif (class_exists(\App\Support\BrandAssets::class) && \App\Support\BrandAssets::has(basename($path))) {
            $bytes = \App\Support\BrandAssets::bytes(basename($path));
            $url .= '?v=' . ($bytes ? substr(md5($bytes), 0, 10) : '1');
        }
        return $url;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        return App::config($key, $default);
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '')
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, $value = null)
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

if (!function_exists('flash_peek')) {
    function flash_peek(string $key)
    {
        return $_SESSION['_flash'][$key] ?? null;
    }
}

if (!function_exists('flash_once')) {
    /** Set a flash message only when that slot is still empty (preserve OAuth errors). */
    function flash_once(string $key, $value): void
    {
        if (flash_peek($key) === null) {
            flash($key, $value);
        }
    }
}

if (!function_exists('sportify_login_redirect_url')) {
    function sportify_login_redirect_url(?string $returnPath = null): string
    {
        $returnPath = $returnPath ?? (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
        if (is_string($query) && $query !== '') {
            $returnPath .= '?' . $query;
        }
        $returnPath = \App\Support\OnlyTalentsContext::sanitizeReturnUrl($returnPath);
        $login = route('login');
        if ($returnPath === '') {
            return $login;
        }

        return $login . '?next=' . rawurlencode($returnPath) . '&redirect=' . rawurlencode($returnPath);
    }
}

if (!function_exists('sportify_redirect_guests_to_login')) {
    /** OPcache-safe auth gate for member routes (bootstrap hook). */
    function sportify_redirect_guests_to_login(): void
    {
        if (\App\Core\Auth::check()) {
            return;
        }
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (!preg_match('#^/(profile(?:/edit)?|membership/(checkout|account|usage|process|cancel|trial))(/|$)#', $path)) {
            return;
        }
        flash_once('error', 'Please log in to continue.');
        if (!headers_sent()) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Location: ' . sportify_login_redirect_url());
        exit;
    }
}

if (!function_exists('flash_keep')) {
    function flash_keep(): void
    {
        // Persist old input + errors for one redirect cycle.
    }
}

if (!function_exists('locale')) {
    function locale(): string
    {
        return Lang::locale();
    }
}

if (!function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return Lang::isRtl();
    }
}

if (!function_exists('active')) {
    /** Returns $class when the current path matches one of $paths. */
    function active(array|string $paths, string $class = 'active'): string
    {
        $current = App::$request->path;
        foreach ((array) $paths as $p) {
            $p = '/' . trim($p, '/');
            if ($current === $p || ($p !== '/' && str_starts_with($current, $p))) {
                return $class;
            }
        }
        return '';
    }
}

if (!function_exists('activeExact')) {
    /** Returns $class when the current path exactly matches one of $paths. */
    function activeExact(array|string $paths, string $class = 'active'): string
    {
        $current = App::$request->path;
        foreach ((array) $paths as $p) {
            $p = '/' . trim($p, '/');
            if ($current === $p) {
                return $class;
            }
        }
        return '';
    }
}

if (!function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $out = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $out .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $out ?: '?';
    }
}

if (!function_exists('player_avatar_onerror')) {
    /** Two-step onerror: alternate URL, then hide img and reveal initials sibling. */
    function player_avatar_onerror(): string
    {
        return "if(this.dataset.fallback){this.src=this.dataset.fallback;this.dataset.fallback=''}else{this.classList.add('player-avatar__img--gone')}";
    }
}

if (!function_exists('player_avatar')) {
    /**
     * Small player thumb (~48–64px) with Unsplash photo and initials fallback on dark green.
     * Never shows a broken-image icon.
     */
    function player_avatar(array $player, string $name = '', int $size = 56, string $class = '', string $extra = ''): string
    {
        $display = $name !== '' ? $name : (string) ($player['name'] ?? 'Player');
        $photos  = \App\Support\Media::resolvePlayerPhotos($player, $size, $size);
        $ini     = e(initials($display));
        $cls     = trim('player-avatar ' . $class);
        if ($photos['photo'] === '') {
            $cls .= ' player-avatar--initials';
        }
        $style = '--avatar-size:' . $size . 'px;width:' . $size . 'px;height:' . $size . 'px';

        $img = '';
        if ($photos['photo'] !== '') {
            $img = '<img class="player-avatar__img" src="' . e($photos['photo']) . '" alt="" width="' . $size . '" height="' . $size . '" loading="lazy" decoding="async"'
                . ' data-fallback="' . e($photos['photo_fallback']) . '"'
                . ' onerror="' . player_avatar_onerror() . '" ' . trim($extra) . '>';
        }

        return '<span class="' . e($cls) . '" style="' . $style . '" role="img" aria-label="' . e($display) . '">'
            . $img
            . '<span class="player-avatar__initials" aria-hidden="true">' . $ini . '</span>'
            . '</span>';
    }
}

if (!function_exists('media_img')) {
    /**
     * Render an <img> that loads a real photo and falls back to a second URL.
     * Pass $initialsName to reveal a CSS initials avatar if both URLs fail.
     */
    function media_img(string $src, string $fallback, string $alt = '', string $classAttr = '', string $extra = '', string $initialsName = ''): string
    {
        $a = e($alt);
        $origSrc = $src;
        $src = \App\Support\Media::sanitizePlayerPhoto($src);
        if ($src === '' && $origSrc !== '') {
            $src = '';
        }
        $origFb = $fallback;
        $fallback = \App\Support\Media::sanitizePlayerPhoto($fallback);
        if ($fallback === '' && $origFb !== '') {
            $fallback = '';
        }
        if ($initialsName !== '') {
            $ini = e(initials($initialsName));
            $onerr = player_avatar_onerror();
            $imgCls = 'player-avatar__img' . ($classAttr !== '' ? ' ' . e($classAttr) : '');
            $img = '<img class="' . $imgCls . '" src="' . e($src) . '" alt="' . $a . '" loading="lazy" decoding="async"'
                . ' data-fallback="' . e($fallback) . '" onerror="' . $onerr . '" ' . $extra . '>';
            return '<span class="player-avatar" role="img" aria-label="' . $a . '">' . $img
                . '<span class="player-avatar__initials" aria-hidden="true">' . $ini . '</span></span>';
        }
        $onerr = $fallback !== ''
            ? "if(this.dataset.fallback){this.src=this.dataset.fallback;this.dataset.fallback=''}else{this.onerror=null;this.hidden=true}"
            : "this.onerror=null;this.hidden=true";
        $cls = $classAttr !== '' ? ' class="' . e($classAttr) . '"' : '';
        $fbAttr = $fallback !== '' ? ' data-fallback="' . e($fallback) . '"' : '';
        $loading = str_contains($extra, 'loading=') ? '' : ' loading="lazy"';
        return '<img src="' . e($src) . '" alt="' . $a . '"' . $loading . ' decoding="async"'
            . $cls . $fbAttr . ' onerror="' . $onerr . '" ' . $extra . '>';
    }
}

if (!function_exists('news_photo')) {
    /**
     * News image — no SVG/cartoon fallback. On load failure, reveal gradient header.
     */
    function news_photo(string $src, string $alt, string $classAttr = '', string $extra = ''): string
    {
        if ($src === '') {
            return '';
        }
        $a = e($alt);
        $onerr = "this.onerror=null;this.hidden=true;this.parentElement?.classList.add('news-cover--fallback')";
        $cls = $classAttr !== '' ? ' class="' . e($classAttr) . '"' : '';
        $dims = str_contains($extra, 'width=') ? '' : ' width="800" height="450"';
        return '<img src="' . e($src) . '" alt="' . $a . '" loading="lazy" decoding="async"'
            . $dims . $cls . ' onerror="' . $onerr . '" ' . $extra . '>';
    }
}

if (!function_exists('news_text_dir_attr')) {
    /** dir attribute for mixed Arabic/English text in RTL layout. */
    function news_text_dir_attr(?string $text, ?string $preset = null): string
    {
        if (!Lang::isRtl()) {
            return '';
        }
        $dir = $preset ?? \App\Services\ContentLocalizer::textDir((string) $text);
        return $dir === 'ltr' ? ' dir="ltr"' : '';
    }
}

if (!function_exists('news_tag_label')) {
    function news_tag_label(array $item): string
    {
        return (string) ($item['category_label'] ?? $item['tag'] ?? '');
    }
}

if (!function_exists('news_source_badge')) {
    /** Short source label for news card badges (BBC, Sky, etc.). */
    function news_source_badge(?string $source): string
    {
        $short = [
            'BBC Sport'    => 'BBC Sport',
            'The Guardian' => 'The Guardian',
            'Sky Sports'   => 'Sky Sports',
            '90min'        => '90min',
        ];
        if ($source && isset($short[$source])) {
            return $short[$source];
        }
        return $source ?: __('news.source_fallback');
    }
}

if (!function_exists('news_source_slug')) {
    /** CSS modifier slug for source-specific badge styling. */
    function news_source_slug(?string $source): string
    {
        $map = [
            'BBC Sport'    => 'bbc',
            'The Guardian' => 'guardian',
            'Sky Sports'   => 'sky',
            '90min'        => 'ninetymin',
        ];
        return $map[$source ?? ''] ?? 'default';
    }
}

if (!function_exists('news_time_ago')) {
    function news_time_ago(?string $datetime): string
    {
        return \App\Models\PostRepository::timeAgo($datetime);
    }
}

if (!function_exists('fit_class')) {
    /** Color band for a fit/rating score (0-100). */
    function fit_class(int $score): string
    {
        if ($score >= 85) return 'fit-elite';
        if ($score >= 75) return 'fit-high';
        if ($score >= 60) return 'fit-mid';
        return 'fit-low';
    }
}

if (!function_exists('avail_label')) {
    function avail_label(string $status): string
    {
        return match ($status) {
            'Free Agent' => __('search.avail.free_agent'),
            'Open to Offers' => __('search.avail.open'),
            'On Loan' => __('search.avail.loan'),
            default => $status,
        };
    }
}

if (!function_exists('next_move_label')) {
    function next_move_label(string $pref): string
    {
        return match ($pref) {
            'transfer' => __('player.career_target.pref_transfer'),
            'loan' => __('player.career_target.pref_loan'),
            'stay' => __('player.career_target.pref_stay'),
            'open' => __('player.career_target.pref_open'),
            default => '',
        };
    }
}

if (!function_exists('transfer_status_label')) {
    function transfer_status_label(string $status): string
    {
        $map = [
            'Rumour' => 'transfers.status.rumour',
            'Negotiating' => 'transfers.status.negotiating',
            'Advanced talks' => 'transfers.status.advanced_talks',
            'Here we go' => 'transfers.status.here_we_go',
            'Confirmed' => 'transfers.status.confirmed',
            'Loan deal' => 'transfers.status.loan_deal',
        ];

        return isset($map[$status]) ? __($map[$status]) : $status;
    }
}

if (!function_exists('lang_switch_url')) {
    function lang_switch_url(string $locale): string
    {
        $redirect = $_SERVER['REQUEST_URI'] ?? '/';
        return '/set-lang.php?locale=' . rawurlencode($locale) . '&redirect=' . rawurlencode($redirect);
    }
}

if (!function_exists('ot_active')) {
    function ot_active(): bool
    {
        return \App\Support\OnlyTalentsContext::isActive();
    }
}

if (!function_exists('ot_url')) {
    function ot_url(string $path = '/'): string
    {
        return \App\Support\OnlyTalentsContext::url($path);
    }
}

if (!function_exists('ot_return_url')) {
    function ot_return_url(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        if (!\App\Support\OnlyTalentsContext::isActive()) {
            return $uri;
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
        $host = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'talents.sportifyplus.de'));
        return $scheme . '://' . $host . ($uri === '/' ? '' : $uri);
    }
}

if (!function_exists('ot_auth_url')) {
    function ot_auth_url(string $path = '/login', ?string $next = null): string
    {
        $path = '/' . ltrim($path, '/');
        $main = \App\Support\OnlyTalentsContext::mainBaseUrl() . $path;
        $target = $next;
        if ($target === null || $target === '') {
            $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
            $target = \App\Support\OnlyTalentsContext::isActive()
                ? \App\Support\OnlyTalentsContext::absoluteUrl($uri)
                : $uri;
        }
        if ($target === '') {
            return $main;
        }
        $sep = str_contains($main, '?') ? '&' : '?';
        return $main . $sep . 'next=' . rawurlencode($target) . '&redirect=' . rawurlencode($target);
    }
}

if (!function_exists('ot_talent_initials')) {
    function ot_talent_initials(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '?';
        }
        $parts = preg_split('/\s+/u', $name) ?: [];
        $parts = array_values(array_filter($parts, static fn(string $p): bool => $p !== ''));
        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        }
        return mb_strtoupper(mb_substr($parts[0] ?? $name, 0, 1));
    }
}

if (!function_exists('ot_route')) {
    /** Named route helper — prefers OT routes when on subdomain. */
    function ot_route(string $name, array $params = []): string
    {
        if (\App\Support\OnlyTalentsContext::isActive() && str_starts_with($name, 'ot.')) {
            return route($name, $params);
        }
        if (str_starts_with($name, 'ot.')) {
            $fallback = match ($name) {
                'ot.home'        => '/only-talents',
                'ot.discover'    => '/only-talents/discover',
                'ot.categories'  => '/only-talents/categories',
                'ot.videos'      => '/only-talents/videos',
                'ot.locations'   => '/only-talents/locations',
                'ot.profile'     => '/only-talents/profile/' . ($params['slug'] ?? ''),
                'ot.profile.edit'=> '/only-talents/profile/edit',
                'ot.category'    => '/only-talents/categories/' . ($params['slug'] ?? ''),
                'ot.communities' => '/only-talents/communities',
                'ot.communities.my' => '/only-talents/my/communities',
                'ot.communities.create' => '/only-talents/communities/create',
                'ot.shorts.load_more' => '/only-talents/api/load-more',
                'ot.shorts.like' => '/only-talents/shorts/' . rawurlencode((string) ($params['id'] ?? '')) . '/like',
                default          => '/only-talents',
            };
            return \App\Support\OnlyTalentsContext::mainBaseUrl() . $fallback;
        }
        return route($name, $params);
    }
}

if (!function_exists('ot_community_url')) {
    function ot_community_url(string $suffix = ''): string
    {
        $base = \App\Support\OnlyTalentsContext::isActive() ? '' : '/only-talents';
        return url($base . '/communities' . $suffix);
    }
}

if (!function_exists('ot_category_name')) {
    /** @param array<string,mixed> $cat */
    function ot_category_name(array $cat): string
    {
        $loc = locale();
        $key = 'name_' . $loc;
        if (!empty($cat[$key])) {
            return (string) $cat[$key];
        }
        return (string) ($cat['name_en'] ?? $cat['slug'] ?? '');
    }
}
