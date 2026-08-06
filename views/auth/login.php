<h1><?= __('auth.login_title') ?></h1>
<p class="muted"><?= __('auth.login_sub') ?></p>

<?php $next = $next ?? ''; ?>
<?= \App\Core\View::partial('partials.social-auth-20260720-visibility', ['oauthContext' => 'login', 'dbReady' => $dbReady ?? true, 'next' => $next]) ?>
<div class="divider"><?= __('auth.or_continue') ?></div>

    <form method="post" action="<?= route('login.post') ?>">
        <?= csrf_field() ?>
        <?php if ($next !== ''): ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">
            <input type="hidden" name="redirect" value="<?= e($next) ?>">
        <?php endif; ?>
        <div class="form-group">
            <label><?= __('auth.email') ?></label>
            <input class="form-control" type="email" name="email" value="<?= e(old('email')) ?>" required autofocus>
        </div>
        <div class="form-group">
            <label><?= __('auth.password') ?></label>
            <input class="form-control" type="password" name="password" required>
        </div>
        <div class="flex between items-center mb-2">
            <label class="field-check small"><input type="checkbox" name="remember"> <?= __('auth.remember') ?></label>
            <a class="text-green small" href="<?= route('password.request') ?>"><?= __('auth.forgot') ?></a>
        </div>
        <button class="btn btn-primary btn-block btn-lg" type="submit"><?= __('auth.login_btn') ?></button>
    </form>

    <?php
    $registerUrl = route('register');
    if ($next !== '') {
        $registerUrl .= '?next=' . rawurlencode($next) . '&redirect=' . rawurlencode($next);
    }
    ?>
    <p class="center muted mt-3"><?= __('auth.no_account') ?> <a class="text-green" href="<?= e($registerUrl) ?>"><?= __('auth.signup') ?></a></p>
<?php unset($_SESSION['_old']); ?>
