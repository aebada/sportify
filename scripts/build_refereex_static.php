<?php
declare(strict_types=1);

$base = dirname(__DIR__);
$css = file_get_contents($base . '/public/assets/css/refereex-ai.css');
$js = file_get_contents($base . '/public/assets/js/refereex-ai.js');
$contact = 'https://sportifyplus.de/contact';
$year = date('Y');

$nav = <<<HTML
<header class="rx-static-nav" role="banner">
  <div class="rx-wrap rx-static-nav__inner">
    <a href="https://sportifyplus.de/" class="rx-static-nav__logo">Sportify</a>
    <nav class="rx-static-nav__links" aria-label="Main">
      <a href="https://sportifyplus.de/">Home</a>
      <a href="https://sportifyplus.de/fit-pass">FIT-Pass</a>
      <a href="https://sportifyplus.de/live-sports">Live Sports</a>
      <a href="#rx-hero" aria-current="page">RefereeX AI</a>
      <a href="{$contact}">Contact</a>
    </nav>
  </div>
</header>
<style>
.rx-static-nav{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(5,10,18,.85);backdrop-filter:blur(12px);border-bottom:1px solid rgba(148,163,184,.14)}
.rx-static-nav__inner{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.25rem;max-width:1240px;margin:0 auto}
.rx-static-nav__logo{font-weight:800;color:#f8fafc;text-decoration:none;font-size:1.1rem}
.rx-static-nav__links{display:flex;flex-wrap:wrap;gap:1rem}
.rx-static-nav__links a{color:#94a3b8;text-decoration:none;font-size:.9rem;font-weight:500}
.rx-static-nav__links a:hover,.rx-static-nav__links a[aria-current=page]{color:#3b82f6}
body.refereex-page{padding-top:56px}
@media(max-width:640px){.rx-static-nav__links{gap:.65rem;font-size:.85rem}}
</style>
HTML;

ob_start();
include $base . '/scripts/refereex-static-body.php';
$body = ob_get_clean();

$html = <<<HTML
<!DOCTYPE html>
<!-- refereex-static-v1 -->
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RefereeX AI — Intelligent Officiating Platform · Sportify</title>
<meta name="description" content="RefereeX AI delivers real-time officiating intelligence for modern football.">
<meta name="deploy-marker" content="refereex-static-v1">
<link rel="canonical" href="https://sportifyplus.de/live-sports.html">
<style>
{$css}
</style>
</head>
<body class="refereex-page">
{$nav}
{$body}
<script>
{$js}
</script>
</body>
</html>
HTML;

$out = $base . '/public/refereex-ai-static.html';
file_put_contents($out, $html);
echo "Wrote {$out} (" . number_format(strlen($html)) . " bytes)\n";
