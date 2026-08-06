<?php
use App\Support\Svg;
use App\Services\SocialAuthService;
use App\Services\SocialAuthSettings;

$oauthContext = $oauthContext ?? 'login';
$oauthRole = $oauthRole ?? 'player';
$dbReady = $dbReady ?? true;
$next = $next ?? '';

$buildAuthUrl = static function (string $provider) use ($oauthContext, $oauthRole, $next): string {
    $params = ['from' => $oauthContext];
    if ($oauthContext === 'register') {
        $params['role'] = $oauthRole;
    }
    if ($next !== '') {
        $params['next'] = $next;
        $params['redirect'] = $next;
    } elseif (\App\Support\OnlyTalentsContext::isActive()) {
        $return = \App\Support\OnlyTalentsContext::currentReturnTarget();
        $params['next'] = $return;
        $params['redirect'] = $return;
    }
    $path = route('auth.' . $provider) . '?' . http_build_query($params);
    if (\App\Support\OnlyTalentsContext::isActive()) {
        return rtrim(\App\Support\OnlyTalentsContext::mainBaseUrl(), '/') . $path;
    }
    return $path;
};

$allProviders = [
    'google' => [
        'label' => 'Google',
        'icon' => static fn (int $s) => Svg::brandGoogle($s),
        'ready' => $dbReady && SocialAuthService::isConfigured('google'),
        'auth_url' => ($dbReady && SocialAuthService::isConfigured('google')) ? $buildAuthUrl('google') : '',
    ],
    'facebook' => [
        'label' => 'Facebook',
        'icon' => static fn (int $s) => Svg::brandFacebook($s),
        'ready' => $dbReady && SocialAuthService::isConfigured('facebook'),
        'auth_url' => ($dbReady && SocialAuthService::isConfigured('facebook')) ? $buildAuthUrl('facebook') : '',
    ],
    'linkedin' => [
        'label' => 'LinkedIn',
        'icon' => static fn (int $s) => Svg::brandLinkedIn($s),
        'ready' => $dbReady && SocialAuthService::isConfigured('linkedin'),
        'auth_url' => ($dbReady && SocialAuthService::isConfigured('linkedin')) ? $buildAuthUrl('linkedin') : '',
    ],
];

$providers = [];
foreach ($allProviders as $key => $provider) {
    if (!SocialAuthSettings::isVisible($key)) {
        continue;
    }
    $providers[$key] = $provider;
}

if ($providers === []) {
    return;
}

$google = $providers['google'] ?? null;
$otherProviders = $providers;
unset($otherProviders['google']);

$visibleLabels = array_column($providers, 'label');
$hintKey = count($visibleLabels) === 1 && ($visibleLabels[0] ?? '') === 'Google'
    ? 'auth.social.hint_google_only'
    : 'auth.social.hint';
?>
<div data-social-auth data-oauth-context="<?= e($oauthContext) ?>"
     data-msg-google="<?= e(__('auth.social.google_soon')) ?>"
     data-msg-facebook="<?= e(__('auth.social.facebook_soon')) ?>"
     data-msg-linkedin="<?= e(__('auth.social.linkedin_soon')) ?>">
    <?php if ($google !== null): ?>
        <?= \App\Core\View::partial('partials.google-auth-button', [
            'authUrl' => $google['auth_url'],
            'ready'   => $google['ready'],
        ]) ?>
    <?php endif; ?>

    <?php if ($otherProviders !== []): ?>
        <div class="social-row mt-2">
            <?php foreach ($otherProviders as $key => $provider): ?>
                <?php
                $soon = !$provider['ready'];
                $aria = $provider['label'] . ($soon ? ' — ' . __('auth.social.coming_soon') : '');
                ?>
                <button
                    class="social-btn<?= $soon ? ' social-btn--soon' : ' social-btn--live' ?><?= $key === 'linkedin' ? ' social-btn--linkedin' : '' ?>"
                    type="button"
                    data-social="<?= e($key) ?>"
                    <?= $provider['ready'] ? 'data-auth-url="' . e($provider['auth_url']) . '"' : 'data-coming-soon="1"' ?>
                    aria-label="<?= e($aria) ?>"
                    <?= $soon ? 'aria-disabled="true"' : '' ?>>
                    <?= $provider['icon'](20) ?>
                    <span class="hide-sm"><?= e($provider['label']) ?></span>
                    <?php if ($soon): ?>
                        <span class="social-badge"><?= __('auth.social.coming_soon') ?></span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<p class="social-hint muted small center"><?= __($hintKey) ?></p>
