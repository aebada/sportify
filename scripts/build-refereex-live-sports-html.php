<?php
declare(strict_types=1);
/**
 * Build self-contained RefereeX HTML for live-sports.html overwrite deploy.
 * deploy-marker: refereex-ai-v1-static-20260705
 */
$root = dirname(__DIR__);
$css = (string) file_get_contents($root . '/public/assets/css/refereex-ai.css');
$js = (string) file_get_contents($root . '/public/assets/js/refereex-ai.js');
$view = (string) file_get_contents($root . '/views/pages/refereex-ai/index.php');

$t = [
    'refereex.theme_toggle' => 'Toggle theme',
    'refereex.hero.badge' => 'Next-generation officiating intelligence',
    'refereex.hero.sub' => 'Real-time decision support for modern football',
    'refereex.hero.desc' => 'RefereeX AI fuses multi-camera feeds, computer vision, and digital twin technology to deliver explainable officiating intelligence for federations, leagues, and broadcasters.',
    'refereex.cta.demo' => 'Request a demo',
    'refereex.cta.watch' => 'Watch overview',
    'refereex.cta.pilot' => 'Start a pilot',
    'refereex.stats.confidence' => 'Decision confidence',
    'refereex.stats.latency' => 'Edge latency',
    'refereex.stats.cameras' => 'Camera inputs',
    'refereex.stats.monitoring' => 'Integrity monitoring',
    'refereex.stats.twin_sync' => 'Twin sync accuracy',
    'refereex.what.eyebrow' => 'Platform overview',
    'refereex.what.title' => 'What is RefereeX AI?',
    'refereex.what.p1' => 'RefereeX AI is Sportify\'s intelligent officiating platform — built to support referees, VAR teams, and integrity officers with real-time AI analysis.',
    'refereex.what.p2' => 'The system ingests live multi-angle video, tracks every player and the ball, and maintains a synchronized digital twin of the match state.',
    'refereex.what.p3' => 'Every recommendation is explainable: federations see which cameras contributed, confidence scores, and replay rationale aligned with IFAB protocols.',
    'refereex.arch.eyebrow' => 'Architecture',
    'refereex.arch.title' => 'System architecture',
    'refereex.arch.sub' => 'From stadium cameras to federation dashboards — a full-stack officiating intelligence pipeline.',
    'refereex.arch.cameras' => 'Multi-camera ingest',
    'refereex.arch.cameras_tip' => 'Synchronized feeds from broadcast, tactical, goal-line, and drone cameras.',
    'refereex.arch.vision' => 'Computer vision',
    'refereex.arch.vision_tip' => 'YOLO detection and pose estimation for players, officials, and ball.',
    'refereex.arch.tracking' => 'Object tracking',
    'refereex.arch.tracking_tip' => 'Sub-frame tracking with occlusion handling across camera handoffs.',
    'refereex.arch.fusion' => 'Decision fusion',
    'refereex.arch.fusion_tip' => 'Bayesian fusion merges multi-camera incident assessments.',
    'refereex.arch.twin' => 'Digital twin',
    'refereex.arch.twin_tip' => 'Live match state model with player, ball, and official entities.',
    'refereex.arch.explain' => 'Explainable AI',
    'refereex.arch.explain_tip' => 'Human-readable reasoning chains for every recommendation.',
    'refereex.arch.support' => 'Officiating support',
    'refereex.arch.support_tip' => 'VAR workflow integration and federation audit trails.',
    'refereex.tech.eyebrow' => 'Technology',
    'refereex.tech.title' => 'Core technologies',
    'refereex.how.eyebrow' => 'Process',
    'refereex.how.title' => 'How it works',
    'refereex.how.s1' => 'Cameras capture live match video',
    'refereex.how.s2' => 'AI detects players, ball, and incidents',
    'refereex.how.s3' => 'Digital twin updates in real time',
    'refereex.how.s4' => 'Multi-camera fusion assesses incidents',
    'refereex.how.s5' => 'Explainable recommendations generated',
    'refereex.how.s6' => 'VAR team reviews with confidence scores',
    'refereex.how.s7' => 'Federation dashboard logs all decisions',
    'refereex.field.eyebrow' => 'Interactive demo',
    'refereex.field.title' => 'Incident simulation',
    'refereex.field.sub' => 'Click an incident type to see how RefereeX AI would analyze it.',
    'refereex.field.controls' => 'Incident types',
    'refereex.field.prompt' => 'Select an incident to see AI analysis…',
    'refereex.cams.eyebrow' => 'Multi-camera',
    'refereex.cams.title' => 'Camera network visualization',
    'refereex.cams.sub' => 'Explore how each camera contributes to incident assessment.',
    'refereex.cams.prompt' => 'Click a camera marker to see its field of view…',
    'refereex.fusion.eyebrow' => 'Decision fusion',
    'refereex.fusion.title' => 'AI decision fusion',
    'refereex.fusion.final' => 'Fused decision',
    'refereex.twin.eyebrow' => 'Digital twin',
    'refereex.twin.title' => 'Live digital twin',
    'refereex.twin.sub' => 'Every player, the ball, and officials mapped to synchronized digital entities.',
    'refereex.twin.sync_label' => 'Timeline synchronization',
    'refereex.explain.eyebrow' => 'Transparency',
    'refereex.explain.title' => 'Explainable AI decisions',
    'refereex.explain.incident' => 'Incident: Possible handball in the penalty area (67\'',
    'refereex.explain.r1' => 'Camera 2 shows arm extension toward ball trajectory',
    'refereex.explain.r2' => 'Ball deflection angle consistent with hand contact',
    'refereex.explain.r3' => 'Player arm position above shoulder line at contact',
    'refereex.explain.r4' => 'No significant body position change from Camera 1',
    'refereex.explain.confidence' => 'confidence',
    'refereex.explain.cameras' => 'cameras',
    'refereex.explain.review' => 'VAR review recommended',
    'refereex.dash.eyebrow' => 'Dashboard',
    'refereex.dash.title' => 'Federation command center',
    'refereex.dash.cta' => 'Explore the dashboard',
    'refereex.features.eyebrow' => 'Capabilities',
    'refereex.features.title' => 'Platform features',
    'refereex.benefits.eyebrow' => 'Comparison',
    'refereex.benefits.title' => 'Traditional vs RefereeX AI',
    'refereex.benefits.traditional' => 'Traditional officiating',
    'refereex.use.eyebrow' => 'Use cases',
    'refereex.use.title' => 'Who uses RefereeX AI',
    'refereex.video.eyebrow' => 'Demo',
    'refereex.video.title' => 'See RefereeX AI in action',
    'refereex.research.eyebrow' => 'Research',
    'refereex.research.title' => 'Research foundations',
    'refereex.faq.eyebrow' => 'FAQ',
    'refereex.faq.title' => 'Frequently asked questions',
    'refereex.faq.q1' => 'Does RefereeX AI replace referees?',
    'refereex.faq.a1' => 'No. RefereeX AI supports human officials with data, replays, and confidence scores. Final decisions remain with the referee and VAR team.',
    'refereex.faq.q2' => 'How many cameras are supported?',
    'refereex.faq.a2' => 'The platform supports 16+ synchronized camera inputs including broadcast, tactical, goal-line, and drone feeds.',
    'refereex.faq.q3' => 'What is the latency?',
    'refereex.faq.a3' => 'Edge deployments achieve sub-100ms incident detection. Cloud fusion adds typically 200–500ms for full multi-camera assessment.',
    'refereex.faq.q4' => 'Is it IFAB compliant?',
    'refereex.faq.a4' => 'Workflows are designed around IFAB protocols. Audit trails document every recommendation for federation review.',
    'refereex.faq.q5' => 'Can it integrate with existing VAR?',
    'refereex.faq.a5' => 'Yes. RESTful APIs and standard video protocols enable integration with existing VAR infrastructure and broadcast systems.',
    'refereex.faq.q6' => 'How do I start a pilot?',
    'refereex.faq.a6' => 'Contact our team for a demo. We offer pilot programs for leagues, federations, and broadcast partners.',
    'refereex.cta.title' => 'Ready to transform officiating?',
    'refereex.cta.sub' => 'Join federations and leagues exploring the future of intelligent match officiating.',
    'refereex.cta.research' => 'Research partnership',
    'refereex.cta.pilot_league' => 'Pilot your league',
    'refereex.cta.contact' => 'Contact us',
    'refereex.footer.aria' => 'RefereeX AI footer',
    'refereex.footer.quick' => 'Quick links',
    'refereex.footer.research' => 'Research',
    'refereex.footer.technology' => 'Technology',
    'refereex.footer.contact' => 'Contact',
    'refereex.footer.newsletter' => 'Newsletter',
    'refereex.footer.newsletter_sub' => 'Get updates on RefereeX AI research and product releases.',
    'refereex.footer.email_placeholder' => 'your@email.com',
    'refereex.footer.subscribe' => 'Subscribe',
    'footer.contact' => 'Contact',
];

