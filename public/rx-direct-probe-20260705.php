<?php
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Sportify-RefereeX: direct-probe-v20260705');
echo "direct_ok\nmtime=" . filemtime(__FILE__) . "\n";
