<?php
use App\Core\Auth;
// deploy-marker:20260708-refereex-nav-v2
$locales = \App\Core\Lang::locales();
$cur = locale();
$ticker = $feedTicker ?? [];
$sources = $feedSources ?? [];
?>
<div class="site-topbar">
    <div class="container site-topbar__inner">
        <span class="live-pill"><span class="live-pill__dot"></span> <?= __('nav.live') ?></span>
        <div class="news-ticker" data-ticker aria-label="<?= e(__('nav.ticker_label')) ?>">
            <div class="news-ticker__track">
                <?php foreach (array_merge($ticker, $ticker) as $t): ?>
                    <a href="<?= e($t['source_url'] ?? route('news')) ?>" target="_blank" rel="noopener" class="news-ticker__item">
                        <strong><?= e($t['source'] ?? __('nav.news_fallback')) ?></strong>
                        <span><?= e($t['title']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="topbar-sources desktop-only">
            <?php foreach ($sources as $src): ?>
                <span><?= e($src) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?= \App\Core\View::partial('partials.scores-ticker', ['scores' => $scoresTicker ?? []]) ?>

<header class="site-header" data-site-header>
    <div class="container">
        <a href="<?= home_url() ?>" class="brand" aria-label="<?= e(__('nav.home_aria')) ?>">
            <!-- brand-asset:logo-png-stadium-v6 -->
            <!-- brand-home-link:v1 -->
            <?= brand_logo_img(42, 168) ?>
        </a>

        <form class="header-search desktop-only" action="<?= route('search') ?>" method="get" role="search">
            <span class="header-search__icon" aria-hidden="true">⌕</span>
            <input type="search" name="q" placeholder="<?= e(__('nav.search_placeholder')) ?>" autocomplete="off">
        </form>

        <?php
        $navExploreActive = active(['/matches', '/live', '/live-streams', '/live-sports', '/tables', '/standings', '/world-cup', '/tournaments/world-cup-2026', '/players', '/only-talents', '/videos', '/highlights', '/fixtures']);
        $navTalentHubActive = active(['/trainers', '/trainer', '/scout', '/clubs', '/only-talents']);
        $navNewsActive = active(['/news', '/transfers', '/markt', '/transfer-bureau', '/feed']);
        $refereexUrl = 'https://aebada.github.io/sportify-refereex/';
        $navMoreActive = active(['/competitions', '/predictions', '/fit-pass', '/stats', '/tournaments', '/providers', '/search', '/tv', '/fixtures', '/rankings', '/marketplace', '/fans', '/bookings', '/pro-coaches', '/membership', '/refereex-ai', '/refereex-ai.html']);
        ?>
        <nav class="nav" aria-label="<?= e(__('nav.primary_aria')) ?>">
            <a href="<?= $refereexUrl ?>" class="nav-link nav-link--featured desktop-only <?= active(['/refereex-ai', '/refereex-ai.html']) ?>">RefereeX AI</a>

            <div class="nav-dropdown desktop-only" data-nav-dropdown>
                <button type="button" class="nav-dropdown__btn <?= $navExploreActive ?>" data-nav-dropdown-btn aria-haspopup="true" aria-expanded="false" aria-controls="nav-explore-menu">
                    <?= __('nav.explore') ?> <span aria-hidden="true">▾</span>
                </button>
                <div class="nav-submenu" id="nav-explore-menu" data-nav-dropdown-menu hidden role="menu">
                    <a href="<?= route('matches.index') ?>" class="<?= active(['/matches', '/live']) ?>" role="menuitem"><?= __('nav.matches') ?></a>
                    <a href="<?= route('fixtures.calendar', ['date' => date('Y-m-d')]) ?>" class="<?= active(['/fixtures', '/matches/calendar']) ?>" role="menuitem"><?= __('nav.fixtures') ?></a>
                    <a href="<?= route('tables.index') ?>" class="<?= active(['/tables', '/standings']) ?>" role="menuitem"><?= __('kooora.nav.tables') ?></a>
                    <a href="<?= route('players.index') ?>" class="<?= active('/players') ?>" role="menuitem"><?= __('nav.players') ?></a>
                    <a href="<?= route('videos') ?>" class="<?= active('/videos') ?>" role="menuitem"><?= __('nav.videos') ?></a>
                    <a href="<?= route('only_talents.index') ?>" class="<?= active('/only-talents') ?>" role="menuitem"><?= __('nav.only_talents') ?></a>
                    <a href="<?= route('highlights') ?>" class="<?= active('/highlights') ?>" role="menuitem"><?= __('nav.highlights') ?></a>
                    <a href="<?= route('live_streams.index') ?>" class="<?= active(['/live-streams', '/live-sports']) ?>" role="menuitem"><?= __('nav.live_streams') ?></a>
                    <?php if (!empty($activeEvent)): ?>
                        <a href="<?= route('world_cup.hub') ?>" class="<?= active('/world-cup') ?>" role="menuitem">🏆 <?= __('event.world_cup.nav') ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nav-dropdown nav-dropdown--featured desktop-only" data-nav-dropdown>
                <button type="button" class="nav-dropdown__btn <?= $navTalentHubActive ?>" data-nav-dropdown-btn aria-haspopup="true" aria-expanded="false" aria-controls="nav-talent-hub-menu">
                    <?= __('nav.talent_hub') ?> <span aria-hidden="true">▾</span>
                </button>
                <div class="nav-submenu nav-submenu--wide" id="nav-talent-hub-menu" data-nav-dropdown-menu hidden role="menu">
                    <a href="<?= route('trainers.index') ?>" class="<?= active('/trainers') ?>" role="menuitem"><?= __('nav.trainers') ?></a>
                    <a href="<?= route('only_talents.index') ?>" class="<?= active('/only-talents') ?>" role="menuitem"><?= __('nav.scouts') ?></a>
                    <a href="<?= route('club.recommendations') ?>" class="<?= active('/clubs') ?>" role="menuitem"><?= __('nav.clubs') ?></a>
                    <a href="<?= route('register') ?>?role=player" class="nav-submenu__label <?= active('/register') ?>" role="menuitem"><?= __('nav.talent_hub_get_started') ?></a>
                    <a href="<?= route('register') ?>?role=trainer" class="<?= active('/trainer') ?>" role="menuitem"><?= __('nav.for_trainers') ?></a>
                    <a href="<?= route('register') ?>?role=scout" class="<?= active('/scout') ?>" role="menuitem"><?= __('nav.for_scouts') ?></a>
                    <a href="<?= route('club.recommendations') ?>" class="<?= active('/clubs/recommendations') ?>" role="menuitem"><?= __('nav.club_recommendations') ?></a>
                </div>
            </div>

            <div class="nav-dropdown desktop-only" data-nav-dropdown>
                <button type="button" class="nav-dropdown__btn <?= $navNewsActive ?>" data-nav-dropdown-btn aria-haspopup="true" aria-expanded="false" aria-controls="nav-news-menu">
                    <?= __('nav.news_transfers') ?> <span aria-hidden="true">▾</span>
                </button>
                <div class="nav-submenu" id="nav-news-menu" data-nav-dropdown-menu hidden role="menu">
                    <a href="<?= route('news') ?>" class="<?= active('/news') ?>" role="menuitem"><?= __('nav.news') ?></a>
                    <a href="<?= route('transfers') ?>" class="<?= active('/transfers') ?>" role="menuitem"><?= __('nav.transfers') ?></a>
                    <?php if (Auth::check()): ?>
                        <a href="<?= route('feed') ?>" class="<?= active('/feed') ?>" role="menuitem"><?= __('nav.feed') ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="nav-dropdown desktop-only" data-nav-dropdown>
                <button type="button" class="nav-dropdown__btn <?= $navMoreActive ?>" data-nav-dropdown-btn aria-haspopup="true" aria-expanded="false" aria-controls="nav-more-menu">
                    <?= __('nav.more') ?> <span aria-hidden="true">▾</span>
                </button>
                <div class="nav-submenu" id="nav-more-menu" data-nav-dropdown-menu hidden role="menu">
                    <a href="<?= $refereexUrl ?>" class="<?= active(['/refereex-ai', '/refereex-ai.html']) ?>" role="menuitem">RefereeX AI</a>
                    <a href="<?= route('fitpass') ?>" class="<?= active('/fit-pass') ?>" role="menuitem"><?= __('nav.fitpass') ?></a>
                    <a href="<?= route('competitions.index') ?>" class="<?= active(['/competitions', '/predictions']) ?>" role="menuitem"><?= __('nav.competitions') ?></a>
                    <a href="<?= route('tv.index') ?>" class="<?= active('/tv') ?>" role="menuitem"><?= __('kooora.nav.tv') ?></a>
                    <a href="<?= route('fixtures.calendar', ['date' => date('Y-m-d')]) ?>" class="<?= active(['/fixtures', '/matches/calendar']) ?>" role="menuitem"><?= __('nav.fixtures') ?></a>
                    <a href="<?= route('stats.scorers') ?>" class="<?= active('/stats') ?>" role="menuitem"><?= __('kooora.nav.stats') ?></a>
                    <a href="<?= route('tournaments.index') ?>" class="<?= active('/tournaments') ?>" role="menuitem"><?= __('kooora.nav.tournaments') ?></a>
                    <a href="<?= route('providers.index') ?>" class="<?= active('/providers') ?>" role="menuitem"><?= __('nav.providers') ?></a>
                </div>
            </div>
        </nav>

        <div class="header-actions">
            <div class="lang-switch desktop-only">
                <button class="lang-btn" data-lang-btn aria-haspopup="true">
                    <span><?= e(strtoupper($cur)) ?></span> ▾
                </button>
                <div class="lang-menu" data-lang-menu>
                    <?php foreach ($locales as $code => $l): ?>
                        <a href="<?= e(lang_switch_url($code)) ?>" class="<?= $code === $cur ? 'active' : '' ?>">
                            <?= e($l['native']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (Auth::check()): ?>
                <?php if (!empty($subscription)): ?>
                    <a href="<?= route('billing.account') ?>" class="chip chip--green chip--sm desktop-only" title="<?= e(__('nav.fitpass_membership')) ?>"><?= e($subscription['plan_name'] ?? ucfirst($subscription['tier'])) ?></a>
                <?php endif; ?>
                <a href="<?= route('referrals.index') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.invite') ?></a>
                <?= \App\Core\View::partial('partials.profile-menu') ?>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= route('admin.dashboard') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.admin') ?></a>
                <?php elseif (Auth::can('crm.access')): ?>
                    <a href="<?= route('admin.crm.dashboard') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.crm') ?></a>
                <?php elseif (Auth::can('pro_coaches.dashboard')): ?>
                    <a href="<?= route('pro_coaches.dashboard') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.pro_coaches') ?></a>
                <?php elseif (Auth::can('scout.dashboard')): ?>
                    <a href="<?= route('scout.dashboard') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.scout_dashboard') ?></a>
                    <?php if (Auth::can('scout.manage_players')): ?>
                        <a href="<?= route('scout.players.create') ?>" class="btn btn-primary btn-sm desktop-only"><?= __('nav.add_player') ?></a>
                    <?php endif; ?>
                <?php elseif (Auth::can('club.dashboard')): ?>
                    <a href="<?= route('club.dashboard') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.dashboard') ?></a>
                <?php elseif (Auth::can('trainer.dashboard')): ?>
                    <a href="<?= route('trainer.dashboard') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.trainer_dashboard') ?></a>
                <?php elseif (Auth::can('provider.panel')): ?>
                    <a href="<?= route('providers.panel') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.provider_panel') ?></a>
                <?php endif; ?>
                <?php if (Auth::can('marketplace.sell')): ?>
                    <a href="<?= route('vendor.dashboard') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.vendor_dashboard') ?></a>
                <?php endif; ?>
                <form method="post" action="<?= route('logout') ?>" style="margin:0">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary btn-sm" type="submit"><?= __('nav.logout') ?></button>
                </form>
            <?php else: ?>
                <a href="<?= route('login') ?>" class="btn btn-ghost btn-sm desktop-only"><?= __('nav.login') ?></a>
                <a href="<?= route('register') ?>" class="btn btn-primary btn-sm"><?= __('nav.join') ?></a>
            <?php endif; ?>

            <button class="nav-toggle mobile-only" type="button" data-nav-toggle aria-label="<?= e(__('a11y.open_menu')) ?>" aria-expanded="false" aria-controls="site-menu">
                <span data-nav-icon>☰</span>
            </button>
        </div>
    </div>
</header>

<div class="nav-backdrop" data-nav-backdrop aria-hidden="true"></div>

<nav class="mobile-nav" id="site-menu" data-mobile-nav aria-label="<?= e(__('nav.primary_aria')) ?>" aria-hidden="true">
    <div class="mobile-nav__head">
        <button type="button" class="mobile-nav__close" data-nav-close aria-label="<?= e(__('a11y.close_menu')) ?>">✕</button>
    </div>
    <form class="header-search header-search--mobile" action="<?= route('search') ?>" method="get" role="search">
        <input type="search" name="q" placeholder="<?= e(__('nav.search_placeholder_short')) ?>" aria-label="<?= e(__('nav.search_placeholder_short')) ?>">
        <button type="submit" class="btn btn-primary btn-sm"><?= __('nav.search_go') ?></button>
    </form>

    <?php if (Auth::check()): ?>
        <div class="mobile-nav__profile">
            <span class="mobile-nav__section mobile-nav__section--profile"><?= __('profile.nav.hub') ?></span>
            <a href="<?= route('profile.index') ?>" class="mobile-nav__profile-link <?= activeExact('/profile') ?>"><?= __('nav.my_profile') ?></a>
            <a href="<?= route('messages') ?>" class="mobile-nav__profile-link <?= active(['/messages', '/chat/messages']) ?>">✉ <?= __('profile.nav.messages') ?><?php if (!empty($unreadMessages)): ?> <span class="nav-badge"><?= (int)$unreadMessages ?></span><?php endif; ?></a>
            <a href="<?= route('profile.edit') ?>" class="mobile-nav__profile-link <?= active('/profile/edit') ?>"><?= __('profile.nav.edit') ?></a>
        </div>
    <?php endif; ?>

    <?php
    $mobileExploreOpen = active(['/matches', '/live', '/live-streams', '/live-sports', '/tables', '/standings', '/world-cup', '/tournaments/world-cup-2026', '/players', '/videos', '/highlights']) === 'active';
    $mobileTalentHubOpen = active(['/trainers', '/trainer', '/scout', '/clubs', '/only-talents']) === 'active';
    $mobileMoreOpen = active(['/news', '/transfers', '/fit-pass', '/refereex-ai', '/refereex-ai.html']) === 'active';
    ?>
    <a href="<?= $refereexUrl ?>" class="mobile-nav__featured <?= active(['/refereex-ai', '/refereex-ai.html']) ?>">RefereeX AI</a>

    <div class="mobile-nav__accordion<?= $mobileExploreOpen ? ' is-open' : '' ?>" data-mobile-accordion>
        <button type="button" class="mobile-nav__accordion-btn" data-mobile-accordion-btn aria-expanded="<?= $mobileExploreOpen ? 'true' : 'false' ?>" aria-controls="mobile-nav-explore">
            <?= __('nav.section_explore') ?> <span class="mobile-nav__chevron" aria-hidden="true">▾</span>
        </button>
        <div class="mobile-nav__accordion-panel" id="mobile-nav-explore" data-mobile-accordion-panel<?= $mobileExploreOpen ? '' : ' hidden' ?>>
            <a href="<?= route('matches.index') ?>" class="<?= active(['/matches', '/live', '/tables', '/standings']) ?>"><?= __('nav.matches_tables') ?></a>
            <a href="<?= route('players.index') ?>" class="<?= active('/players') ?>"><?= __('nav.players') ?></a>
            <span class="mobile-nav__subgroup"><?= __('nav.section_watch') ?></span>
            <a href="<?= route('videos') ?>" class="mobile-nav__sub-link <?= active('/videos') ?>"><?= __('nav.videos') ?></a>
            <a href="<?= route('only_talents.index') ?>" class="mobile-nav__sub-link <?= active('/only-talents') ?>"><?= __('nav.only_talents') ?></a>
            <a href="<?= route('highlights') ?>" class="mobile-nav__sub-link <?= active('/highlights') ?>"><?= __('nav.highlights') ?></a>
            <a href="<?= route('live_streams.index') ?>" class="mobile-nav__sub-link <?= active(['/live-streams', '/live-sports']) ?>"><?= __('nav.live_streams') ?></a>
            <?php if (!empty($activeEvent)): ?>
                <a href="<?= route('world_cup.hub') ?>" class="<?= active(['/world-cup', '/tournaments/world-cup-2026']) ?>">🏆 <?= __('event.world_cup.nav') ?></a>
            <?php endif; ?>
        </div>
    </div>

    <div class="mobile-nav__accordion mobile-nav__accordion--featured<?= $mobileTalentHubOpen ? ' is-open' : '' ?>" data-mobile-accordion>
        <button type="button" class="mobile-nav__accordion-btn" data-mobile-accordion-btn aria-expanded="<?= $mobileTalentHubOpen ? 'true' : 'false' ?>" aria-controls="mobile-nav-talent-hub">
            <?= __('nav.section_talent_hub') ?> <span class="mobile-nav__chevron" aria-hidden="true">▾</span>
        </button>
        <div class="mobile-nav__accordion-panel" id="mobile-nav-talent-hub" data-mobile-accordion-panel<?= $mobileTalentHubOpen ? '' : ' hidden' ?>>
            <a href="<?= route('trainers.index') ?>" class="<?= active('/trainers') ?>"><?= __('nav.trainers') ?></a>
            <a href="<?= route('only_talents.index') ?>" class="<?= active('/only-talents') ?>"><?= __('nav.scouts') ?></a>
            <a href="<?= route('club.recommendations') ?>" class="<?= active('/clubs') ?>"><?= __('nav.clubs') ?></a>
            <a href="<?= route('register') ?>?role=player" class="mobile-nav__subgroup <?= active('/register') ?>"><?= __('nav.talent_hub_get_started') ?></a>
            <a href="<?= route('register') ?>?role=trainer" class="mobile-nav__sub-link <?= active('/trainer') ?>"><?= __('nav.for_trainers') ?></a>
            <a href="<?= route('register') ?>?role=scout" class="mobile-nav__sub-link <?= active('/scout') ?>"><?= __('nav.for_scouts') ?></a>
            <a href="<?= route('club.recommendations') ?>" class="mobile-nav__sub-link <?= active('/clubs/recommendations') ?>"><?= __('nav.club_recommendations') ?></a>
        </div>
    </div>

    <div class="mobile-nav__accordion<?= $mobileMoreOpen ? ' is-open' : '' ?>" data-mobile-accordion>
        <button type="button" class="mobile-nav__accordion-btn" data-mobile-accordion-btn aria-expanded="<?= $mobileMoreOpen ? 'true' : 'false' ?>" aria-controls="mobile-nav-more">
            <?= __('nav.section_more') ?> <span class="mobile-nav__chevron" aria-hidden="true">▾</span>
        </button>
        <div class="mobile-nav__accordion-panel" id="mobile-nav-more" data-mobile-accordion-panel<?= $mobileMoreOpen ? '' : ' hidden' ?>>
            <a href="<?= $refereexUrl ?>" class="<?= active(['/refereex-ai', '/refereex-ai.html']) ?>">RefereeX AI</a>
            <a href="<?= route('news') ?>" class="<?= active('/news') ?>"><?= __('nav.news') ?></a>
            <a href="<?= route('transfers') ?>" class="<?= active('/transfers') ?>"><?= __('nav.transfers') ?></a>
            <a href="<?= route('fitpass') ?>" class="<?= active('/fit-pass') ?>"><?= __('nav.fitpass') ?></a>
        </div>
    </div>
    <?php if (Auth::check()): ?>
        <?php if (Auth::isAdmin()): ?>
            <a href="<?= route('admin.dashboard') ?>"><?= __('nav.admin') ?></a>
        <?php elseif (Auth::can('crm.access')): ?>
            <a href="<?= route('admin.crm.dashboard') ?>"><?= __('nav.crm') ?></a>
        <?php elseif (Auth::can('pro_coaches.dashboard')): ?>
            <a href="<?= route('pro_coaches.dashboard') ?>"><?= __('nav.pro_coaches') ?></a>
        <?php elseif (Auth::can('scout.dashboard')): ?>
            <a href="<?= route('scout.dashboard') ?>"><?= __('nav.scout_dashboard') ?></a>
            <?php if (Auth::can('scout.manage_players')): ?>
                <a href="<?= route('scout.players.create') ?>"><?= __('nav.add_player') ?></a>
            <?php endif; ?>
        <?php elseif (Auth::can('club.dashboard')): ?>
            <a href="<?= route('club.dashboard') ?>"><?= __('nav.dashboard') ?></a>
        <?php elseif (Auth::can('trainer.dashboard')): ?>
            <a href="<?= route('trainer.dashboard') ?>"><?= __('nav.trainer_dashboard') ?></a>
        <?php endif; ?>
        <?php if (Auth::can('provider.panel')): ?>
            <a href="<?= route('providers.panel') ?>"><?= __('nav.provider_panel') ?></a>
        <?php endif; ?>
        <?php if (Auth::can('marketplace.sell')): ?>
            <a href="<?= route('vendor.dashboard') ?>"><?= __('nav.vendor_dashboard') ?></a>
        <?php endif; ?>
        <form method="post" action="<?= route('logout') ?>" style="margin:0">
            <?= csrf_field() ?>
            <button class="btn btn-primary btn-block" type="submit"><?= __('nav.logout') ?></button>
        </form>
    <?php else: ?>
        <a href="<?= route('login') ?>"><?= __('nav.login') ?></a>
        <a href="<?= route('register') ?>" class="btn btn-primary btn-block"><?= __('nav.join') ?></a>
    <?php endif; ?>
    <div class="flex gap-1 mt-2">
        <?php foreach ($locales as $code => $l): ?>
            <a href="<?= e(lang_switch_url($code)) ?>" class="chip <?= $code === $cur ? 'chip--green' : '' ?>"><?= e(strtoupper($code)) ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<?= \App\Core\View::partial('partials.kooora-nav') ?>
