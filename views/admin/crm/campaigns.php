<?php /** @var array $campaigns */ ?>
<div class="admin-topbar">
    <div>
        <h1 style="margin:0"><?= __('crm.campaigns') ?></h1>
    </div>
    <div class="flex gap-1">
        <a href="<?= route('admin.crm.campaigns.new') ?>" class="btn btn-primary btn-sm"><?= __('crm.new_campaign') ?></a>
        <a href="<?= route('admin.crm.dashboard') ?>" class="btn btn-ghost btn-sm">← CRM</a>
    </div>
</div>

<div class="panel">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th><?= __('crm.col.name') ?></th>
                    <th><?= __('crm.col.type') ?></th>
                    <th><?= __('crm.col.status') ?></th>
                    <th><?= __('crm.col.dates') ?></th>
                    <th><?= __('crm.leads') ?></th>
                    <th>UTM</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$campaigns): ?>
                <tr><td colspan="6" class="muted center"><?= __('crm.no_campaigns') ?></td></tr>
            <?php endif; ?>
            <?php foreach ($campaigns as $c): ?>
                <tr>
                    <td><a href="<?= route('admin.crm.campaigns.edit', ['id' => $c['id']]) ?>"><b><?= e($c['name']) ?></b></a></td>
                    <td><?= e(__('crm.campaign_type.' . $c['type'])) ?></td>
                    <td><span class="chip chip--sm"><?= e($c['status']) ?></span></td>
                    <td class="muted small"><?= e(($c['start_date'] ?? '—') . ' → ' . ($c['end_date'] ?? '—')) ?></td>
                    <td><?= (int) ($c['contact_count'] ?? 0) ?></td>
                    <td class="muted small"><?= e(trim(($c['utm_source'] ?? '') . '/' . ($c['utm_medium'] ?? '') . '/' . ($c['utm_campaign'] ?? ''), '/')) ?: '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
