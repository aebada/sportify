<?php
declare(strict_types=1);

/**
 * Upsert Manus AI env keys on production Hostinger FTP roots.
 * Does not print secret values.
 *
 * Usage:
 *   php scripts/patch_production_manus_env.php \
 *     --host=92.113.19.130 --user=u234903558.sportifywebsite --pass="$FTP_PASS" \
 *     --api-key="$MANUS_API_KEY" \
 *     [--base-url=https://api.manus.ai/v2] \
 *     [--agent-profile=manus-1.6-lite] \
 *     [--poll-timeout=45] [--poll-interval=2]
 */

$opts = getopt('', [
    'host:',
    'user:',
    'pass:',
    'api-key:',
    'base-url::',
    'agent-profile::',
    'poll-timeout::',
    'poll-interval::',
]);

$host = (string) ($opts['host'] ?? '92.113.19.130');
$user = (string) ($opts['user'] ?? 'u234903558.sportifywebsite');
$pass = (string) ($opts['pass'] ?? '');
$apiKey = (string) ($opts['api-key'] ?? '');
$baseUrl = (string) ($opts['base-url'] ?? 'https://api.manus.ai/v2');
$agentProfile = (string) ($opts['agent-profile'] ?? 'manus-1.6-lite');
$pollTimeout = (string) ($opts['poll-timeout'] ?? '45');
$pollInterval = (string) ($opts['poll-interval'] ?? '2');

if ($pass === '' || $apiKey === '') {
    fwrite(STDERR, "missing --pass or --api-key\n");
    exit(1);
}

$roots = [
    '.',
    'public_html',
    'domains/sportifyplus.de/public_html',
];

$conn = ftp_ssl_connect($host, 21, 20) ?: ftp_connect($host, 21, 20);
if (!$conn || !@ftp_login($conn, $user, $pass)) {
    fwrite(STDERR, "FTP login failed\n");
    exit(1);
}
ftp_pasv($conn, true);

$keys = [
    'MANUS_API_KEY' => $apiKey,
    'HOPN_MANUS_API_KEY' => $apiKey,
    'MANUS_BASE_URL' => $baseUrl,
    'MANUS_AGENT_PROFILE' => $agentProfile,
    'MANUS_POLL_TIMEOUT' => $pollTimeout,
    'MANUS_POLL_INTERVAL' => $pollInterval,
];

foreach ($roots as $root) {
    $remote = ($root === '.' ? '' : rtrim($root, '/') . '/') . '.env';
    $tmp = tempnam(sys_get_temp_dir(), 'sportify-manus-env-');
    if (!@ftp_get($conn, $tmp, $remote, FTP_BINARY)) {
        @unlink($tmp);
        echo "skip missing /$remote\n";
        continue;
    }

    $lines = file($tmp, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        @unlink($tmp);
        continue;
    }

    $seen = array_fill_keys(array_keys($keys), false);
    $out = [];
    foreach ($lines as $line) {
        $trim = trim($line);
        $matched = false;
        foreach ($keys as $key => $value) {
            if (str_starts_with($trim, $key . '=')) {
                $out[] = $key . '=' . $value;
                $seen[$key] = true;
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            $out[] = $line;
        }
    }
    foreach ($keys as $key => $value) {
        if (!$seen[$key]) {
            $out[] = $key . '=' . $value;
        }
    }

    file_put_contents($tmp, implode("\n", $out) . "\n");
    if (@ftp_put($conn, $remote, $tmp, FTP_BINARY)) {
        echo "patched /$remote (manus keys set, values redacted)\n";
    } else {
        echo "failed /$remote\n";
    }
    @unlink($tmp);
}

ftp_close($conn);
