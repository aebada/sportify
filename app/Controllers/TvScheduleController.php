<?php

namespace App\Controllers;

use App\Core\App;
use App\Core\Controller;
use App\Services\FixtureCalendarService;
use App\Services\LiveHd7SyncService;
use App\Services\TvBroadcastFeedService;

class TvScheduleController extends Controller
{
    public function index(): string
    {
        $tab = (string) App::$request->input('tab', '');
        if ($tab === 'live') {
            return $this->liveStreams();
        }

        $date = App::$request->input('date', date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        return $this->view('pages.tv.index', [
            'title'       => __('kooora.tv.title'),
            'metaDesc'    => __('kooora.tv.meta'),
            'date'        => $date,
            'dates'       => FixtureCalendarService::dates(3),
            'broadcasts'  => TvBroadcastFeedService::forDate($date),
            'feedSource'  => TvBroadcastFeedService::sourceLabel(),
            'feedUpdated' => TvBroadcastFeedService::lastFetchedAt($date),
        ]);
    }

    public function liveStreams(): string
    {
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (!empty($_GET['refereex']) || $uriPath === '/refereex-ai' || str_starts_with($uriPath, '/refereex-ai/')) {
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('X-Sportify-RefereeX: live-sports-bridge-v20260705');
            return (new RefereeXController())->index();
        }

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-LiteSpeed-Cache-Control: no-cache');

        $root = App::config('paths.root');
        if (function_exists('opcache_invalidate')) {
            foreach ([
                $root . '/app/Services/LiveHd7SyncService.php',
                $root . '/app/Controllers/TvScheduleController.php',
                $root . '/views/partials/livehd7-matches.php',
                $root . '/views/pages/live-streams.php',
                $root . '/lang/en/messages.php',
                $root . '/lang/de/messages.php',
            ] as $file) {
                if (is_file($file)) {
                    @opcache_invalidate(realpath($file) ?: $file, true);
                }
            }
        }

        LiveHd7SyncService::ensureFreshForPage();

        $config = require App::config('paths.root') . '/config/live_streams.php';
        $livehd7Matches = LiveHd7SyncService::matchesForToday();
        if ($livehd7Matches === []) {
            $seedPath = App::config('paths.root') . '/config/livehd7_today_seed.php';
            if (is_file($seedPath)) {
                $seed = require $seedPath;
                $livehd7Matches = is_array($seed['matches'] ?? null) ? $seed['matches'] : [];
            }
        }
        $livehd7FetchedAt = LiveHd7SyncService::lastFetchedAt();

        header('X-Sportify-LiveHD7-Refresh: on-load');
        header('X-Sportify-LiveHD7-Fetched: ' . ($livehd7FetchedAt ?? 0));

        return $this->view('pages.live-streams', [
            'title'            => __('live_streams.title'),
            'metaDesc'         => __('live_streams.meta'),
            'categories'       => $config['categories'] ?? [],
            'livehd7Matches'   => $livehd7Matches,
            'livehd7FetchedAt' => $livehd7FetchedAt,
        ]);
    }
}
