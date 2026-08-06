<?php
/** RefereeX AI static body — English strings from index.php */
$contactUrl = $contact ?? 'https://sportifyplus.de/contact';
?>
<button type="button" class="rx-theme-toggle" data-rx-theme-toggle aria-label="Toggle theme">☀️</button>

<!-- Hero -->
<section class="rx-hero" id="rx-hero" aria-labelledby="rx-hero-title">
    <div class="rx-hero__bg" aria-hidden="true">
        <div class="rx-hero__gradient"></div>
        <div class="rx-hero__grid"></div>
        <div class="rx-hero__lights"></div>
        <canvas class="rx-hero__particles" data-rx-particles></canvas>
        <div class="rx-hero__cameras">
            <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="rx-hero__camera" style="animation-delay:<?= $i * -3 ?>s">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 8h4l2-3h4l2 3h4v10H4V8z"/><circle cx="12" cy="13" r="3"/></svg>
            </div>
            <?php endfor; ?>
        </div>
        <svg class="rx-hero__network" viewBox="0 0 400 300" preserveAspectRatio="xMidYMid slice">
            <defs><linearGradient id="rx-net-grad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#22d3ee"/></linearGradient></defs>
            <line x1="50" y1="50" x2="200" y2="150" stroke="url(#rx-net-grad)" stroke-width="0.5" opacity="0.4"/>
            <line x1="350" y1="80" x2="200" y2="150" stroke="url(#rx-net-grad)" stroke-width="0.5" opacity="0.4"/>
            <line x1="100" y1="250" x2="200" y2="150" stroke="url(#rx-net-grad)" stroke-width="0.5" opacity="0.4"/>
            <line x1="300" y1="220" x2="200" y2="150" stroke="url(#rx-net-grad)" stroke-width="0.5" opacity="0.4"/>
            <circle cx="200" cy="150" r="6" fill="#3b82f6" opacity="0.6"/>
        </svg>
    </div>
    <div class="rx-wrap">
        <div class="rx-hero__content">
            <div class="rx-hero__badge"><span class="rx-hero__badge-dot"></span> Next-Gen Officiating Intelligence</div>
            <h1 class="rx-hero__title" id="rx-hero-title">RefereeX AI</h1>
            <p class="rx-hero__sub">Real-time AI for modern football officiating</p>
            <p class="rx-hero__desc">RefereeX AI fuses multi-camera vision, digital twin simulation, and explainable decision intelligence to support referees, leagues, and broadcasters with sub-100ms insights.</p>
            <div class="rx-btns rx-hero__btns">
                <a href="<?= htmlspecialchars($contactUrl) ?>?subject=RefereeX%20Demo" class="rx-btn rx-btn--primary">Request Demo</a>
                <a href="#rx-video" class="rx-btn rx-btn--ghost">Watch Overview</a>
                <a href="<?= htmlspecialchars($contactUrl) ?>?subject=RefereeX%20Pilot" class="rx-btn rx-btn--green">Start Pilot</a>
            </div>
        </div>
    </div>
</section>

