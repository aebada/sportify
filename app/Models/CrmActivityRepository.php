<?php

namespace App\Models;

use App\Core\Database;

class CrmActivityRepository
{
    public const TYPES = ['call', 'email', 'note', 'status_change'];

    public static function forContact(int $contactId): array
    {
        if (!Database::tableExists('crm_activities')) {
            return [];
        }
        return Database::select(
            'SELECT a.*, u.name AS user_name
             FROM crm_activities a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.contact_id = ?
             ORDER BY a.created_at DESC',
            [$contactId]
        );
    }

    public static function log(
        int $contactId,
        ?int $userId,
        string $type,
        ?string $subject,
        ?string $body,
        ?array $meta = null
    ): ?int {
        $now = date('Y-m-d H:i:s');
        $ok = Database::execute(
            'INSERT INTO crm_activities (contact_id, user_id, type, subject, body, meta, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $contactId,
                $userId,
                $type,
                $subject,
                $body,
                $meta ? json_encode($meta) : null,
                $now,
            ]
        );
        return $ok ? (int) Database::lastInsertId() : null;
    }
}
