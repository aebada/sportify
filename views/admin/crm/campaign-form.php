<?php
/** @var array|null $campaign */
$isEdit = $campaign !== null;
?>
<div class="admin-topbar">
    <div>
        <h1 style="margin:0"><?= $isEdit ? __('crm.edit_campaign') : __('crm.new_campaign') ?></h1>
    </div>
    <a href="<?= route('admin.crm.campaigns') ?>" class="btn btn-ghost btn-sm">← <?= __('crm.campaigns') ?></a>
</div>

<div class="panel" style="max-width:720px">
    <form method="post" action="<?= $isEdit ? route('admin.crm.campaigns.update', ['id' => $campaign['id']]) : route('admin.crm.campaigns.store') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label><?= __('crm.col.name') ?> *</label>
            <input class="form-control" type="text" name="name" value="<?= e($campaign['name'] ?? '') ?>" required>
        </div>
        <div class="grid-2 gap-2">
            <div class="form-group">
                <label><?= __('crm.col.type') ?></label>
                <select class="form-control" name="type">
                    <?php foreach (\App\Models\CrmCampaignRepository::TYPES as $t): ?>
                        <option value="<?= $t ?>" <?= ($campaign['type'] ?? 'email') === $t ? 'selected' : '' ?>><?= e(__('crm.campaign_type.' . $t)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= __('crm.col.status') ?></label>
                <select class="form-control" name="status">
                    <?php foreach (\App\Models\CrmCampaignRepository::STATUSES as $s): ?>
                        <option value="<?= $s ?>" <?= ($campaign['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="grid-2 gap-2">
            <div class="form-group">
                <label><?= __('crm.start_date') ?></label>
                <input class="form-control" type="date" name="start_date" value="<?= e($campaign['start_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label><?= __('crm.end_date') ?></label>
                <input class="form-control" type="date" name="end_date" value="<?= e($campaign['end_date'] ?? '') ?>">
            </div>
        </div>
        <div class="grid-3 gap-2">
            <div class="form-group">
                <label>utm_source</label>
                <input class="form-control" type="text" name="utm_source" value="<?= e($campaign['utm_source'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>utm_medium</label>
                <input class="form-control" type="text" name="utm_medium" value="<?= e($campaign['utm_medium'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>utm_campaign</label>
                <input class="form-control" type="text" name="utm_campaign" value="<?= e($campaign['utm_campaign'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label><?= __('crm.budget') ?> (EUR)</label>
            <input class="form-control" type="number" step="0.01" min="0" name="budget" value="<?= isset($campaign['budget_cents']) ? e(number_format($campaign['budget_cents'] / 100, 2, '.', '')) : '' ?>">
        </div>
        <div class="form-group">
            <label><?= __('crm.notes') ?></label>
            <textarea class="form-control" name="notes" rows="3"><?= e($campaign['notes'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit"><?= __('crm.save') ?></button>
    </form>
</div>
