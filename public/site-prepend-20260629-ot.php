<?php
declare(strict_types=1);
if (PHP_SAPI === 'cli') {
    return;
}
$host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (!in_array($host, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    return;
}
$otRoot = __DIR__;
if (!is_file($otRoot . '/app/Core/Autoloader.php') && is_file(dirname(__DIR__) . '/talents/app/Core/Autoloader.php')) {
    $otRoot = dirname(__DIR__) . '/talents';
}
if (is_file($otRoot . '/app/Core/Autoloader.php')) {
    require_once $otRoot . '/app/Core/Autoloader.php';
    \App\Core\Autoloader::register($otRoot . '/app');
}
if (is_file($otRoot . '/app/Support/ot_helpers_hotfix.php')) {
    require_once $otRoot . '/app/Support/ot_helpers_hotfix.php';
}
