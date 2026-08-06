<?php
/** @var array $roles */
$errors = flash('errors') ?: [];
$selectedRole = old('role') ?: ($_GET['role'] ?? $_GET['as'] ?? 'player');
if (!isset($roles[$selectedRole])) {
    $selectedRole = 'player';
}
$dbReady = $dbReady ?? true;
$next = $next ?? '';
?>
<h1><?= __('auth.register_title') ?></h1>
<p class="muted"><?= __('auth.register_sub') ?></p>
<?php if (!$dbReady): ?>
    <div class="demo-banner mt-2" role="status"><?= __('auth.db_not_ready') ?></div>
    <p class="muted small mt-1"><?= __('auth.db_setup_hint') ?></p>
<?php endif; ?>
<?php if (!empty($pendingPlan)): ?>
    <div class="demo-banner mt-2">Selected plan: <b><?= e(ucfirst($pendingPlan)) ?> FIT-Pass</b> — you'll complete payment after signup.</div>
<?php endif; ?>

<?= \App\Core\View::partial('partials.social-auth-20260720-visibility', ['oauthContext' => 'register', 'oauthRole' => $selectedRole, 'dbReady' => $dbReady, 'next' => $next]) ?>
<div class="divider"><?= __('auth.or_continue') ?></div>

<form method="post" action="<?= route('register.post') ?>">
    <?= csrf_field() ?>
    <?php if ($next !== ''): ?>
        <input type="hidden" name="next" value="<?= e($next) ?>">
        <input type="hidden" name="redirect" value="<?= e($next) ?>">
    <?php endif; ?>
    <?php if (!empty($referralCode)): ?>
        <input type="hidden" name="ref" value="<?= e($referralCode) ?>">
        <div class="demo-banner mb-2"><?= __('referrals.register_banner', ['code' => $referralCode]) ?></div>
    <?php endif; ?>

    <div class="form-group">
        <label><?= __('auth.role') ?></label>
        <div class="role-grid">
            <?php foreach ($roles as $key => $r): ?>
                <?php $descKey = 'register.role_desc.' . $key; $desc = __($descKey); ?>
                <div class="role-opt">
                    <input type="radio" name="role" id="role-<?= $key ?>" value="<?= $key ?>" <?= $selectedRole === $key ? 'checked' : '' ?>>
                    <label for="role-<?= $key ?>">
                        <span class="ic"><?= \App\Support\Svg::roleIcon($key) ?></span>
                        <span class="role-opt__label"><?= e($r['label']) ?></span>
                        <?php if ($desc !== $descKey): ?><span class="role-opt__desc"><?= e($desc) ?></span><?php endif; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($errors['role'])): ?><div class="field-error"><?= e($errors['role']) ?></div><?php endif; ?>
    </div>

    <div class="form-group">
        <label><?= __('auth.name') ?></label>
        <input class="form-control" type="text" name="name" value="<?= e(old('name')) ?>" required>
        <?php if (!empty($errors['name'])): ?><div class="field-error"><?= e($errors['name']) ?></div><?php endif; ?>
    </div>
    <div class="form-group">
        <label><?= __('auth.email') ?></label>
        <input class="form-control" type="email" name="email" value="<?= e(old('email')) ?>" required>
        <?php if (!empty($errors['email'])): ?><div class="field-error"><?= e($errors['email']) ?></div><?php endif; ?>
    </div>
    <div class="form-group">
        <label><?= __('auth.password') ?></label>
        <input class="form-control" type="password" name="password" required>
        <?php if (!empty($errors['password'])): ?><div class="field-error"><?= e($errors['password']) ?></div><?php endif; ?>
    </div>
    <div class="form-group">
        <label><?= __('auth.confirm') ?></label>
        <input class="form-control" type="password" name="password_confirm" required>
        <?php if (!empty($errors['password_confirm'])): ?><div class="field-error"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
    </div>

    <div class="form-group" id="tb-player-fields" style="display:none">
        <label class="checkbox-opt">
            <input type="checkbox" name="seeking_club" value="1" <?= old('seeking_club') ? 'checked' : '' ?>>
            <?= __('transfer_bureau.register.seeking') ?>
        </label>
        <div class="form-row mt-2">
            <div class="form-group">
                <label><?= __('transfer_bureau.form.positions') ?></label>
                <select class="form-control" name="position">
                    <option value="">—</option>
                    <?php foreach (\App\Services\TransferBureauService::config('positions', []) as $code => $label): ?>
                        <?php if ($code === 'TRAINER') continue; ?>
                        <option value="<?= e($code) ?>" <?= old('position') === $code ? 'selected' : '' ?>><?= e($code) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= __('transfer_bureau.form.city') ?></label>
                <select class="form-control" name="city">
                    <option value="">—</option>
                    <?php foreach (array_keys(\App\Services\TransferBureauService::config('cities', [])) as $c): ?>
                        <option value="<?= e($c) ?>" <?= old('city') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group" id="tb-club-fields" style="display:none">
        <div class="form-row">
            <div class="form-group">
                <label><?= __('transfer_bureau.form.team_name') ?></label>
                <input class="form-control" type="text" name="club_team_name" value="<?= e(old('club_team_name')) ?>">
            </div>
            <div class="form-group">
                <label><?= __('transfer_bureau.form.team_level') ?></label>
                <select class="form-control" name="club_team_level">
                    <?php foreach (\App\Services\TransferBureauService::config('team_levels', []) as $key => $tl): ?>
                        <option value="<?= e($key) ?>" <?= old('club_team_level') === $key ? 'selected' : '' ?>><?= e($tl['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= __('transfer_bureau.form.city') ?></label>
                <select class="form-control" name="city">
                    <option value="">—</option>
                    <?php foreach (array_keys(\App\Services\TransferBureauService::config('cities', [])) as $c): ?>
                        <option value="<?= e($c) ?>" <?= old('city') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-group" id="tb-trainer-fields" style="display:none">
        <div class="form-row">
            <div class="form-group">
                <label><?= __('trainer.register.license') ?></label>
                <input class="form-control" type="text" name="coaching_license" value="<?= e(old('coaching_license')) ?>" placeholder="<?= e(__('trainer.register.license_placeholder')) ?>">
            </div>
            <div class="form-group">
                <label><?= __('trainer.register.years') ?></label>
                <input class="form-control" type="number" name="years_experience" min="0" max="60" value="<?= e(old('years_experience')) ?>">
            </div>
        </div>
        <div class="form-group">
            <label><?= __('trainer.register.specialties') ?></label>
            <div class="flex gap-2" style="flex-wrap:wrap">
                <?php foreach (\App\Models\TrainerProfileRepository::specialtyOptions() as $key => $label): ?>
                    <label class="checkbox-opt">
                        <input type="checkbox" name="specialties[]" value="<?= e($key) ?>" <?= in_array($key, (array) old('specialties', []), true) ? 'checked' : '' ?>>
                        <?= e($label) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label><?= __('trainer.register.current_club') ?></label>
                <input class="form-control" type="text" name="current_club" value="<?= e(old('current_club')) ?>" placeholder="<?= e(__('trainer.register.current_club_placeholder')) ?>">
            </div>
            <div class="form-group">
                <label><?= __('transfer_bureau.form.city') ?></label>
                <select class="form-control" name="city">
                    <option value="">—</option>
                    <?php foreach (array_keys(\App\Services\TransferBureauService::config('cities', [])) as $c): ?>
                        <option value="<?= e($c) ?>" <?= old('city') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-block btn-lg" type="submit"<?= $dbReady ? '' : ' disabled aria-disabled="true"' ?>><?= __('auth.register_btn') ?></button>
</form>
<script>
(function() {
    function updateAuthRoleUrls(role) {
        document.querySelectorAll('[data-auth-url]').forEach(function(btn) {
            var authUrl = btn.getAttribute('data-auth-url');
            if (!authUrl) return;
            if (/([?&])role=[^&]*/.test(authUrl)) {
                authUrl = authUrl.replace(/([?&])role=[^&]*/, '$1role=' + encodeURIComponent(role));
            } else {
                authUrl += (authUrl.indexOf('?') > -1 ? '&' : '?') + 'role=' + encodeURIComponent(role);
            }
            btn.setAttribute('data-auth-url', authUrl);
        });
    }

    function syncRoleInUrl(role) {
        if (!window.history.replaceState) return;
        var url = new URL(window.location.href);
        url.searchParams.set('role', role);
        window.history.replaceState({}, '', url.toString());
    }

    function toggleTbFields() {
        var role = document.querySelector('input[name="role"]:checked');
        var player = document.getElementById('tb-player-fields');
        var club = document.getElementById('tb-club-fields');
        var trainer = document.getElementById('tb-trainer-fields');
        if (!role || !player || !club) return;
        player.style.display = role.value === 'player' ? '' : 'none';
        club.style.display = role.value === 'club' ? '' : 'none';
        if (trainer) trainer.style.display = role.value === 'trainer' ? '' : 'none';
        updateAuthRoleUrls(role.value);
        syncRoleInUrl(role.value);
    }
    document.querySelectorAll('input[name="role"]').forEach(function(r) {
        r.addEventListener('change', toggleTbFields);
    });
    toggleTbFields();
})();
</script>

<?php
$loginUrl = route('login');
if ($next !== '') {
    $loginUrl .= '?next=' . rawurlencode($next) . '&redirect=' . rawurlencode($next);
}
?>
<p class="center muted mt-3"><?= __('auth.have_account') ?> <a class="text-green" href="<?= e($loginUrl) ?>"><?= __('auth.signin') ?></a></p>
<?php unset($_SESSION['_old']); ?>
