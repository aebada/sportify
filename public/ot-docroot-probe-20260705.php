<?php
header('Content-Type: text/plain; charset=UTF-8');
echo 'marker=probe-php-20260705' . "\n";
echo 'dir=' . __DIR__ . "\n";
echo 'host=' . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
