<?php

namespace App\Models;

use App\Core\Database;

class CrmContactRepository
{
    public const STATUSES = ['new', 'contacted', 'qualified', 'converted', 'lost'];
    public const SOURCES = ['website', 'referral', 'social', 'event', 'email', 'scout', 'other'];
    public const TAGS = ['player_prospect', 'club', 'scout', 'newsletter'];

    public static function all(array $filters = []): array
    {
        if (!Database::tableExists('crm_contacts')) {
            return [];
        }
        $sql = 'SELECT c.*, camp.name AS campaign_name, u.name AS assigned_name
                FROM crm_contacts c
                LEFT JOIN crm_campaigns camp ON camp.id = c.campaign_id
                LEFT JOIN users u ON u.id = c.assigned_to
                WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND c.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['source'])) {
            $sql .= ' AND c.source = ?';
            $params[] = $filters['source'];
        }
        if (!empty($filters['campaign_id'])) {
            $sql .= ' AND c.campaign_id = ?';
            $params[] = (int) $filters['campaign_id'];
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= ' ORDER BY c.updated_at DESC, c.created_at DESC';
        $rows = Database::select($sql, $params);
        return array_map([self::class, 'decorate'], $rows);
    }

    public static function find(int $id): ?array
    {
        if (!Database::tableExists('crm_contacts')) {
            return null;
        }
        $row = Database::first(
            'SELECT c.*, camp.name AS campaign_name, u.name AS assigned_name, lu.name AS linked_user_name
             FROM crm_contacts c
             LEFT JOIN crm_campaigns camp ON camp.id = c.campaign_id
             LEFT JOIN users u ON u.id = c.assigned_to
             LEFT JOIN users lu ON lu.id = c.user_id
             WHERE c.id = ? LIMIT 1',
            [$id]
        );
        return $row ? self::decorate($row) : null;
    }

    public static function findByEmail(string $email): ?array
    {
        if (!Database::tableExists('crm_contacts') || $email === '') {
            return null;
        }
        $row = Database::first(
            'SELECT * FROM crm_contacts WHERE email = ? ORDER BY id DESC LIMIT 1',
            [strtolower($email)]
        );
        return $row ? self::decorate($row) : null;
    }

    public static function create(array $data): ?int
    {
        $now = date('Y-m-d H:i:s');
        $tags = self::encodeTags($data['tags'] ?? []);
        $ok = Database::execute(
            'INSERT INTO crm_contacts (name, email, phone, source, status, notes, assigned_to, user_id, campaign_id, referral_code, tags, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['email'] ? strtolower($data['email']) : null,
                $data['phone'] ?: null,
                $data['source'] ?? 'website',
                $data['status'] ?? 'new',
                $data['notes'] ?: null,
                !empty($data['assigned_to']) ? (int) $data['assigned_to'] : null,
                !empty($data['user_id']) ? (int) $data['user_id'] : null,
                !empty($data['campaign_id']) ? (int) $data['campaign_id'] : null,
                $data['referral_code'] ?: null,
                $tags,
                $now,
                $now,
            ]
        );
        return $ok ? (int) Database::lastInsertId() : null;
    }

    public static function update(int $id, array $data): bool
    {
        $tags = self::encodeTags($data['tags'] ?? []);
        return Database::execute(
            'UPDATE crm_contacts SET name = ?, email = ?, phone = ?, source = ?, status = ?, notes = ?,
             assigned_to = ?, user_id = ?, campaign_id = ?, referral_code = ?, tags = ?, updated_at = ?
             WHERE id = ?',
            [
                $data['name'],
                $data['email'] ? strtolower($data['email']) : null,
                $data['phone'] ?: null,
                $data['source'] ?? 'website',
                $data['status'] ?? 'new',
                $data['notes'] ?: null,
                !empty($data['assigned_to']) ? (int) $data['assigned_to'] : null,
                !empty($data['user_id']) ? (int) $data['user_id'] : null,
                !empty($data['campaign_id']) ? (int) $data['campaign_id'] : null,
                $data['referral_code'] ?: null,
                $tags,
                date('Y-m-d H:i:s'),
                $id,
            ]
        );
    }

    public static function linkUserByEmail(string $email, int $userId): void
    {
        $contact = self::findByEmail($email);
        if (!$contact || !empty($contact['user_id'])) {
            return;
        }
        self::update((int) $contact['id'], array_merge($contact, [
            'user_id' => $userId,
            'status'  => 'converted',
            'tags'    => $contact['tags_list'] ?? [],
        ]));
        CrmActivityRepository::log(
            (int) $contact['id'],
            null,
            'status_change',
            'Converted to registered user',
            'Lead linked to user #' . $userId,
            ['user_id' => $userId, 'new_status' => 'converted']
        );
    }

    public static function pipelineStats(): array
    {
        if (!Database::tableExists('crm_contacts')) {
            return array_fill_keys(self::STATUSES, 0);
        }
        $rows = Database::select('SELECT status, COUNT(*) AS c FROM crm_contacts GROUP BY status');
        $stats = array_fill_keys(self::STATUSES, 0);
        foreach ($rows as $r) {
            $stats[$r['status']] = (int) $r['c'];
        }
        return $stats;
    }

    public static function recent(int $limit = 8): array
    {
        if (!Database::tableExists('crm_contacts')) {
            return [];
        }
        $rows = Database::select(
            'SELECT * FROM crm_contacts ORDER BY created_at DESC LIMIT ' . (int) $limit
        );
        return array_map([self::class, 'decorate'], $rows);
    }

    /** @return array<int, array<string, mixed>> */
    public static function exportRows(array $filters = []): array
    {
        return self::all($filters);
    }

    protected static function decorate(array $row): array
    {
        $row['tags_list'] = self::decodeTags($row['tags'] ?? null);
        return $row;
    }

    /** @param array<int,string>|string|null $tags */
    protected static function encodeTags(array|string|null $tags): ?string
    {
        if (is_string($tags)) {
            return $tags;
        }
        if (!$tags) {
            return null;
        }
        return json_encode(array_values($tags));
    }

    /** @return array<int,string> */
    protected static function decodeTags(?string $json): array
    {
        if (!$json) {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
