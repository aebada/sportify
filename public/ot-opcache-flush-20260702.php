<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
if (($_GET['key'] ?? '') !== 'ot-flush-20260702') { http_response_code(403); echo "Forbidden\n"; exit; }
$root = __DIR__;
foreach (glob($root . '/app/Support/helpers*.php') ?: [] as $f) {
    if (function_exists('opcache_invalidate')) { @opcache_invalidate($f, true); }
}
if (function_exists('opcache_reset')) { @opcache_reset(); }
echo "marker=ot-opcache-flush-20260702\nok\n";
