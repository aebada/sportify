<?php

namespace App\Services;

use App\Core\Database;

/**
 * Ensures schema exists (e.g. first registration on a fresh checkout).
 */
class DatabaseSetup
{
    public static function ensure(): bool
    {
        if (!Database::connection()) {
            return false;
        }
        if (Database::tableExists('users')) {
            return true;
        }
        return self::migrate();
    }

    public static function migrate(): bool
    {
        $pdo = Database::connection();
        if (!$pdo) {
            return false;
        }

        $schema = require dirname(__DIR__, 2) . '/database/schema.php';
        $isSqlite = Database::isSqlite();
        $auto = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $json = $isSqlite ? 'TEXT' : 'JSON';

        foreach ($schema as $sql) {
            $sql = str_replace(['{{AUTO}}', '{{JSON}}'], [$auto, $json], $sql);
            $pdo->exec($sql);
        }

        return Database::tableExists('users');
    }
}
