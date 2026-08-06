<section class="section home-section" data-reveal>
    <div class="container">
        <div class="home-refereex-promo">
            <div>
                <span class="home-refereex-promo__badge">✨ <?= __('refereex.promo.badge') ?></span>
                <h2>RefereeX AI</h2>
                <p><?= __('refereex.promo.sub') ?></p>
                <div class="flex gap-1 wrap mt-3">
                    <a href="<?= route('refereex_ai') ?>" class="btn btn-primary"><?= __('refereex.promo.cta') ?></a>
                    <a href="<?= route('refereex_ai') ?>#rx-dashboard" class="btn btn-ghost"><?= __('refereex.cta.watch') ?: 'Watch overview' ?></a>
                </div>
            </div>
            <div class="home-refereex-promo__visual" aria-hidden="true">🤖</div>
        </div>
    </div>
</section>
