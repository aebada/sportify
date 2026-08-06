<?php
declare(strict_types=1);
/**
 * Build fully static RefereeX HTML (no PHP tags) with Sportify header/footer shell.
 * deploy-marker: refereex-static-full-20260714
 */
$root = dirname(__DIR__);
$marker = 'refereex-static-full-20260714';

$messages = require $root . '/lang/en/refereex-messages.php';

function rx__(string $key, array $messages): string
{
    return $messages[$key] ?? $key;
}

function rx_e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rx_route(string $name): string
{
    return match ($name) {
        'contact' => '/contact',
        'fitpass' => '/fit-pass',
        'home' => '/',
        default => '/',
    };
}

$contactUrl = rx_route('contact');

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        global $messages;
        $text = rx__($key, $messages);
        foreach ($replace as $k => $v) {
            $text = str_replace(':' . $k, (string) $v, $text);
        }
        return $text;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return rx_e($value);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        return rx_route($name);
    }
}

ob_start();
include $root . '/views/pages/refereex-ai/index.php';
$body = ob_get_clean();

if (str_contains($body, '<?')) {
    fwrite(STDERR, "ERROR: PHP tags remain in rendered body\n");
    exit(1);
}

$shellUrl = 'https://sportifyplus.de/fit-pass';
$shell = @file_get_contents($shellUrl);
if ($shell === false) {
    fwrite(STDERR, "WARN: Could not fetch shell from {$shellUrl}\n");
    $shell = '';
}

$head = '';
$preMain = '';
$footer = '';
$scripts = '';

if ($shell !== '') {
    if (preg_match('/<head[^>]*>.*?<\/head>/is', $shell, $m)) {
        $head = $m[0];
        $head = preg_replace('/<title>.*?<\/title>/is', '<title>RefereeX AI — Intelligent Officiating Platform · Sportify</title>', $head);
        $head = preg_replace('/<meta name="description" content="[^"]*"/', '<meta name="description" content="RefereeX AI delivers real-time officiating intelligence, multi-camera fusion, and digital twin technology for modern football."', $head);
        if (!str_contains($head, 'refereex-ai.css')) {
            $head = str_replace('</head>', '    <link rel="stylesheet" href="/assets/css/refereex-ai.css">' . "\n</head>", $head);
        }
        $head = str_replace('</head>', '    <link rel="canonical" href="https://sportifyplus.de/refereex-ai">' . "\n</head>", $head);
    }
    if (preg_match('/<body[^>]*>.*?<main id="main-content"/is', $shell, $m, PREG_OFFSET_CAPTURE)) {
        $preMain = substr($shell, 0, $m[0][1] + strlen($m[0][0]));
        $preMain = preg_replace('/<main id="main-content"/', '<main id="main-content"', $preMain);
    }
    if (preg_match('/<footer[^>]*>.*?<\/footer>/is', $shell, $m)) {
        $footer = $m[0];
    }
    if (preg_match('/<script[^>]*src="[^"]*app\.js[^"]*"[^>]*><\/script>/', $shell, $m)) {
        $scripts = $m[0];
    }
}

if ($head === '') {
    $head = <<<'HTML'
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RefereeX AI — Intelligent Officiating Platform · Sportify</title>
    <meta name="description" content="RefereeX AI delivers real-time officiating intelligence, multi-camera fusion, and digital twin technology for modern football.">
    <link rel="canonical" href="https://sportifyplus.de/refereex-ai">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/refereex-ai.css">
    <link rel="icon" href="/assets/img/favicon.png" type="image/png">
</head>
HTML;
}

$navRx = '/refereex-ai';
if ($preMain !== '') {
    $preMain = preg_replace('#href="/refereex-ai\.html"#', 'href="' . $navRx . '"', $preMain);
    $preMain = preg_replace('#href="/refereex-ai"#', 'href="' . $navRx . '" class="active"', $preMain, 1);
}

$html = "<!DOCTYPE html>\n<html lang=\"en\" dir=\"ltr\">\n";
$html .= $head . "\n";
$html .= $preMain !== '' ? $preMain : "<body>\n<a class=\"skip-link\" href=\"#main-content\">Skip to content</a>\n<main id=\"main-content\">\n";
$html .= "\n<!-- deploy-marker:{$marker} -->\n";
$html .= $body;
$html .= "\n</main>\n";
$html .= $footer !== '' ? $footer . "\n" : '';
$html .= '<script src="/assets/js/refereex-ai.js" defer></script>' . "\n";
$html .= $scripts !== '' ? $scripts . "\n" : '';
$html .= <<<'HTML'
<script>
(function(){
  var obs='IntersectionObserver'in window?new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('is-revealed');obs.unobserve(e.target);}});},{threshold:0.12}):null;
  document.querySelectorAll('[data-reveal]').forEach(function(el){if(obs)obs.observe(el);else el.classList.add('is-revealed');});
  document.querySelectorAll('[data-count]').forEach(function(el){
    var target=parseFloat(el.getAttribute('data-count')||'0'),suffix=el.getAttribute('data-suffix')||'';
    var start=0,dur=1200,t0=null;
    function step(ts){if(!t0)t0=ts;var p=Math.min((ts-t0)/dur,1);el.textContent=(start+(target-start)*p).toFixed(target%1?1:0)+suffix;if(p<1)requestAnimationFrame(step);}
    requestAnimationFrame(step);
  });
})();
</script>
</body>
</html>
HTML;

foreach (['public/refereex-ai.html', 'public/refereex-ai-static.html', 'public/live-sports.html'] as $rel) {
    $out = $root . '/' . $rel;
    file_put_contents($out, $html);
    echo "Wrote {$rel} (" . number_format(strlen($html)) . " bytes)\n";
}
