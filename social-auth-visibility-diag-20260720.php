<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Purge: *');

$root = __DIR__;
require $root . '/bootstrap.php';

$partial = $root . '/views/partials/social-auth-20260614-linkedin.php';
echo 'partial_exists=' . (is_file($partial) ? 'yes' : 'no') . "\n";
echo 'partial_marker=' . (str_contains((string) file_get_contents($partial), 'SocialAuthSettings') ? 'yes' : 'no') . "\n";
echo 'settings_class=' . (class_exists(\App\Services\SocialAuthSettings::class) ? 'yes' : 'no') . "\n";
if (class_exists(\App\Services\SocialAuthSettings::class)) {
    echo 'facebook_visible=' . (\App\Services\SocialAuthSettings::isVisible('facebook') ? 'yes' : 'no') . "\n";
    echo 'linkedin_visible=' . (\App\Services\SocialAuthSettings::isVisible('linkedin') ? 'yes' : 'no') . "\n";
}

$html = \App\Core\View::partial('partials.social-auth-20260614-linkedin', ['oauthContext' => 'login', 'dbReady' => true]);
echo 'partial_html_bytes=' . strlen($html) . "\n";
echo 'has_facebook=' . (str_contains($html, 'data-social="facebook"') ? 'yes' : 'no') . "\n";
echo 'has_linkedin=' . (str_contains($html, 'data-social="linkedin"') ? 'yes' : 'no') . "\n";
echo 'has_google=' . (str_contains($html, 'data-social="google"') ? 'yes' : 'no') . "\n";