<!-- Statistics -->
<section class="rx-section rx-section--tight" id="rx-stats" aria-labelledby="rx-stats-head">
    <div class="rx-wrap">
        <div class="rx-stats">
            <?php
            $stats = [
                ['count' => '98.7', 'suffix' => '%', 'label' => 'Decision Confidence'],
                ['count' => '100', 'suffix' => ' ms', 'prefix' => '<', 'label' => 'Fusion Latency'],
                ['count' => '16', 'suffix' => '+', 'label' => 'Camera Feeds'],
                ['count' => '24', 'suffix' => '/7', 'label' => 'Live Monitoring'],
                ['count' => '99', 'suffix' => '%', 'label' => 'Twin Sync Accuracy'],
            ];
            foreach ($stats as $s):
            ?>
            <div class="rx-stat rx-glass" data-reveal>
                <div class="rx-stat__value"><?php if (!empty($s['prefix'])) echo $s['prefix']; ?><span data-count="<?= htmlspecialchars($s['count']) ?>" data-suffix="<?= htmlspecialchars($s['suffix']) ?>"><?= htmlspecialchars($s['count'] . $s['suffix']) ?></span></div>
                <div class="rx-stat__label"><?= htmlspecialchars($s['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- What is RefereeX AI -->
<section class="rx-section" id="rx-what" aria-labelledby="rx-what-head">
    <div class="rx-wrap">
        <div class="rx-split">
            <div class="rx-stadium-viz" data-rx-stadium-viz data-reveal>
                <div class="rx-stadium-viz__pitch"></div>
                <span class="rx-stadium-viz__twin" style="top:35%;left:25%"></span>
                <span class="rx-stadium-viz__twin" style="top:45%;left:40%"></span>
                <span class="rx-stadium-viz__twin" style="top:55%;left:60%"></span>
                <span class="rx-stadium-viz__twin" style="top:40%;left:75%"></span>
                <span class="rx-stadium-viz__twin rx-stadium-viz__twin--ball" style="top:50%;left:50%"></span>
                <span class="rx-stadium-viz__link" style="top:35%;left:25%;width:80px;transform:rotate(25deg)"></span>
                <span class="rx-stadium-viz__link" style="top:45%;left:40%;width:60px;transform:rotate(-15deg)"></span>
            </div>
            <div data-reveal>
                <span class="rx-eyebrow">Platform</span>
                <h2 id="rx-what-head">What is RefereeX AI?</h2>
                <p class="rx-lead">RefereeX AI is an intelligent officiating platform that augments human referees with computer vision, sensor fusion, and digital twin technology.</p>
                <p class="rx-lead mt-2">It processes multi-angle stadium feeds in real time, reconstructs match state, and delivers explainable decision support for offsides, fouls, and critical incidents.</p>
                <p class="rx-lead mt-2">Built for leagues, federations, and broadcast partners who need precision, transparency, and scalable deployment.</p>
            </div>
        </div>
    </div>
</section>

<!-- System Architecture -->
<section class="rx-section rx-section--dark" id="rx-architecture" aria-labelledby="rx-arch-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Architecture</span>
            <h2 id="rx-arch-head">System Architecture</h2>
            <p>End-to-end pipeline from stadium camera feeds to real-time officiating support.</p>
        </div>
        <div class="rx-pipeline" data-reveal>
            <?php
            $pipeline = [
                ['icon' => '📹', 'label' => 'Multi-Camera Input', 'tip' => 'Synchronized feeds from 4–16+ calibrated stadium cameras'],
                ['icon' => '👁️', 'label' => 'Computer Vision', 'tip' => 'YOLO detection and scene understanding on every frame'],
                ['icon' => '🎯', 'label' => 'Object Tracking', 'tip' => 'Sub-centimeter player and ball tracking across views'],
                ['icon' => '⚡', 'label' => 'Decision Fusion', 'tip' => 'Bayesian fusion of multi-camera incident assessments'],
                ['icon' => '🔮', 'label' => 'Digital Twin', 'tip' => 'Real-time match state reconstruction in a virtual model'],
                ['icon' => '💡', 'label' => 'Explainable AI', 'tip' => 'Visual evidence trails and confidence scoring for every call'],
                ['icon' => '🏁', 'label' => 'Referee Support', 'tip' => 'Live alerts, replay triggers, and integrity dashboards'],
            ];
            foreach ($pipeline as $i => $node):
                if ($i > 0): ?><div class="rx-pipeline__arrow" aria-hidden="true"></div><?php endif; ?>
            <div class="rx-glass rx-pipeline__node" data-rx-pipeline-node tabindex="0">
                <div class="rx-pipeline__icon"><?= $node['icon'] ?></div>
                <div class="rx-pipeline__label"><?= htmlspecialchars($node['label']) ?></div>
                <div class="rx-pipeline__tooltip" role="tooltip"><?= htmlspecialchars($node['tip']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Core Technologies -->
<section class="rx-section" id="rx-tech" aria-labelledby="rx-tech-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Technology</span>
            <h2 id="rx-tech-head">Core Technologies</h2>
        </div>
        <div class="rx-tech-grid">
            <?php
            $techs = [
                ['icon' => '🤖', 'name' => 'Physical AI'],
                ['icon' => '🔮', 'name' => 'Digital Twins'],
                ['icon' => '👁️', 'name' => 'Computer Vision'],
                ['icon' => '📹', 'name' => 'Multi-Camera Fusion'],
                ['icon' => '🎯', 'name' => 'YOLO Detection'],
                ['icon' => '📍', 'name' => 'Object Tracking'],
                ['icon' => '💡', 'name' => 'Explainable AI'],
                ['icon' => '📊', 'name' => 'Bayesian Decision Fusion'],
                ['icon' => '⚡', 'name' => 'Edge AI'],
                ['icon' => '☁️', 'name' => 'Cloud AI'],
                ['icon' => '📈', 'name' => 'Real-Time Analytics'],
                ['icon' => '🛡️', 'name' => 'Sports Integrity'],
            ];
            foreach ($techs as $t):
            ?>
            <div class="rx-glass rx-tech-card" data-reveal>
                <div class="rx-tech-card__icon"><?= $t['icon'] ?></div>
                <h3><?= htmlspecialchars($t['name']) ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="rx-section rx-section--dark" id="rx-how" aria-labelledby="rx-how-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Process</span>
            <h2 id="rx-how-head">How It Works</h2>
        </div>
        <div class="rx-timeline">
            <?php
            $steps = [
                'Calibrated cameras capture the pitch from multiple angles',
                'Computer vision detects players, ball, and key events',
                'Tracking fuses views into unified positional data',
                'Digital twin reconstructs live match state',
                'AI evaluates incidents against rule models',
                'Decision fusion aggregates multi-camera confidence',
                'Referees receive explainable alerts and replay cues',
            ];
            foreach ($steps as $step):
            ?>
            <div class="rx-timeline__step" data-rx-timeline-step>
                <div class="rx-timeline__dot"></div>
                <h3><?= htmlspecialchars($step) ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Interactive Football Field -->
<section class="rx-section" id="rx-field" aria-labelledby="rx-field-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Demo</span>
            <h2 id="rx-field-head">Interactive Incident Demo</h2>
            <p>Select an incident type to see how RefereeX AI analyzes the play.</p>
        </div>
        <div class="rx-field-panel" data-rx-field data-reveal>
            <div class="rx-field">
                <div class="rx-field__lines"></div>
                <span class="rx-field__player" style="left:30%;top:40%"></span>
                <span class="rx-field__player" style="left:50%;top:35%"></span>
                <span class="rx-field__player rx-field__player--away" style="left:65%;top:50%"></span>
                <span class="rx-field__player rx-field__player--away" style="left:45%;top:60%"></span>
                <span class="rx-field__ball" data-rx-ball style="left:50%;top:50%"></span>
                <div class="rx-field__overlay" data-rx-field-overlay></div>
            </div>
            <div class="rx-field-controls" role="group" aria-label="Incident types">
                <?php foreach (['penalty', 'offside', 'handball', 'goal', 'foul'] as $inc): ?>
                <button type="button" class="rx-field-btn" data-rx-incident="<?= $inc ?>"><?= ucfirst($inc) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="rx-field-output" data-rx-field-output aria-live="polite">Click an incident button to simulate AI analysis.</div>
        </div>
    </div>
</section>

<!-- Multi-Camera Visualization -->
<section class="rx-section rx-section--dark" id="rx-cameras" aria-labelledby="rx-cam-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Coverage</span>
            <h2 id="rx-cam-head">Multi-Camera Visualization</h2>
            <p>Click a camera marker to explore field of view and positioning.</p>
        </div>
        <div data-reveal>
            <div class="rx-camera-viz" data-rx-cameras>
                <div class="rx-camera-viz__pitch"></div>
                <button type="button" class="rx-cam-marker" data-rx-cam="cam1" style="top:10%;left:15%">C1</button>
                <div class="rx-cam-cone" data-rx-cone="cam1" style="top:18%;left:18%;border-width:0 40px 120px 40px;border-color:transparent transparent rgba(59,130,246,0.3) transparent"></div>
                <button type="button" class="rx-cam-marker" data-rx-cam="cam2" style="top:10%;right:15%">C2</button>
                <div class="rx-cam-cone" data-rx-cone="cam2" style="top:18%;right:18%;border-width:0 40px 120px 40px;border-color:transparent transparent rgba(59,130,246,0.3) transparent"></div>
                <button type="button" class="rx-cam-marker" data-rx-cam="goal" style="bottom:20%;left:5%">G</button>
                <button type="button" class="rx-cam-marker" data-rx-cam="drone" style="top:5%;left:50%;transform:translateX(-50%)">D</button>
                <button type="button" class="rx-cam-marker" data-rx-cam="broadcast" style="bottom:5%;right:10%">BC</button>
            </div>
            <div class="rx-cam-info" data-rx-cam-info>Select a camera to view coverage details.</div>
        </div>
    </div>
</section>

<!-- AI Decision Fusion -->
<section class="rx-section" id="rx-fusion" aria-labelledby="rx-fusion-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Intelligence</span>
            <h2 id="rx-fusion-head">Decision Fusion Engine</h2>
        </div>
        <div class="rx-fusion" data-rx-fusion data-reveal>
            <div class="rx-fusion__inputs">
                <div class="rx-glass rx-fusion__cam">
                    <div class="rx-fusion__cam-decision">Camera 1 — Penalty</div>
                    <div class="rx-fusion__cam-conf">92%</div>
                </div>
                <div class="rx-glass rx-fusion__cam" style="transition-delay:0.15s">
                    <div class="rx-fusion__cam-decision">Camera 2 — Penalty</div>
                    <div class="rx-fusion__cam-conf">89%</div>
                </div>
                <div class="rx-glass rx-fusion__cam" style="transition-delay:0.3s">
                    <div class="rx-fusion__cam-decision">Camera 3 — No Penalty</div>
                    <div class="rx-fusion__cam-conf">43%</div>
                </div>
            </div>
            <div class="rx-fusion__center">AI<br>Fusion</div>
            <div class="rx-glass rx-fusion__result">
                <div class="rx-fusion__result-label">Final Decision</div>
                <div class="rx-fusion__result-value">Penalty — 95%</div>
            </div>
        </div>
    </div>
</section>

<!-- Digital Twin -->
<section class="rx-section rx-section--dark" id="rx-twin" aria-labelledby="rx-twin-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Simulation</span>
            <h2 id="rx-twin-head">Digital Twin Layer</h2>
            <p>Every player, ball, and official mirrored in a live virtual model.</p>
        </div>
        <div class="rx-twin-viz" data-reveal>
            <div class="rx-twin-grid">
                <?php for ($p = 1; $p <= 8; $p++): ?>
                <div class="rx-twin-entity"><div class="rx-twin-entity__dot"></div>Player <?= $p ?> Twin</div>
                <?php endfor; ?>
                <div class="rx-twin-entity"><div class="rx-twin-entity__dot"></div>Ball Twin</div>
                <div class="rx-twin-entity"><div class="rx-twin-entity__dot"></div>Referee Twin</div>
            </div>
            <p class="small muted mb-1">Live sync timeline</p>
            <div class="rx-twin-timeline" data-rx-twin-timeline>
                <?php for ($s = 0; $s < 12; $s++): ?>
                <div class="rx-twin-timeline__seg" data-rx-twin-seg></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

<!-- Explainable AI -->
<section class="rx-section" id="rx-explain" aria-labelledby="rx-explain-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Transparency</span>
            <h2 id="rx-explain-head">Explainable AI</h2>
        </div>
        <div class="rx-glass rx-explain" data-rx-explain data-reveal>
            <div class="rx-explain__incident">Penalty — Handball in the box</div>
            <ul class="rx-explain__list">
                <li>Ball trajectory intersects defender arm at 67′</li>
                <li>Arm extended beyond natural silhouette (Camera 2 + Goal Cam)</li>
                <li>Contact alters ball path by 12° — above noise threshold</li>
                <li>Digital twin confirms hand-ball contact point inside penalty area</li>
            </ul>
            <div class="rx-explain__conf">94% confidence · 3 cameras · Review advised</div>
        </div>
    </div>
</section>

<!-- Dashboard Preview -->
<section class="rx-section rx-section--dark" id="rx-dashboard" aria-labelledby="rx-dash-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Operations</span>
            <h2 id="rx-dash-head">Referee Dashboard</h2>
        </div>
        <div class="rx-glass rx-dashboard" data-rx-dashboard data-reveal>
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
                ['label' => 'Referee Performance', 'value' => 'A+', 'w' => 92],
                ['label' => 'Integrity Score', 'value' => '98/100', 'w' => 98],
            ];
            foreach ($widgets as $w):
            ?>
            <div class="rx-dash-widget<?= !empty($w['wide']) ? ' rx-dash-widget--wide' : '' ?>">
                <div class="rx-dash-widget__label"><?= htmlspecialchars($w['label']) ?></div>
                <div class="rx-dash-widget__value"><?= htmlspecialchars($w['value']) ?></div>
                <?php if (!empty($w['w'])): ?>
                <div class="rx-dash-widget__bar"><div class="rx-dash-widget__bar-fill" style="--w:<?= (int)$w['w'] ?>%"></div></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="center mt-3" data-reveal>
            <a href="<?= htmlspecialchars($contactUrl) ?>?subject=RefereeX%20Dashboard" class="rx-btn rx-btn--primary">Request Dashboard Access</a>
        </div>
    </div>
</section>

<!-- Features -->
<section class="rx-section" id="rx-features" aria-labelledby="rx-features-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Capabilities</span>
            <h2 id="rx-features-head">Platform Features</h2>
        </div>
        <div class="rx-features">
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
            <div class="rx-glass rx-feature" data-reveal>
                <span class="rx-feature__icon"><?= $f[0] ?></span>
                <div><h3><?= htmlspecialchars($f[1]) ?></h3><p><?= htmlspecialchars($f[2]) ?></p></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Benefits -->
<section class="rx-section rx-section--dark" id="rx-benefits" aria-labelledby="rx-benefits-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Comparison</span>
            <h2 id="rx-benefits-head">Why RefereeX AI?</h2>
        </div>
        <div class="rx-compare" data-reveal>
            <div class="rx-glass rx-compare__col rx-compare__col--old">
                <h3 class="center mb-2">Traditional Officiating</h3>
                <?php foreach (['Single Camera', 'Manual Review', 'No Explainability', 'Static Replay'] as $row): ?>
                <div class="rx-compare__row"><span><?= htmlspecialchars($row) ?></span></div>
                <?php endforeach; ?>
            </div>
            <div class="rx-compare__vs">vs</div>
            <div class="rx-glass rx-compare__col rx-compare__col--new">
                <h3 class="center mb-2">RefereeX AI</h3>
                <?php foreach (['Multi-Camera', 'AI Decision Support', 'Explainable AI', 'Digital Twin'] as $row): ?>
                <div class="rx-compare__row"><span><?= htmlspecialchars($row) ?></span><span style="color:var(--rx-green)">✓</span></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Use Cases -->
<section class="rx-section" id="rx-usecases" aria-labelledby="rx-use-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Applications</span>
            <h2 id="rx-use-head">Use Cases</h2>
        </div>
        <div class="rx-use-grid">
            <?php
            $uses = [
                ['⚽', 'Professional Football'], ['🎓', 'Youth Academies'], ['🏟️', 'Regional Leagues'],
                ['🏛️', 'Federations'], ['📺', 'Broadcasters'], ['🎥', 'VAR Support'],
                ['📊', 'Sports Analytics'], ['🏋️', 'Training Centers'], ['🛡️', 'Integrity Monitoring'],
            ];
            foreach ($uses as $u):
            ?>
            <div class="rx-glass rx-use-card" data-reveal>
                <div class="rx-use-card__icon"><?= $u[0] ?></div>
                <h3><?= htmlspecialchars($u[1]) ?></h3>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Video -->
<section class="rx-section rx-section--dark" id="rx-video" aria-labelledby="rx-video-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Overview</span>
            <h2 id="rx-video-head">Product Video</h2>
        </div>
        <div class="rx-video-wrap" data-rx-video data-reveal>
            <iframe title="RefereeX AI Product Video" data-src="https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&rel=0&modestbranding=1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
            <div class="rx-video-play" data-rx-video-play role="button" tabindex="0" aria-label="Watch Overview">
                <div class="rx-video-play__btn">▶</div>
            </div>
        </div>
    </div>
</section>

<!-- Research -->
<section class="rx-section" id="rx-research" aria-labelledby="rx-research-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">Research</span>
            <h2 id="rx-research-head">Research Stack</h2>
        </div>
        <div class="rx-research" data-reveal>
            <?php
            $research = ['Computer Vision', 'Decision Fusion', 'Physical AI', 'Digital Twin', 'Explainable AI', 'Referee Intelligence'];
            foreach ($research as $i => $r):
                if ($i > 0): ?><span class="rx-research__arrow" aria-hidden="true">↓</span><?php endif; ?>
            <div class="rx-glass rx-research__item"><?= htmlspecialchars($r) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="rx-section rx-section--dark" id="rx-faq" aria-labelledby="rx-faq-head">
    <div class="rx-wrap">
        <div class="rx-head rx-head--center" data-reveal>
            <span class="rx-eyebrow">FAQ</span>
            <h2 id="rx-faq-head">Frequently Asked Questions</h2>
        </div>
        <div class="rx-faq" data-reveal>
            <?php
            $faqs = [
                ['q' => 'Does RefereeX replace human referees?', 'a' => 'No. RefereeX AI assists officials with real-time intelligence while preserving human authority on the pitch.'],
                ['q' => 'What latency can leagues expect?', 'a' => 'Fusion pipelines target sub-100ms decision support for live officiating workflows.'],
                ['q' => 'How many cameras are supported?', 'a' => 'The platform scales from 4 to 16+ calibrated camera feeds per venue.'],
                ['q' => 'Is it VAR-compatible?', 'a' => 'Yes. RefereeX integrates with VAR rooms and broadcast replay systems.'],
                ['q' => 'Can we run a pilot league?', 'a' => 'Pilot programs are available with deployment support and analytics dashboards.'],
                ['q' => 'Is decision logic explainable?', 'a' => 'Every recommendation includes visual evidence trails and confidence scoring.'],
            ];
            foreach ($faqs as $faq):
            ?>
            <div class="rx-faq__item" data-rx-faq-item>
                <button type="button" class="rx-faq__btn" data-rx-faq-btn aria-expanded="false">
                    <?= htmlspecialchars($faq['q']) ?>
                    <span class="rx-faq__icon" aria-hidden="true">+</span>
                </button>
                <div class="rx-faq__panel"><p><?= htmlspecialchars($faq['a']) ?></p></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="rx-cta" id="rx-cta" aria-labelledby="rx-cta-head">
    <div class="rx-cta__bg" aria-hidden="true"></div>
    <div class="rx-wrap rx-cta__content">
        <h2 id="rx-cta-head" data-reveal>Ready to transform officiating?</h2>
        <p data-reveal>Partner with Sportify to deploy RefereeX AI in your league or broadcast stack.</p>
        <div class="rx-btns" style="justify-content:center" data-reveal>
            <a href="<?= htmlspecialchars($contactUrl) ?>?subject=RefereeX%20Demo" class="rx-btn rx-btn--primary">Request Demo</a>
            <a href="<?= htmlspecialchars($contactUrl) ?>?subject=RefereeX%20Research%20Partner" class="rx-btn rx-btn--ghost">Research Partnership</a>
            <a href="<?= htmlspecialchars($contactUrl) ?>?subject=RefereeX%20Pilot%20League" class="rx-btn rx-btn--green">Pilot League</a>
            <a href="<?= htmlspecialchars($contactUrl) ?>" class="rx-btn rx-btn--ghost">Contact Us</a>
        </div>
    </div>
</section>

<!-- Page Footer -->
<footer class="rx-page-footer" aria-label="RefereeX footer">
    <div class="rx-wrap">
        <div class="rx-page-footer__grid">
            <div>
                <h4>Quick Links</h4>
                <a href="#rx-what">What is RefereeX AI?</a>
                <a href="#rx-tech">Core Technologies</a>
                <a href="#rx-dashboard">Referee Dashboard</a>
                <a href="#rx-faq">FAQ</a>
            </div>
            <div>
                <h4>Research</h4>
                <a href="#rx-research">Research Stack</a>
                <a href="#rx-architecture">System Architecture</a>
            </div>
            <div>
                <h4>Technology</h4>
                <a href="#rx-tech">Core Technologies</a>
                <a href="#rx-fusion">Decision Fusion</a>
                <a href="#rx-twin">Digital Twin</a>
            </div>
            <div>
                <h4>Contact</h4>
                <a href="<?= htmlspecialchars($contactUrl) ?>">Contact</a>
                <a href="https://linkedin.com/company/sportify" target="_blank" rel="noopener">LinkedIn</a>
                <a href="https://github.com/sportify" target="_blank" rel="noopener">GitHub</a>
                <a href="https://munich-tech-expo.com" target="_blank" rel="noopener">MunichTech EXPO</a>
            </div>
            <div>
                <h4>Newsletter</h4>
                <p class="small muted">Get RefereeX updates and research notes.</p>
                <form class="rx-newsletter" data-rx-newsletter>
                    <input type="email" placeholder="your@email.com" required aria-label="Email">
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
        <p class="small muted center">© <?= $year ?? date('Y') ?> Sportify · RefereeX AI · <a href="https://sportifyplus.de/" style="color:inherit">Home</a></p>
    </div>
</footer>
