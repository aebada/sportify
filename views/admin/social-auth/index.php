<h1><?= e(__('admin.social_auth.title')) ?></h1>
<p class="muted"><?= __('admin.social_auth.sub') ?></p>

<div class="card" style="padding:16px;max-width:640px">
    <form method="post" action="<?= route('admin.social_auth.save') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <p class="muted small" style="margin:0 0 12px"><?= __('admin.social_auth.google_note') ?></p>
            <label class="field-check">
                <input type="checkbox" checked disabled>
                <span>
                    <strong>Google</strong>
                    <?php if (!empty($configured['google'])): ?>
                        <span class="text-green small"> — <?= __('admin.social_auth.configured') ?></span>
                    <?php else: ?>
                        <span class="muted small"> — <?= __('admin.social_auth.not_configured') ?></span>
                    <?php endif; ?>
                </span>
            </label>
        </div>

        <div class="form-group">
            <label class="field-check">
                <input type="checkbox" name="show_facebook" value="1" <?= !empty($visibility['facebook']) ? 'checked' : '' ?>>
                <span>
                    <strong>Facebook</strong>
                    <?php if (!empty($configured['facebook'])): ?>
                        <span class="text-green small"> — <?= __('admin.social_auth.configured') ?></span>
                    <?php else: ?>
                        <span class="muted small"> — <?= __('admin.social_auth.not_configured') ?></span>
                    <?php endif; ?>
                </span>
            </label>
            <p class="muted small"><?= __('admin.social_auth.facebook_help') ?></p>
        </div>

        <div class="form-group">
            <label class="field-check">
                <input type="checkbox" name="show_linkedin" value="1" <?= !empty($visibility['linkedin']) ? 'checked' : '' ?>>
                <span>
                    <strong>LinkedIn</strong>
                    <?php if (!empty($configured['linkedin'])): ?>
                        <span class="text-green small"> — <?= __('admin.social_auth.configured') ?></span>
                    <?php else: ?>
                        <span class="muted small"> — <?= __('admin.social_auth.not_configured') ?></span>
                    <?php endif; ?>
                </span>
            </label>
            <p class="muted small"><?= __('admin.social_auth.linkedin_help') ?></p>
        </div>

        <button type="submit" class="btn btn-primary"><?= __('admin.social_auth.save') ?></button>
    </form>
</div>

<p class="muted small"><?= __('admin.social_auth.default_note') ?></p>
