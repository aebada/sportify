<?php

namespace App\Core;

/**
 * Simple i18n layer. Translation files live in /lang/{locale}/messages.php
 * and return a flat key => string array. Falls back to English, then the key.
 *
 * Optional /lang/{locale}/refereex-messages.php is merged after messages.php.
 */
class Lang
{
    protected static string $locale = 'en';
    protected static array $messages = [];
    protected static array $fallback = [];
    protected static array $locales = [];

    public static function boot(array $config): void
    {
        self::$locales = $config['locales'];
        $default = $config['app']['locale'] ?? 'en';

        if (!empty($_GET['lang']) && isset(self::$locales[$_GET['lang']])) {
            $_SESSION['locale'] = $_GET['lang'];
        }

        $locale = $_SESSION['locale'] ?? $_COOKIE['sportify_locale'] ?? $default;
        if (!isset(self::$locales[$locale])) {
            $locale = 'en';
        }
        self::$locale = $locale;

        self::$fallback = self::load('en');
        self::$messages = $locale === 'en' ? self::$fallback : self::load($locale);

        if (self::$locale === 'ar' && PHP_SAPI !== 'cli' && empty($_SESSION['ar_content_warmed'])
            && !\App\Support\OnlyTalentsContext::isActive()) {
            try {
                \App\Services\ContentLocalizer::warmVisible();
                $_SESSION['ar_content_warmed'] = time();
            } catch (\Throwable $e) {
            }
        }
    }

    protected static function load(string $locale): array
    {
        $dir = App::config('paths.lang') . '/' . $locale;
        $messages = is_file($dir . '/messages.php') ? (require $dir . '/messages.php') : [];
        $extra = is_file($dir . '/refereex-messages.php') ? (require $dir . '/refereex-messages.php') : [];

        return array_merge($messages, $extra);
    }

    public static function set(string $locale): void
    {
        if (isset(self::$locales[$locale])) {
            if ($locale !== 'ar') {
                unset($_SESSION['ar_content_warmed']);
            }
            $_SESSION['locale'] = $locale;
            self::$locale = $locale;
            self::$messages = $locale === 'en' ? self::$fallback : self::load($locale);
            if ($locale === 'ar' && !\App\Support\OnlyTalentsContext::isActive()) {
                unset($_SESSION['ar_content_warmed']);
                \App\Services\ContentLocalizer::warmVisible();
                $_SESSION['ar_content_warmed'] = time();
            }
        }
    }

    public static function get(string $key, array $replace = []): string
    {
        $value = self::$messages[$key] ?? self::$fallback[$key] ?? $key;
        if ($value === $key && $key === 'auth.social.continue_google') {
            $value = match (self::$locale) {
                'de' => 'Mit Google fortfahren',
                'ar' => 'المتابعة عبر Google',
                default => 'Continue with Google',
            };
        }
        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, (string) $v, $value);
        }
        return $value;
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function dir(): string
    {
        return self::$locales[self::$locale]['dir'] ?? 'ltr';
    }

    public static function isRtl(): bool
    {
        return self::dir() === 'rtl';
    }

    public static function locales(): array
    {
        return self::$locales;
    }
}
