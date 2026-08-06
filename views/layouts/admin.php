<?php
use App\Core\Auth;
$pageTitle = isset($title) ? $title . ' · Admin · Sportify' : 'Admin · Sportify';
$resources = [
    'users'           => ['admin.nav.users', '👥', 'admin.users', 'admin.users.view'],
    'players'         => ['admin.nav.players', '⚽', 'admin.players', 'admin.access'],
    'clubs'           => ['admin.nav.clubs', '🛡️', 'admin.resource', 'admin.access'],
    'videos'          => ['admin.nav.videos', '🎬', 'admin.resource', 'admin.access'],
    'news'            => ['admin.nav.news', '📰', 'admin.resource', 'admin.access'],
    'recommendations' => ['admin.nav.recommendations', '◆', 'admin.resource', 'admin.access'],
    'fitpass'         => ['admin.nav.fitpass', '🎟️', 'admin.resource', 'admin.access'],
    'marketplace'     => ['marketplace.admin.title', '🏪', 'admin.marketplace', 'marketplace.admin'],
    'pro_coaches'     => ['pro_coaches.admin.title', '🏋️', 'admin.pro_coaches', 'pro_coaches.admin'],
    'transfer_bureau' => ['transfer_bureau.admin.title', '⚽', 'admin.transfer_bureau', 'transfer_bureau.admin'],
    'memberships'     => ['membership.admin.title', '🎟️', 'admin.memberships', 'membership.manage'],
    'talent_categories' => ['ot.admin.categories_title', '🎯', 'admin.talent_categories', 'admin.access'],
    'discovery'       => ['ot.admin.discovery_title', '🔍', 'admin.discovery', 'admin.access'],
    'social_auth'     => ['admin.nav.social_auth', '🔐', 'admin.social_auth', 'admin.access'],
    'messages'        => ['admin.nav.messages', '✉️', 'admin.resource', 'admin.access'],
];
$activeResource = $resource ?? null;
$uri = $_SERVER['REQUEST_URI'] ?? '';
$onUsers = ($activeResource ?? '') === 'users' || str_contains($uri, '/admin/users');
$onPlayers = str_contains($uri, '/admin/players');
$adminContentAnchor = static fn (string $url): string => str_contains($url, '#') ? $url : $url . '#admin-content';
?>
<!DOCTYPE html>
<html lang="<?= e(locale()) ?>" dir="<?= e(\App\Core\Lang::dir()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= asset('img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-side">
        <a href="<?= home_url() ?>" class="brand"><?= brand_logo_img(30, 120) ?></a>
        <nav class="admin-nav">
            <?php if (Auth::can('admin.access')): ?>
            <a href="<?= e($adminContentAnchor(route('admin.dashboard'))) ?>" class="<?= !$activeResource && !$onUsers && !$onPlayers ? 'active' : '' ?>">▤ <?= __('admin.nav.dashboard') ?></a>
            <?php endif; ?>
            <?php foreach ($resources as $key => $r): ?>
                <?php
                if (!Auth::can($r[3] ?? 'admin.access')) {
                    continue;
                }
                $href = match ($r[2]) {
                    'admin.users' => route('admin.users'),
                    'admin.players' => route('admin.players'),
                    'admin.marketplace' => route('admin.marketplace'),
                    'admin.pro_coaches' => route('admin.pro_coaches'),
                    'admin.transfer_bureau' => route('admin.transfer_bureau'),
                    'admin.memberships' => route('admin.memberships'),
                    'admin.talent_categories' => route('admin.talent_categories'),
                    'admin.discovery' => route('admin.discovery'),
                    'admin.social_auth' => route('admin.social_auth'),
                    default => route('admin.resource', ['resource' => $key]),
                };
                $isActive = ($key === 'users' && $onUsers) || ($key === 'players' && $onPlayers) || ($key === 'marketplace' && str_contains($uri, '/admin/marketplace')) || ($key === 'pro_coaches' && str_contains($uri, '/admin/pro-coaches')) || ($key === 'transfer_bureau' && str_contains($uri, '/admin/transfer-bureau')) || ($key === 'memberships' && (str_contains($uri, '/admin/memberships') || str_contains($uri, '/admin/fitpass-plans'))) || ($key === 'talent_categories' && str_contains($uri, '/admin/talent-categories')) || ($key === 'discovery' && str_contains($uri, '/admin/discovery')) || ($key === 'social_auth' && str_contains($uri, '/admin/social-auth')) || $activeResource === $key;
                ?>
                <a href="<?= e($adminContentAnchor($href)) ?>" class="<?= $isActive ? 'active' : '' ?>">
                    <?= $r[1] ?> <?= e(__($r[0])) ?>
                </a>
            <?php endforeach; ?>
            <?php if (Auth::can('crm.access')): ?>
                <a href="<?= e($adminContentAnchor(route('admin.crm.dashboard'))) ?>" class="<?= str_contains($uri, '/admin/crm') ? 'active' : '' ?>">📊 CRM</a>
            <?php endif; ?>
            <a href="<?= route('home') ?>" style="margin-top:14px"><?= __('admin.back_to_site') ?></a>
        </nav>
    </aside>
    <main class="admin-main" id="admin-content" tabindex="-1">
        <?= \App\Core\View::partial('partials.alerts') ?>
        <?= $content ?>
    </main>
</div>
<script>
(function () {
    function scrollAdminContent(behavior) {
        var main = document.getElementById('admin-content');
        if (!main || !window.matchMedia('(max-width: 980px)').matches) return;
        main.scrollIntoView({ behavior: behavior || 'smooth', block: 'start' });
        main.focus({ preventScroll: true });
    }
    document.addEventListener('DOMContentLoaded', function () {
        scrollAdminContent(location.hash === '#admin-content' ? 'smooth' : 'instant');
    });
    document.querySelectorAll('.admin-nav a[href*="#admin-content"]').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 980px)').matches) {
                sessionStorage.setItem('sportifyAdminScrollContent', '1');
            }
        });
    });
    if (sessionStorage.getItem('sportifyAdminScrollContent') === '1') {
        sessionStorage.removeItem('sportifyAdminScrollContent');
        scrollAdminContent('smooth');
    }
})();
</script>
<?= \App\Core\View::partial('partials.chatbot') ?>
</body>
</html>
