<?php
/**
 * Live Matches hub configuration.
 * Set MATCH_API_ENABLED=true in .env when wiring an external provider (e.g. API-Football).
 */

$mhGet = static function (string $key, $default = null) {
    $fromEnv = getenv($key);
    return $fromEnv !== false ? $fromEnv : $default;
};

return [
    'api_enabled'   => filter_var($mhGet('MATCH_API_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
    'api_provider'  => $mhGet('MATCH_API_PROVIDER', 'api-football'),
    'api_key'       => $mhGet('MATCH_API_KEY', ''),
    'stale_hours'   => (int) $mhGet('MATCH_AI_STALE_HOURS', 24),
    'model_version' => 'heuristic-v1',
];
