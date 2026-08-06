<?php
declare(strict_types=1);
/**
 * Site-wide output buffer — trust club logos + match flag hotfix (bypasses stale OPcache/CDN).
 * RefereeX early handler (2026-07-04) — runs before ob_start when OPcache is stale.
 */
if (PHP_SAPI === 'cli') {
    return;
}

if (!defined('SPORTIFY_START')) {
    $__p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($__p === '/refereex-ai' || str_starts_with($__p, '/refereex-ai/')) {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store');
        header('X-Sportify-RefereeX: prepend-20260705-public');
        require dirname(__DIR__) . '/bootstrap-20260621-authfix.php';
        echo (new \App\Controllers\RefereeXController())->index();
        exit;
    }
}

$__sportifyOtHost = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')) ?: '');
if (in_array($__sportifyOtHost, ['talents.sportifyplus.de', 'onlytalents.sportifyplus.de'], true)) {
    $otUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if ($otUri === '/' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && empty($_GET)) {
        header('Location: https://sportifyplus.de/only-talents/home', true, 302);
        header('Cache-Control: no-store');
        exit;
    }
    $otRoot = '/home/u234903558/domains/sportifyplus.de/public_html/talents';
    if (is_file($otRoot . '/app/Core/Autoloader.php')) {
        require_once $otRoot . '/app/Core/Autoloader.php';
        \App\Core\Autoloader::register($otRoot . '/app');
    }
    if (is_file($otRoot . '/app/Support/ot_helpers_hotfix.php')) {
        require_once $otRoot . '/app/Support/ot_helpers_hotfix.php';
    }
    return;
}


ob_start(static function (string $buffer): string {
    if (!str_contains($buffer, '</body>')) {
        return $buffer;
    }

    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');

    if (preg_match('#/(matches|fixtures)(/|$|\?)#', $uri)) {
        $tag = '<script src="/assets/js/flag-fix-20260619.js?v=20260619" defer></script>';
        if (!str_contains($buffer, 'flag-fix-20260619.js') && !str_contains($buffer, $tag)) {
            $buffer = str_replace('</body>', $tag . '</body>', $buffer);
        }
    }

    if (preg_match('#^/matches/(\d+)(?:\?|$)#', $uri, $matchWatch)) {
        $buffer = sportify_match_watch_buffer_fix($buffer, (int) $matchWatch[1]);
    }

    if (
        str_contains($buffer, 'trust--spec')
        && str_contains($buffer, '<b>FC Atlas</b>')
        && !str_contains($buffer, 'trust-logos__badge')
    ) {
        $buffer = (string) preg_replace(
            '#<div class="logos">\s*(?:<b>[^<]+</b>\s*)+</div>#',
            trust_logos_markup(),
            $buffer,
            1
        );

        if (str_contains($buffer, 'trust-logos__badge') && !str_contains($buffer, 'sportify-trust-logos-style')) {
            $buffer = str_replace('</head>', trust_logos_inline_style() . '</head>', $buffer);
        } elseif (!str_contains($buffer, 'trust-logos__badge')) {
            $css = '<link rel="stylesheet" href="/assets/css/trust-logos-legacy-20260621.css?v=20260621">';
            if (!str_contains($buffer, 'trust-logos-legacy-20260621.css')) {
                $buffer = str_replace('</head>', $css . '</head>', $buffer);
            }
            if (!str_contains($buffer, 'sportify-trust-logos-inline')) {
                $buffer = str_replace('</body>', trust_logos_inline_script() . '</body>', $buffer);
            }
        }
    }

    return $buffer;
});

