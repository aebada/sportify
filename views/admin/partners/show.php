<?php /** @var array $partner */ ?>
<div class="admin-topbar">
    <div>
        <h1 style="margin:0"><?= e($partner['name']) ?></h1>
        <span class="muted small"><?= e($partner['type']) ?> · invite <?= e($partner['invite_status']) ?></span>
    </div>
    <div class="flex gap-1">
        <a class="btn btn-primary btn-sm" href="<?= route('admin.partners.edit', ['id' => $partner['id']]) ?>">Edit</a>
        <a class="btn btn-ghost btn-sm" href="<?= route('admin.partners') ?>">← Partners</a>
    </div>
</div>
<div class="panel">
    <dl style="display:grid;grid-template-columns:160px 1fr;gap:8px 16px">
        <dt class="muted">Email</dt><dd><?= e($partner['email'] ?: '—') ?> (<?= e($partner['email_confidence']) ?>)</dd>
        <dt class="muted">Website</dt><dd><?php if ($partner['website']): ?><a href="<?= e($partner['website']) ?>" target="_blank" rel="noopener"><?= e($partner['website']) ?></a><?php else: ?>—<?php endif; ?></dd>
        <dt class="muted">Source</dt><dd><?php if ($partner['source_url']): ?><a href="<?= e($partner['source_url']) ?>" target="_blank" rel="noopener"><?= e($partner['source_url']) ?></a><?php else: ?>—<?php endif; ?></dd>
        <dt class="muted">Country / city</dt><dd><?= e(trim(($partner['country'] ?? '') . ' ' . ($partner['city'] ?? ''))) ?></dd>
        <dt class="muted">League</dt><dd><?= e($partner['league'] ?: '—') ?></dd>
        <dt class="muted">Subtype</dt><dd><?= e($partner['subtype'] ?: '—') ?></dd>
        <dt class="muted">Tags</dt><dd><?= e(implode(', ', $partner['tags_list'] ?? [])) ?></dd>
        <dt class="muted">Notes</dt><dd><?= nl2br(e($partner['notes'] ?: '—')) ?></dd>
        <dt class="muted">Invited</dt><dd><?= e($partner['invited_at'] ?: '—') ?></dd>
        <dt class="muted">Accepted</dt><dd><?= e($partner['accepted_at'] ?: '—') ?></dd>
    </dl>
</div>
