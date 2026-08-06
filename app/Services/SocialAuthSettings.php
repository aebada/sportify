<?php

namespace App\Services;

use App\Core\App;

/** Admin-controlled visibility for social login providers (storage/cache JSON). */
class SocialAuthSettings
{
    /** @var array<string,bool>|null */
    private static ?array $cache = null;

    /** @return array<string,bool> */
    public static function defaults(): array
    {
        return [
            'facebook' => false,
            'linkedin' => false,
        ];
    }

    public static function isVisible(string $provider): bool
    {
        if ($provider === 'google') {
            return true;
        }

        if (!in_array($provider, ['facebook', 'linkedin'], true)) {
            return false;
        }

        $settings = self::all();

        return (bool) ($settings[$provider] ?? false);
    }

    /** @return array<string,bool> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        self::$cache = array_merge(self::defaults(), self::loadFromDisk());

        return self::$cache;
    }

    /** @param array<string,mixed> $input */
    public static function save(array $input): void
    {
        $data = [
            'facebook' => !empty($input['facebook']),
            'linkedin' => !empty($input['linkedin']),
        ];

        $path = self::path();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        self::$cache = $data;
    }

    /** @return list<string> */
    public static function visibleProviderKeys(): array
    {
        $keys = [];
        foreach (['google', 'facebook', 'linkedin'] as $provider) {
            if (self::isVisible($provider)) {
                $keys[] = $provider;
            }
        }

        return $keys;
    }

    /** @return array<string,bool> */
    private static function loadFromDisk(): array
    {
        $path = self::path();
        if (!is_file($path)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    private static function path(): string
    {
        return App::config('paths.storage') . '/cache/social_auth_visibility.json';
    }
}
