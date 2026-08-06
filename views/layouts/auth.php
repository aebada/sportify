<?php
$pageTitle = isset($title) ? $title . ' · Sportify' : 'Sportify';
$homeHref = function_exists('home_url') ? home_url() : '/';
$logoPng = function_exists('asset') ? asset('img/logo.png') : '/assets/img/logo.png';
$webpFile = \App\Core\App::config('paths.root') . '/public/assets/img/logo.webp';
$logoWebp = is_file($webpFile) && function_exists('asset') ? asset('img/logo.webp') : '';
?>
<!DOCTYPE html>
<html lang="<?= e(locale()) ?>" dir="<?= e(\App\Core\Lang::dir()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= asset('img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
      /* auth-brand-topleft-v1 — inline so Hostinger CDN immutable app.css cannot hide it */
      .auth-body { position: relative; }
      .auth-brand {
        position: absolute;
        top: 20px;
        inset-inline-start: 24px;
        z-index: 5;
        display: inline-flex;
        align-items: center;
        line-height: 0;
      }
      .auth-brand .brand-logo,
      .auth-brand .brand-logo-img {
        height: 36px;
        width: auto;
        display: block;
        object-fit: contain;
        filter: drop-shadow(0 1px 2px rgba(0,0,0,.35));
        -webkit-filter: drop-shadow(0 1px 2px rgba(0,0,0,.35));
      }
      @media (min-width: 981px) {
        .auth-brand { display: none; }
      }
      @media (max-width: 980px) {
        .auth-main { padding-top: 72px !important; }
      }
      /* google-btn-v1 — inline: Hostinger CDN may serve immutable stale app.css */
      .google-btn {
        position: relative;
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 0.875rem 1.25rem;
        border: 1px solid var(--border-2, #d0d7de);
        border-radius: 10px;
        background: #fff;
        color: #1f2328;
        font-size: 0.9375rem;
        font-weight: 600;
        font-family: inherit;
        text-decoration: none;
        box-sizing: border-box;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
      }
      .google-btn:hover:not(.google-btn--soon):not([aria-disabled="true"]) {
        border-color: #dadce0;
        box-shadow: 0 1px 3px rgba(60, 64, 67, 0.2);
      }
      .google-btn--soon { cursor: not-allowed; opacity: 0.92; }
      .google-btn--soon:hover { border-color: var(--border-2, #d0d7de); box-shadow: none; }
      .google-btn__icon { flex-shrink: 0; display: block; }
      .google-btn .social-badge {
        position: static;
        margin-inline-start: 0.35rem;
        font-size: 0.62rem;
      }
    </style>
</head>
<body class="auth-body">
<!-- deploy-marker:20260720-auth-brand-topleft-v1 -->
<a href="<?= e($homeHref) ?>" class="auth-brand brand" aria-label="<?= e(__('nav.home_aria')) ?>">
    <!-- brand-asset:logo-png-stadium-v6 -->
    <?php if ($logoWebp !== ''): ?>
        <picture>
            <source type="image/webp" srcset="<?= e($logoWebp) ?>">
            <img src="<?= e($logoPng) ?>" alt="Sportify" class="brand-logo brand-logo-img" width="136" height="36" decoding="async" fetchpriority="high">
        </picture>
    <?php else: ?>
        <img src="<?= e($logoPng) ?>" alt="Sportify" class="brand-logo brand-logo-img" width="136" height="36" decoding="async" fetchpriority="high">
    <?php endif; ?>
</a>
<div class="auth-wrap">
    <aside class="auth-side">
        <a href="<?= e($homeHref) ?>" class="brand auth-side__brand" aria-label="<?= e(__('nav.home_aria')) ?>">
            <?php if ($logoWebp !== ''): ?>
                <picture>
                    <source type="image/webp" srcset="<?= e($logoWebp) ?>">
                    <img src="<?= e($logoPng) ?>" alt="Sportify" class="brand-logo brand-logo-img" width="150" height="42" decoding="async" fetchpriority="high">
                </picture>
            <?php else: ?>
                <img src="<?= e($logoPng) ?>" alt="Sportify" class="brand-logo brand-logo-img" width="150" height="42" decoding="async" fetchpriority="high">
            <?php endif; ?>
        </a>
        <div>
            <span class="eyebrow">⚽ <?= __('hero.eyebrow') ?></span>
            <h2 class="mt-2"><?= __('hero.title_a') ?> <span class="text-green"><?= __('hero.title_b') ?></span></h2>
            <p class="quote mt-3"><?= __('auth.quote') ?></p>
            <p class="qmeta"><?= __('auth.quote_meta') ?></p>
        </div>
        <div class="hero__stats">
            <div class="hero__stat"><strong>5K+</strong><span><?= __('hero.stat_players') ?></span></div>
            <div class="hero__stat"><strong>480+</strong><span><?= __('hero.stat_clubs') ?></span></div>
        </div>
    </aside>
    <div class="auth-main">
        <div class="auth-card">
            <?= \App\Core\View::partial('partials.alerts') ?>
            <?= $content ?>
        </div>
    </div>
</div>
<div class="toast-stack" data-toasts aria-live="polite"></div>
<script>
(function () {
  function socialToast(msg) {
    var stack = document.querySelector('[data-toasts]');
    if (!stack) { window.alert(msg); return; }
    var t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML = '<span class="ic">ℹ</span><span>' + msg + '</span>';
    stack.appendChild(t);
    setTimeout(function () { t.classList.add('out'); setTimeout(function () { t.remove(); }, 300); }, 3200);
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-social-auth] [data-social]');
    if (!btn) return;
    var authUrl = btn.getAttribute('data-auth-url');
    if (authUrl) {
      if (window.__sportifySocialReady) return;
      e.preventDefault();
      window.location.href = authUrl;
      return;
    }
    if (window.__sportifySocialReady) return;
    e.preventDefault();
    var provider = btn.getAttribute('data-social') || 'social';
    var wrap = btn.closest('[data-social-auth]');
    var msg = wrap && wrap.getAttribute('data-msg-' + provider);
    if (!msg) {
      var label = provider.charAt(0).toUpperCase() + provider.slice(1);
      msg = label + ' login coming soon';
    }
    socialToast(msg);
    btn.classList.add('social-btn--pulse');
    setTimeout(function () { btn.classList.remove('social-btn--pulse'); }, 450);
  }, true);
})();
</script>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
