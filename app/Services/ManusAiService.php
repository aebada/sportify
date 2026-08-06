<?php

namespace App\Services;

use App\Core\App;

/**
 * Thin Manus AI client (HOPn / Manus API v2).
 *
 * Async agent tasks: POST task.create → poll GET task.listMessages.
 * Auth: x-manus-api-key (not OpenAI-style Bearer for API keys).
 * Docs: https://open.manus.ai/docs/v2/task.create
 */
class ManusAiService
{
    public static function isConfigured(): bool
    {
        return self::apiKey() !== '' && function_exists('curl_init');
    }

    public static function apiKey(): string
    {
        $key = trim((string) (App::config('manus.api_key') ?? ''));
        if ($key === '') {
            $key = trim((string) (App::config('manus.hopn_api_key') ?? ''));
        }
        return $key;
    }

    /**
     * Run a text prompt (optional system preamble) and return the assistant reply.
     *
     * @param  array<string,mixed>|null  $structuredSchema  optional JSON Schema for structured_output_schema
     */
    public static function complete(string $prompt, ?string $system = null, ?array $structuredSchema = null): ?string
    {
        $messages = [];
        if ($system !== null && trim($system) !== '') {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        return self::chat($messages, $structuredSchema);
    }

    /**
     * @param  list<array{role:string,content:string}>  $messages
     * @param  array<string,mixed>|null  $structuredSchema
     */
    public static function chat(array $messages, ?array $structuredSchema = null): ?string
    {
        $apiKey = self::apiKey();
        if ($apiKey === '' || !function_exists('curl_init')) {
            return null;
        }

        $baseUrl = rtrim((string) (App::config('manus.base_url') ?? 'https://api.manus.ai/v2'), '/');
        $agentProfile = (string) (App::config('manus.agent_profile') ?? 'manus-1.6-lite');
        $pollTimeout = max(5, (int) (App::config('manus.poll_timeout') ?? 45));
        $pollInterval = max(1, (int) (App::config('manus.poll_interval') ?? 2));

        $prompt = self::flattenMessages($messages);
        if ($prompt === '') {
            return null;
        }

        $body = [
            'message' => ['content' => $prompt],
            'agent_profile' => $agentProfile,
            'hide_in_task_list' => true,
        ];
        if (is_array($structuredSchema) && $structuredSchema !== []) {
            $body['structured_output_schema'] = $structuredSchema;
        }

        $headers = [
            'Content-Type: application/json',
            'x-manus-api-key: ' . $apiKey,
        ];

        $createRaw = self::httpJson('POST', $baseUrl . '/task.create', $headers, $body, 30);
        if ($createRaw === null) {
            return null;
        }

        $taskId = trim((string) ($createRaw['task_id'] ?? ''));
        if ($taskId === '') {
            return null;
        }

        $deadline = microtime(true) + $pollTimeout;
        while (microtime(true) < $deadline) {
            sleep($pollInterval);

            $pollRaw = self::httpJson(
                'GET',
                $baseUrl . '/task.listMessages?' . http_build_query([
                    'task_id' => $taskId,
                    'order' => 'asc',
                    'limit' => 200,
                ]),
                $headers,
                null,
                30
            );
            if ($pollRaw === null) {
                continue;
            }

            $events = $pollRaw['messages'] ?? [];
            if (!is_array($events)) {
                continue;
            }

            $reply = null;
            $stopped = false;
            foreach ($events as $event) {
                if (!is_array($event)) {
                    continue;
                }
                if (($event['type'] ?? null) === 'assistant_message') {
                    $text = self::extractContent($event['content'] ?? '');
                    if ($text !== '') {
                        $reply = $text;
                    }
                }
                if (($event['type'] ?? null) === 'status_update'
                    && in_array((string) ($event['agent_status'] ?? ''), ['stopped', 'error', 'finished'], true)) {
                    $stopped = true;
                }
            }

            if ($reply !== null) {
                return $reply;
            }
            if ($stopped) {
                return null;
            }
        }

        return null;
    }

    /**
     * @param  list<array{role:string,content:string}>  $messages
     */
    protected static function flattenMessages(array $messages): string
    {
        $lines = [];
        foreach ($messages as $message) {
            $text = trim((string) ($message['content'] ?? ''));
            if ($text === '') {
                continue;
            }
            $role = strtoupper((string) ($message['role'] ?? 'user'));
            $lines[] = $role . ': ' . $text;
        }
        return implode("\n\n", $lines);
    }

    /** @param mixed $content */
    protected static function extractContent($content): string
    {
        if (is_string($content)) {
            return trim($content);
        }
        if (!is_array($content)) {
            return '';
        }
        $parts = [];
        foreach ($content as $part) {
            if (is_string($part)) {
                $parts[] = $part;
                continue;
            }
            if (!is_array($part)) {
                continue;
            }
            if (($part['type'] ?? '') === 'text' && isset($part['text'])) {
                $parts[] = (string) $part['text'];
            } elseif (isset($part['text'])) {
                $parts[] = (string) $part['text'];
            }
        }
        return trim(implode("\n", $parts));
    }

    /**
     * @param  list<string>  $headers
     * @param  array<string,mixed>|null  $body
     * @return array<string,mixed>|null
     */
    protected static function httpJson(string $method, string $url, array $headers, ?array $body, int $timeout): ?array
    {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ];
        if (strtoupper($method) === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $code < 200 || $code >= 300) {
            return null;
        }

        $json = json_decode((string) $raw, true);
        return is_array($json) ? $json : null;
    }
}
