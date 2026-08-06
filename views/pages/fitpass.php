<?php
/** View bridge — renders RefereeX when ?view=refereex (OPcache-safe, loads from disk). */
if (($_GET['view'] ?? '') === 'refereex') {
    header('X-Sportify-RefereeX: fitpass-view-bridge-20260714');
    $rxView = dirname(__DIR__) . '/pages/refereex-ai/index.php';
    if (is_readable($rxView)) {
        $contactUrl = route('contact');
        ob_start();
        include $rxView;
        echo ob_get_clean();
        return;
    }
}
/** @var array $services @var array $plans @var array $comparison */
use App\Core\Auth;
use App\Models\FitPassRepository;
$icons = ['dumbbell'=>'🏋️','building'=>'🏟️','shield'=>'🛡️','play'=>'💻','heart'=>'❤️','chart'=>'📈'];
?>
<section class="hero" style="padding:60px 0 40px">
    <div class="container">
        <div class="section-head center" style="margin:0 auto">
            <span class="eyebrow">🎟️ FIT-Pass</span>
            <h1 style="font-size:clamp(2rem,4.5vw,3rem)"><?= __('fitpass.home_title') ?></h1>
            <p><?= __('fitpass.home_sub') ?></p>
            <?php if (Auth::check() && !empty($subscription)): ?>
                <p class="mt-2"><span class="chip chip--green"><?= __('fitpass.active_badge', ['plan' => e($subscription['plan_name'] ?? ucfirst($subscription['tier']))]) ?></span>
                <a href="<?= route('billing.account') ?>" class="chip"><?= __('fitpass.manage_billing') ?></a></p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section section--tight">
    <div class="container">
        <div class="grid grid-3">
            <?php foreach ($services as $s): ?>
                <div class="card card__body">
                    <div class="step__num" style="border-radius:13px"><?= $icons[$s['icon']] ?? '⚽' ?></div>
                    <h3><?= e($s['title']) ?></h3>
                    <p class="mb-0"><?= e($s['desc']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" style="background:var(--bg-2)">
    <div class="container">
        <div class="section-head center">
            <span class="eyebrow"><?= __('fitpass.plans.eyebrow') ?></span>
            <h2><?= __('fitpass.plans.title') ?></h2>
            <p><?= __('fitpass.plans.sub') ?></p>
            <?php if (Auth::check() && \App\Core\Auth::can('wellness.access')): ?>
                <p class="mt-2"><a href="<?= route('wellness.dashboard') ?>" class="btn btn-primary btn-sm"><?= __('nav.wellness') ?> →</a></p>
            <?php endif; ?>
            <?php if (!empty($membershipHub)): ?>
                <p class="mt-2"><a href="<?= e($membershipHub) ?>" class="btn btn-primary btn-sm"><?= __('nav.membership') ?> →</a></p>
            <?php endif; ?>
        </div>

        <div class="billing-toggle center mb-4" data-billing-toggle>
            <button type="button" class="billing-toggle__btn active" data-cycle="monthly"><?= __('billing.monthly') ?></button>
            <button type="button" class="billing-toggle__btn" data-cycle="yearly"><?= __('billing.yearly') ?> <span class="chip chip--green chip--sm"><?= __('billing.save_yearly') ?></span></button>
        </div>

        <div class="price-grid">
            <?php foreach ($plans as $plan): ?>
                <div class="card price-card <?= !empty($plan['popular']) ? 'popular' : '' ?>" data-plan-card data-tier="<?= e($plan['tier']) ?>">
                    <?php if (!empty($plan['popular'])): ?><span class="popular-flag"><?= __('fitpass.most_popular') ?></span><?php endif; ?>
                    <div class="flex between items-center">
                        <span class="tier-badge tier-<?= e($plan['tier']) ?>"><?= e(mb_substr($plan['name'],0,1)) ?></span>
                    </div>
                    <div>
                        <div class="tier-name"><?= e($plan['name']) ?></div>
                        <div class="tier-tag"><?= e($plan['tagline']) ?></div>
                    </div>
                    <div class="price" data-price-monthly>
                        <?= e($plan['price_monthly']) ?><span>/mo</span>
                    </div>
                    <div class="price hidden" data-price-yearly>
                        <?= e($plan['price_yearly']) ?><span>/yr</span>
                        <div class="price-sub muted small">≈ <?= e($plan['price_yearly_per_month']) ?>/mo · save <?= e($plan['yearly_savings']) ?></div>
                    </div>
                    <ul class="feature-list">
                        <?php foreach ($plan['features'] as $feat): ?>
                            <li><span class="tick">✓</span> <?= e($feat) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php
                    $checkoutUrl = Auth::check()
                        ? route('billing.checkout', ['tier' => $plan['tier']])
                        : route('register') . '?plan=' . urlencode($plan['tier']);
                    ?>
                    <a href="<?= e($checkoutUrl) ?>" class="btn <?= !empty($plan['popular']) ? 'btn-primary' : 'btn-ghost' ?> btn-block"<?php if (Auth::check()): ?> data-checkout-link="<?= e(route('billing.checkout', ['tier' => $plan['tier']])) ?>"<?php endif; ?>>
                        <?= __('fitpass.get_plan', ['plan' => e($plan['name'])]) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="panel mt-4 compare-table-wrap">
            <h3 class="center mb-3"><?= __('fitpass.compare_title') ?></h3>
            <div class="compare-table-scroll">
                <table class="compare-table">
                    <thead>
                        <tr>
                            <th><?= __('fitpass.compare.feature') ?></th>
                            <?php foreach ($plans as $plan): ?>
                                <th><?= e($plan['name']) ?><br><span class="muted small"><?= e($plan['price_monthly']) ?>/mo</span></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td><?= __('fitpass.compare.monthly') ?></td><?php foreach ($plans as $p): ?><td><?= e($p['price_monthly']) ?></td><?php endforeach; ?></tr>
                        <tr><td><?= __('fitpass.compare.yearly') ?></td><?php foreach ($plans as $p): ?><td><?= e($p['price_yearly']) ?></td><?php endforeach; ?></tr>
                        <?php foreach ($comparison as $row): ?>
                        <tr>
                            <td><?= e($row['feature']) ?></td>
                            <?php foreach ($plans as $p): ?>
                                <td><?= FitPassRepository::hasFeature($p['tier'], $row['tiers'] ?? []) ? '✓' : '—' ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="payment-icons center mt-4">
            <p class="muted small mb-2"><?= __('billing.payment_methods') ?></p>
            <div class="payment-icons__row">
                <span class="pay-icon">💳 Visa</span>
                <span class="pay-icon">💳 Mastercard</span>
                <span class="pay-icon">🅿️ PayPal</span>
                <span class="pay-icon"> Apple Pay</span>
                <span class="pay-icon">🔵 Google Pay</span>
                <span class="pay-icon">🏦 SEPA</span>
                <span class="pay-icon">🏛️ Bank transfer</span>
            </div>
        </div>
    </div>
</section>
