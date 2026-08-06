<?php
header('Content-Type: text/plain; charset=UTF-8');
echo 'gateway_ok docroot=' . __DIR__ . ' host=' . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