$body = preg_replace_callback("/__\('([^']+)'\)/", static function (array $m) use ($t): string {
    return htmlspecialchars($t[$m[1]] ?? $m[1], ENT_QUOTES, 'UTF-8');
}, $view);

$body = preg_replace('/<\?php[^?]*\?>\s*/s', '', $body);
$body = str_replace("route('contact')", '/contact', $body);
$body = preg_replace('/<\?= e\(\$contactUrl\) \?>/', '/contact', $body);
$body = preg_replace('/<\?= e\(([^)]+)\) \?>/', '$1', $body);
$body = preg_replace('/<\?= ([^?]+) \?>/', '$1', $body);
$body = preg_replace('/<\?php[^?]+\?>/s', '', $body);
$body = str_replace('<?= date(\'Y\') ?>', (string) date('Y'), $body);

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RefereeX AI — Intelligent Officiating Platform · Sportify</title>
    <meta name="description" content="RefereeX AI delivers real-time officiating intelligence, multi-camera fusion, and digital twin technology for modern football.">
    <meta property="og:title" content="RefereeX AI · Sportify">
    <meta property="og:description" content="Real-time officiating intelligence for modern football.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://sportifyplus.de/refereex-ai">
    <meta name="theme-color" content="#050a12">
    <link rel="canonical" href="https://sportifyplus.de/refereex-ai">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/refereex-ai.css">
    <link rel="icon" href="/assets/img/favicon.png" type="image/png">
    <style>
[data-reveal]{opacity:0;transform:translateY(24px);transition:opacity .7s,transform .7s}[data-reveal].is-revealed{opacity:1;transform:none}
    </style>
</head>
<body>
<!-- deploy-marker:refereex-ai-static-20260708 -->
<main id="main-content">
HTML;
$html .= $body;
$html .= '</main>';
$html .= '<script src="/assets/js/refereex-ai.js" defer></script>';
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

$out = $root . '/public/refereex-ai.html';
file_put_contents($out, $html);
echo "Wrote {$out} (" . number_format(strlen($html)) . " bytes)\n";
