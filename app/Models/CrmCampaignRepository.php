<?php

namespace App\Models;

use App\Core\Database;

class CrmCampaignRepository
{
    public const TYPES = ['email', 'social', 'referral', 'event'];
    public const STATUSES = ['draft', 'active', 'paused', 'completed'];

    public static function all(?string $status = null): array
    {
        if (!Database::tableExists('crm_campaigns')) {
            return [];
        }
        $sql = 'SELECT * FROM crm_campaigns';
        $params = [];
        if ($status) {
            $sql .= ' WHERE status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY created_at DESC';
        return Database::select($sql, $params);
    }

    public static function find(int $id): ?array
    {
        if (!Database::tableExists('crm_campaigns')) {
            return null;
        }
        return Database::first('SELECT * FROM crm_campaigns WHERE id = ? LIMIT 1', [$id]);
    }

    public static function findByName(string $name): ?array
    {
        if (!Database::tableExists('crm_campaigns') || trim($name) === '') {
            return null;
        }
        $row = Database::first(
            'SELECT * FROM crm_campaigns WHERE name = ? OR name LIKE ? ORDER BY CASE WHEN name = ? THEN 0 ELSE 1 END LIMIT 1',
            [trim($name), '%' . trim($name) . '%', trim($name)]
        );
        return $row ?: null;
    }

    public static function create(array $data): ?int
    {
        $now = date('Y-m-d H:i:s');
        $ok = Database::execute(
            'INSERT INTO crm_campaigns (name, type, status, start_date, end_date, utm_source, utm_medium, utm_campaign, budget_cents, notes, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['type'] ?? 'email',
                $data['status'] ?? 'draft',
                $data['start_date'] ?: null,
                $data['end_date'] ?: null,
                $data['utm_source'] ?: null,
                $data['utm_medium'] ?: null,
                $data['utm_campaign'] ?: null,
                isset($data['budget_cents']) && $data['budget_cents'] !== '' ? (int) $data['budget_cents'] : null,
                $data['notes'] ?: null,
                $now,
                $now,
            ]
        );
        return $ok ? (int) Database::lastInsertId() : null;
    }

    public static function update(int $id, array $data): bool
    {
        return Database::execute(
            'UPDATE crm_campaigns SET name = ?, type = ?, status = ?, start_date = ?, end_date = ?,
             utm_source = ?, utm_medium = ?, utm_campaign = ?, budget_cents = ?, notes = ?, updated_at = ?
             WHERE id = ?',
            [
                $data['name'],
                $data['type'] ?? 'email',
                $data['status'] ?? 'draft',
                $data['start_date'] ?: null,
                $data['end_date'] ?: null,
                $data['utm_source'] ?: null,
                $data['utm_medium'] ?: null,
                $data['utm_campaign'] ?: null,
                isset($data['budget_cents']) && $data['budget_cents'] !== '' ? (int) $data['budget_cents'] : null,
                $data['notes'] ?: null,
                date('Y-m-d H:i:s'),
                $id,
            ]
        );
    }

    public static function contactCount(int $campaignId): int
    {
        $row = Database::first('SELECT COUNT(*) AS c FROM crm_contacts WHERE campaign_id = ?', [$campaignId]);
        return (int) ($row['c'] ?? 0);
    }
}
