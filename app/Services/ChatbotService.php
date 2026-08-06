<?php

namespace App\Services;

use App\Core\App;
use App\Core\Auth;
use App\Core\Database;
use App\Models\CrmCampaignRepository;
use App\Models\CrmContactRepository;
use App\Models\PlayerHealthRepository;
use App\Models\PlayerRepository;
use App\Services\PlayerAnalysisService;

/**
 * Role-aware Sportify Assistant — discovery chat for everyone,
 * management commands for scouts/admins with write permissions.
 */
class ChatbotService
{
    public const SESSION_PENDING = 'chatbot_pending';
    public const SESSION_WIZARD  = 'chatbot_wizard';
    public const SESSION_HISTORY = 'chatbot_history';

    /** @return array{type:string,message:string,data:array,actions:array,cards:array} */
    public static function handle(string $message, ?array $file = null, ?string $action = null): array
    {
        $message = trim($message);
        $caps = self::capabilities();

        if ($action === 'confirm' || self::isAffirmative($message)) {
            $pending = $_SESSION[self::SESSION_PENDING] ?? null;
            if (is_array($pending)) {
                return self::executePending($pending);
            }
        }

        if ($action === 'cancel' || self::isNegative($message)) {
            unset($_SESSION[self::SESSION_PENDING], $_SESSION[self::SESSION_WIZARD]);
            return self::response('text', __('chatbot.cancelled'));
        }

        if ($file && !empty($file['tmp_name'])) {
            $photoResult = self::handlePhotoUpload($file, $message);
            if ($photoResult) {
                return $photoResult;
            }
        }

        $wizard = $_SESSION[self::SESSION_WIZARD] ?? null;
        if (is_array($wizard) && $message !== '') {
            return self::advanceWizard($wizard, $message);
        }

        $intent = self::detectIntent($message);
        if (!$intent['intent']) {
            $intent = self::enhanceWithLlm($message) ?? $intent;
        }

        return match ($intent['intent']) {
            'add_player'       => self::handleAddPlayer($intent['entities']),
            'bulk_add_players' => self::handleBulkAdd($intent['entities']),
            'update_player'    => self::handleUpdatePlayer($intent['entities']),
            'set_photo'        => self::handleSetPhoto($intent['entities']),
            'log_injury'       => self::handleLogInjury($intent['entities']),
            'add_lead'         => self::handleAddLead($intent['entities']),
            'search_player'    => self::handleSearch($message, $intent['entities']),
            'help'             => self::handleHelp($caps),
            default            => self::handleGeneral($message, $caps),
        };
    }

    /** @return array{mode:string,manage_players:bool,manage_crm:bool,manage_health:bool,role:?string} */
    public static function capabilities(): array
    {
        return [
            'mode'           => self::managementMode() ? 'management' : 'discovery',
            'manage_players' => Auth::can('chatbot.manage_players'),
            'manage_crm'     => Auth::can('chatbot.manage_crm'),
            'manage_health'  => Auth::can('analysis.health.manage'),
            'role'           => Auth::role(),
        ];
    }

    public static function managementMode(): bool
    {
        return Auth::can('chatbot.manage_players')
            || Auth::can('chatbot.manage_crm')
            || Auth::can('analysis.health.manage');
    }

    /** @return array{type:string,message:string,data:array,actions:array,cards:array} */
    protected static function response(string $type, string $message, array $data = [], array $actions = [], array $cards = []): array
    {
        self::rememberHistory('assistant', $message);
        return [
            'type'     => $type,
            'message'  => $message,
            'data'     => $data,
            'actions'  => $actions,
            'cards'    => $cards,
        ];
    }

    protected static function rememberHistory(string $role, string $text): void
    {
        $_SESSION[self::SESSION_HISTORY] ??= [];
        $_SESSION[self::SESSION_HISTORY][] = ['role' => $role, 'text' => $text, 'at' => time()];
        if (count($_SESSION[self::SESSION_HISTORY]) > 40) {
            $_SESSION[self::SESSION_HISTORY] = array_slice($_SESSION[self::SESSION_HISTORY], -40);
        }
    }

    public static function recordUserMessage(string $message): void
    {
        self::rememberHistory('user', $message);
    }

