<?php
use App\Core\View;
/** @var array $rec */
$r = $rec;
$p = $r['player'] ?? [];
$slug = (string) ($p['slug'] ?? $r['player_slug'] ?? '');
$name = (string) ($r['player_name'] ?? $p['name'] ?? '');
?>
<article class="ai-rec">
    <div class="ai-rec__head">
        <?= View::partial('partials.player-avatar', ['player' => $p, 'name' => $name, 'size' => 54, 'class' => 'ai-rec__avatar']) ?>
        <div>
            <div class="name"><?= e($name) ?></div>
            <div class="pos"><?= e($r['position']) ?> · <?= e($r['availability']) ?></div>
        </div>
        <div class="ai-rec__fit">
            <b class="<?= fit_class((int) $r['fit']) ?>"><?= (int) $r['fit'] ?></b>
            <span><?= __('ai.fit_score') ?></span>
        </div>
    </div>
    <div>
        <span class="ai-badge">◆ <?= __('ai.why') ?> · <?= e($r['confidence']) ?></span>
        <ul class="ai-rec__reasons mt-1">
            <?php foreach (array_slice($r['reasons'], 0, 3) as $reason): ?>
                <li><?= e($reason) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="ai-rec__actions">
        <?php if ($slug !== ''): ?>
            <a href="<?= route('players.show', ['slug' => $slug]) ?>" class="btn btn-ghost btn-sm"><?= __('player.view_profile') ?></a>
            <button class="btn btn-ghost btn-sm" type="button"
                    data-toggle="shortlist"
                    data-id="<?= e($slug) ?>"
                    data-name="<?= e($name) ?>">★ <span data-label><?= __('ai.shortlist') ?></span></button>
            <a href="<?= route('players.show', ['slug' => $slug]) ?>#contact" class="btn btn-primary btn-sm"><?= __('ai.contact') ?></a>
        <?php else: ?>
            <button class="btn btn-dark btn-sm" type="button" disabled>★ <?= __('ai.shortlist') ?></button>
            <button class="btn btn-primary btn-sm" type="button" disabled><?= __('ai.contact') ?></button>
        <?php endif; ?>
    </div>
</article>
