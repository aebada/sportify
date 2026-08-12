<?php

namespace App\Models;

use App\Core\Database;

class PartnerLeadRepository
{
    public const TYPES = [
        'media', 'club', 'league', 'association', 'academy',
        'agency', 'tech', 'brand', 'facility', 'community',
    ];

    public const INVITE_STATUSES = [
        'pending', 'queued', 'sent', 'accepted', 'skipped', 'bounced', 'failed',
    ];

    public const EMAIL_CONFIDENCE = ['verified', 'needs_research', 'unverified'];

    public static function ensureSchema(): void
    {
        if (Database::tableExists('partner_leads')) {
            return;
        }
        $pdo = Database::connection();
        if (!$pdo) {
            return;
        }
        $isSqlite = Database::isSqlite();
        $auto = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $json = $isSqlite ? 'TEXT' : 'JSON';
        $pdo->exec("CREATE TABLE IF NOT EXISTS partner_leads (
            id {$auto},
            name VARCHAR(190) NOT NULL,
            type VARCHAR(40) NOT NULL DEFAULT 'media',
            subtype VARCHAR(80) NULL,
            country VARCHAR(8) NULL,
            city VARCHAR(80) NULL,
            league VARCHAR(120) NULL,
            website VARCHAR(255) NULL,
            email VARCHAR(190) NULL,
            email_confidence VARCHAR(30) NOT NULL DEFAULT 'needs_research',
            source_url VARCHAR(500) NULL,
            invite_status VARCHAR(30) NOT NULL DEFAULT 'pending',
            invited_at DATETIME NULL,
            accepted_at DATETIME NULL,
            notes TEXT NULL,
            tags {$json} NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS partner_invite_logs (
            id {$auto},
            partner_id INT NOT NULL,
            campaign VARCHAR(120) NOT NULL DEFAULT 'official_partner',
            from_address VARCHAR(190) NOT NULL,
            to_address VARCHAR(190) NOT NULL,
            subject VARCHAR(255) NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'queued',
            error_message TEXT NULL,
            meta {$json} NULL,
            created_at DATETIME NULL
        )");
    }

    public static function all(array $filters = []): array
    {
        self::ensureSchema();
        if (!Database::tableExists('partner_leads')) {
            return [];
        }
        $sql = 'SELECT * FROM partner_leads WHERE 1=1';
        $params = [];
        if (!empty($filters['type'])) {
            $sql .= ' AND type = ?';
            $params[] = $filters['type'];
        }
        if (!empty($filters['invite_status'])) {
            $sql .= ' AND invite_status = ?';
            $params[] = $filters['invite_status'];
        }
        if (!empty($filters['email_confidence'])) {
            $sql .= ' AND email_confidence = ?';
            $params[] = $filters['email_confidence'];
        }
        if (!empty($filters['country'])) {
            $sql .= ' AND country = ?';
            $params[] = strtoupper($filters['country']);
        }
        if (isset($filters['has_email'])) {
            $sql .= !empty($filters['has_email'])
                ? " AND email IS NOT NULL AND email != ''"
                : " AND (email IS NULL OR email = '')";
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (name LIKE ? OR email LIKE ? OR league LIKE ? OR city LIKE ? OR website LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        $sql .= ' ORDER BY type ASC, name ASC';
        return array_map([self::class, 'decorate'], Database::select($sql, $params));
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        $row = Database::first('SELECT * FROM partner_leads WHERE id = ? LIMIT 1', [$id]);
        return $row ? self::decorate($row) : null;
    }

    public static function upsert(array $data): ?int
    {
        self::ensureSchema();
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            return null;
        }
        $email = isset($data['email']) && $data['email'] !== ''
            ? strtolower(trim((string) $data['email']))
            : null;
        $existing = null;
        if ($email) {
            $existing = Database::first(
                'SELECT id FROM partner_leads WHERE LOWER(name) = ? AND LOWER(email) = ? LIMIT 1',
                [mb_strtolower($name), $email]
            );
        }
        if (!$existing) {
            $existing = Database::first(
                'SELECT id FROM partner_leads WHERE LOWER(name) = ? AND type = ? AND (email IS NULL OR email = ?) LIMIT 1',
                [mb_strtolower($name), $data['type'] ?? 'media', $email ?? '']
            );
        }

        $now = date('Y-m-d H:i:s');
        $tags = self::encodeTags($data['tags'] ?? []);
        $type = in_array($data['type'] ?? '', self::TYPES, true) ? $data['type'] : 'media';
        $confidence = in_array($data['email_confidence'] ?? '', self::EMAIL_CONFIDENCE, true)
            ? $data['email_confidence']
            : ($email ? 'needs_research' : 'needs_research');
        $invite = in_array($data['invite_status'] ?? '', self::INVITE_STATUSES, true)
            ? $data['invite_status']
            : (($email && $confidence === 'verified') ? 'queued' : 'pending');

        $payload = [
            $name,
            $type,
            $data['subtype'] ?? null,
            isset($data['country']) ? strtoupper((string) $data['country']) : null,
            $data['city'] ?? null,
            $data['league'] ?? null,
            $data['website'] ?? null,
            $email,
            $confidence,
            $data['source_url'] ?? null,
            $invite,
            $data['notes'] ?? null,
            $tags,
            $now,
        ];

        if ($existing) {
            Database::execute(
                'UPDATE partner_leads SET name=?, type=?, subtype=?, country=?, city=?, league=?, website=?,
                 email=?, email_confidence=?, source_url=?, invite_status=?, notes=?, tags=?, updated_at=?
                 WHERE id=?',
                [...$payload, (int) $existing['id']]
            );
            return (int) $existing['id'];
        }

        Database::execute(
            'INSERT INTO partner_leads
             (name, type, subtype, country, city, league, website, email, email_confidence, source_url,
              invite_status, notes, tags, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [...$payload, $now]
        );
        return (int) Database::lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $row = self::find($id);
        if (!$row) {
            return false;
        }
        $merged = array_merge($row, $data);
        $merged['tags'] = $data['tags'] ?? ($row['tags_list'] ?? []);
        return (bool) self::upsert($merged);
    }

    public static function markInvite(int $id, string $status, ?string $error = null): void
    {
        $now = date('Y-m-d H:i:s');
        $accepted = $status === 'accepted' ? $now : null;
        Database::execute(
            'UPDATE partner_leads SET invite_status = ?, invited_at = COALESCE(invited_at, ?),
             accepted_at = COALESCE(?, accepted_at), updated_at = ?,
             notes = CASE WHEN ? IS NOT NULL AND ? != \'\' THEN TRIM(COALESCE(notes,\'\') || \' | invite: \' || ?) ELSE notes END
             WHERE id = ?',
            [$status, $now, $accepted, $now, $error, $error, $error, $id]
        );
        // SQLite concat may differ — fallback simple update if needed
        if ($error) {
            Database::execute(
                'UPDATE partner_leads SET invite_status = ?, invited_at = COALESCE(invited_at, ?), updated_at = ? WHERE id = ?',
                [$status, $now, $now, $id]
            );
        }
    }

    public static function setInviteStatus(int $id, string $status): void
    {
        $now = date('Y-m-d H:i:s');
        $params = [$status, $now, $id];
        $sql = 'UPDATE partner_leads SET invite_status = ?, updated_at = ?';
        if ($status === 'sent') {
            $sql .= ', invited_at = COALESCE(invited_at, ?)';
            $params = [$status, $now, $now, $id];
        } elseif ($status === 'accepted') {
            $sql .= ', accepted_at = ?';
            $params = [$status, $now, $now, $id];
        }
        $sql .= ' WHERE id = ?';
        Database::execute($sql, $params);
    }

    public static function stats(): array
    {
        self::ensureSchema();
        if (!Database::tableExists('partner_leads')) {
            return ['total' => 0, 'with_email' => 0, 'verified' => 0, 'by_type' => [], 'by_invite' => []];
        }
        $total = (int) (Database::first('SELECT COUNT(*) AS c FROM partner_leads')['c'] ?? 0);
        $withEmail = (int) (Database::first("SELECT COUNT(*) AS c FROM partner_leads WHERE email IS NOT NULL AND email != ''")['c'] ?? 0);
        $verified = (int) (Database::first("SELECT COUNT(*) AS c FROM partner_leads WHERE email_confidence = 'verified' AND email IS NOT NULL AND email != ''")['c'] ?? 0);
        $byType = [];
        foreach (Database::select('SELECT type, COUNT(*) AS c FROM partner_leads GROUP BY type') as $r) {
            $byType[$r['type']] = (int) $r['c'];
        }
        $byInvite = [];
        foreach (Database::select('SELECT invite_status, COUNT(*) AS c FROM partner_leads GROUP BY invite_status') as $r) {
            $byInvite[$r['invite_status']] = (int) $r['c'];
        }
        return compact('total', 'withEmail', 'verified', 'byType', 'byInvite');
    }

    public static function inviteCandidates(bool $verifiedOnly = true, ?string $type = null): array
    {
        $filters = ['has_email' => 1];
        if ($verifiedOnly) {
            $filters['email_confidence'] = 'verified';
        }
        if ($type) {
            $filters['type'] = $type;
        }
        return array_values(array_filter(
            self::all($filters),
            static fn ($p) => !in_array($p['invite_status'], ['sent', 'accepted', 'opted_out'], true)
                && !empty($p['email'])
        ));
    }

    public static function logInvite(int $partnerId, string $from, string $to, string $subject, string $status, ?string $error = null, array $meta = []): void
    {
        self::ensureSchema();
        Database::execute(
            'INSERT INTO partner_invite_logs (partner_id, campaign, from_address, to_address, subject, status, error_message, meta, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $partnerId,
                'official_partner',
                $from,
                $to,
                $subject,
                $status,
                $error,
                json_encode($meta),
                date('Y-m-d H:i:s'),
            ]
        );
    }

    private static function decorate(array $row): array
    {
        $tags = $row['tags'] ?? '[]';
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $row['tags_list'] = is_array($decoded) ? $decoded : [];
        } else {
            $row['tags_list'] = is_array($tags) ? $tags : [];
        }
        return $row;
    }

    private static function encodeTags($tags): string
    {
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $tags = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $tags)));
        }
        if (!is_array($tags)) {
            $tags = [];
        }
        return json_encode(array_values(array_unique($tags)));
    }
}
