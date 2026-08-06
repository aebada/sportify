<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
$root = __DIR__;
echo "root={$root}\n";
echo "host=" . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
foreach ([
    'bootstrap-20260703-ot.php',
    'app/Support/helpers-20260703-ot-live.php',
    'app/Support/helpers.php',
    'index.php',
] as $rel) {
    $f = $root . '/' . $rel;
    echo $rel . ' exists=' . (is_file($f) ? 'yes' : 'no');
    if (is_file($f)) {
        echo ' mtime=' . date('c', filemtime($f)) . ' size=' . filesize($f);
        if (str_ends_with($rel, 'helpers.php') || str_contains($rel, 'helpers-20260703')) {
            $c = file_get_contents($f);
            echo ' has_currentReturnTarget=' . (str_contains($c, 'currentReturnTarget()') ? 'yes' : 'no');
        }
    }
    echo "\n";
}
if (function_exists('opcache_reset')) {
    @opcache_reset();
    echo "opcache_reset=ok\n";
}
