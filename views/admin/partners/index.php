<?php
/** @var array $partners @var array $filters @var array $stats @var string $mail_from @var bool $mail_configured */
?>
<div class="admin-topbar">
    <div>
        <h1 style="margin:0">Potential Partners</h1>
        <span class="muted small"><?= (int) ($stats['total'] ?? 0) ?> partners · <?= (int) ($stats['withEmail'] ?? 0) ?> with email · <?= (int) ($stats['verified'] ?? 0) ?> verified</span>
    </div>
    <div class="flex gap-1" style="flex-wrap:wrap">
        <form method="post" action="<?= route('admin.partners.import') ?>" style="display:inline">
            <?= \App\Core\Csrf::field() ?>
            <button class="btn btn-ghost btn-sm" type="submit">Import seeds</button>
        </form>
        <form method="post" action="<?= route('admin.partners.invite_all') ?>" style="display:inline" onsubmit="return confirm('Queue/invite verified partners as official Sportify partners from <?= e($mail_from) ?>?');">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="verified_only" value="1">
            <input type="hidden" name="limit" value="100">
            <button class="btn btn-primary btn-sm" type="submit">Invite all (queue)</button>
        </form>
        <form method="post" action="<?= route('admin.partners.invite_all') ?>" style="display:inline" onsubmit="return confirm('DRY RUN only — no emails sent.');">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="dry_run" value="1">
            <input type="hidden" name="verified_only" value="1">
            <button class="btn btn-ghost btn-sm" type="submit">Dry-run invite</button>
        </form>
        <?php if ($mail_configured): ?>
        <form method="post" action="<?= route('admin.partners.invite_all') ?>" style="display:inline" onsubmit="return confirm('SEND real emails from <?= e($mail_from) ?> to verified partners? This cannot be undone.');">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="send" value="1">
            <input type="hidden" name="verified_only" value="1">
            <input type="hidden" name="limit" value="25">
            <button class="btn btn-ghost btn-sm" type="submit" style="color:#b91c1c">Send verified (batch 25)</button>
        </form>
        <?php endif; ?>
        <a href="<?= route('admin.crm.dashboard') ?>" class="btn btn-ghost btn-sm">← CRM</a>
    </div>
</div>

<div class="panel mb-3" style="padding:12px 16px">
    <p class="muted small" style="margin:0">
        From-address: <strong><?= e($mail_from) ?></strong> ·
        Mail configured: <strong><?= $mail_configured ? 'yes' : 'no (invites queue until MAIL_* set)' ?></strong>
    </p>
    <?php if (!empty($stats['byType'])): ?>
        <p class="muted small" style="margin:8px 0 0">
            <?php foreach ($stats['byType'] as $t => $c): ?>
                <span style="margin-right:10px"><?= e($t) ?>: <?= (int) $c ?></span>
            <?php endforeach; ?>
        </p>
    <?php endif; ?>
</div>

<form class="panel crm-filters mb-3" method="get" action="<?= route('admin.partners') ?>">
    <div class="filter-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px">
        <input class="form-control" type="search" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Search name, email, league…">
        <select class="form-control" name="type">
            <option value="">All types</option>
            <?php foreach (\App\Models\PartnerLeadRepository::TYPES as $t): ?>
                <option value="<?= $t ?>" <?= ($filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" name="invite_status">
            <option value="">All invite statuses</option>
            <?php foreach (\App\Models\PartnerLeadRepository::INVITE_STATUSES as $s): ?>
                <option value="<?= $s ?>" <?= ($filters['invite_status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" name="email_confidence">
            <option value="">All confidence</option>
            <?php foreach (\App\Models\PartnerLeadRepository::EMAIL_CONFIDENCE as $s): ?>
                <option value="<?= $s ?>" <?= ($filters['email_confidence'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" name="has_email">
            <option value="">Email any</option>
            <option value="1" <?= ($filters['has_email'] ?? null) === 1 || ($filters['has_email'] ?? '') === '1' ? 'selected' : '' ?>>Has email</option>
            <option value="0" <?= ($filters['has_email'] ?? null) === 0 || ($filters['has_email'] ?? '') === '0' ? 'selected' : '' ?>>No email</option>
        </select>
        <button class="btn btn-primary" type="submit">Filter</button>
    </div>
</form>

<div class="panel" style="overflow:auto">
    <table class="table" style="width:100%;border-collapse:collapse">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Country</th>
                <th>League</th>
                <th>Email</th>
                <th>Confidence</th>
                <th>Invite</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$partners): ?>
            <tr><td colspan="8" class="muted">No partners yet. Click <strong>Import seeds</strong> or run <code>php sportify seed-partners</code>.</td></tr>
        <?php endif; ?>
        <?php foreach ($partners as $p): ?>
            <tr>
                <td>
                    <a href="<?= route('admin.partners.show', ['id' => $p['id']]) ?>"><?= e($p['name']) ?></a>
                    <?php if (!empty($p['website'])): ?>
                        <div class="muted small"><a href="<?= e($p['website']) ?>" target="_blank" rel="noopener">site</a></div>
                    <?php endif; ?>
                </td>
                <td><?= e($p['type']) ?><?= !empty($p['subtype']) ? ' · ' . e($p['subtype']) : '' ?></td>
                <td><?= e($p['country'] ?? '') ?></td>
                <td><?= e($p['league'] ?? '') ?></td>
                <td><?= e($p['email'] ?? '—') ?></td>
                <td><?= e($p['email_confidence'] ?? '') ?></td>
                <td><?= e($p['invite_status'] ?? '') ?></td>
                <td><a class="btn btn-ghost btn-sm" href="<?= route('admin.partners.edit', ['id' => $p['id']]) ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
