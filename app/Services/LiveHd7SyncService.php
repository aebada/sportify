<?php

namespace App\Services;

/**
 * Fetches today's match listings from livehd7top.com and caches parsed stream links.
 * Run via: php sportify livehd7-sync
 */
class LiveHd7SyncService
{
    public const SOURCE_URL = 'https://www.livehd7top.com/matches-today/';

    public const CACHE_TTL = 1800;

    /** Max seconds to wait for LiveHD7 when rendering /live-streams. */
    public const PAGE_FETCH_TIMEOUT = 8;

    protected static bool $pageRefreshDone = false;

    /** @var list<string> */
    protected const TRUSTED_HOSTS = [
        'www.livehd7top.com',
        'livehd7top.com',
        'www.livehd7sport.com',
        'livehd7sport.com',
    ];

    public static function cachePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/cache/livehd7-today.json';
    }

    public static function publicJsonPath(): string
    {
        return dirname(__DIR__, 2) . '/public/data/livehd7-today.json';
    }

    /**
     * Pull fresh LiveHD7 listings on each web request (deduped via lock + static guard).
     */
    public static function ensureFreshForPage(): void
    {
        if (PHP_SAPI !== 'cli') {
            $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            if (!empty($_GET['refereex']) || $uriPath === '/refereex-ai' || str_starts_with($uriPath, '/refereex-ai/')) {
                header('Content-Type: text/html; charset=UTF-8');
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('X-Sportify-RefereeX: livehd7-bridge-v20260705');
                echo (new \App\Controllers\RefereeXController())->index();
                exit;
            }
        }

        if (PHP_SAPI === 'cli' || self::$pageRefreshDone) {
            return;
        }

        self::$pageRefreshDone = true;
        self::refreshForPageLoad();
    }

    /**
     * @return array{source:string,fetched_at:int,date:string,match_count:int,matches:list<array<string,mixed>>,error?:string}
     */
    public static function refreshForPageLoad(): array
    {
        $lockPath = dirname(__DIR__, 2) . '/storage/cache/livehd7-sync.lock';
        $dir = dirname($lockPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $lock = @fopen($lockPath, 'c+');
        if ($lock === false) {
            return self::refresh(self::PAGE_FETCH_TIMEOUT);
        }

        $exclusive = flock($lock, LOCK_EX | LOCK_NB);
        if (!$exclusive) {
            $waitUntil = microtime(true) + (float) self::PAGE_FETCH_TIMEOUT + 2.0;
            while (microtime(true) < $waitUntil) {
                usleep(100_000);
                if (!is_file($lockPath)) {
                    break;
                }
                $mtime = @filemtime($lockPath);
                if ($mtime !== false && (time() - $mtime) <= 2) {
                    break;
                }
            }
            flock($lock, LOCK_UN);
            fclose($lock);

            return self::load();
        }

        try {
            return self::refresh(self::PAGE_FETCH_TIMEOUT);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * @return array{source:string,fetched_at:int,date:string,match_count:int,matches:list<array<string,mixed>>,error?:string}
     */
    public static function refresh(?int $timeoutSeconds = null): array
    {
        $html = self::fetchPage($timeoutSeconds ?? 15);
        if ($html === null) {
            $stale = self::load();
            $stale['error'] = 'fetch_failed';
            return $stale;
        }

        $matches = self::parseHtml($html);
        $payload = [
            'source'       => self::SOURCE_URL,
            'fetched_at'   => time(),
            'date'         => date('Y-m-d'),
            'match_count'  => count($matches),
            'matches'      => $matches,
        ];

        self::writeCache($payload);

        return $payload;
    }

    /**
     * @return array{source:string,fetched_at:int,date?:string,match_count?:int,matches:list<array<string,mixed>>}
     */
    public static function load(): array
    {
        foreach ([self::cachePath(), self::publicJsonPath()] as $path) {
            if (!is_file($path)) {
                continue;
            }
            $data = json_decode((string) file_get_contents($path), true);
            if (is_array($data) && !empty($data['matches'])) {
                return $data;
            }
        }

        return [
            'source'     => self::SOURCE_URL,
            'fetched_at' => 0,
            'matches'    => [],
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function matchesForToday(): array
    {
        self::ensureFreshForPage();

        $data = self::load();
        $matches = $data['matches'] ?? [];
        if (!is_array($matches)) {
            $matches = [];
        }

        $today = date('Y-m-d');
        $cacheFresh = ($data['date'] ?? '') === $today && $matches !== [];

        if (!$cacheFresh) {
            $seed = self::seedData();
            if (!empty($seed['matches']) && is_array($seed['matches'])) {
                return array_values(array_filter($seed['matches'], static fn ($m) => is_array($m)));
            }
        }

        return array_values(array_filter($matches, static fn ($m) => is_array($m)));
    }

    /**
     * @return array<string,mixed>
     */
    protected static function seedData(): array
    {
        $path = dirname(__DIR__, 2) . '/config/livehd7_today_seed.php';

        return is_file($path) ? (require $path) : [];
    }

    public static function lastFetchedAt(): ?int
    {
        $data = self::load();
        $ts = (int) ($data['fetched_at'] ?? 0);

        return $ts > 0 ? $ts : null;
    }

    public static function sourceLabel(): string
    {
        return 'LiveHD7';
    }

    /**
     * Find today's best LiveHD7 stream row for a Sportify fixture (team-name match).
     *
     * @return array<string,mixed>|null
     */
    public static function matchForFixture(array $fixture): ?array
    {
        $home = (string) ($fixture['home_team'] ?? '');
        $away = (string) ($fixture['away_team'] ?? '');
        if ($home === '' || $away === '') {
            return null;
        }

        $kickoffTs = strtotime((string) ($fixture['kickoff_at'] ?? '')) ?: null;
        $best = null;
        $bestScore = -1;

        foreach (self::matchesForToday() as $row) {
            if (!is_array($row)) {
                continue;
            }
            if (!self::fixtureTeamsMatch($home, $away, (string) ($row['home'] ?? ''), (string) ($row['away'] ?? ''))) {
                continue;
            }

            $score = 0;
            $streamUrl = trim((string) ($row['stream_url'] ?? ''));
            if ($streamUrl !== '') {
                $score += 100;
            }
            if (($row['status'] ?? '') === 'live') {
                $score += 50;
            } elseif (($row['status'] ?? '') === 'upcoming') {
                $score += 20;
            }

            if ($kickoffTs && !empty($row['kickoff']) && preg_match('/^(\d{1,2}):(\d{2})$/', (string) $row['kickoff'], $m)) {
                $rowTs = strtotime(date('Y-m-d', $kickoffTs) . sprintf(' %02d:%02d:00', (int) $m[1], (int) $m[2]));
                if ($rowTs) {
                    $delta = abs($rowTs - $kickoffTs);
                    if ($delta <= 7200) {
                        $score += max(0, 30 - (int) floor($delta / 600));
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }

        return $best;
    }

    public static function streamUrlForFixture(array $fixture): ?string
    {
        $match = self::matchForFixture($fixture);
        if (!$match) {
            return null;
        }

        $url = trim((string) ($match['stream_url'] ?? ''));

        return $url !== '' ? $url : null;
    }

    public static function teamNameMatches(string $a, string $b): bool
    {
        $aNorm = self::normalizeTeamToken($a);
        $bNorm = self::normalizeTeamToken($b);
        if ($aNorm === '' || $bNorm === '') {
            return false;
        }
        if ($aNorm === $bNorm) {
            return true;
        }
        if (str_contains($aNorm, $bNorm) || str_contains($bNorm, $aNorm)) {
            return true;
        }

        foreach (self::teamAliasGroups() as $group) {
            $tokens = array_map([self::class, 'normalizeTeamToken'], $group);
            if (in_array($aNorm, $tokens, true) && in_array($bNorm, $tokens, true)) {
                return true;
            }
        }

        return false;
    }

    protected static function fixtureTeamsMatch(string $homeA, string $awayA, string $homeB, string $awayB): bool
    {
        return self::teamNameMatches($homeA, $homeB) && self::teamNameMatches($awayA, $awayB);
    }

    protected static function normalizeTeamToken(string $name): string
    {
        $name = html_entity_decode(trim($name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $name = mb_strtolower($name);
        $name = preg_replace('/[^\p{L}\p{N}\s]/u', '', $name) ?? $name;
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return trim($name);
    }

    /**
     * @return list<list<string>>
     */
    protected static function teamAliasGroups(): array
    {
        return [
            ['Tunisia', 'تونس'],
            ['Japan', 'اليابان'],
            ['Spain', 'إسبانيا'],
            ['Saudi Arabia', 'السعودية'],
            ['Belgium', 'بلجيكا'],
            ['Iran', 'إيران'],
            ['Germany', 'ألمانيا', 'Germany'],
            ['Ivory Coast', "Côte d'Ivoire", 'Cote d Ivoire', 'كوت ديفوار'],
            ['Ecuador', 'الإكوادور'],
            ['Curacao', 'Curaçao', 'كوراساو'],
            ['United States', 'USA', 'US', 'الولايات المتحدة'],
            ['Australia', 'أستراليا'],
            ['Canada', 'كندا'],
            ['Qatar', 'قطر'],
            ['Mexico', 'المكسيك'],
            ['South Korea', 'Korea Republic', 'كوريا الجنوبية'],
            ['Morocco', 'المغرب'],
            ['France', 'فرنسا'],
            ['England', 'إنجلترا'],
            ['Brazil', 'البرازيل'],
            ['Argentina', 'الأرجنتين'],
            ['Portugal', 'البرتغال'],
            ['Netherlands', 'هولندا'],
            ['Italy', 'إيطاليا'],
            ['Croatia', 'كرواتيا'],
            ['Uruguay', 'أوروغواي'],
            ['Colombia', 'كولومبيا'],
            ['Switzerland', 'سويسرا'],
            ['Senegal', 'السنغال'],
            ['Denmark', 'الدنمارك'],
            ['Serbia', 'صربيا'],
            ['Cameroon', 'الكاميرون'],
            ['Costa Rica', 'كosta Rica'],
            ['Poland', 'بولندا'],
            ['Ghana', 'غانا'],
            ['Wales', 'ويلز'],
            ['Scotland', 'اسكتلندا'],
            ['Norway', 'النرويج'],
            ['Iraq', 'العراق'],
        ];
    }

    protected static function fetchPage(int $timeoutSeconds = 15): ?string
    {
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'timeout'       => max(3, $timeoutSeconds),
                'header'        => implode("\r\n", [
                    'User-Agent: Mozilla/5.0 (compatible; SportifyBot/1.0; +https://sportifyplus.de)',
                    'Accept: text/html,application/xhtml+xml',
                    'Accept-Language: en-US,en;q=0.9,ar;q=0.8',
                ]),
            ],
        ]);

        $html = @file_get_contents(self::SOURCE_URL, false, $ctx);
        if (!is_string($html) || strlen($html) < 500) {
            return null;
        }

        return $html;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public static function parseHtml(string $html): array
    {
        $matches = [];
        $chunks = preg_split('/<div class="AY_Match\s+([^"]+)">/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($chunks) || count($chunks) < 3) {
            return [];
        }

        for ($i = 1; $i < count($chunks); $i += 2) {
            $statusClass = trim((string) ($chunks[$i] ?? ''));
            $block = (string) ($chunks[$i + 1] ?? '');
            if ($block === '') {
                continue;
            }

            $parsed = self::parseMatchBlock($statusClass, $block);
            if ($parsed !== null) {
                $matches[] = $parsed;
            }
        }

        return $matches;
    }

    /**
     * @return array<string,mixed>|null
     */
    protected static function parseMatchBlock(string $statusClass, string $block): ?array
    {
        if (!preg_match_all('/<div class="TM_Name">([^<]+)<\/div>/u', $block, $teamMatches)) {
            return null;
        }

        $teams = array_map(static fn ($t) => html_entity_decode(trim($t), ENT_QUOTES | ENT_HTML5, 'UTF-8'), $teamMatches[1]);
        if (count($teams) < 2) {
            return null;
        }

        $home = $teams[0];
        $away = $teams[1];

        $kickoff = '';
        if (preg_match('/<span class=[\'"]MT_Time[\'"]>([^<]+)<\/span>/u', $block, $timeMatch)) {
            $kickoff = trim(html_entity_decode($timeMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $statusText = '';
        if (preg_match('/<div class=[\'"]MT_Stat[\'"]>([^<]+)<\/div>/u', $block, $statMatch)) {
            $statusText = trim(html_entity_decode($statMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $scoreHome = null;
        $scoreAway = null;
        if (preg_match('/<span class="MT_Result"><span class="RS-goals">(\d+)<\/span><span>-<\/span><span class="RS-goals">(\d+)<\/span><\/span>/u', $block, $scoreMatch)) {
            $scoreHome = (int) $scoreMatch[1];
            $scoreAway = (int) $scoreMatch[2];
        }

        $league = '';
        if (preg_match('/<div class="MT_Info"><ul>(.*?)<\/ul><\/div>/us', $block, $infoMatch)) {
            if (preg_match_all('/<li><span>([^<]+)<\/span><\/li>/u', $infoMatch[1], $infoItems)) {
                $items = array_map(static fn ($item) => trim(html_entity_decode($item, ENT_QUOTES | ENT_HTML5, 'UTF-8')), $infoItems[1]);
                $league = (string) (end($items) ?: '');
            }
        }

        $streamUrl = '';
        if (preg_match('/<div class="MT_Info">.*?<\/div>\s*<a\s+href="([^"]+)"/us', $block, $linkMatch)) {
            $streamUrl = self::normalizeStreamUrl(html_entity_decode($linkMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $status = self::mapStatus($statusClass, $statusText);

        return [
            'home'        => $home,
            'away'        => $away,
            'title'       => $home . ' vs ' . $away,
            'kickoff'     => $kickoff,
            'league'      => $league,
            'status'      => $status,
            'status_text' => $statusText,
            'score_home'  => $scoreHome,
            'score_away'  => $scoreAway,
            'stream_url'  => $streamUrl,
        ];
    }

    protected static function normalizeStreamUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return '';
        }

        if (str_starts_with($url, '/')) {
            $url = 'https://www.livehd7top.com' . $url;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || !in_array(strtolower($host), self::TRUSTED_HOSTS, true)) {
            return '';
        }

        return $url;
    }

    protected static function mapStatus(string $statusClass, string $statusText): string
    {
        $class = strtolower($statusClass);
        if (str_contains($class, 'live') || str_contains($class, 'playing')) {
            return 'live';
        }
        if (str_contains($class, 'finished') || str_contains($class, 'ended')) {
            return 'finished';
        }

        $text = mb_strtolower($statusText);
        if (str_contains($text, 'مباشر') || str_contains($text, 'live')) {
            return 'live';
        }
        if (str_contains($text, 'انته') || str_contains($text, 'finished') || str_contains($text, 'ended')) {
            return 'finished';
        }

        return 'upcoming';
    }

    /**
     * @param array<string,mixed> $payload
     */
    protected static function writeCache(array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) {
            return;
        }

        foreach ([self::cachePath(), self::publicJsonPath()] as $path) {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            file_put_contents($path, $json);
        }
    }
}
