<?php

namespace App\Support;

use App\Core\App;

/**
 * Detects OnlyTalents subdomain / forced dev mode and builds cross-domain URLs.
 */
class OnlyTalentsContext
{
    protected static ?bool $active = null;

    /** @var list<string> */
    protected static array $hosts = [
        'onlytalents.sportifyplus.de',
        'talents.sportifyplus.de',
    ];

    public static function isActive(): bool
    {
        if (self::$active !== null) {
            return self::$active;
        }

        if (filter_var(App::config('onlytalents.force', false), FILTER_VALIDATE_BOOLEAN)) {
            return self::$active = true;
        }

        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host) ?: $host;

        $configured = trim((string) App::config('onlytalents.host', ''));
        if ($configured !== '' && $host === strtolower($configured)) {
            return self::$active = true;
        }

        foreach (self::$hosts as $h) {
            if ($host === $h) {
                return self::$active = true;
            }
        }

        return self::$active = false;
    }

    public static function layout(): string
    {
        return self::isActive() ? 'onlytalents' : 'app';
    }

    public static function brandName(): string
    {
        return 'OnlyTalents';
    }

    /** Canonical OT subdomain base URL (no trailing slash). */
    public static function baseUrl(): string
    {
        $configured = rtrim((string) App::config('onlytalents.url', ''), '/');
        if ($configured !== '') {
            return $configured;
        }
        return 'https://talents.sportifyplus.de';
    }

    /** Main Sportify site base URL for shared auth. */
    public static function mainBaseUrl(): string
    {
        $main = rtrim((string) App::config('onlytalents.main_url', ''), '/');
        if ($main !== '') {
            return $main;
        }
        $appUrl = rtrim((string) App::config('app.url', ''), '/');
        return $appUrl !== '' ? $appUrl : 'https://sportifyplus.de';
    }

    public static function url(string $path = '/'): string
    {
        $path = '/' . ltrim($path, '/');
        if (self::isActive()) {
            return $path === '/' ? '/' : rtrim($path, '/');
        }
        return self::baseUrl() . ($path === '/' ? '' : $path);
    }

    public static function absoluteUrl(string $path = '/'): string
    {
        if (self::isActive()) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
            $host = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $rel = self::url($path);
            return $scheme . '://' . $host . ($rel === '/' ? '' : $rel);
        }
        return self::baseUrl() . (self::url($path) === '/' ? '' : self::url($path));
    }

    /** Current page as a safe post-auth return target (absolute on OT subdomain). */
    public static function currentReturnTarget(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        if (!self::isActive()) {
            return $uri;
        }

        return self::absoluteUrlFromUri($uri);
    }

    /** Build absolute OT URL from a request URI (path + query). */
    public static function absoluteUrlFromUri(string $uri): string
    {
        $uri = trim($uri);
        if ($uri === '' || str_starts_with($uri, '//')) {
            $uri = '/';
        }
        if (!str_starts_with($uri, '/')) {
            $uri = '/' . $uri;
        }

        $scheme = self::requestScheme();
        $host = self::normalizeHost((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '') {
            $host = self::normalizeHost((string) parse_url(self::baseUrl(), PHP_URL_HOST));
        }

        return $scheme . '://' . $host . ($uri === '/' ? '' : $uri);
    }

    /**
     * Sanitize a post-auth return URL.
     * Allows same-origin relative paths and absolute URLs on Sportify / OnlyTalents hosts.
     * Preserves cross-subdomain targets as absolute URLs.
     */
    public static function sanitizeReturnUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '//')) {
            return '';
        }

        $currentHost = self::normalizeHost((string) ($_SERVER['HTTP_HOST'] ?? ''));

        if (str_starts_with($url, '/')) {
            if (self::isAuthPath($url)) {
                return '';
            }
            if (self::isSubdomainOnlyPath($url) && !self::isActive()) {
                return rtrim(self::baseUrl(), '/') . $url;
            }

            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return '';
        }

        $host = self::normalizeHost((string) ($parts['host'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if ($host === '' || !in_array($scheme, ['http', 'https'], true)) {
            return '';
        }
        if (!self::isAllowedReturnHost($host)) {
            return '';
        }

        $path = (string) ($parts['path'] ?? '/');
        if (self::isAuthPath($path)) {
            return '';
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        $target = ($path === '' ? '/' : $path) . $query . $fragment;

        if ($host !== $currentHost) {
            return $scheme . '://' . $host . ($target === '/' ? '' : $target);
        }

        return $target;
    }

    /**
     * Post-auth return URL that must stay on the current host (OAuth callbacks).
     * Drops cross-subdomain absolute URLs so the session cookie always applies.
     */
    public static function sanitizeSameHostReturnUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $currentHost = self::normalizeHost((string) ($_SERVER['HTTP_HOST'] ?? ''));

        if (str_starts_with($url, '/')) {
            if (self::isAuthPath($url)) {
                return '';
            }
            if (self::isSubdomainOnlyPath($url) && !self::isActive()) {
                return '';
            }

            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return '';
        }

        $host = self::normalizeHost((string) ($parts['host'] ?? ''));
        if ($host === '' || $host !== $currentHost) {
            return '';
        }

        $path = (string) ($parts['path'] ?? '/');
        if (self::isAuthPath($path)) {
            return '';
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return ($path === '' ? '/' : $path) . $query . $fragment;
    }

    /** OT subdomain paths that do not exist at the same path on the main site. */
    public static function isSubdomainOnlyPath(string $path): bool
    {
        $pathOnly = (string) parse_url($path, PHP_URL_PATH);
        $pathOnly = '/' . ltrim($pathOnly, '/');
        if (str_starts_with($pathOnly, '/only-talents')) {
            return false;
        }

        $prefixes = [
            '/discover',
            '/categories',
            '/locations',
            '/videos',
            '/communities',
            '/profile',
            '/my',
            '/upload',
        ];
        foreach ($prefixes as $prefix) {
            if ($pathOnly === $prefix || str_starts_with($pathOnly, $prefix . '/')) {
                return true;
            }
        }

        return $pathOnly === '/';
    }

    public static function isAuthPath(string $path): bool
    {
        $pathOnly = (string) parse_url($path, PHP_URL_PATH);
        $pathOnly = '/' . ltrim($pathOnly, '/');

        return in_array($pathOnly, ['/login', '/register', '/logout'], true)
            || str_starts_with($pathOnly, '/password');
    }

    public static function isAllowedReturnHost(string $host): bool
    {
        $host = self::normalizeHost($host);
        $allowed = [
            self::normalizeHost((string) parse_url(self::baseUrl(), PHP_URL_HOST)),
            self::normalizeHost((string) parse_url(self::mainBaseUrl(), PHP_URL_HOST)),
        ];
        foreach (self::$hosts as $h) {
            $allowed[] = self::normalizeHost($h);
        }

        return in_array($host, array_filter(array_unique($allowed)), true);
    }

    protected static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));

        return preg_replace('/:\d+$/', '', $host) ?: $host;
    }

    protected static function requestScheme(): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        return $https ? 'https' : 'http';
    }

    /** Auth on main domain with redirect back to OT after login. */
    public static function authUrl(string $path, ?string $nextAfter = null): string
    {
        $path = '/' . ltrim($path, '/');
        $main = self::mainBaseUrl() . $path;
        if ($nextAfter !== null && $nextAfter !== '') {
            $next = self::sanitizeReturnUrl($nextAfter);
        } else {
            $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
            $next = self::isActive() ? self::absoluteUrl($uri) : $uri;
        }
        if ($next === '') {
            return $main;
        }
        $sep = str_contains($main, '?') ? '&' : '?';
        $qs = http_build_query([
            'next' => $next,
            'redirect' => $next,
        ]);

        return $main . $sep . $qs;
    }

    public static function sessionDomain(): string
    {
        return (string) App::config('onlytalents.session_domain', '.sportifyplus.de');
    }
}
