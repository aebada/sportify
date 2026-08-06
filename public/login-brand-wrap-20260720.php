<?php
/**
 * Auth brand wrapper — explicit rewrite target (bypasses stale front-controller opcache).
 * deploy-marker: login-brand-wrap-20260720
 */
declare(strict_types=1);

header('X-Sportify-Auth-Brand-Wrap: 20260720-v1');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap-20260621-authfix.php')) {
    $root = dirname(__DIR__, 1);
}
if (!is_file($root . '/bootstrap-20260621-authfix.php') && is_file(__DIR__ . '/../bootstrap.php')) {
    $root = dirname(__DIR__);
}

// Prefer auth-fix bootstrap used by live front controller
$boot = $root . '/bootstrap-20260621-authfix.php';
if (!is_file($boot)) {
    $boot = $root . '/bootstrap.php';
}
require $boot;

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . trim($path, '/');
if ($path === '') {
    $path = '/';
}

ob_start();
try {
    if ($path === '/register' || str_starts_with($path, '/register')) {
        echo (new \App\Controllers\AuthController())->showRegister();
    } elseif ($path === '/forgot-password' || str_starts_with($path, '/forgot-password')) {
        echo (new \App\Controllers\AuthController())->showForgot();
    } else {
        echo (new \App\Controllers\AuthController())->showLogin();
    }
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "auth-brand-wrap-error\n" . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine();
    exit;
}
$html = (string) ob_get_clean();

if ($html !== '' && !str_contains($html, 'class="auth-brand') && !str_contains($html, 'deploy-marker:20260720-auth-brand-topleft-v1')) {
    $brand = '<!-- deploy-marker:20260720-auth-brand-topleft-v1 -->'
        . '<style>.auth-body{position:relative}.auth-brand{position:absolute;top:20px;inset-inline-start:24px;z-index:5;display:inline-flex;align-items:center;line-height:0}.auth-brand .brand-logo,.auth-brand .brand-logo-img{height:36px;width:auto;display:block;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.35));-webkit-filter:drop-shadow(0 1px 2px rgba(0,0,0,.35))}@media (min-width:981px){.auth-brand{display:none}}@media (max-width:980px){.auth-main{padding-top:72px!important}}</style>'
        . '<a href="/" class="auth-brand brand" aria-label="Home">'
        . '<picture><source type="image/webp" srcset="/assets/img/logo.webp"><img src="/assets/img/logo.png" alt="Sportify" class="brand-logo brand-logo-img" width="136" height="36" decoding="async" fetchpriority="high"></picture>'
        . '</a>';
    if (preg_match('/<body([^>]*)>/i', $html)) {
        $html = (string) preg_replace('/<body([^>]*)>/i', '<body$1>' . $brand, $html, 1);
    }
}

echo $html;
