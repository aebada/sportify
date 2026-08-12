<?php

namespace App\Core;

class Request
{
    public string $method;
    public string $path;
    public array $query;
    public array $body;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Support running from a subdirectory (e.g. shared hosting subfolder).
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir));
        }

        $path = '/' . trim($path, '/');

        // Root .htaccess forwards into /public; nav may still link to /public/* on stale OPcache.
        if ($path === '/public' || str_starts_with($path, '/public/')) {
            $path = $path === '/public' ? '/' : '/' . trim(substr($path, 7), '/');
        }

        $this->path  = $path === '/' ? '/' : rtrim($path, '/');
        $this->query = $_GET;
        $this->body  = $_POST;
    }

    public function input(string $key, $default = null)
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function only(array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = $this->input($key);
        }
        return $out;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /** @return array<string,array>|null */
    public function file(string $key): ?array
    {
        $f = $_FILES[$key] ?? null;
        if (!$f || !is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        return $f;
    }
}
