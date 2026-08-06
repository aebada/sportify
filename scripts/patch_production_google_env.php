<?php
declare(strict_types=1);

$opts = getopt('', ['host:', 'user:', 'pass:', 'client-id:', 'client-secret:', 'redirect-uri:']);
$host = (string) ($opts['host'] ?? '92.113.19.130');
$user = (string) ($opts['user'] ?? 'u234903558.sportifywebsite');
$pass = (string) ($opts['pass'] ?? '');
$clientId = (string) ($opts['client-id'] ?? '');
$clientSecret = (string) ($opts['client-secret'] ?? '');
$redirectUri = (string) ($opts['redirect-uri'] ?? 'https://sportifyplus.de/auth/google/callback');

if ($pass === '' || $clientId === '' || $clientSecret === '') {
    fwrite(STDERR, "missing --pass, --client-id, or --client-secret\n");
    exit(1);
}

$roots = [
    '/domains/sportifyplus.de/public_html',
    '/domains/sportifyplus.de/public_html/talents',
];

$conn = ftp_ssl_connect($host, 21, 20) ?: ftp_connect($host, 21, 20);
if (!$conn || !@ftp_login($conn, $user, $pass)) {
    fwrite(STDERR, "FTP login failed\n");
    exit(1);
}
ftp_pasv($conn, true);

foreach ($roots as $root) {
    $remote = rtrim($root, '/') . '/.env';
    $tmp = tempnam(sys_get_temp_dir(), 'sportify-env-');
    if (!@ftp_get($conn, $tmp, $remote, FTP_BINARY)) {
        @unlink($tmp);
        echo "skip missing $remote\n";
        continue;
    }

    $lines = file($tmp, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        @unlink($tmp);
        continue;
    }

    $keys = [
        'GOOGLE_CLIENT_ID' => $clientId,
        'GOOGLE_CLIENT_SECRET' => $clientSecret,
        'GOOGLE_REDIRECT_URI' => $redirectUri,
    ];
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
        echo "patched $remote\n";
    } else {
        echo "failed $remote\n";
    }
    @unlink($tmp);
}

ftp_close($conn);
