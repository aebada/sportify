<?php
declare(strict_types=1);
/**
 * FTP-safe Google OAuth column migrate (users.google_id + member_profiles.avatar).
 * Visit once after deploy: /migrate-google-oauth-20260720.php
 * deploy-marker: migrate-google-oauth-20260720f
 */
header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');

$root = dirname(__DIR__);
if (!is_file($root . '/bootstrap.php') && is_file(__DIR__ . '/bootstrap.php')) {
    $root = __DIR__;
}

require $root . '/bootstrap.php';

use App\Core\Database;

$pdo = Database::connection();
if (!$pdo) {
    echo "db=unavailable\n";
    exit(1);
}

$done = [];

if (!Database::tableExists('users')) {
    echo "users=missing\n";
    exit(1);
}

if (!Database::columnExists('users', 'google_id')) {
    try {
        if (Database::isSqlite()) {
            $pdo->exec('ALTER TABLE users ADD COLUMN google_id TEXT NULL');
        } else {
            $pdo->exec('ALTER TABLE users ADD COLUMN google_id VARCHAR(64) NULL');
            try {
                $pdo->exec('CREATE UNIQUE INDEX users_google_id_unique ON users (google_id)');
            } catch (Throwable $e) {
                echo 'google_id_index_note=' . $e->getMessage() . "\n";
            }
        }
        $done[] = 'users.google_id=added';
    } catch (Throwable $e) {
        echo 'users.google_id_error=' . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    $done[] = 'users.google_id=exists';
}

if (Database::tableExists('member_profiles')) {
    if (!Database::columnExists('member_profiles', 'avatar')) {
        try {
            if (Database::isSqlite()) {
                $pdo->exec('ALTER TABLE member_profiles ADD COLUMN avatar TEXT NULL');
            } else {
                $pdo->exec('ALTER TABLE member_profiles ADD COLUMN avatar VARCHAR(255) NULL');
            }
            $done[] = 'member_profiles.avatar=added';
        } catch (Throwable $e) {
            echo 'member_profiles.avatar_error=' . $e->getMessage() . "\n";
            exit(1);
        }
    } else {
        $done[] = 'member_profiles.avatar=exists';
    }
} else {
    $done[] = 'member_profiles=missing';
}

foreach ($done as $line) {
    echo $line . "\n";
}
echo "ok\n";