    /** @return array{intent:?string,entities:array} */
    protected static function detectIntent(string $message): array
    {
        $q = trim($message);
        $lower = mb_strtolower($q);

        if ($q === '' || preg_match('/^(help|\?|commands)$/i', $q)) {
            return ['intent' => 'help', 'entities' => []];
        }

        if (preg_match('/\b(add|create|register)\s+(a\s+)?lead\b/i', $q)
            || preg_match('/\b(add|create)\s+(crm\s+)?contact\b/i', $q)) {
            return ['intent' => 'add_lead', 'entities' => self::parseLeadEntities($q)];
        }

        if (preg_match('/\b(bulk|add\s+\d+)\s+players?\b/i', $q)
            || preg_match('/\badd\s+\d+\s+players?\s*:/i', $q)) {
            return ['intent' => 'bulk_add_players', 'entities' => self::parseBulkEntities($q)];
        }

        if (preg_match('/\b(add|create|register)\s+(a\s+)?player\b/i', $q)
            || preg_match('/\bnew\s+player\s*:/i', $q)) {
            return ['intent' => 'add_player', 'entities' => self::parsePlayerEntities($q)];
        }

        if (preg_match('/\b(update|change|set)\b.+\b(position|age|country|club|nationality)\b/i', $q)) {
            return ['intent' => 'update_player', 'entities' => self::parseUpdateEntities($q)];
        }

        if (preg_match('/\b(set|change|update)\s+photo\b/i', $q)
            || preg_match('/\bphoto\s+(for|of)\b/i', $q)) {
            return ['intent' => 'set_photo', 'entities' => self::parsePhotoEntities($q)];
        }

        if (preg_match('/\b(injured|injury|hamstring|sprain|fracture|out\s+for)\b/i', $q)) {
            $entities = self::parseInjuryEntities($q);
            if (!empty($entities['name'])) {
                return ['intent' => 'log_injury', 'entities' => $entities];
            }
        }

        if (preg_match('/\b(find|search|show|recommend|scout|looking\s+for|who\s+is|best)\b/i', $q)
            || preg_match('/\b(striker|strikers|winger|wingers|goalkeeper|goalkeepers|midfielder|midfielders|defender|defenders|under\s+\d+|free\s+agent)\b/i', $q)) {
            return ['intent' => 'search_player', 'entities' => self::parseSearchEntities($q)];
        }

        return ['intent' => null, 'entities' => []];
    }

    /** @return array{intent:?string,entities:array}|null */
    protected static function enhanceWithLlm(string $message): ?array
    {
        $system = 'Extract intent and entities from a football admin chat message. '
            . 'Return JSON only: {"intent":"add_player|bulk_add_players|update_player|set_photo|search_player|add_lead|log_injury|help|general_chat","entities":{}}. '
            . 'Entities may include: name, position, country, age, email, campaign, injury_type, weeks, photo_url, lines, field, value, query.';

        $parsed = self::parseIntentFromOpenAi($message, $system);
        if ($parsed === null) {
            $parsed = self::parseIntentFromManus($message, $system);
        }
        return $parsed;
    }

    /** @return array{intent:?string,entities:array}|null */
    protected static function parseIntentFromOpenAi(string $message, string $system): ?array
    {
        $key = (string) (App::config('openai.api_key') ?? '');
        if ($key === '' || !function_exists('curl_init')) {
            return null;
        }

        $payload = json_encode([
            'model' => App::config('ai_analysis.openai_model') ?? 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $message],
            ],
            'temperature' => 0,
            'response_format' => ['type' => 'json_object'],
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 12,
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);
        if (!$raw) {
            return null;
        }

