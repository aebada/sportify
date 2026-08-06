<?php
header('Content-Type: text/plain; charset=UTF-8');
echo "marker=ot-test-20260707\n";
echo 'root=' . dirname(__DIR__) . "\n";
echo 'css_public=' . (is_file(dirname(__DIR__) . '/public/assets/css/onlytalents.css') ? 'yes' : 'no') . "\n";
echo 'css_assets=' . (is_file(dirname(__DIR__) . '/assets/css/onlytalents.css') ? 'yes' : 'no') . "\n";
if (is_file(dirname(__DIR__) . '/assets/css/onlytalents.css')) {
    echo 'css_assets_bytes=' . filesize(dirname(__DIR__) . '/assets/css/onlytalents.css') . "\n";
}
if (is_file(dirname(__DIR__) . '/public/assets/css/onlytalents.css')) {
    echo 'css_public_bytes=' . filesize(dirname(__DIR__) . '/public/assets/css/onlytalents.css') . "\n";
}
echo 'home_view=' . (is_file(dirname(__DIR__) . '/views/pages/onlytalents/home-20260703.php') ? 'yes' : 'no') . "\n";
if (is_file(dirname(__DIR__) . '/views/pages/onlytalents/home-20260703.php')) {
    $h = (string) file_get_contents(dirname(__DIR__) . '/views/pages/onlytalents/home-20260703.php');
    echo 'home_marker=' . (preg_match('/deploy-marker:([^\\s]+)/', $h, $m) ? $m[1] : 'none') . "\n";
}
echo 'controller_home=' . (is_file(dirname(__DIR__) . '/app/Controllers/OnlyTalentsDiscoveryController.php') ? 'yes' : 'no') . "\n";
if (is_file(dirname(__DIR__) . '/app/Controllers/OnlyTalentsDiscoveryController.php')) {
    $c = (string) file_get_contents(dirname(__DIR__) . '/app/Controllers/OnlyTalentsDiscoveryController.php');
    echo 'controller_view=' . (preg_match("/home-20[0-9]{6}/", $c, $m) ? $m[0] : 'none') . "\n";
}
