<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Purge: *');

$root = dirname(__DIR__);
$files = [
    $root . '/lang/en/messages.php',
    $root . '/lang/de/messages.php',
    $root . '/lang/ar/messages.php',
    $root . '/views/partials/google-auth-button.php',
    $root . '/views/partials/social-auth.php',
    $root . '/views/partials/social-auth-20260720-visibility.php',
    $root . '/views/layouts/auth.php',
    $root . '/views/layouts/auth-v2.php',
];

foreach ($files as $file) {
    if (is_file($file)) {
        @touch($file);
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(realpath($file) ?: $file, true);
        }
        $has = str_contains((string) @file_get_contents($file), 'continue_google')
            || str_contains((string) @file_get_contents($file), 'google-btn-v1')
            || str_contains((string) @file_get_contents($file), 'Continue with Google');
        echo 'invalidate=' . basename(dirname($file)) . '/' . basename($file) . ' marker=' . ($has ? 'yes' : 'no') . "\n";
    } else {
        echo 'missing=' . $file . "\n";
    }
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "reset=done\n";
}
echo "done\n";