function sportify_match_watch_buffer_fix(string $buffer, int $fixtureId): string
{
    if (!str_contains($buffer, 'match-detail-head') || !str_contains($buffer, 'Watch live')) {
        return $buffer;
    }

    $buffer = str_replace('</body>', '<!-- match-watch-prepend-v2 --></body>', $buffer);

    $root = is_file(__DIR__ . '/bootstrap.php') ? __DIR__ : dirname(__DIR__);
    foreach ([
        $root . '/app/Services/MatchWatchService.php',
        $root . '/app/Services/LiveHd7SyncService.php',
        $root . '/config/live_streams.php',
    ] as $file) {
        if (function_exists('opcache_invalidate') && is_file($file)) {
            @opcache_invalidate(realpath($file) ?: $file, true);
        }
    }

    try {
        if (!class_exists(\App\Models\FixtureRepository::class, false)) {
            return $buffer;
        }
        $fixture = \App\Models\FixtureRepository::find($fixtureId);
        if (!$fixture) {
            return $buffer;
        }
        $watch = \App\Services\MatchWatchService::forFixture($fixture);
        $streamUrl = trim((string) ($watch['primary_free_stream']['url'] ?? ''));
        $matchPath = '/matches/' . $fixtureId;
        $cta = htmlspecialchars(__('matches.watch.cta'), ENT_QUOTES, 'UTF-8');
        $noStream = htmlspecialchars(__('matches.watch.no_stream'), ENT_QUOTES, 'UTF-8');

        if ($streamUrl !== '') {
            $safeUrl = htmlspecialchars($streamUrl, ENT_QUOTES, 'UTF-8');
            $replacement = '<a href="' . $safeUrl . '" class="btn btn-primary btn-sm" target="_blank" rel="noopener noreferrer">▶ ' . $cta . '</a>';
            $buffer = (string) preg_replace(
                '#<a href="' . preg_quote($matchPath, '#') . '\?tab=watch" class="btn btn-primary btn-sm">▶ Watch live</a>#',
                $replacement,
                $buffer,
                1
            );
            $buffer = (string) preg_replace(
                '#<a href="' . preg_quote($matchPath, '#') . '\?tab=watch" class="btn btn-ghost btn-sm">Watch live →</a>#',
                '<a href="' . $safeUrl . '" class="btn btn-primary btn-sm" target="_blank" rel="noopener noreferrer">▶ ' . $cta . '</a>',
                $buffer,
                1
            );
            if (!str_contains($buffer, 'match-watch-stream-card') && str_contains($buffer, 'match-watch-teaser')) {
                $card = '<div class="card mb-3 match-watch-stream-card"><div class="card__body">'
                    . '<h3 class="h6 mb-2">' . htmlspecialchars(__('matches.watch.free_stream'), ENT_QUOTES, 'UTF-8') . '</h3>'
                    . '<p class="muted small mb-2">' . htmlspecialchars(__('matches.watch.free_fallback_note'), ENT_QUOTES, 'UTF-8') . '</p>'
                    . '<a href="' . $safeUrl . '" class="btn btn-primary btn-sm" target="_blank" rel="noopener noreferrer">▶ ' . $cta . '</a>'
                    . '</div></div>';
                $buffer = (string) preg_replace(
                    '#(<div class="card mb-3 match-watch-teaser">)#',
                    $card . '$1',
                    $buffer,
                    1
                );
            }
            return $buffer;
        }

        if (empty($watch['has_broadcast'])) {
            $buffer = (string) preg_replace(
                '#<p class="center mt-3 mb-0">\s*<a href="' . preg_quote($matchPath, '#') . '\?tab=watch" class="btn btn-primary btn-sm">▶ Watch live</a>\s*</p>#',
                '<p class="center mt-3 mb-0 muted small">' . $noStream . '</p>',
                $buffer,
                1
            );
        }
    } catch (\Throwable $e) {
        return $buffer;
    }

    return $buffer;
}

function trust_logos_markup(): string
{
    $clubs = [
        ['name' => 'FC Atlas', 'file' => 'fc-atlas.svg'],
        ['name' => 'Lagos City', 'file' => 'lagos-city.svg'],
        ['name' => 'Osaka Future', 'file' => 'osaka-future.svg'],
        ['name' => 'Norrland Utd', 'file' => 'norrland-utd.svg'],
        ['name' => 'Porto Litoral', 'file' => 'porto-litoral.svg'],
        ['name' => 'Cairo Nike', 'file' => 'cairo-nike.svg'],
    ];

    $items = '';
    foreach ($clubs as $club) {
        $src = trust_logo_data_uri($club['file']);
        $name = htmlspecialchars($club['name'], ENT_QUOTES, 'UTF-8');
        $items .= <<<HTML
                <span class="trust-logos__item" role="listitem">
                    <img src="{$src}" alt="{$name}" width="48" height="48" loading="lazy" decoding="async" class="trust-logos__badge">
                </span>

HTML;
    }

    return <<<HTML
<div class="logos trust-logos" role="list" aria-label="Trusted by clubs and scouts worldwide">
{$items}        </div>
HTML;
}

