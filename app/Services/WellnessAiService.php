<?php

namespace App\Services;

use App\Core\App;
use App\Core\Auth;
use App\Models\BookingRepository;
use App\Models\ProviderRepository;
use App\Models\WellnessCalendarRepository;
use App\Models\WellnessRecommendationRepository;
use App\Models\WellnessUserProfileRepository;

/**
 * Rule-based wellness concierge for Phase 1 MVP.
 * Phase 2: replace mock slots with Google Calendar free/busy; add weather/traffic signals.
 */
class WellnessAiService
{
    public static function goalsCatalog(): array
    {
        return App::config('wellness.goals', []);
    }

    public static function sportsCatalog(): array
    {
        return App::config('wellness.sports', []);
    }

    /** @return array<int,array{day:string,start:string,end:string,label?:string}> */
    public static function analyzeFreeSlots(int $userId): array
    {
        if (WellnessCalendarRepository::isConnected($userId, 'google')) {
            // Phase 2: fetch real busy blocks from Google Calendar API
            return self::mockFreeSlots();
        }
        return self::mockFreeSlots();
    }

    /** @return array<int,array{day:string,start:string,end:string}> */
    protected static function mockFreeSlots(): array
    {
        return App::config('wellness.mock_free_slots', []);
    }

    /**
     * Rank partners by goal match, time fit, and distance placeholder.
     *
     * @param array<string,mixed> $filters distance_km, sport, modality, time_of_day
     * @return array<int,array<string,mixed>>
     */
    public static function rankPartners(int $userId, array $filters = []): array
    {
        $cacheKey = 'feed_' . md5(json_encode($filters));
        $cached = WellnessRecommendationRepository::get($userId, $cacheKey);
        if ($cached && !empty($cached['items'])) {
            return $cached['items'];
        }

        $profile = WellnessUserProfileRepository::findByUser($userId) ?? [];
        $goals = $profile['goals'] ?? [];
        $preferredSports = $profile['preferred_sports'] ?? [];
        $goalSports = self::sportsForGoals($goals);
        $targetSports = !empty($preferredSports) ? $preferredSports : $goalSports;

        $providers = ProviderRepository::all();
        $slots = self::analyzeFreeSlots($userId);
        $userLat = (float) ($profile['location_lat'] ?? App::config('wellness.default_location.lat', 52.52));
        $userLng = (float) ($profile['location_lng'] ?? App::config('wellness.default_location.lng', 13.405));

        $items = [];
        foreach ($providers as $p) {
            $type = $p['type'] ?? 'wellness';
            $modality = self::inferModality($p);
            $distance = self::estimateDistanceKm($p, $userLat, $userLng);

            $goalScore = in_array($type, $targetSports, true) ? 40 : (in_array($type, $goalSports, true) ? 25 : 10);
            $timeScore = self::timeFitScore($slots, $profile['preferred_hours'] ?? null);
            $distScore = max(0, 30 - (int) ($distance * 2));
            $bookingBoost = self::hasPastBookingAtProvider($userId, (int) ($p['id'] ?? 0)) ? 10 : 0;
            $score = $goalScore + $timeScore + $distScore + $bookingBoost;

            $suggestedSlot = self::pickSuggestedSlot($slots, $profile['preferred_hours'] ?? null);
            $plan = $p['plans'][0] ?? null;

            $items[] = [
                'provider_id'    => (int) ($p['id'] ?? 0),
                'slug'           => $p['slug'] ?? '',
                'name'           => $p['name'] ?? '',
                'type'           => $type,
                'type_label'     => $p['type_label'] ?? ucfirst($type),
                'type_icon'      => $p['type_icon'] ?? '🏢',
                'city'           => $p['city'] ?? '',
                'tagline'        => $p['tagline'] ?? '',
                'modality'       => $modality,
                'distance_km'    => round($distance, 1),
                'score'          => $score,
                'match_reason'   => self::matchReason($goalScore, $timeScore, $distScore, $type, $goals),
                'suggested_slot' => $suggestedSlot,
                'plan_slug'      => $plan['slug'] ?? null,
                'plan_name'      => $plan['name'] ?? null,
                'plan_price'     => $plan['price'] ?? null,
                'badge'          => $p['badge'] ?? '',
            ];
        }

        usort($items, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        if (!empty($filters['sport'])) {
            $items = array_values(array_filter($items, fn($i) => ($i['type'] ?? '') === $filters['sport']));
        }
        if (!empty($filters['modality'])) {
            $items = array_values(array_filter($items, fn($i) => ($i['modality'] ?? '') === $filters['modality']));
        }
        if (!empty($filters['time_of_day'])) {
            $items = array_values(array_filter($items, fn($i) => self::slotMatchesTimeOfDay($i['suggested_slot'] ?? [], $filters['time_of_day'])));
        }
        if (!empty($filters['distance_km'])) {
            $max = (float) $filters['distance_km'];
            $items = array_values(array_filter($items, fn($i) => ($i['distance_km'] ?? 99) <= $max));
        }

        WellnessRecommendationRepository::put($userId, $cacheKey, ['items' => $items], 1800);
        return $items;
    }

    public static function dailySuggestion(int $userId): string
    {
        $items = self::rankPartners($userId);
        $profile = WellnessUserProfileRepository::findByUser($userId);
        $goals = $profile['goals'] ?? [];
        $goalLabel = '';
        if (!empty($goals[0])) {
            $cat = self::goalsCatalog();
            $goalLabel = $cat[$goals[0]]['label'] ?? $goals[0];
        }
        if (empty($items)) {
            return __('wellness.daily_empty');
        }
        $top = $items[0];
        $slot = $top['suggested_slot'] ?? [];
        $when = !empty($slot['start']) ? ucfirst($slot['day'] ?? 'today') . ' ' . $slot['start'] : __('wellness.flexible_time');
        return __('wellness.daily_suggestion', [
            'goal' => $goalLabel ?: __('wellness.general_goal'),
            'name' => $top['name'],
            'when' => $when,
            'reason' => $top['match_reason'],
        ]);
    }

    /** @return array<int,array{day:string,activity:string,provider?:string,time?:string}> */
    public static function weeklyPlan(int $userId): array
    {
        $items = self::rankPartners($userId);
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $plan = [];
        $used = 0;
        foreach ($days as $day) {
            if ($used >= count($items)) {
                $plan[] = ['day' => $day, 'activity' => __('wellness.rest_day'), 'provider' => '', 'time' => ''];
                continue;
            }
            $rec = $items[$used++];
            $slot = $rec['suggested_slot'] ?? [];
            $plan[] = [
                'day'      => $day,
                'activity' => $rec['type_label'] . ' — ' . ($rec['tagline'] ?: $rec['name']),
                'provider' => $rec['name'],
                'time'     => ($slot['start'] ?? '18:00') . '–' . ($slot['end'] ?? '19:00'),
            ];
        }
        return $plan;
    }

    /** @return array{consistency:int,bookings_30d:int,streak:int,label:string} */
    public static function wellnessScores(int $userId): array
    {
        $bookings = BookingRepository::forFan($userId);
        $now = time();
        $thirtyDaysAgo = $now - (30 * 86400);
        $recent = array_filter($bookings, function ($b) use ($thirtyDaysAgo) {
            $t = strtotime($b['slot_start'] ?? $b['created_at'] ?? '');
            return $t && $t >= $thirtyDaysAgo;
        });
        $count = count($recent);
        $consistency = min(100, $count * 12 + (count($bookings) > 0 ? 20 : 0));
        $streak = min(7, (int) floor($count / 2));

        $label = match (true) {
            $consistency >= 80 => __('wellness.score_excellent'),
            $consistency >= 50 => __('wellness.score_good'),
            $consistency >= 25 => __('wellness.score_building'),
            default            => __('wellness.score_start'),
        };

        return [
            'consistency'  => $consistency,
            'bookings_30d' => $count,
            'streak'       => $streak,
            'label'        => $label,
        ];
    }

    /** @return array{type:string,message:string,actions:array<int,array<string,string>>} */
    public static function chat(int $userId, string $message): array
    {
        $message = trim($message);
        if ($message === '') {
            return self::chatResponse(__('wellness.chat_welcome'), [
                ['label' => __('wellness.nav.recommendations'), 'href' => route('wellness.recommendations')],
                ['label' => __('wellness.nav.goals'), 'href' => route('wellness.goals')],
            ]);
        }

        $lower = mb_strtolower($message);
        if (preg_match('/\b(plan|week|weekly)\b/u', $lower)) {
            $plan = self::weeklyPlan($userId);
            $lines = array_map(fn($d) => ucfirst($d['day']) . ': ' . $d['activity'] . ($d['provider'] ? ' @ ' . $d['provider'] : ''), $plan);
            return self::chatResponse(__('wellness.chat_weekly_intro') . "\n\n" . implode("\n", $lines));
        }
        if (preg_match('/\b(today|daily|suggest)\b/u', $lower)) {
            return self::chatResponse(self::dailySuggestion($userId));
        }
        if (preg_match('/\b(score|progress|consistency)\b/u', $lower)) {
            $s = self::wellnessScores($userId);
            return self::chatResponse(__('wellness.chat_score', [
                'score' => $s['consistency'],
                'label' => $s['label'],
                'bookings' => $s['bookings_30d'],
            ]));
        }
        if (preg_match('/\b(book|gym|massage|yoga|provider|partner)\b/u', $lower)) {
            $items = array_slice(self::rankPartners($userId), 0, 3);
            if (empty($items)) {
                return self::chatResponse(__('wellness.chat_no_partners'));
            }
            $lines = array_map(fn($i) => '• ' . $i['name'] . ' (' . $i['distance_km'] . ' km) — ' . $i['match_reason'], $items);
            return self::chatResponse(__('wellness.chat_partners_intro') . "\n" . implode("\n", $lines), [
                ['label' => __('wellness.view_all'), 'href' => route('wellness.recommendations')],
            ]);
        }
        if (preg_match('/\b(calendar|schedule|free|busy)\b/u', $lower)) {
            $connected = WellnessCalendarRepository::isConnected($userId, 'google');
            $slots = self::analyzeFreeSlots($userId);
            $slotText = implode(', ', array_map(fn($s) => ucfirst($s['day']) . ' ' . $s['start'], array_slice($slots, 0, 3)));
            return self::chatResponse($connected
                ? __('wellness.chat_calendar_connected', ['slots' => $slotText])
                : __('wellness.chat_calendar_mock', ['slots' => $slotText]), [
                ['label' => __('wellness.nav.calendar'), 'href' => route('wellness.calendar')],
            ]);
        }

        $llm = self::tryOpenAi($userId, $message);
        if ($llm === null) {
            $llm = self::tryManus($userId, $message);
        }
        if ($llm !== null) {
            return self::chatResponse($llm);
        }

        return self::chatResponse(__('wellness.chat_fallback', [
            'hint' => self::dailySuggestion($userId),
        ]));
    }

    protected static function tryOpenAi(int $userId, string $message): ?string
    {
        $key = trim((string) (App::config('openai.api_key') ?? ''));
        if ($key === '') {
            return null;
        }
        $profile = WellnessUserProfileRepository::findByUser($userId);
        $goals = implode(', ', $profile['goals'] ?? []);
        $system = 'You are FIT-Pass AI Wellness Assistant on Sportify. Be a concise wellness concierge. '
            . 'Never auto-book; always suggest and ask for confirmation. User goals: ' . ($goals ?: 'general wellness') . '.';
        $payload = [
            'model' => App::config('ai_analysis.openai_model') ?? 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $message],
            ],
            'max_tokens' => 300,
            'temperature' => 0.6,
        ];
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $key],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 15,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        $resp = json_decode((string) $raw, true);
        return $resp['choices'][0]['message']['content'] ?? null;
    }

    protected static function tryManus(int $userId, string $message): ?string
    {
        if (!ManusAiService::isConfigured()) {
            return null;
        }
        $profile = WellnessUserProfileRepository::findByUser($userId);
        $goals = implode(', ', $profile['goals'] ?? []);
        $system = 'You are FIT-Pass AI Wellness Assistant on Sportify. Be a concise wellness concierge. '
            . 'Never auto-book; always suggest and ask for confirmation. User goals: ' . ($goals ?: 'general wellness') . '. '
            . 'Reply in plain text only (no task plans or tool narration).';

        $reply = ManusAiService::complete($message, $system);
        $reply = is_string($reply) ? trim($reply) : '';
        return $reply !== '' ? $reply : null;
    }

    /** @param array<int,array<string,string>> $actions */
    protected static function chatResponse(string $message, array $actions = []): array
    {
        return ['type' => 'text', 'message' => $message, 'actions' => $actions];
    }

    /** @param array<int,string> $goals */
    protected static function sportsForGoals(array $goals): array
    {
        $cat = self::goalsCatalog();
        $sports = [];
        foreach ($goals as $g) {
            foreach ($cat[$g]['sports'] ?? [] as $s) {
                $sports[$s] = true;
            }
        }
        return array_keys($sports);
    }

    protected static function inferModality(array $provider): string
    {
        $type = $provider['type'] ?? '';
        if (in_array($type, ['nutrition', 'wellness'], true)) {
            return 'hybrid';
        }
        if (!empty($provider['data']['online'])) {
            return 'online';
        }
        return 'offline';
    }

    protected static function estimateDistanceKm(array $provider, float $lat, float $lng): float
    {
        $city = mb_strtolower($provider['city'] ?? '');
        $defaultCity = mb_strtolower((string) App::config('wellness.default_location.city', 'Berlin'));
        if ($city === $defaultCity) {
            return (float) (rand(1, 8) + (crc32($provider['slug'] ?? '') % 50) / 10);
        }
        if ($city !== '') {
            return (float) (rand(15, 45) + (crc32($city) % 20));
        }
        return 25.0;
    }

    protected static function timeFitScore(array $slots, ?string $preferredHours): int
    {
        if (empty($slots)) {
            return 5;
        }
        if ($preferredHours === null || $preferredHours === '') {
            return 20;
        }
        foreach ($slots as $s) {
            if (self::slotMatchesTimeOfDay($s, $preferredHours)) {
                return 25;
            }
        }
        return 10;
    }

    /** @param array<string,mixed> $slot */
    protected static function slotMatchesTimeOfDay(array $slot, string $timeOfDay): bool
    {
        $ranges = App::config('wellness.time_of_day.' . $timeOfDay . '.hours', null);
        if (!$ranges || empty($slot['start'])) {
            return true;
        }
        $hour = (int) substr($slot['start'], 0, 2);
        return $hour >= $ranges[0] && $hour < $ranges[1];
    }

    /** @param array<int,array<string,string>> $slots */
    protected static function pickSuggestedSlot(array $slots, ?string $preferredHours): array
    {
        if (empty($slots)) {
            return ['day' => 'today', 'start' => '18:00', 'end' => '19:00'];
        }
        foreach ($slots as $s) {
            if ($preferredHours && self::slotMatchesTimeOfDay($s, $preferredHours)) {
                return $s;
            }
        }
        return $slots[0];
    }

    protected static function matchReason(int $goalScore, int $timeScore, int $distScore, string $type, array $goals): string
    {
        if ($goalScore >= 35) {
            $cat = self::goalsCatalog();
            foreach ($goals as $g) {
                if (in_array($type, $cat[$g]['sports'] ?? [], true)) {
                    return __('wellness.reason_goal', ['goal' => $cat[$g]['label'] ?? $g]);
                }
            }
        }
        if ($distScore >= 20) {
            return __('wellness.reason_nearby');
        }
        if ($timeScore >= 20) {
            return __('wellness.reason_time');
        }
        return __('wellness.reason_general');
    }

    protected static function hasPastBookingAtProvider(int $userId, int $providerId): bool
    {
        if ($providerId <= 0) {
            return false;
        }
        return false;
    }
}
