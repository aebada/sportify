<?php
/**
 * One-time patch: inject home-refereex partial into home-20260621-roles.php
 * deploy-marker: patch-home-refereex-20260706
 */
declare(strict_types=1);

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');

if (($_GET['key'] ?? '') !== 'refereex-home-20260706') {
    http_response_code(403);
    echo "Forbidden — append ?key=refereex-home-20260706\n";
    exit;
}

$root = dirname(__DIR__);
$homeFile = $root . '/views/pages/home-20260621-roles.php';
$marker = "partials.home-refereex";
$include = "<?= \\App\\Core\\View::partial('partials.home-refereex') ?>\n";

if (!is_file($homeFile)) {
    echo "home_file=missing path={$homeFile}\n";
    exit(1);
}

$content = (string) file_get_contents($homeFile);
if (str_contains($content, $marker) || str_contains($content, 'home-refereex')) {
    echo "home_file=already_patched\n";
    exit(0);
}

// Insert after first hero section or at top of main content
if (preg_match('#(<section[^>]*class="[^"]*hero[^"]*"[^>]*>.*?</section>)#is', $content, $m, PREG_OFFSET_CAPTURE)) {
    $pos = $m[0][1] + strlen($m[0][0]);
    $content = substr($content, 0, $pos) . "\n" . $include . substr($content, $pos);
} else {
    $content = $include . $content;
}

if (@file_put_contents($homeFile, $content) === false) {
    echo "home_file=write_failed\n";
    exit(1);
}

if (function_exists('opcache_invalidate')) {
    @opcache_invalidate(realpath($homeFile) ?: $homeFile, true);
}

echo "home_file=patched bytes=" . strlen($content) . "\n";