function trust_logo_data_uri(string $file): string
{
    static $cache = [];
    if (isset($cache[$file])) {
        return $cache[$file];
    }

    $paths = [
        __DIR__ . '/assets/img/clubs/trusted/' . $file,
        __DIR__ . '/public/assets/img/clubs/trusted/' . $file,
        dirname(__DIR__) . '/assets/img/clubs/trusted/' . $file,
        dirname(__DIR__) . '/public/assets/img/clubs/trusted/' . $file,
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            $svg = trim((string) file_get_contents($path));
            if ($svg !== '') {
                return $cache[$file] = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }
    }

    return $cache[$file] = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjAgMTIwIj48Y2lyY2xlIGN4PSI2MCIgY3k9IjYwIiByPSI0MCIgZmlsbD0iIzI1NjNlYiIvPjwvc3ZnPg==';
}

function trust_logos_inline_style(): string
{
    return <<<'HTML'
<style id="sportify-trust-logos-style">
.trust-logos{display:flex;flex-wrap:wrap;align-items:center;gap:clamp(20px,4vw,36px)}
.trust-logos__item{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto}
.trust-logos__badge{width:auto;height:clamp(40px,5vw,52px);max-width:52px;object-fit:contain;opacity:.82;filter:grayscale(.15) brightness(1.05);transition:opacity .2s ease,filter .2s ease,transform .2s ease}
.trust-logos__badge:hover{opacity:1;filter:none;transform:translateY(-1px)}
@media (max-width:640px){.trust-logos{width:100%;justify-content:space-between;gap:16px 12px}.trust-logos__badge{height:40px;max-width:44px}}
</style>
HTML;
}

function trust_logos_inline_script(): string
{
    return <<<'HTML'
<script id="sportify-trust-logos-inline">
(function () {
  function initTrustLogos() {
    var root = document.querySelector('.trust--spec .logos');
    if (!root || root.classList.contains('trust-logos') || root.querySelector('img.trust-logos__badge')) {
      return;
    }
    var clubs = [
      { name: 'FC Atlas', initials: 'FA', kit: ['#059669', '#ffffff', '#1a1a1a'] },
      { name: 'Lagos City', initials: 'LC', kit: ['#059669', '#ffffff', '#1a1a1a'] },
      { name: 'Osaka Future', initials: 'OF', kit: ['#2563eb', '#ffffff', '#dc2626'] },
      { name: 'Norrland Utd', initials: 'NU', kit: ['#2563eb', '#facc15', '#ffffff'] },
      { name: 'Porto Litoral', initials: 'PL', kit: ['#2563eb', '#ffffff', '#1a1a1a'] },
      { name: 'Cairo Nike', initials: 'CN', kit: ['#dc2626', '#ffffff', '#facc15'] }
    ];
    function badgeUri(initials, kit) {
      var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">' +
        '<defs><linearGradient id="b" x1="0" y1="0" x2="1" y2="1">' +
        '<stop offset="0" stop-color="' + kit[0] + '"/><stop offset="1" stop-color="' + kit[2] + '"/></linearGradient></defs>' +
        '<path d="M60 6 L108 22 V64 C108 92 86 108 60 116 C34 108 12 92 12 64 V22 Z" fill="url(#b)" stroke="' + kit[1] + '" stroke-opacity="0.5" stroke-width="3"/>' +
        '<path d="M60 22 L92 32 V64 C92 84 78 96 60 102 C42 96 28 84 28 64 V32 Z" fill="none" stroke="' + kit[1] + '" stroke-opacity="0.3" stroke-width="2"/>' +
        '<circle cx="60" cy="48" r="4" fill="' + kit[1] + '"/>' +
        '<text x="60" y="84" font-family="Arial,sans-serif" font-size="30" font-weight="800" fill="' + kit[1] + '" text-anchor="middle">' + initials + '</text></svg>';
      return 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
    }
    root.classList.add('trust-logos');
    root.setAttribute('role', 'list');
    root.innerHTML = '';
    clubs.forEach(function (club) {
      var item = document.createElement('span');
      item.className = 'trust-logos__item';
      item.setAttribute('role', 'listitem');
      var img = document.createElement('img');
      img.src = badgeUri(club.initials, club.kit);
      img.alt = club.name;
      img.width = 48;
      img.height = 48;
      img.loading = 'lazy';
      img.decoding = 'async';
      img.className = 'trust-logos__badge';
      item.appendChild(img);
      root.appendChild(item);
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTrustLogos);
  } else {
    initTrustLogos();
  }
})();
</script>
HTML;
}