        $json = json_decode($raw, true);
        $content = $json['choices'][0]['message']['content'] ?? '';
        return self::normalizeIntentPayload($content);
    }

    /** @return array{intent:?string,entities:array}|null */
    protected static function parseIntentFromManus(string $message, string $system): ?array
    {
        if (!ManusAiService::isConfigured()) {
            return null;
        }

        $content = ManusAiService::complete(
            $message . "\n\nRespond with JSON only, no markdown.",
            $system
        );
        return self::normalizeIntentPayload((string) $content);
    }

    /** @return array{intent:?string,entities:array}|null */
    protected static function normalizeIntentPayload(string $content): ?array
    {
        $content = trim($content);
        if ($content === '') {
            return null;
        }
        if (preg_match('/\{.*\}/s', $content, $m)) {
            $content = $m[0];
        }
        $parsed = json_decode($content, true);
        if (!is_array($parsed) || empty($parsed['intent'])) {
            return null;
        }

        return [
            'intent'   => (string) $parsed['intent'],
            'entities' => is_array($parsed['entities'] ?? null) ? $parsed['entities'] : [],
        ];
    }

    /** @param array<string,mixed> $entities */
    protected static function handleAddPlayer(array $entities): array
    {
        if (!Auth::can('chatbot.manage_players')) {
            return self::response('text', __('chatbot.error.no_permission_players'));
        }
        if (!Database::available()) {
            return self::response('text', __('chatbot.error.no_database'));
        }

        $name = trim((string) ($entities['name'] ?? ''));
        if ($name === '') {
            $_SESSION[self::SESSION_WIZARD] = ['intent' => 'add_player', 'data' => [], 'step' => 'name'];
            return self::response('form', __('chatbot.wizard.ask_name'));
        }

        $data = [
            'name'        => $name,
            'position'    => (string) ($entities['position'] ?? 'CM'),
            'nationality' => (string) ($entities['country'] ?? $entities['nationality'] ?? ''),
            'age'         => (int) ($entities['age'] ?? 20),
            'photo'       => (string) ($entities['photo_url'] ?? ''),
        ];

        $_SESSION[self::SESSION_PENDING] = ['intent' => 'add_player', 'data' => $data];
        return self::confirmPlayerCreate($data);
    }

    /** @param array<string,mixed> $data */
    protected static function confirmPlayerCreate(array $data): array
    {
        $summary = __('chatbot.confirm.add_player', [
            'name'     => $data['name'],
            'position' => $data['position'] ?: 'CM',
            'country'  => $data['nationality'] ?: __('chatbot.unspecified'),
            'age'      => (string) ($data['age'] ?? 20),
        ]);

        return self::response('confirm', $summary, $data, [
            ['label' => __('chatbot.yes'), 'action' => 'confirm'],
            ['label' => __('chatbot.no'), 'action' => 'cancel'],
        ]);
    }

    /** @param array<string,mixed> $wizard */
    protected static function advanceWizard(array $wizard, string $message): array
    {
        $data = $wizard['data'] ?? [];
        $step = $wizard['step'] ?? 'name';

        if ($step === 'name') {
            $data['name'] = trim($message);
            if ($data['name'] === '') {
                return self::response('form', __('chatbot.wizard.ask_name'));
            }
            $_SESSION[self::SESSION_WIZARD] = ['intent' => 'add_player', 'data' => $data, 'step' => 'position'];
            return self::response('form', __('chatbot.wizard.ask_position', ['name' => $data['name']]));
        }

        if ($step === 'position') {
            $data['position'] = trim($message) ?: 'CM';
            $_SESSION[self::SESSION_WIZARD] = ['intent' => 'add_player', 'data' => $data, 'step' => 'country'];
            return self::response('form', __('chatbot.wizard.ask_country', ['name' => $data['name']]));
        }

        if ($step === 'country') {
            $data['nationality'] = trim($message);
            $data['age'] = $data['age'] ?? 20;
            unset($_SESSION[self::SESSION_WIZARD]);
            $_SESSION[self::SESSION_PENDING] = ['intent' => 'add_player', 'data' => $data];
            return self::confirmPlayerCreate($data);
        }

        unset($_SESSION[self::SESSION_WIZARD]);
        return self::response('text', __('chatbot.wizard.reset'));
    }

    /** @param array<string,mixed> $entities */
    protected static function handleBulkAdd(array $entities): array
    {
        if (!Auth::can('chatbot.manage_players')) {
            return self::response('text', __('chatbot.error.no_permission_players'));
        }
        if (!Database::available()) {
            return self::response('text', __('chatbot.error.no_database'));
        }

        $lines = $entities['lines'] ?? [];
        if (!is_array($lines) || $lines === []) {
            return self::response('text', __('chatbot.bulk.hint'));
        }

        $_SESSION[self::SESSION_PENDING] = ['intent' => 'bulk_add_players', 'data' => ['lines' => $lines]];
        return self::response('confirm', __('chatbot.confirm.bulk_add', ['count' => count($lines)]), ['count' => count($lines)], [
            ['label' => __('chatbot.yes'), 'action' => 'confirm'],
            ['label' => __('chatbot.no'), 'action' => 'cancel'],
        ]);
    }

    /** @param array<string,mixed> $entities */
    protected static function handleUpdatePlayer(array $entities): array
    {
        if (!Auth::can('chatbot.manage_players')) {
            return self::response('text', __('chatbot.error.no_permission_players'));
        }
        if (!Database::available()) {
            return self::response('text', __('chatbot.error.no_database'));
        }

        $name = trim((string) ($entities['name'] ?? ''));
        $field = strtolower((string) ($entities['field'] ?? 'position'));
        $value = trim((string) ($entities['value'] ?? ''));

        if ($name === '' || $value === '') {
            return self::response('text', __('chatbot.update.hint'));
        }

        $player = PlayerRepository::findByName($name);
        if (!$player || empty($player['id'])) {
            return self::response('text', __('chatbot.error.player_not_found', ['name' => $name]));
        }

        $update = ['name' => $player['name']];
        if (in_array($field, ['position', 'pos'], true)) {
            $update['position'] = $value;
        } elseif ($field === 'age') {
            $update['age'] = (int) $value;
        } elseif (in_array($field, ['country', 'nationality'], true)) {
            $update['nationality'] = $value;
        } elseif ($field === 'club') {
            $update['club'] = $value;
        } else {
            return self::response('text', __('chatbot.update.hint'));
        }

        $_SESSION[self::SESSION_PENDING] = [
            'intent' => 'update_player',
            'data'   => ['id' => (int) $player['id'], 'slug' => $player['slug'], 'update' => $update, 'field' => $field, 'value' => $value],
        ];

        return self::response('confirm', __('chatbot.confirm.update', [
            'name'  => $player['name'],
            'field' => $field,
            'value' => $value,
        ]), [], [
            ['label' => __('chatbot.yes'), 'action' => 'confirm'],
            ['label' => __('chatbot.no'), 'action' => 'cancel'],
        ]);
    }

    /** @param array<string,mixed> $entities */
    protected static function handleSetPhoto(array $entities): array
    {
        if (!Auth::can('chatbot.manage_players')) {
            return self::response('text', __('chatbot.error.no_permission_players'));
        }

        $name = trim((string) ($entities['name'] ?? ''));
        $url = trim((string) ($entities['photo_url'] ?? ''));

        if ($name === '') {
            return self::response('text', __('chatbot.photo.hint'));
        }

        $player = PlayerRepository::findByName($name);
        if (!$player || empty($player['id'])) {
            return self::response('text', __('chatbot.error.player_not_found', ['name' => $name]));
        }

        if ($url === '') {
            $_SESSION[self::SESSION_PENDING] = [
                'intent' => 'set_photo',
                'data'   => ['id' => (int) $player['id'], 'slug' => $player['slug'], 'name' => $player['name'], 'await_upload' => true],
            ];
            return self::response('form', __('chatbot.photo.upload_prompt', ['name' => $player['name']]));
        }

        $_SESSION[self::SESSION_PENDING] = [
            'intent' => 'set_photo',
            'data'   => ['id' => (int) $player['id'], 'slug' => $player['slug'], 'name' => $player['name'], 'photo' => $url],
        ];

        return self::response('confirm', __('chatbot.confirm.set_photo', ['name' => $player['name']]), [], [
            ['label' => __('chatbot.yes'), 'action' => 'confirm'],
            ['label' => __('chatbot.no'), 'action' => 'cancel'],
        ]);
    }

    /** @param array<string,mixed> $entities */
    protected static function handleLogInjury(array $entities): array
    {
        if (!Auth::can('analysis.health.manage')) {
            return self::response('text', __('chatbot.error.no_permission_health'));
        }
        if (!Database::available()) {
            return self::response('text', __('chatbot.error.no_database'));
        }

        $name = trim((string) ($entities['name'] ?? ''));
        if ($name === '') {
            return self::response('text', __('chatbot.injury.hint'));
        }

        $player = PlayerRepository::findByName($name);
        if (!$player || empty($player['id'])) {
            return self::response('text', __('chatbot.error.player_not_found', ['name' => $name]));
        }

        $data = [
            'player_id'       => (int) $player['id'],
            'slug'            => $player['slug'],
            'name'            => $player['name'],
            'injury_type'     => (string) ($entities['injury_type'] ?? 'injury'),
            'body_part'       => (string) ($entities['body_part'] ?? ''),
            'weeks'           => (int) ($entities['weeks'] ?? 2),
            'status'          => 'injured',
        ];

        $_SESSION[self::SESSION_PENDING] = ['intent' => 'log_injury', 'data' => $data];

        return self::response('confirm', __('chatbot.confirm.injury', [
            'name'   => $player['name'],
            'injury' => $data['injury_type'],
            'weeks'  => (string) $data['weeks'],
        ]), [], [
            ['label' => __('chatbot.yes'), 'action' => 'confirm'],
            ['label' => __('chatbot.no'), 'action' => 'cancel'],
        ]);
    }

    /** @param array<string,mixed> $entities */
    protected static function handleAddLead(array $entities): array
    {
        if (!Auth::can('chatbot.manage_crm')) {
            return self::response('text', __('chatbot.error.no_permission_crm'));
        }
        if (!Database::available() || !Database::tableExists('crm_contacts')) {
            return self::response('text', __('chatbot.error.no_crm'));
        }

        $name = trim((string) ($entities['name'] ?? ''));
        $email = trim((string) ($entities['email'] ?? ''));
        if ($name === '' || $email === '') {
            return self::response('text', __('chatbot.lead.hint'));
        }

        $campaignId = null;
        $campaignName = trim((string) ($entities['campaign'] ?? ''));
        if ($campaignName !== '') {
            $camp = CrmCampaignRepository::findByName($campaignName);
            $campaignId = $camp['id'] ?? null;
        }

        $_SESSION[self::SESSION_PENDING] = [
            'intent' => 'add_lead',
            'data'   => [
                'name'        => $name,
                'email'       => $email,
                'campaign_id' => $campaignId,
                'campaign'    => $campaignName,
                'source'      => 'chatbot',
            ],
        ];

        return self::response('confirm', __('chatbot.confirm.add_lead', [
            'name'  => $name,
            'email' => $email,
        ]), [], [
            ['label' => __('chatbot.yes'), 'action' => 'confirm'],
            ['label' => __('chatbot.no'), 'action' => 'cancel'],
        ]);
    }

    protected static function handleSearch(string $message, array $entities): array
    {
        $hasStructured = !empty($entities['position']) || !empty($entities['country'])
            || !empty($entities['nationality']) || !empty($entities['age_max']) || !empty($entities['free']);

        $filters = [
            'q'            => $hasStructured ? '' : (string) ($entities['query'] ?? $message),
            'position'     => (string) ($entities['position'] ?? ''),
            'nationality'  => (string) ($entities['country'] ?? $entities['nationality'] ?? ''),
            'foot'         => (string) ($entities['foot'] ?? ''),
            'availability' => !empty($entities['free']) ? 'Free Agent' : '',
            'age_max'      => isset($entities['age_max']) ? (int) $entities['age_max'] : null,
        ];

        $pool = PlayerRepository::search(array_filter($filters, fn($v) => $v !== null && $v !== ''));
        usort($pool, fn($a, $b) => ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0));
        $top = array_slice($pool, 0, 5);

        if ($top === []) {
            return self::response('text', __('chatbot.search.none'));
        }

        $cards = array_map(fn($p) => [
            'name'  => $p['name'],
            'meta'  => ($p['position_short'] ?? $p['position'] ?? '') . ' · ' . ($p['age'] ?? '') . 'y · ' . ($p['nationality'] ?? ''),
            'fit'   => (int) ($p['fit'] ?? $p['rating'] ?? 0),
            'slug'  => $p['slug'],
            'url'   => route('players.show', ['slug' => $p['slug']]),
        ], $top);

        return self::response('card', __('chatbot.search.found', ['count' => count($pool)]), ['count' => count($pool)], [], $cards);
    }

    /** @param array<string,bool> $caps */
    protected static function handleHelp(array $caps): array
    {
        $lines = [__('chatbot.help.intro')];

        $lines[] = '• ' . __('chatbot.help.search_example');
        if ($caps['manage_players']) {
            $lines[] = '• ' . __('chatbot.help.add_player_example');
            $lines[] = '• ' . __('chatbot.help.bulk_example');
            $lines[] = '• ' . __('chatbot.help.update_example');
            $lines[] = '• ' . __('chatbot.help.photo_example');
        }
        if ($caps['manage_crm']) {
            $lines[] = '• ' . __('chatbot.help.lead_example');
        }
        if ($caps['manage_health']) {
            $lines[] = '• ' . __('chatbot.help.injury_example');
        }

        return self::response('text', implode("\n", $lines));
    }

    /** @param array<string,bool> $caps */
    protected static function handleGeneral(string $message, array $caps): array
    {
        if ($caps['mode'] === 'management') {
            return self::response('text', __('chatbot.general.management', ['hint' => __('chatbot.help.add_player_example')]));
        }
        return self::handleSearch($message, ['query' => $message]);
    }

    /** @param array<string,mixed> $pending */
    protected static function executePending(array $pending): array
    {
        unset($_SESSION[self::SESSION_PENDING]);
        $intent = $pending['intent'] ?? '';
        $data = $pending['data'] ?? [];

        return match ($intent) {
            'add_player'       => self::execAddPlayer($data),
            'bulk_add_players' => self::execBulkAdd($data),
            'update_player'    => self::execUpdatePlayer($data),
            'set_photo'        => self::execSetPhoto($data),
            'log_injury'       => self::execLogInjury($data),
            'add_lead'         => self::execAddLead($data),
            default            => self::response('text', __('chatbot.error.unknown_action')),
        };
    }

    /** @param array<string,mixed> $data */
    protected static function execAddPlayer(array $data): array
    {
        $payload = [
            'name'        => $data['name'],
            'position'    => $data['position'] ?? 'CM',
            'nationality' => $data['nationality'] ?? '',
            'age'         => (int) ($data['age'] ?? 20),
            'photo'       => $data['photo'] ?? '',
        ];
        if (Auth::can('scout.manage_players') && Auth::id()) {
            $payload['scout_user_id'] = (int) Auth::id();
        }

        $result = PlayerRepository::create($payload);

        if (!$result) {
            return self::response('text', __('chatbot.error.create_failed'));
        }

        $url = route('players.show', ['slug' => $result['slug']]);
        return self::response('card', __('chatbot.success.player_created', ['name' => $data['name']]), $result, [], [[
            'name' => $data['name'],
            'meta' => __('chatbot.success.view_profile'),
            'slug' => $result['slug'],
            'url'  => $url,
            'fit'  => 100,
        ]]);
    }

    /** @param array<string,mixed> $data */
    protected static function execBulkAdd(array $data): array
    {
        $lines = $data['lines'] ?? [];
        $scoutId = Auth::can('scout.manage_players') ? (int) Auth::id() : null;
        $result = PlayerRepository::bulkCreate($lines, false, $scoutId ?: null);
        $msg = __('chatbot.success.bulk_created', ['count' => $result['created']]);
        if (!empty($result['errors'])) {
            $msg .= ' ' . __('chatbot.success.bulk_errors', ['count' => count($result['errors'])]);
        }
        return self::response('text', $msg, $result);
    }

    /** @param array<string,mixed> $data */
    protected static function execUpdatePlayer(array $data): array
    {
        $ok = PlayerRepository::update((int) $data['id'], $data['update'] ?? []);
        if (!$ok) {
            return self::response('text', __('chatbot.error.update_failed'));
        }

        $url = route('players.show', ['slug' => $data['slug']]);
        return self::response('card', __('chatbot.success.player_updated', ['name' => $data['update']['name'] ?? '']), [], [], [[
            'name' => $data['update']['name'] ?? '',
            'meta' => __('chatbot.success.view_profile'),
            'slug' => $data['slug'],
            'url'  => $url,
            'fit'  => 100,
        ]]);
    }

    /** @param array<string,mixed> $data */
    protected static function execSetPhoto(array $data): array
    {
        $photo = $data['photo'] ?? '';
        $ok = PlayerRepository::update((int) $data['id'], [
            'name'  => $data['name'],
            'photo' => $photo,
        ]);

        if (!$ok) {
            return self::response('text', __('chatbot.error.photo_failed'));
        }

        $url = route('players.show', ['slug' => $data['slug']]);
        return self::response('card', __('chatbot.success.photo_set', ['name' => $data['name']]), [], [], [[
            'name' => $data['name'],
            'meta' => __('chatbot.success.view_profile'),
            'slug' => $data['slug'],
            'url'  => $url,
            'fit'  => 100,
        ]]);
    }

    /** @param array<string,mixed> $data */
    protected static function execLogInjury(array $data): array
    {
        $weeks = max(1, (int) ($data['weeks'] ?? 2));
        $returnDate = date('Y-m-d', strtotime('+' . $weeks . ' weeks'));

        PlayerHealthRepository::create((int) $data['player_id'], [
            'status'          => 'injured',
            'injury_type'     => $data['injury_type'] ?? 'injury',
            'body_part'       => $data['body_part'] ?? null,
            'severity'        => 'moderate',
            'notes'           => __('chatbot.injury.logged_via_chat'),
            'expected_return' => $returnDate,
        ]);

        if (!empty($data['slug'])) {
            PlayerAnalysisService::regenerateBySlug($data['slug']);
        }

        return self::response('text', __('chatbot.success.injury_logged', [
            'name'   => $data['name'],
            'weeks'  => (string) $weeks,
            'return' => $returnDate,
        ]));
    }

    /** @param array<string,mixed> $data */
    protected static function execAddLead(array $data): array
    {
        $id = CrmContactRepository::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'source'      => $data['source'] ?? 'chatbot',
            'status'      => 'new',
            'campaign_id' => $data['campaign_id'] ?? null,
            'notes'       => $data['campaign'] ? __('chatbot.lead.from_campaign', ['campaign' => $data['campaign']]) : null,
            'tags'        => ['player_prospect'],
        ]);

        if (!$id) {
            return self::response('text', __('chatbot.error.lead_failed'));
        }

        $url = route('admin.crm.contacts.show', ['id' => $id]);
        return self::response('card', __('chatbot.success.lead_created', ['name' => $data['name']]), ['id' => $id], [], [[
            'name' => $data['name'],
            'meta' => $data['email'],
            'url'  => $url,
            'fit'  => 0,
        ]]);
    }

    /** @return array{type:string,message:string,data:array,actions:array,cards:array}|null */
    protected static function handlePhotoUpload(array $file, string $message): ?array
    {
        if (!Auth::can('chatbot.manage_players')) {
            return self::response('text', __('chatbot.error.no_permission_players'));
        }

        $stored = self::storePlayerPhoto($file);
        if (!$stored['ok']) {
            return self::response('text', $stored['error'] ?? __('chatbot.error.photo_failed'));
        }

        $pending = $_SESSION[self::SESSION_PENDING] ?? null;
        if (is_array($pending) && ($pending['intent'] ?? '') === 'set_photo') {
            $pending['data']['photo'] = $stored['path'];
            unset($pending['data']['await_upload']);
            $_SESSION[self::SESSION_PENDING] = $pending;
            return self::response('confirm', __('chatbot.confirm.set_photo', ['name' => $pending['data']['name']]), [], [
                ['label' => __('chatbot.yes'), 'action' => 'confirm'],
                ['label' => __('chatbot.no'), 'action' => 'cancel'],
            ]);
        }

        if (preg_match('/\b(add|create)\s+(a\s+)?player\b/i', $message)) {
            $entities = self::parsePlayerEntities($message);
            $entities['photo_url'] = $stored['path'];
            return self::handleAddPlayer($entities);
        }

        return self::response('text', __('chatbot.photo.received', ['hint' => __('chatbot.photo.hint')]));
    }

    /** @return array{ok:bool,path?:string,error?:string} */
    public static function storePlayerPhoto(array $file): array
    {
        $mime = mime_content_type($file['tmp_name']) ?: ($file['type'] ?? '');
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowed, true)) {
            return ['ok' => false, 'error' => __('chatbot.error.photo_type')];
        }
        if (($file['size'] ?? 0) > 5_242_880) {
            return ['ok' => false, 'error' => __('chatbot.error.photo_size')];
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
            default => 'jpg',
        };

        $root = App::config('paths.root');
        $dir = $root . '/public/uploads/players';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return ['ok' => false, 'error' => __('chatbot.error.photo_dir')];
        }

        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['ok' => false, 'error' => __('chatbot.error.photo_failed')];
        }

        return ['ok' => true, 'path' => 'uploads/players/' . $name];
    }

    protected static function isAffirmative(string $message): bool
    {
        return (bool) preg_match('/^(yes|y|confirm|ok|okay|ja|si|sí|نعم|أكد)$/iu', trim($message));
    }

    protected static function isNegative(string $message): bool
    {
        return (bool) preg_match('/^(no|n|cancel|stop|nein|لا)$/iu', trim($message));
    }

    /** @return array<string,mixed> */
    protected static function parsePlayerEntities(string $q): array
    {
        $entities = [];
        if (preg_match('/\b(?:add|create|register)\s+(?:a\s+)?player\s*[:\-]?\s*(.+)$/iu', $q, $m)) {
            $rest = trim($m[1]);
        } elseif (preg_match('/\bnew\s+player\s*[:\-]?\s*(.+)$/iu', $q, $m)) {
            $rest = trim($m[1]);
        } else {
            $rest = $q;
        }

        $parts = array_map('trim', preg_split('/[,;]/', $rest) ?: []);
        if ($parts !== []) {
            $entities['name'] = array_shift($parts);
        }

        foreach ($parts as $part) {
            if (preg_match('/\b(?:age|aged)\s*(\d{1,2})\b/i', $part, $am)) {
                $entities['age'] = (int) $am[1];
            } elseif (preg_match('/^(https?:\/\/.+)$/i', $part, $um)) {
                $entities['photo_url'] = $um[1];
            } elseif (preg_match('/\b(striker|winger|goalkeeper|midfielder|defender|cam|cdm|cm|st|rw|lw|gk|cb)\b/i', $part)) {
                $entities['position'] = $part;
            } elseif ($part !== '') {
                $entities['country'] = $part;
            }
        }

        if (preg_match('/\bage\s*(\d{1,2})\b/i', $q, $am)) {
            $entities['age'] = (int) $am[1];
        }
        if (preg_match('/(https?:\/\/[^\s,;]+)/i', $q, $um)) {
            $entities['photo_url'] = $um[1];
        }

        return $entities;
    }

    /** @return array<string,mixed> */
    protected static function parseBulkEntities(string $q): array
    {
        $lines = [];
        if (preg_match('/:\s*(.+)$/s', $q, $m)) {
            $block = trim($m[1]);
            $lines = preg_split('/\r\n|\r|\n|(?:\d+[\.\)]\s)/', $block) ?: [];
        } else {
            $lines = preg_split('/\r\n|\r|\n/', $q) ?: [];
        }

        $parsed = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/^\d+[\.\)]\s*/', '', $line));
            if ($line === '' || preg_match('/^(add|bulk)\b/i', $line)) {
                continue;
            }
            $line = preg_replace('/\b(and|und)\b/i', ',', $line);
            if (str_contains($line, ',')) {
                $parsed[] = $line;
            } elseif (preg_match('/^([A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s\-\']+)\s+([A-Za-z]{2,3})\s+(.+)$/u', $line, $pm)) {
                $parsed[] = trim($pm[1]) . ', ' . trim($pm[2]) . ', ' . trim($pm[3]);
            }
        }

        return ['lines' => $parsed];
    }

    /** @return array<string,mixed> */
    protected static function parseUpdateEntities(string $q): array
    {
        $entities = [];
        if (preg_match('/\b(?:update|change|set)\s+(.+?)\s+(position|age|country|club|nationality)\s+(?:to\s+)?(.+)$/iu', $q, $m)) {
            $entities['name'] = trim($m[1]);
            $entities['field'] = strtolower(trim($m[2]));
            $entities['value'] = trim($m[3]);
        }
        return $entities;
    }

    /** @return array<string,mixed> */
    protected static function parsePhotoEntities(string $q): array
    {
        $entities = [];
        if (preg_match('/\b(?:for|of)\s+([A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s\-\']+?)(?:\s*[:,\s]|$)/u', $q, $m)) {
            $entities['name'] = trim($m[1]);
        }
        if (preg_match('/(https?:\/\/[^\s,;]+)/i', $q, $um)) {
            $entities['photo_url'] = $um[1];
        }
        return $entities;
    }

    /** @return array<string,mixed> */
    protected static function parseInjuryEntities(string $q): array
    {
        $entities = ['weeks' => 2];
        if (preg_match('/\b(?:for\s+)?([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/u', $q, $m)) {
            $entities['name'] = trim($m[1]);
        } elseif (preg_match('/^([A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s\-\']+?)\s+(?:injured|injury|hamstring)/iu', $q, $m)) {
            $entities['name'] = trim($m[1]);
        }
        if (preg_match('/\b(hamstring|ankle|knee|groin|shoulder|concussion)\b/i', $q, $im)) {
            $entities['injury_type'] = strtolower($im[1]);
            $entities['body_part'] = strtolower($im[1]);
        }
        if (preg_match('/\b(\d+)\s*(weeks?|w)\b/i', $q, $wm)) {
            $entities['weeks'] = (int) $wm[1];
        }
        return $entities;
    }

    /** @return array<string,mixed> */
    protected static function parseLeadEntities(string $q): array
    {
        $entities = [];
        if (preg_match('/([a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,})/i', $q, $em)) {
            $entities['email'] = $em[1];
        }
        if (preg_match('/\b(?:lead|contact)\s+([A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s\-\']+?)\s+' . preg_quote($entities['email'] ?? '@', '/') . '/iu', $q, $nm)) {
            $entities['name'] = trim($nm[1]);
        } elseif (preg_match('/\b(?:add|create)\s+(?:lead|contact)\s+([A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s\-\']+)/iu', $q, $nm)) {
            $entities['name'] = trim(preg_replace('/\s+' . preg_quote($entities['email'] ?? '', '/') . '$/i', '', $nm[1]));
        }
        if (preg_match('/\b(?:campaign|from)\s+([A-Za-z0-9][A-Za-z0-9\s\-]+)$/iu', $q, $cm)) {
            $entities['campaign'] = trim($cm[1]);
        }
        return $entities;
    }

    /** @return array<string,mixed> */
    protected static function parseSearchEntities(string $q): array
    {
        $entities = ['query' => $q];
        if (preg_match('/under\s*(\d{2})|u-?(\d{2})/i', $q, $m)) {
            $entities['age_max'] = (int) ($m[1] ?: $m[2]);
        }
        if (preg_match('/free\s+agent/i', $q)) {
            $entities['free'] = true;
        }

        $posMap = [
            'striker' => 'ST', 'strikers' => 'ST', 'forward' => 'ST', 'forwards' => 'ST',
            'winger' => 'RW', 'wingers' => 'RW', 'goalkeeper' => 'GK', 'goalkeepers' => 'GK',
            'keeper' => 'GK', 'keepers' => 'GK', 'defender' => 'CB', 'defenders' => 'CB',
            'midfielder' => 'CM', 'midfielders' => 'CM',
        ];
        if (preg_match('/\b(striker|strikers|winger|wingers|goalkeeper|goalkeepers|keeper|keepers|defender|defenders|midfielder|midfielders|cam|cdm|cm|st|rw|lw|gk|cb)\b/i', $q, $pm)) {
            $word = strtolower($pm[1]);
            $entities['position'] = $posMap[$word] ?? strtoupper($pm[1]);
        }

        if (preg_match('/\bfrom\s+([A-Za-zÀ-ÿ\s]+?)(?:\s+under|\s*$)/iu', $q, $cm)) {
            $entities['country'] = trim($cm[1]);
        }
        return $entities;
    }
}
