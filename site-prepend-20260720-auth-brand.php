<?php
declare(strict_types=1);
/**
 * Site prepend — auth brand top-left (2026-07-20).
 * deploy-marker: site-prepend-auth-brand-20260720
 */
if (PHP_SAPI === 'cli') {
    return;
}

if (!headers_sent()) {
    header('X-Sportify-Auth-Brand-Prepend: 20260720-v1');
}

$__p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Keep RefereeX redirect behavior
$__rxRefereex = !empty($_GET['refereex'])
    || $__p === '/refereex-ai'
    || str_starts_with($__p, '/refereex-ai/')
    || $__p === '/refereex-ai.php'
    || str_ends_with($__p, '/refereex-ai.php');

if ($__rxRefereex) {
    header('Location: https://aebada.github.io/sportify-refereex/', true, 302);
    header('Cache-Control: no-store');
    exit;
}

ob_start(static function (string $buffer): string {
    if ($buffer === '' || !str_contains($buffer, '</body>')) {
        return $buffer;
    }

    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $authPath = parse_url($uri, PHP_URL_PATH) ?: '';

    if (
        in_array($authPath, ['/login', '/register', '/forgot-password'], true)
        || str_starts_with((string) $authPath, '/reset-password')
        || str_starts_with((string) $authPath, '/password/')
    ) {
        if (!str_contains($buffer, 'class="auth-brand') && !str_contains($buffer, 'deploy-marker:20260720-auth-brand-topleft-v1')) {
            $brand = '<!-- deploy-marker:20260720-auth-brand-topleft-v1 -->'
                . '<style>.auth-body{position:relative}.auth-brand{position:absolute;top:20px;inset-inline-start:24px;z-index:5;display:inline-flex;align-items:center;line-height:0}.auth-brand .brand-logo,.auth-brand .brand-logo-img{height:36px;width:auto;display:block;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.35));-webkit-filter:drop-shadow(0 1px 2px rgba(0,0,0,.35))}@media (min-width:981px){.auth-brand{display:none}}@media (max-width:980px){.auth-main{padding-top:72px!important}}</style>'
                . '<a href="/" class="auth-brand brand" aria-label="Home">'
                . '<picture><source type="image/webp" srcset="/assets/img/logo.webp"><img src="/assets/img/logo.png" alt="Sportify" class="brand-logo brand-logo-img" width="136" height="36" decoding="async" fetchpriority="high"></picture>'
                . '</a>';
            if (preg_match('/<body([^>]*)>/i', $buffer)) {
                $buffer = (string) preg_replace('/<body([^>]*)>/i', '<body$1>' . $brand, $buffer, 1);
            }
        }
    }

    return $buffer;
});

// Chain prior prepend for trust logos / OT / match-watch if present
foreach ([
    __DIR__ . '/site-prepend-20260705-refereex-live.php',
    __DIR__ . '/public/site-prepend-20260705-refereex-live.php',
] as $__legacy) {
    if (is_file($__legacy)) {
        // Avoid double ob_start from legacy — only load non-ob parts by skipping if already buffering
        // Legacy starts its own ob_start; nested callbacks still run. Prefer not requiring legacy's full file
        // when it would nest. Instead require only if marker absent.
        break;
    }
}

// Load legacy AFTER our ob_start so nested buffers flush inner-first (legacy transforms), then ours (brand).
$__legacy = __DIR__ . '/site-prepend-20260705-refereex-live.php';
if (is_file($__legacy)) {
    require $__legacy;
}
