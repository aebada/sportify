<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * PDO database wrapper supporting MySQL (Hostinger) and SQLite (local).
 * Gracefully reports availability so repositories can fall back to seed data.
 */
class Database
{
    protected static ?PDO $pdo = null;
    protected static ?bool $available = null;

    public static function connection(): ?PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        if (self::$available === false) {
            return null;
        }

        $cfg = App::config('db');
        try {
            if (($cfg['driver'] ?? 'sqlite') === 'sqlite') {
                $path = $cfg['database'];
                $dir = dirname($path);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                }
                $dsn = 'sqlite:' . $path;
                self::$pdo = new PDO($dsn);
            } else {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $cfg['host'], $cfg['port'], $cfg['database'], $cfg['charset']
                );
                self::$pdo = new PDO($dsn, $cfg['username'], $cfg['password']);
            }
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$available = true;
        } catch (PDOException $e) {
            self::$available = false;
            self::$pdo = null;
        }

        return self::$pdo;
    }

    public static function available(): bool
    {
        if (self::$available === null) {
            self::connection();
        }
        return (bool) self::$available;
    }

    public static function tableExists(string $table): bool
    {
        $pdo = self::connection();
        if (!$pdo) {
            return false;
        }
        try {
            if (self::isSqlite()) {
                $stmt = $pdo->prepare(
                    "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = ? LIMIT 1"
                );
                $stmt->execute([$table]);
                return (bool) $stmt->fetchColumn();
            }
            $stmt = $pdo->prepare(
                'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1'
            );
            $stmt->execute([$table]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function columnExists(string $table, string $column): bool
    {
        $pdo = self::connection();
        if (!$pdo || !self::tableExists($table)) {
            return false;
        }
        try {
            if (self::isSqlite()) {
                $rows = $pdo->query("PRAGMA table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $r) {
                    if (($r['name'] ?? '') === $column) {
                        return true;
                    }
                }
                return false;
            }
            $stmt = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
            $stmt->execute([$column]);
            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /** Returns true if a given table exists and has rows. */
    public static function hasData(string $table): bool
    {
        $pdo = self::connection();
        if (!$pdo) {
            return false;
        }
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function select(string $sql, array $params = []): array
    {
        $pdo = self::connection();
        if (!$pdo) {
            return [];
        }
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function first(string $sql, array $params = []): ?array
    {
        $rows = self::select($sql, $params);
        return $rows[0] ?? null;
    }

    public static function execute(string $sql, array $params = []): bool
    {
        $pdo = self::connection();
        if (!$pdo) {
            return false;
        }
        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function lastInsertId(): string
    {
        return self::$pdo ? self::$pdo->lastInsertId() : '0';
    }

    public static function isSqlite(): bool
    {
        return (App::config('db.driver') ?? 'sqlite') === 'sqlite';
    }
}
