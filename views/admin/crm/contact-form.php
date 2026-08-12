<?php
/** @var array|null $contact @var array $campaigns @var array $assignees */
$isEdit = $contact !== null;
$tags = $contact['tags_list'] ?? [];
?>
<div class="admin-topbar">
    <div>
        <h1 style="margin:0"><?= $isEdit ? __('crm.edit_contact') : __('crm.new_contact') ?></h1>
    </div>
    <a href="<?= route('admin.crm.contacts') ?>" class="btn btn-ghost btn-sm">← <?= __('crm.contacts') ?></a>
</div>

<div class="panel" style="max-width:720px">
    <form method="post" action="<?= $isEdit ? route('admin.crm.contacts.update', ['id' => $contact['id']]) : route('admin.crm.contacts.store') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label><?= __('crm.col.name') ?> *</label>
            <input class="form-control" type="text" name="name" value="<?= e($contact['name'] ?? old('name')) ?>" required>
        </div>
        <div class="grid-2 gap-2">
            <div class="form-group">
                <label><?= __('crm.col.email') ?></label>
                <input class="form-control" type="email" name="email" value="<?= e($contact['email'] ?? old('email')) ?>">
            </div>
            <div class="form-group">
                <label><?= __('crm.col.phone') ?></label>
                <input class="form-control" type="text" name="phone" value="<?= e($contact['phone'] ?? old('phone')) ?>">
            </div>
        </div>
        <div class="grid-2 gap-2">
            <div class="form-group">
                <label><?= __('crm.col.source') ?></label>
                <select class="form-control" name="source">
                    <?php foreach (\App\Models\CrmContactRepository::SOURCES as $s): ?>
                        <option value="<?= $s ?>" <?= ($contact['source'] ?? 'website') === $s ? 'selected' : '' ?>><?= e(__('crm.source.' . $s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= __('crm.col.status') ?></label>
                <select class="form-control" name="status">
                    <?php foreach (\App\Models\CrmContactRepository::STATUSES as $s): ?>
                        <option value="<?= $s ?>" <?= ($contact['status'] ?? 'new') === $s ? 'selected' : '' ?>><?= e(__('crm.status.' . $s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="grid-2 gap-2">
            <div class="form-group">
                <label><?= __('crm.col.campaign') ?></label>
                <select class="form-control" name="campaign_id">
                    <option value="">—</option>
                    <?php foreach ($campaigns as $camp): ?>
                        <option value="<?= (int) $camp['id'] ?>" <?= (string) ($contact['campaign_id'] ?? '') === (string) $camp['id'] ? 'selected' : '' ?>><?= e($camp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= __('crm.assigned_to') ?></label>
                <select class="form-control" name="assigned_to">
                    <option value="">—</option>
                    <?php foreach ($assignees as $u): ?>
                        <option value="<?= (int) $u['id'] ?>" <?= (string) ($contact['assigned_to'] ?? '') === (string) $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?> (<?= e($u['role']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label><?= __('referrals.code') ?></label>
            <input class="form-control" type="text" name="referral_code" value="<?= e($contact['referral_code'] ?? '') ?>" placeholder="SPORTIFY-...">
        </div>
        <div class="form-group">
            <label><?= __('crm.col.tags') ?></label>
            <div class="tag-checkboxes">
                <?php foreach (\App\Models\CrmContactRepository::TAGS as $tag): ?>
                    <label class="chip chip--sm">
                        <input type="checkbox" name="tags[]" value="<?= $tag ?>" <?= in_array($tag, $tags, true) ? 'checked' : '' ?>>
                        <?= e(__('crm.tag.' . $tag)) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label><?= __('crm.notes') ?></label>
            <textarea class="form-control" name="notes" rows="4"><?= e($contact['notes'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit"><?= __('crm.save') ?></button>
    </form>
</div>
