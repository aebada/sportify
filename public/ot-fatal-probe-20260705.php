<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);
register_shutdown_function(static function (): void {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo "\nfatal=" . $e['message'] . '@' . $e['file'] . ':' . $e['line'] . "\n";
    }
});
$root = __DIR__;
if (function_exists('opcache_reset')) { @opcache_reset(); }
require_once $root . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register($root . '/app');
if (is_file($root . '/app/Support/ot_helpers_hotfix.php')) {
    require_once $root . '/app/Support/ot_helpers_hotfix.php';
}
require $root . '/bootstrap-20260703-ot.php';
echo "pre_run=ok ot=" . (\App\Support\OnlyTalentsContext::isActive() ? 'yes' : 'no') . "\n";
try {
    \App\Core\App::run();
} catch (\Throwable $e) {
    echo "throwable=" . $e->getMessage() . '@' . $e->getFile() . ':' . $e->getLine() . "\n";
}
