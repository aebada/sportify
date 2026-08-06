<?php
/** RefereeX AI — Sportify layout + app.css (17 sections) */
$contactUrl = route('contact');
?>
<div class="refereex-page" data-refereex-page>

<!-- 1. Hero -->
<section class="hero" id="rx-hero" aria-labelledby="rx-hero-title" style="padding:clamp(64px,10vw,96px) 0 clamp(48px,8vw,72px)">
    <div class="container">
        <div class="hero-grid">
            <div>
                <span class="eyebrow"><?= __('refereex.hero.badge') ?: 'Next-generation officiating intelligence' ?></span>
                <h1 id="rx-hero-title">RefereeX <span class="accent">AI</span></h1>
                <p class="hero__sub"><?= __('refereex.hero.sub') ?: 'Real-time decision support for modern football' ?></p>
                <p class="lead"><?= __('refereex.hero.desc') ?: 'RefereeX AI fuses multi-camera feeds, computer vision, and digital twin technology to deliver explainable officiating intelligence for federations, leagues, and broadcasters.' ?></p>
                <div class="hero__cta">
                    <a href="<?= e($contactUrl) ?>?subject=RefereeX%20Demo" class="btn btn-primary"><?= __('refereex.cta.demo') ?: 'Request a demo' ?></a>
                    <a href="#rx-dashboard" class="btn btn-ghost"><?= __('refereex.cta.watch') ?: 'Watch overview' ?></a>
                    <a href="<?= e($contactUrl) ?>?subject=RefereeX%20Pilot" class="btn btn-dark"><?= __('refereex.cta.pilot') ?: 'Start a pilot' ?></a>
                </div>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <div class="panel refereex-hero-panel center" data-reveal>
                    <div class="refereex-hero-panel__icon">📹</div>
                    <p class="mb-0"><strong><?= __('refereex.hero.panel_title') ?: 'Live officiating intelligence' ?></strong></p>
                    <p class="small muted mb-0"><?= __('refereex.hero.panel_sub') ?: 'Multi-camera fusion · Digital twin · Explainable AI' ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hero stats -->
