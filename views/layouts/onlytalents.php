<?php
/** @var string $content */
use App\Core\Csrf;
$pageTitle = isset($title) ? $title . ' · OnlyTalents' : 'OnlyTalents · ' . __('ot.home.title');
$metaDesc = $metaDesc ?? __('ot.home.sub');
$tiktokLanding = !empty($tiktokLanding);
$bodyClass = 'ot-body' . ($tiktokLanding ? ' ot-tiktok-page ot-landing-shorts' : '');
?>
<!DOCTYPE html>
<html lang="<?= e(locale()) ?>" dir="<?= e(\App\Core\Lang::dir()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="theme-color" content="#050a12">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://img.youtube.com">
    <link rel="preconnect" href="https://www.youtube.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= asset('img/favicon.png') ?>" type="image/png">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/onlytalents.css') ?>">
    <link rel="stylesheet" href="<?= \App\Core\Router::base() ?>/assets/css/onlytalents-immersive-v5.css?v=20260707">
    <?php if ($tiktokLanding): ?>
    <style id="ot-shorts-immersive-v5">
      html:has(body.ot-landing-shorts),body.ot-landing-shorts{overflow:hidden!important;overflow-x:hidden!important;width:100%;max-width:100%;height:100%;background:#000!important}
      body.ot-landing-shorts,body.ot-landing-shorts .ot-main--shorts,body.ot-landing-shorts .ot-tiktok--landing{background:#000!important}
      body.ot-landing-shorts .ot-main--shorts,body.ot-landing-shorts #main-content{overflow:hidden;width:100%;max-width:100%;padding:0;margin:0}
      body.ot-landing-shorts .ot-shorts-nav{overflow:hidden;max-width:100%}
      body.ot-landing-shorts *{scrollbar-width:none;-ms-overflow-style:none}
      body.ot-landing-shorts *::-webkit-scrollbar{display:none;width:0;height:0}
      body.ot-landing-shorts .ot-tiktok--landing{--ot-shorts-top:0;--ot-shorts-bottom:0;height:100dvh;min-height:100dvh;overflow:hidden}
      body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__scroll,body.ot-landing-shorts .ot-tiktok__scroll{position:fixed;inset:0;width:100%;max-width:none!important;height:100dvh;min-height:100dvh;max-height:100dvh;margin:0!important;flex:none!important;transform:none;border:0!important;border-radius:0!important;border-top:0!important;background:#000;overflow-x:hidden!important;overflow-y:scroll;scroll-snap-type:y mandatory;scroll-behavior:auto;overscroll-behavior:none;-webkit-overflow-scrolling:touch;touch-action:pan-y;box-shadow:none!important}
      body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__slide{width:100%;max-width:none!important;margin:0!important;height:100dvh;min-height:100dvh;max-height:100dvh;flex:0 0 100dvh;scroll-snap-align:start;scroll-snap-stop:always}
      body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__chrome--minimal{position:fixed;top:max(48px,calc(40px + env(safe-area-inset-top,0px)));left:0;right:0;z-index:56;padding:0 10px;pointer-events:none;background:linear-gradient(180deg,rgba(0,0,0,.55) 0%,transparent 100%)}
      body.ot-landing-shorts .ot-tiktok__toolbar--immersive{display:flex;align-items:center;gap:6px;flex-wrap:nowrap;overflow:hidden;pointer-events:auto;padding:2px 0 8px}
      body.ot-landing-shorts .ot-filters--immersive{display:flex;gap:5px;flex:1 1 auto;min-width:0;overflow:hidden;flex-wrap:nowrap}
      body.ot-landing-shorts .ot-filters--immersive .ot-filter-pill,body.ot-landing-shorts .ot-filter-pill--categories,body.ot-landing-shorts .ot-filter-pill--category-clear{flex:0 0 auto;font-size:.68rem;padding:5px 10px;white-space:nowrap}
      body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__counter{flex:0 0 auto;margin-left:auto;font-size:.68rem;color:rgba(255,255,255,.72);background:rgba(0,0,0,.35);padding:4px 8px;border-radius:999px}
      body.ot-landing-shorts .ot-shorts-bottom{z-index:60;background:linear-gradient(0deg,rgba(0,0,0,.88) 0%,rgba(0,0,0,.45) 70%,transparent 100%);border-top:0}
      body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__meta{bottom:calc(62px + env(safe-area-inset-bottom,0px))}
      body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__side{bottom:calc(88px + env(safe-area-inset-bottom,0px))}
      body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__dots,body.ot-landing-shorts .ot-category-chips,body.ot-landing-shorts .ot-category-chips--scroll{display:none!important}
      body.ot-landing-shorts .ot-tiktok--landing [data-ot-slide-media],body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__media iframe,body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__media video,body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__poster{pointer-events:none!important;touch-action:pan-y}
      body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__play,body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__side,body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__meta{pointer-events:auto!important}
      @media (min-width:900px){
        body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__scroll{left:50%;right:auto;width:min(420px,100%);max-width:min(420px,100%)!important;transform:translateX(-50%);box-shadow:none}
        body.ot-landing-shorts .ot-shorts-bottom,body.ot-landing-shorts .ot-tiktok--landing .ot-tiktok__chrome--minimal{left:50%;right:auto;width:min(420px,100%);max-width:min(420px,100%);transform:translateX(-50%)}
      }
    </style>
    <?php endif; ?>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main-content"><?= __('a11y.skip_to_content') ?></a>
<?php if (!$tiktokLanding): ?>
    <?= \App\Core\View::partial('partials.ot-nav') ?>
<?php endif; ?>

<main id="main-content" class="ot-main<?= $tiktokLanding ? ' ot-main--shorts' : '' ?>">
    <?= \App\Core\View::partial('partials.alerts') ?>
    <?= $content ?>
</main>

<?php if (!$tiktokLanding): ?>
<footer class="ot-footer">
    <div class="container ot-footer__inner">
        <div>
            <strong class="ot-brand-text">OnlyTalents</strong>
            <p class="muted small"><?= __('ot.footer.tagline') ?></p>
        </div>
        <div class="ot-footer__links">
            <a href="<?= ot_route('ot.discover') ?>"><?= __('ot.nav.discover') ?></a>
            <a href="<?= route('only_talents.index') ?>"><?= __('only_talents.title') ?></a>
            <a href="<?= \App\Support\OnlyTalentsContext::mainBaseUrl() ?>"><?= __('ot.footer.main_site') ?></a>
        </div>
        <div class="ot-footer__lang">
            <a href="<?= lang_switch_url('en') ?>">EN</a>
            <a href="<?= lang_switch_url('de') ?>">DE</a>
            <a href="<?= lang_switch_url('ar') ?>">AR</a>
        </div>
    </div>
</footer>
<?php endif; ?>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
