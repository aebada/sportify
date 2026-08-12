<?php
/** @var array $contact @var array $activities @var array $campaigns @var array $assignees */
?>
<div class="admin-topbar">
    <div>
        <h1 style="margin:0"><?= e($contact['name']) ?></h1>
        <span class="muted small"><?= e($contact['email'] ?? '') ?></span>
    </div>
    <div class="flex gap-1">
        <a href="<?= route('admin.crm.contacts.edit', ['id' => $contact['id']]) ?>" class="btn btn-ghost btn-sm"><?= __('crm.edit') ?></a>
        <a href="<?= route('admin.crm.contacts') ?>" class="btn btn-ghost btn-sm">← <?= __('crm.contacts') ?></a>
    </div>
</div>

<div class="grid-2 gap-2">
    <div class="panel">
        <h3 style="margin-top:0"><?= __('crm.contact_details') ?></h3>
        <dl class="detail-list">
            <dt><?= __('crm.col.status') ?></dt>
            <dd><span class="chip"><?= e(__('crm.status.' . $contact['status'])) ?></span></dd>
            <dt><?= __('crm.col.source') ?></dt>
            <dd><?= e(__('crm.source.' . ($contact['source'] ?? 'website'))) ?></dd>
            <dt><?= __('crm.col.phone') ?></dt>
            <dd><?= e($contact['phone'] ?? '—') ?></dd>
            <dt><?= __('crm.col.campaign') ?></dt>
            <dd><?= e($contact['campaign_name'] ?? '—') ?></dd>
            <dt><?= __('referrals.code') ?></dt>
            <dd><?= e($contact['referral_code'] ?? '—') ?></dd>
            <dt><?= __('crm.assigned_to') ?></dt>
            <dd><?= e($contact['assigned_name'] ?? '—') ?></dd>
            <dt><?= __('crm.linked_user') ?></dt>
            <dd><?= !empty($contact['user_id']) ? e($contact['linked_user_name'] ?? '#' . $contact['user_id']) : '—' ?></dd>
            <dt><?= __('crm.col.tags') ?></dt>
            <dd>
                <?php foreach ($contact['tags_list'] ?? [] as $tag): ?>
                    <span class="chip chip--sm chip--green"><?= e(__('crm.tag.' . $tag)) ?></span>
                <?php endforeach; ?>
                <?php if (empty($contact['tags_list'])): ?>—<?php endif; ?>
            </dd>
        </dl>
        <?php if (!empty($contact['notes'])): ?>
            <h4><?= __('crm.notes') ?></h4>
            <p class="muted"><?= nl2br(e($contact['notes'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h3 style="margin-top:0"><?= __('crm.add_activity') ?></h3>
        <form method="post" action="<?= route('admin.crm.contacts.activity', ['id' => $contact['id']]) ?>">
            <?= csrf_field() ?>
            <div class="form-group">
                <label><?= __('crm.activity_type') ?></label>
                <select class="form-control" name="type">
                    <?php foreach (\App\Models\CrmActivityRepository::TYPES as $t): ?>
                        <option value="<?= $t ?>"><?= e(__('crm.activity.' . $t)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= __('crm.activity_subject') ?></label>
                <input class="form-control" type="text" name="subject">
            </div>
            <div class="form-group">
                <label><?= __('crm.activity_body') ?></label>
                <textarea class="form-control" name="body" rows="3" required></textarea>
            </div>
            <button class="btn btn-primary btn-sm" type="submit"><?= __('crm.log_activity') ?></button>
        </form>
    </div>
</div>

<div class="panel mt-3">
    <h3 style="margin-top:0"><?= __('crm.timeline') ?></h3>
    <?php if (!$activities): ?>
        <p class="muted"><?= __('crm.no_activities') ?></p>
    <?php else: ?>
        <ul class="crm-timeline">
            <?php foreach ($activities as $a): ?>
                <li class="crm-timeline__item">
                    <div class="crm-timeline__meta">
                        <span class="chip chip--sm"><?= e(__('crm.activity.' . $a['type'])) ?></span>
                        <span class="muted small"><?= e($a['created_at']) ?></span>
                        <?php if (!empty($a['user_name'])): ?>
                            <span class="muted small">· <?= e($a['user_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($a['subject'])): ?><strong><?= e($a['subject']) ?></strong><?php endif; ?>
                    <?php if (!empty($a['body'])): ?><p class="muted"><?= nl2br(e($a['body'])) ?></p><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
