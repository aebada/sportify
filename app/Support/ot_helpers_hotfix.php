<?php
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
