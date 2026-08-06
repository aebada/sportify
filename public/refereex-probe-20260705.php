<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-LiteSpeed-Purge: *');
header('X-Sportify-RefereeX-Probe: 20260705');
echo "probe_ok\n";
echo 'time=' . date('c') . "\n";
echo 'uri=' . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