<section class="section section--tight" id="rx-stats" aria-label="<?= e(__('refereex.stats.aria') ?: 'Key metrics') ?>">
    <div class="container">
        <div class="stat-grid">
            <?php
            $stats = [
                ['count' => '98.7', 'suffix' => '%', 'label' => __('refereex.stats.confidence') ?: 'Decision confidence'],
                ['count' => '100', 'suffix' => ' ms', 'prefix' => '<', 'label' => __('refereex.stats.latency') ?: 'Edge latency'],
                ['count' => '16', 'suffix' => '+', 'label' => __('refereex.stats.cameras') ?: 'Camera inputs'],
                ['count' => '24', 'suffix' => '/7', 'label' => __('refereex.stats.monitoring') ?: 'Integrity monitoring'],
            ];
            foreach ($stats as $s):
            ?>
            <div class="stat-box accent" data-reveal>
                <b class="counter"><?php if (!empty($s['prefix'])) echo $s['prefix']; ?><span data-count="<?= e($s['count']) ?>" data-suffix="<?= e($s['suffix']) ?>">0</span></b>
                <span><?= e($s['label']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 2. Problem -->
<section class="section" id="rx-problem" style="background:var(--bg-2)" aria-labelledby="rx-problem-head">
    <div class="container">
        <div class="layout-2col">
            <div data-reveal>
                <span class="eyebrow"><?= __('refereex.problem.eyebrow') ?: 'The challenge' ?></span>
                <h2 id="rx-problem-head"><?= __('refereex.problem.title') ?: 'Modern football outpaced traditional officiating' ?></h2>
                <p class="lead"><?= __('refereex.problem.p1') ?: 'Matches are faster, cameras are everywhere, and fans expect instant fairness — yet VAR still relies on manual replay selection and single-angle review.' ?></p>
            </div>
            <div class="grid grid-2" data-reveal>
                <?php foreach ([
                    ['⚠️', __('refereex.problem.c1_title') ?: 'Delayed decisions', __('refereex.problem.c1_desc') ?: 'Critical incidents reviewed minutes after play resumes'],
                    ['📹', __('refereex.problem.c2_title') ?: 'Camera blind spots', __('refereex.problem.c2_desc') ?: 'Single-angle replay misses off-ball contact and positioning'],
                    ['❓', __('refereex.problem.c3_title') ?: 'No explainability', __('refereex.problem.c3_desc') ?: 'Federations cannot audit why a recommendation was made'],
                    ['📉', __('refereex.problem.c4_title') ?: 'Integrity risk', __('refereex.problem.c4_desc') ?: 'Inconsistent calls erode trust with fans and broadcasters'],
                ] as $p): ?>
                <div class="card card__body">
                    <div class="step__icon mb-2"><?= $p[0] ?></div>
                    <h3><?= e($p[1]) ?></h3>
                    <p class="small mb-0"><?= e($p[2]) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- 3. Solution -->
<section class="section" id="rx-solution" aria-labelledby="rx-solution-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.solution.eyebrow') ?: 'The solution' ?></span>
            <h2 id="rx-solution-head"><?= __('refereex.solution.title') ?: 'Intelligent officiating from kickoff to final whistle' ?></h2>
            <p><?= __('refereex.solution.sub') ?: 'RefereeX AI is Sportify\'s end-to-end platform — ingesting live video, maintaining a digital twin, and delivering explainable recommendations aligned with IFAB protocols.' ?></p>
        </div>
        <div class="grid grid-4">
            <?php foreach ([
                ['🔮', __('refereex.solution.c1_title') ?: 'Digital twin', __('refereex.solution.c1_desc') ?: 'Synchronized match state for every player, ball, and official'],
                ['📹', __('refereex.solution.c2_title') ?: 'Multi-camera fusion', __('refereex.solution.c2_desc') ?: 'Bayesian fusion across broadcast, tactical, and goal-line feeds'],
                ['💡', __('refereex.solution.c3_title') ?: 'Explainable AI', __('refereex.solution.c3_desc') ?: 'Human-readable reasoning chains for every recommendation'],
                ['🏛️', __('refereex.solution.c4_title') ?: 'Federation ready', __('refereex.solution.c4_desc') ?: 'Audit trails, dashboards, and API integration for VAR workflows'],
            ] as $s): ?>
            <div class="card card__body" data-reveal>
                <div class="step__icon"><?= $s[0] ?></div>
                <h3><?= e($s[1]) ?></h3>
                <p class="small mb-0"><?= e($s[2]) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4. How It Works -->
<section class="section" id="rx-how" style="background:var(--bg-2)" aria-labelledby="rx-how-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.how.eyebrow') ?: 'Process' ?></span>
            <h2 id="rx-how-head"><?= __('refereex.how.title') ?: 'How it works' ?></h2>
        </div>
        <div class="grid grid-4">
            <?php
            $steps = [
                [__('refereex.how.s1') ?: 'Cameras capture live match video', __('refereex.how.s1d') ?: 'Broadcast, tactical, goal-line, and drone feeds synchronized'],
                [__('refereex.how.s2') ?: 'AI detects players, ball, and incidents', __('refereex.how.s2d') ?: 'YOLO detection and pose estimation at the edge'],
                [__('refereex.how.s3') ?: 'Digital twin updates in real time', __('refereex.how.s3d') ?: 'Every entity mapped to sub-centimeter coordinates'],
                [__('refereex.how.s4') ?: 'Multi-camera fusion assesses incidents', __('refereex.how.s4d') ?: 'Bayesian fusion merges per-camera confidence scores'],
                [__('refereex.how.s5') ?: 'Explainable recommendations generated', __('refereex.how.s5d') ?: 'Reasoning chains document camera contributions'],
                [__('refereex.how.s6') ?: 'VAR team reviews with confidence scores', __('refereex.how.s6d') ?: 'Workflow integrated with existing VAR infrastructure'],
                [__('refereex.how.s7') ?: 'Federation dashboard logs all decisions', __('refereex.how.s7d') ?: 'Full audit trail for integrity officers'],
                [__('refereex.how.s8') ?: 'Post-match analytics and reports', __('refereex.how.s8d') ?: 'Referee performance and integrity scoring'],
            ];
            foreach ($steps as $i => $step):
            ?>
            <div class="card step card__body" data-reveal>
                <div class="step__num"><?= $i + 1 ?></div>
                <h3><?= e($step[0]) ?></h3>
                <p class="small mb-0"><?= e($step[1]) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. Architecture -->
<section class="section" id="rx-architecture" aria-labelledby="rx-arch-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.arch.eyebrow') ?: 'Architecture' ?></span>
            <h2 id="rx-arch-head"><?= __('refereex.arch.title') ?: 'System architecture' ?></h2>
            <p><?= __('refereex.arch.sub') ?: 'From stadium cameras to federation dashboards — a full-stack officiating intelligence pipeline.' ?></p>
        </div>
        <div class="refereex-pipeline" data-reveal>
            <?php
            $pipeline = [
                ['icon' => '📹', 'label' => __('refereex.arch.cameras') ?: 'Multi-camera ingest'],
                ['icon' => '👁️', 'label' => __('refereex.arch.vision') ?: 'Computer vision'],
                ['icon' => '🎯', 'label' => __('refereex.arch.tracking') ?: 'Object tracking'],
                ['icon' => '⚡', 'label' => __('refereex.arch.fusion') ?: 'Decision fusion'],
                ['icon' => '🔮', 'label' => __('refereex.arch.twin') ?: 'Digital twin'],
                ['icon' => '💡', 'label' => __('refereex.arch.explain') ?: 'Explainable AI'],
                ['icon' => '🏁', 'label' => __('refereex.arch.support') ?: 'Officiating support'],
            ];
            foreach ($pipeline as $node):
            ?>
            <div class="refereex-pipeline__node card card__body center">
                <div class="step__icon"><?= $node['icon'] ?></div>
                <div class="small fw-800"><?= e($node['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 6. Algorithm -->
<section class="section" id="rx-algorithm" style="background:var(--bg-2)" aria-labelledby="rx-algo-head">
    <div class="container">
        <div class="layout-2col">
            <div data-reveal>
                <span class="eyebrow"><?= __('refereex.algo.eyebrow') ?: 'Edge performance' ?></span>
                <h2 id="rx-algo-head"><?= __('refereex.algo.title') ?: 'Lightweight algorithm stack' ?></h2>
                <p class="lead"><?= __('refereex.algo.p1') ?: 'RefereeX runs optimized YOLO-nano and custom tracking kernels on edge GPUs — achieving sub-100ms detection without cloud round-trips for initial incident flagging.' ?></p>
                <ul class="feature-list">
                    <li><span class="tick">✓</span> YOLO-nano @ 60 FPS per camera stream</li>
                    <li><span class="tick">✓</span> ByteTrack occlusion handling with camera handoff</li>
                    <li><span class="tick">✓</span> Quantized inference — 4× smaller model footprint</li>
                    <li><span class="tick">✓</span> Adaptive frame sampling during low-action periods</li>
                </ul>
            </div>
            <div class="panel bar-chart" data-reveal>
                <div class="bar-row"><span class="bar-row__label">Detection</span><div class="bar-row__track"><span class="bar-row__fill" data-w="92" style="--w:92%"></span></div><span class="bar-row__val">92 FPS</span></div>
                <div class="bar-row"><span class="bar-row__label">Tracking</span><div class="bar-row__track"><span class="bar-row__fill" data-w="78" style="--w:78%"></span></div><span class="bar-row__val">78 FPS</span></div>
                <div class="bar-row"><span class="bar-row__label">Fusion</span><div class="bar-row__track"><span class="bar-row__fill" data-w="95" style="--w:95%"></span></div><span class="bar-row__val">&lt;100ms</span></div>
                <div class="bar-row"><span class="bar-row__label">Twin sync</span><div class="bar-row__track"><span class="bar-row__fill" data-w="88" style="--w:88%"></span></div><span class="bar-row__val">99%</span></div>
            </div>
        </div>
    </div>
</section>

<!-- 7. Fusion -->
<section class="section" id="rx-fusion" aria-labelledby="rx-fusion-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.fusion.eyebrow') ?: 'Decision fusion' ?></span>
            <h2 id="rx-fusion-head"><?= __('refereex.fusion.title') ?: 'Adaptive multi-camera fusion' ?></h2>
            <p><?= __('refereex.fusion.sub') ?: 'Each camera contributes a confidence-weighted assessment. RefereeX adaptively weights feeds based on angle quality and occlusion.' ?></p>
        </div>
        <div class="refereex-fusion" data-rx-fusion data-reveal>
            <div class="refereex-fusion__inputs grid grid-3">
                <div class="card card__body" data-rx-fusion-cam>
                    <div class="small muted">Camera 1 — Main</div>
                    <div class="fw-800">Penalty</div>
                    <div class="text-green" data-rx-conf-display>92%</div>
                    <input type="range" class="refereex-fusion__slider" data-rx-conf-slider min="0" max="100" value="92" aria-label="Camera 1 confidence">
                </div>
                <div class="card card__body" data-rx-fusion-cam>
                    <div class="small muted">Camera 2 — Wide</div>
                    <div class="fw-800">Penalty</div>
                    <div class="text-green" data-rx-conf-display>89%</div>
                    <input type="range" class="refereex-fusion__slider" data-rx-conf-slider min="0" max="100" value="89" aria-label="Camera 2 confidence">
                </div>
                <div class="card card__body" data-rx-fusion-cam>
                    <div class="small muted">Camera 3 — Behind goal</div>
                    <div class="fw-800">No penalty</div>
                    <div class="text-green" data-rx-conf-display>43%</div>
                    <input type="range" class="refereex-fusion__slider" data-rx-conf-slider min="0" max="100" value="43" aria-label="Camera 3 confidence">
                </div>
            </div>
            <div class="refereex-fusion__result panel center mt-3">
                <div class="small muted"><?= __('refereex.fusion.final') ?: 'Fused decision' ?></div>
                <div class="fw-800 text-green" data-rx-fusion-result>Penalty — 95%</div>
            </div>
        </div>
    </div>
</section>

<!-- 8. Digital Twin -->
<section class="section" id="rx-twin" style="background:var(--bg-2)" aria-labelledby="rx-twin-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.twin.eyebrow') ?: 'Digital twin' ?></span>
            <h2 id="rx-twin-head"><?= __('refereex.twin.title') ?: 'Live digital twin' ?></h2>
            <p><?= __('refereex.twin.sub') ?: 'Every player, the ball, and officials mapped to synchronized digital entities.' ?></p>
        </div>
        <div class="panel" data-reveal>
            <div class="chips wrap mb-2">
                <?php for ($p = 1; $p <= 8; $p++): ?>
                <span class="chip chip--green">Player <?= $p ?> Twin</span>
                <?php endfor; ?>
                <span class="chip chip--green">Ball Twin</span>
                <span class="chip chip--green">Referee Twin</span>
            </div>
            <p class="small muted mb-1"><?= __('refereex.twin.sync_label') ?: 'Timeline synchronization' ?></p>
            <div class="refereex-twin-timeline" data-rx-twin-timeline>
                <?php for ($s = 0; $s < 12; $s++): ?>
                <div class="refereex-twin-timeline__seg" data-rx-twin-seg></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

<!-- 9. Explainable AI -->
<section class="section" id="rx-explain" aria-labelledby="rx-explain-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.explain.eyebrow') ?: 'Transparency' ?></span>
            <h2 id="rx-explain-head"><?= __('refereex.explain.title') ?: 'Explainable AI decisions' ?></h2>
        </div>
        <div class="panel ai-rec" data-reveal>
            <div class="ai-badge mb-2"><?= __('refereex.explain.incident') ?: 'Incident: Possible handball in the penalty area (67\')' ?></div>
            <ul class="ai-rec__reasons">
                <li><?= __('refereex.explain.r1') ?: 'Camera 2 shows arm extension toward ball trajectory' ?></li>
                <li><?= __('refereex.explain.r2') ?: 'Ball deflection angle consistent with hand contact' ?></li>
                <li><?= __('refereex.explain.r3') ?: 'Player arm position above shoulder line at contact' ?></li>
                <li><?= __('refereex.explain.r4') ?: 'No significant body position change from Camera 1' ?></li>
            </ul>
            <p class="small text-green mb-0">94% <?= __('refereex.explain.confidence') ?: 'confidence' ?> · 3 <?= __('refereex.explain.cameras') ?: 'cameras' ?> · <?= __('refereex.explain.review') ?: 'VAR review recommended' ?></p>
        </div>
    </div>
</section>

<!-- 10. Dashboard -->
<section class="section" id="rx-dashboard" style="background:var(--bg-2)" aria-labelledby="rx-dash-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.dash.eyebrow') ?: 'Dashboard' ?></span>
            <h2 id="rx-dash-head"><?= __('refereex.dash.title') ?: 'Federation command center' ?></h2>
        </div>
        <div class="grid grid-4 refereex-dashboard" data-reveal>
            <?php
            $widgets = [
                ['label' => 'Live Match', 'value' => 'FC Bayern vs Dortmund', 'wide' => true, 'w' => 85],
                ['label' => 'Incident Feed', 'value' => '3 active reviews'],
                ['label' => 'Timeline', 'value' => "67' Handball check"],
                ['label' => 'Camera Views', 'value' => '5 streams live'],
                ['label' => 'Digital Twin', 'value' => '99% synced', 'w' => 99],
                ['label' => 'Confidence', 'value' => '94.2%', 'w' => 94],
                ['label' => 'Replay', 'value' => 'Ready'],
                ['label' => 'AI Recommendations', 'value' => 'Review advised', 'wide' => true, 'w' => 78],
            ];
            foreach ($widgets as $w):
            ?>
            <div class="card card__body<?= !empty($w['wide']) ? ' refereex-dashboard__wide' : '' ?>">
                <div class="small muted"><?= e($w['label']) ?></div>
                <div class="fw-800"><?= e($w['value']) ?></div>
                <?php if (!empty($w['w'])): ?>
                <div class="meter"><span style="width:<?= (int)$w['w'] ?>%"></span></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="center mt-3" data-reveal>
            <a href="<?= e($contactUrl) ?>?subject=RefereeX%20Dashboard" class="btn btn-primary"><?= __('refereex.dash.cta') ?: 'Explore the dashboard' ?></a>
        </div>
    </div>
</section>

<!-- 11. Features -->
<section class="section" id="rx-features" aria-labelledby="rx-features-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.features.eyebrow') ?: 'Capabilities' ?></span>
            <h2 id="rx-features-head"><?= __('refereex.features.title') ?: 'Platform features' ?></h2>
        </div>
        <div class="grid grid-auto">
            <?php
            $features = [
                ['⚡', 'Real-Time Alerts', 'Instant notifications for critical match events'],
                ['🔄', 'Automatic Replay', 'AI-triggered replay sequences on demand'],
                ['🛡️', 'Integrity Monitoring', 'Continuous match integrity scoring'],
                ['📋', 'AI Reports', 'Post-match analytical reports'],
                ['📊', 'Referee Analytics', 'Performance insights and trends'],
                ['📍', 'Player Tracking', 'Sub-centimeter positional accuracy'],
                ['📄', 'Match Reports', 'Comprehensive officiating documentation'],
                ['🎬', 'Video Intelligence', 'Deep video understanding pipeline'],
                ['☁️', 'Cloud Deployment', 'Scalable cloud infrastructure'],
                ['⚡', 'Edge AI', 'On-premise low-latency processing'],
                ['🔌', 'API Integration', 'RESTful APIs for federation systems'],
                ['🏛️', 'Federation Dashboard', 'Multi-league oversight console'],
            ];
            foreach ($features as $f):
            ?>
            <div class="card card__body flex gap-1" data-reveal>
                <span class="step__icon"><?= $f[0] ?></span>
                <div><h3><?= e($f[1]) ?></h3><p class="small mb-0"><?= e($f[2]) ?></p></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 12. Use Cases -->
<section class="section" id="rx-usecases" style="background:var(--bg-2)" aria-labelledby="rx-use-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.use.eyebrow') ?: 'Use cases' ?></span>
            <h2 id="rx-use-head"><?= __('refereex.use.title') ?: 'Who uses RefereeX AI' ?></h2>
        </div>
        <div class="grid grid-4">
            <?php
            $uses = [
                ['⚽', 'Professional Football'], ['🏛️', 'Federations'], ['📺', 'Broadcasters'],
                ['🎥', 'VAR Support'], ['🏟️', 'Regional Leagues'], ['🎓', 'Youth Academies'],
                ['📊', 'Sports Analytics'], ['🛡️', 'Integrity Monitoring'],
            ];
            foreach ($uses as $u):
            ?>
            <div class="card card__body center" data-reveal>
                <div class="step__icon"><?= $u[0] ?></div>
                <h3><?= e($u[1]) ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 13. Comparison -->
<section class="section" id="rx-benefits" aria-labelledby="rx-benefits-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.benefits.eyebrow') ?: 'Comparison' ?></span>
            <h2 id="rx-benefits-head"><?= __('refereex.benefits.title') ?: 'Traditional VAR vs RefereeX AI' ?></h2>
        </div>
        <div class="table-wrap" data-reveal>
            <table class="data">
                <thead>
                    <tr>
                        <th scope="col">Capability</th>
                        <th scope="col"><?= __('refereex.benefits.traditional') ?: 'Traditional VAR' ?></th>
                        <th scope="col">RefereeX AI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ([
                        ['Camera coverage', '1–2 angles', '16+ synchronized feeds'],
                        ['Incident detection', 'Manual operator', 'AI auto-flagging &lt;100ms'],
                        ['Decision support', 'Replay only', 'Confidence-scored recommendations'],
                        ['Explainability', 'None', 'Full reasoning chains'],
                        ['Match state', 'Static replay', 'Live digital twin'],
                        ['Audit trail', 'Limited logs', 'Federation-grade audit dashboard'],
                        ['Integration', 'Proprietary', 'RESTful APIs & standard protocols'],
                    ] as $row): ?>
                    <tr>
                        <th scope="row"><?= e($row[0]) ?></th>
                        <td><?= e($row[1]) ?></td>
                        <td><span class="text-green">✓</span> <?= e($row[2]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- 14. Research -->
<section class="section" id="rx-research" style="background:var(--bg-2)" aria-labelledby="rx-research-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.research.eyebrow') ?: 'Research' ?></span>
            <h2 id="rx-research-head"><?= __('refereex.research.title') ?: 'Research foundations' ?></h2>
        </div>
        <div class="chips wrap center" data-reveal>
            <?php foreach (['Computer Vision', 'Decision Fusion', 'Physical AI', 'Digital Twin', 'Explainable AI', 'Referee Intelligence'] as $r): ?>
            <span class="chip chip--green chip--sm"><?= e($r) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 15. Pilot -->
<section class="section" id="rx-pilot" aria-labelledby="rx-pilot-head">
    <div class="container">
        <div class="layout-2col">
            <div data-reveal>
                <span class="eyebrow"><?= __('refereex.pilot.eyebrow') ?: 'Pilot program' ?></span>
                <h2 id="rx-pilot-head"><?= __('refereex.pilot.title') ?: 'Start your league pilot' ?></h2>
                <p class="lead"><?= __('refereex.pilot.p1') ?: 'We partner with federations and leagues for structured pilot programs — from single-match trials to full-season deployments with dedicated support.' ?></p>
                <ul class="feature-list">
                    <li><span class="tick">✓</span> <strong>Week 1–2</strong> — Stadium assessment & camera mapping</li>
                    <li><span class="tick">✓</span> <strong>Week 3–4</strong> — Edge deployment & calibration</li>
                    <li><span class="tick">✓</span> <strong>Week 5+</strong> — Live match integration with VAR team</li>
                </ul>
                <a href="<?= e($contactUrl) ?>?subject=RefereeX%20Pilot%20League" class="btn btn-primary mt-2"><?= __('refereex.cta.pilot_league') ?: 'Pilot your league' ?></a>
            </div>
            <div class="panel" data-reveal>
                <h3><?= __('refereex.pilot.form_title') ?: 'Request pilot information' ?></h3>
                <form action="<?= e($contactUrl) ?>" method="get">
                    <input type="hidden" name="subject" value="RefereeX Pilot">
                    <div class="form-group">
                        <label for="rx-pilot-org">Organization</label>
                        <input class="form-control" id="rx-pilot-org" name="organization" type="text" placeholder="Federation / League name" required>
                    </div>
                    <div class="form-group">
                        <label for="rx-pilot-email">Email</label>
                        <input class="form-control" id="rx-pilot-email" name="email" type="email" placeholder="you@federation.org" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><?= __('refereex.pilot.submit') ?: 'Get pilot details' ?></button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- 16. FAQ -->
<section class="section" id="rx-faq" style="background:var(--bg-2)" aria-labelledby="rx-faq-head">
    <div class="container">
        <div class="section-head center" data-reveal>
            <span class="eyebrow"><?= __('refereex.faq.eyebrow') ?: 'FAQ' ?></span>
            <h2 id="rx-faq-head"><?= __('refereex.faq.title') ?: 'Frequently asked questions' ?></h2>
        </div>
        <div class="refereex-faq" data-reveal>
            <?php
            $faqs = [
                ['q' => __('refereex.faq.q1') ?: 'Does RefereeX AI replace referees?', 'a' => __('refereex.faq.a1') ?: 'No. RefereeX AI supports human officials with data, replays, and confidence scores. Final decisions remain with the referee and VAR team.'],
                ['q' => __('refereex.faq.q2') ?: 'How many cameras are supported?', 'a' => __('refereex.faq.a2') ?: 'The platform supports 16+ synchronized camera inputs including broadcast, tactical, goal-line, and drone feeds.'],
                ['q' => __('refereex.faq.q3') ?: 'What is the latency?', 'a' => __('refereex.faq.a3') ?: 'Edge deployments achieve sub-100ms incident detection. Cloud fusion adds typically 200–500ms for full multi-camera assessment.'],
                ['q' => __('refereex.faq.q4') ?: 'Is it IFAB compliant?', 'a' => __('refereex.faq.a4') ?: 'Workflows are designed around IFAB protocols. Audit trails document every recommendation for federation review.'],
                ['q' => __('refereex.faq.q5') ?: 'Can it integrate with existing VAR?', 'a' => __('refereex.faq.a5') ?: 'Yes. RESTful APIs and standard video protocols enable integration with existing VAR infrastructure and broadcast systems.'],
            ];
            foreach ($faqs as $faq):
            ?>
            <details class="panel refereex-faq__item mb-2">
                <summary class="fw-800"><?= e($faq['q']) ?></summary>
                <p class="small mb-0 mt-2"><?= e($faq['a']) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 17. Final CTA -->
<section class="section section--tight" id="rx-cta" aria-labelledby="rx-cta-head">
    <div class="container">
        <div class="cta-banner" data-reveal>
            <h2 id="rx-cta-head"><?= __('refereex.cta.title') ?: 'Ready to transform officiating?' ?></h2>
            <p><?= __('refereex.cta.sub') ?: 'Join federations and leagues exploring the future of intelligent match officiating.' ?></p>
            <div class="hero__cta">
                <a href="<?= e($contactUrl) ?>?subject=RefereeX%20Demo" class="btn btn-primary"><?= __('refereex.cta.demo') ?: 'Request a demo' ?></a>
                <a href="<?= e($contactUrl) ?>?subject=RefereeX%20Research%20Partner" class="btn btn-ghost"><?= __('refereex.cta.research') ?: 'Research partnership' ?></a>
                <a href="<?= e($contactUrl) ?>?subject=RefereeX%20Pilot%20League" class="btn btn-dark"><?= __('refereex.cta.pilot_league') ?: 'Pilot your league' ?></a>
            </div>
        </div>
    </div>
</section>

</div>
