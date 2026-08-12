<?php
/** @var array $pipeline @var array $funnel @var int $total @var float $conversion @var array $recent @var array $campaigns */
?>
<div class="admin-topbar">
    <div>
        <h1 style="margin:0"><?= __('crm.title') ?></h1>
        <span class="muted small"><?= __('crm.subtitle') ?></span>
    </div>
    <div class="flex gap-1">
        <a href="<?= route('admin.crm.contacts.new') ?>" class="btn btn-primary btn-sm"><?= __('crm.new_contact') ?></a>
        <a href="<?= route('admin.crm.campaigns.new') ?>" class="btn btn-ghost btn-sm"><?= __('crm.new_campaign') ?></a>
        <a href="<?= route('admin.referrals') ?>" class="btn btn-ghost btn-sm"><?= __('referrals.admin_title') ?></a>
    </div>
</div>

<div class="stat-grid mb-3">
    <?php foreach ($pipeline as $status => $count): ?>
        <div class="stat-box <?= $status === 'converted' ? 'accent' : '' ?>">
            <b><?= (int) $count ?></b>
            <span><?= e(__('crm.status.' . $status)) ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid-2 gap-2 mb-3">
    <div class="panel">
        <h3 style="margin-top:0"><?= __('crm.conversion_funnel') ?></h3>
        <p class="muted small"><?= __('crm.total_leads') ?>: <b><?= (int) $total ?></b> · <?= __('crm.conversion_rate') ?>: <b class="text-green"><?= e((string) $conversion) ?>%</b></p>
        <div class="funnel-bars">
            <?php foreach ($funnel as $step => $count): ?>
                <div class="funnel-row">
                    <span class="funnel-label"><?= e(__('crm.status.' . $step)) ?></span>
                    <div class="funnel-bar-wrap">
                        <div class="funnel-bar" style="width:<?= $total > 0 ? max(4, round(($count / $total) * 100)) : 0 ?>%"></div>
                    </div>
                    <span class="funnel-val"><?= (int) $count ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel">
        <div class="flex between items-center mb-2">
            <h3 style="margin:0"><?= __('crm.active_campaigns') ?></h3>
            <a href="<?= route('admin.crm.campaigns') ?>" class="btn btn-ghost btn-sm"><?= __('crm.view_all') ?></a>
        </div>
        <?php if (!$campaigns): ?>
            <p class="muted"><?= __('crm.no_campaigns') ?></p>
        <?php else: ?>
            <ul class="crm-list">
                <?php foreach ($campaigns as $c): ?>
                    <li>
                        <a href="<?= route('admin.crm.campaigns.edit', ['id' => $c['id']]) ?>"><b><?= e($c['name']) ?></b></a>
                        <span class="chip chip--sm"><?= e(__('crm.campaign_type.' . $c['type'])) ?></span>
                        <span class="muted small"><?= e($c['status']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="flex between items-center mb-2">
        <h3 style="margin:0"><?= __('crm.recent_leads') ?></h3>
        <a href="<?= route('admin.crm.contacts') ?>" class="btn btn-ghost btn-sm"><?= __('crm.view_all') ?></a>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th><?= __('crm.col.name') ?></th>
                    <th><?= __('crm.col.email') ?></th>
                    <th><?= __('crm.col.source') ?></th>
                    <th><?= __('crm.col.status') ?></th>
                    <th><?= __('crm.col.created') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recent as $c): ?>
                <tr>
                    <td><a href="<?= route('admin.crm.contacts.show', ['id' => $c['id']]) ?>"><b><?= e($c['name']) ?></b></a></td>
                    <td><?= e($c['email'] ?? '—') ?></td>
                    <td><?= e(__('crm.source.' . ($c['source'] ?? 'website'))) ?></td>
                    <td><span class="chip chip--sm"><?= e(__('crm.status.' . $c['status'])) ?></span></td>
                    <td class="muted small"><?= e(substr($c['created_at'] ?? '', 0, 10)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
