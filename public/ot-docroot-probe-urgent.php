<?php
header('Content-Type: text/plain');
echo "ok docroot=" . __DIR__ . " host=" . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
