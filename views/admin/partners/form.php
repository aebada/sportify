<?php /** @var array $partner */ ?>
<div class="admin-topbar">
    <div><h1 style="margin:0">Edit partner</h1></div>
    <a class="btn btn-ghost btn-sm" href="<?= route('admin.partners.show', ['id' => $partner['id']]) ?>">← Back</a>
</div>
<form class="panel" method="post" action="<?= route('admin.partners.update', ['id' => $partner['id']]) ?>">
    <?= \App\Core\Csrf::field() ?>
    <div class="form-grid" style="display:grid;gap:12px;max-width:720px">
        <label>Name <input class="form-control" name="name" required value="<?= e($partner['name']) ?>"></label>
        <label>Type
            <select class="form-control" name="type">
                <?php foreach (\App\Models\PartnerLeadRepository::TYPES as $t): ?>
                    <option value="<?= $t ?>" <?= $partner['type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Subtype <input class="form-control" name="subtype" value="<?= e($partner['subtype'] ?? '') ?>"></label>
        <label>Country <input class="form-control" name="country" value="<?= e($partner['country'] ?? '') ?>"></label>
        <label>City <input class="form-control" name="city" value="<?= e($partner['city'] ?? '') ?>"></label>
        <label>League <input class="form-control" name="league" value="<?= e($partner['league'] ?? '') ?>"></label>
        <label>Website <input class="form-control" name="website" value="<?= e($partner['website'] ?? '') ?>"></label>
        <label>Email <input class="form-control" name="email" type="email" value="<?= e($partner['email'] ?? '') ?>"></label>
        <label>Email confidence
            <select class="form-control" name="email_confidence">
                <?php foreach (\App\Models\PartnerLeadRepository::EMAIL_CONFIDENCE as $s): ?>
                    <option value="<?= $s ?>" <?= ($partner['email_confidence'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Source URL <input class="form-control" name="source_url" value="<?= e($partner['source_url'] ?? '') ?>"></label>
        <label>Invite status
            <select class="form-control" name="invite_status">
                <?php foreach (\App\Models\PartnerLeadRepository::INVITE_STATUSES as $s): ?>
                    <option value="<?= $s ?>" <?= ($partner['invite_status'] ?? '') === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Tags (comma) <input class="form-control" name="tags" value="<?= e(implode(', ', $partner['tags_list'] ?? [])) ?>"></label>
        <label>Notes <textarea class="form-control" name="notes" rows="4"><?= e($partner['notes'] ?? '') ?></textarea></label>
        <button class="btn btn-primary" type="submit">Save</button>
    </div>
</form>
