<?php
use App\Core\Auth;
use App\Core\View;
/** @var array $feed @var string $filter @var array $filters @var string|null $lastSynced @var bool $syncedToday @var string $category */
use App\Models\TalentShortRepository;
$locales = \App\Core\Lang::locales();
$cur = locale();
$totalShorts = (int) ($feed['total'] ?? count($feed['items']));
$slideTotal = max($totalShorts, count($feed['items']));
$loadMoreUrl = ot_route('ot.shorts.load_more');
$likeTemplate = ot_route('ot.shorts.like', ['id' => '__ID__']);
$profileBase = ot_route('ot.profile', ['slug' => '']);
?>
<script>document.body.classList.add('ot-tiktok-page', 'ot-landing-shorts');</script>
<!-- deploy-marker:20260707-ot-immersive-v5 -->

<nav class="ot-shorts-nav" aria-label="OnlyTalents">
    <a href="<?= ot_route('ot.home') ?>" class="ot-shorts-nav__brand" aria-label="OnlyTalents">
        <span class="ot-shorts-nav__mark">OT</span>
    </a>
    <div class="ot-shorts-nav__lang ot-lang-switch">
        <button class="ot-lang-btn" type="button" data-lang-btn aria-haspopup="true" aria-label="<?= e(__('ot.filter.language')) ?>">
            <span><?= e(strtoupper($cur)) ?></span> ▾
        </button>
        <div class="ot-lang-menu" data-lang-menu>
            <?php foreach ($locales as $code => $l): ?>
                <a href="<?= e(lang_switch_url($code)) ?>" class="<?= $code === $cur ? 'active' : '' ?>">
                    <?= e($l['native']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="ot-shorts-nav__auth">
        <?php if (Auth::check()): ?>
            <a href="<?= ot_route('ot.profile.edit') ?>" class="ot-shorts-nav__avatar" title="<?= e(Auth::user()['name'] ?? '') ?>">
                <?= e(mb_substr((string) (Auth::user()['name'] ?? 'U'), 0, 1)) ?>
            </a>
        <?php else: ?>
            <a href="<?= ot_auth_url('/login') ?>" class="ot-shorts-nav__login"><?= __('nav.login') ?></a>
        <?php endif; ?>
    </div>
</nav>

<section class="ot-tiktok ot-tiktok--landing" data-ot-tiktok>
    <div class="ot-tiktok__chrome ot-tiktok__chrome--minimal">
        <div class="ot-tiktok__toolbar ot-tiktok__toolbar--immersive" id="shorts-feed">
            <div class="ot-filters ot-filters--immersive" role="tablist" aria-label="<?= e(__('only_talents.shorts_feed')) ?>">
                <?php foreach ($filters as $f): ?>
                    <?php
                    $filterQs = '?filter=' . urlencode($f);
                    if (!empty($category)) {
                        $filterQs .= '&category=' . urlencode($category);
                    }
                    ?>
                    <a href="<?= ot_route('ot.home') . $filterQs ?>#shorts-feed"
                       class="ot-filter-pill <?= $filter === $f ? 'is-active' : '' ?>">
                        <?= __('only_talents.filter.' . $f) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($category)): ?>
                <a href="<?= ot_route('ot.home') . '?filter=' . urlencode($filter) ?>#shorts-feed"
                   class="ot-filter-pill ot-filter-pill--category-clear"
                   title="<?= e(__('only_talents.filter.all')) ?>">
                    <?= e(TalentShortRepository::categoryLabel($category)) ?> ×
                </a>
            <?php else: ?>
                <a href="<?= ot_route('ot.categories') ?>" class="ot-filter-pill ot-filter-pill--categories">
                    <?= __('ot.nav.categories') ?>
                </a>
            <?php endif; ?>
            <div class="ot-tiktok__counter" data-ot-counter>
                <span data-ot-counter-current>1</span> / <span data-ot-counter-total><?= (int) $slideTotal ?><?= $slideTotal >= 500 ? '+' : '' ?></span>
                <?php if (!empty($syncedToday)): ?>
                    <span class="ot-tiktok__sync ot-tiktok__sync--today" title="<?= e(__('only_talents.last_sync', ['time' => $lastSynced ?? ''])) ?>">●</span>
                <?php elseif (!empty($lastSynced)): ?>
                    <span class="ot-tiktok__sync" title="<?= e(__('only_talents.last_sync', ['time' => $lastSynced])) ?>">○</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!$feed['items']): ?>
        <div class="ot-tiktok__empty panel center">
            <p class="mb-1 muted"><?= __('only_talents.empty') ?></p>
            <p class="small muted mb-0"><?= __('only_talents.empty_seed_hint') ?></p>
            <a href="<?= ot_route('ot.discover') ?>" class="ot-btn ot-btn--primary" style="margin-top:16px">
                <?= __('ot.home.cta_discover') ?>
            </a>
        </div>
    <?php else: ?>
        <div class="ot-tiktok__scroll"
             data-ot-feed
             data-ot-scroll
             data-filter="<?= e($filter) ?>"
             data-category="<?= e($category ?? '') ?>"
             data-page="<?= (int) $feed['page'] ?>"
             data-pages="<?= (int) $feed['pages'] ?>"
             data-total="<?= (int) $slideTotal ?>"
             data-load-more-url="<?= e($loadMoreUrl) ?>"
             data-like-template="<?= e($likeTemplate) ?>"
             data-profile-base="<?= e($profileBase) ?>"
             tabindex="0"
             aria-label="<?= e(__('only_talents.shorts_feed')) ?>">
            <?php foreach ($feed['items'] as $i => $short): ?>
                <?= View::partial('partials.ot-talent-short-slide', [
                    'short' => $short,
                    'index' => $i,
                    'total' => $slideTotal,
                ]) ?>
            <?php endforeach; ?>
        </div>

        <div class="ot-tiktok__hint" data-ot-nav-hint role="status">
            <span aria-hidden="true">↑↓</span>
            <?= __('only_talents.nav_hint') ?>
            <button type="button" class="ot-tiktok__hint-dismiss" data-ot-nav-hint-dismiss aria-label="<?= e(__('a11y.close')) ?>">×</button>
        </div>
    <?php endif; ?>
</section>

<nav class="ot-shorts-bottom" aria-label="OnlyTalents navigation">
    <a href="<?= ot_route('ot.home') ?>" class="is-active" aria-current="page">
        <span aria-hidden="true">▶</span>
        <?= __('ot.home.shorts') ?>
    </a>
    <a href="<?= ot_route('ot.discover') ?>">
        <span aria-hidden="true">⌕</span>
        <?= __('ot.nav.discover') ?>
    </a>
    <a href="<?= ot_route('ot.categories') ?>">
        <span aria-hidden="true">☰</span>
        <?= __('ot.nav.categories') ?>
    </a>
    <?php if (Auth::check()): ?>
        <a href="<?= ot_route('ot.profile.edit') ?>">
            <span aria-hidden="true">◎</span>
            <?= __('ot.profile.edit_title') ?>
        </a>
    <?php else: ?>
        <a href="<?= ot_auth_url('/login') ?>">
            <span aria-hidden="true">◎</span>
            <?= __('nav.login') ?>
        </a>
    <?php endif; ?>
</nav>
