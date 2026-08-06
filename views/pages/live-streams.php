<?php
/** RefereeX bridge — view loads from disk on hotfix PHP paths (OPcache-safe). */
if (!empty($_GET['refereex']) || ($_GET['view'] ?? '') === 'refereex') {
    header('X-Sportify-RefereeX: live-streams-view-bridge-20260714');
    echo '<link rel="stylesheet" href="/assets/css/refereex-ai.css">';
    include __DIR__ . '/refereex-ai/index.php';
    echo '<script src="/assets/js/refereex-ai.js" defer></script>';
    return;
}
/** @var array $categories */ ?>
<section class="section live-streams-page">
    <div class="container">
        <header class="section-head" data-reveal>
            <span class="eyebrow live-streams-page__eyebrow">
                <span class="live-pill__dot" aria-hidden="true"></span>
                <?= __('live_streams.eyebrow') ?>
            </span>
            <h1><?= __('live_streams.title') ?></h1>
            <p><?= __('live_streams.sub') ?></p>
        </header>

        <div class="live-streams-disclaimer card mb-4" role="note">
            <div class="card__body">
                <p class="mb-0 muted small"><?= __('live_streams.disclaimer') ?></p>
            </div>
        </div>

        <?php require __DIR__ . '/../partials/livehd7-matches.php'; ?>

        <?php foreach ($categories as $category): ?>
            <section class="live-streams-group mb-4" data-reveal>
                <header class="live-streams-group__head">
                    <span class="live-streams-group__icon" aria-hidden="true"><?= e($category['icon'] ?? '📺') ?></span>
                    <h2><?= e(__($category['label_key'] ?? '')) ?></h2>
                </header>

                <div class="grid grid-3 live-streams-grid">
                    <?php foreach ($category['streams'] ?? [] as $stream): ?>
                        <a href="<?= e($stream['url'] ?? '#') ?>"
                           class="card live-stream-card"
                           target="_blank"
                           rel="noopener noreferrer">
                            <div class="card__body live-stream-card__body">
                                <div class="live-stream-card__top">
                                    <span class="live-stream-card__sport-icon" aria-hidden="true"><?= e($stream['icon'] ?? '📺') ?></span>
                                    <span class="chip chip--sm chip--green live-stream-card__badge">
                                        <span class="live-pill__dot" aria-hidden="true"></span>
                                        <?= __('live_streams.free') ?>
                                    </span>
                                </div>
                                <h3 class="live-stream-card__title"><?= e(__($stream['label_key'] ?? '')) ?></h3>
                                <span class="live-stream-card__cta"><?= __('live_streams.watch') ?> →</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</section>
