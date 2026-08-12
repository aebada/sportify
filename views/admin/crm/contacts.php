<?php
/** @var array $contacts @var array $filters @var array $campaigns */
$query = http_build_query(array_filter($filters));
?>
<div class="admin-topbar">
    <div>
        <h1 style="margin:0"><?= __('crm.contacts') ?></h1>
        <span class="muted small"><?= count($contacts) ?> <?= __('crm.leads') ?></span>
    </div>
    <div class="flex gap-1">
        <a href="<?= route('admin.crm.contacts.export') ?><?= $query ? '?' . e($query) : '' ?>" class="btn btn-ghost btn-sm"><?= __('crm.export_csv') ?></a>
        <a href="<?= route('admin.crm.contacts.new') ?>" class="btn btn-primary btn-sm"><?= __('crm.new_contact') ?></a>
        <a href="<?= route('admin.crm.dashboard') ?>" class="btn btn-ghost btn-sm">← CRM</a>
    </div>
</div>

<form class="panel crm-filters mb-3" method="get" action="<?= route('admin.crm.contacts') ?>">
    <div class="filter-grid">
        <input class="form-control" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="<?= e(__('crm.search_placeholder')) ?>">
        <select class="form-control" name="status">
            <option value=""><?= __('crm.all_statuses') ?></option>
            <?php foreach (\App\Models\CrmContactRepository::STATUSES as $s): ?>
                <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e(__('crm.status.' . $s)) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" name="source">
            <option value=""><?= __('crm.all_sources') ?></option>
            <?php foreach (\App\Models\CrmContactRepository::SOURCES as $s): ?>
                <option value="<?= $s ?>" <?= $filters['source'] === $s ? 'selected' : '' ?>><?= e(__('crm.source.' . $s)) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" name="campaign_id">
            <option value=""><?= __('crm.all_campaigns') ?></option>
            <?php foreach ($campaigns as $camp): ?>
                <option value="<?= (int) $camp['id'] ?>" <?= (string) $filters['campaign_id'] === (string) $camp['id'] ? 'selected' : '' ?>><?= e($camp['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary" type="submit"><?= __('crm.filter') ?></button>
    </div>
</form>

<div class="panel">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th><?= __('crm.col.name') ?></th>
                    <th><?= __('crm.col.email') ?></th>
                    <th><?= __('crm.col.source') ?></th>
                    <th><?= __('crm.col.status') ?></th>
                    <th><?= __('crm.col.campaign') ?></th>
                    <th><?= __('crm.col.tags') ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$contacts): ?>
                <tr><td colspan="6" class="muted center"><?= __('crm.no_contacts') ?></td></tr>
            <?php endif; ?>
            <?php foreach ($contacts as $c): ?>
                <tr>
                    <td><a href="<?= route('admin.crm.contacts.show', ['id' => $c['id']]) ?>"><b><?= e($c['name']) ?></b></a></td>
                    <td><?= e($c['email'] ?? '—') ?></td>
                    <td><?= e(__('crm.source.' . ($c['source'] ?? 'website'))) ?></td>
                    <td><span class="chip chip--sm"><?= e(__('crm.status.' . $c['status'])) ?></span></td>
                    <td><?= e($c['campaign_name'] ?? '—') ?></td>
                    <td>
                        <?php foreach ($c['tags_list'] ?? [] as $tag): ?>
                            <span class="chip chip--sm chip--green"><?= e(__('crm.tag.' . $tag)) ?></span>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
