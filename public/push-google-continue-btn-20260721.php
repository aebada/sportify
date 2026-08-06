<?php
declare(strict_types=1);
/**
 * One-shot: write Continue-with-Google lang keys + partials onto PHP-visible roots.
 * Visit: /push-google-continue-btn-20260721.php?key=google-btn-20260721
 * Then delete this file.
 */
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');

if (($_GET['key'] ?? '') !== 'google-btn-20260721') {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

$selfDir = dirname(__DIR__);
$candidates = [
    $selfDir,
    dirname($selfDir),
    $selfDir . '/domains/sportifyplus.de/public_html',
    dirname($selfDir) . '/domains/sportifyplus.de/public_html',
    $selfDir . '/public_html',
    dirname($selfDir) . '/public_html',
    '/home/u234903558/domains/sportifyplus.de/public_html',
];

$relFiles = [
    'bootstrap.php',
    'app/Controllers/AuthController.php',
    'app/Core/Lang.php',
    'lang/en/messages.php',
    'lang/de/messages.php',
    'lang/ar/messages.php',
    'views/partials/google-auth-button.php',
    'views/partials/social-auth.php',
    'views/partials/social-auth-20260720-visibility.php',
    'views/partials/social-auth-20260614-linkedin.php',
    'views/layouts/auth.php',
    'views/layouts/auth-v2.php',
    'public/assets/css/app.css',
];

echo 'cwd=' . getcwd() . "\n";
echo 'self=' . $selfDir . "\n";

$sources = [];
foreach ($relFiles as $rel) {
    foreach (array_unique($candidates) as $root) {
        $path = $root . '/' . $rel;
        if (is_file($path) && str_contains((string) @file_get_contents($path), 'continue_google')) {
            $sources[$rel] = $path;
            break;
        }
        if (is_file($path) && !isset($sources[$rel])) {
            $sources[$rel] = $path;
        }
    }
}

$ok = 0;
$fail = 0;
$rootsTouched = [];

foreach (array_unique(array_filter($candidates, 'is_dir')) as $root) {
    $looksLikeApp = is_dir($root . '/lang') || is_dir($root . '/views') || is_dir($root . '/app');
    if (!$looksLikeApp) {
        continue;
    }
    $rootsTouched[] = $root;
    foreach ($relFiles as $rel) {
        $src = $sources[$rel] ?? null;
        if ($src === null || !is_file($src)) {
            echo "skip_missing_src={$rel}\n";
            continue;
        }
        $dest = $root . '/' . $rel;
        $dir = dirname($dest);
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            echo "fail_mkdir={$dir}\n";
            $fail++;
            continue;
        }
        $content = (string) file_get_contents($src);
        if (@file_put_contents($dest, $content) !== false) {
            echo "ok={$dest}\n";
            $ok++;
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($dest, true);
            }
            @touch($dest);
        } else {
            // Fallback: inject only the missing translation key into existing messages.php
            if (str_ends_with($rel, 'messages.php') && is_file($dest)) {
                $existing = (string) file_get_contents($dest);
                if (!str_contains($existing, "'auth.social.continue_google'")) {
                    $locale = basename(dirname($rel));
                    $line = match ($locale) {
                        'de' => "    'auth.social.continue_google' => 'Mit Google fortfahren',\n",
                        'ar' => "    'auth.social.continue_google' => 'المتابعة عبر Google',\n",
                        default => "    'auth.social.continue_google' => 'Continue with Google',\n",
                    };
                    $hintOnly = "    'auth.social.hint_google_only'";
                    if (str_contains($existing, $hintOnly)) {
                        $patched = preg_replace(
                            '/' . preg_quote($hintOnly, '/') . " => '[^']*',\n/",
                            "$0" . $line,
                            $existing,
                            1
                        );
                    } else {
                        $patched = preg_replace(
                            "/('auth\.social\.hint' => '[^']*',\n)/",
                            "$1" . "    'auth.social.hint_google_only' => " . match ($locale) {
                                'de' => "'Mit Google fortfahren — oder das E-Mail-Formular nutzen.',\n",
                                'ar' => "'تابع عبر Google — أو استخدم نموذج البريد أدناه.',\n",
                                default => "'Continue with Google — or use the email form below.',\n",
                            } . $line,
                            $existing,
                            1
                        );
                    }
                    if (is_string($patched) && $patched !== $existing && @file_put_contents($dest, $patched) !== false) {
                        echo "patched_key={$dest}\n";
                        $ok++;
                        if (function_exists('opcache_invalidate')) {
                            @opcache_invalidate($dest, true);
                        }
                        continue;
                    }
                }
            }
            echo "fail={$dest}\n";
            $fail++;
        }
    }
}

if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "opcache_reset=done\n";
}

echo 'roots=' . implode('|', $rootsTouched) . "\n";
echo "done ok={$ok} fail={$fail}\n";
