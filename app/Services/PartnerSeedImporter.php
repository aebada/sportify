<?php

namespace App\Services;

use App\Models\PartnerLeadRepository;

/**
 * Merges partners-*.json seed files (research agents + curated) into partner_leads.
 */
class PartnerSeedImporter
{
    public static function seedFiles(): array
    {
        $dir = dirname(__DIR__, 2) . '/database/seeds';
        $preferred = [
            'partners-media.json',
            'partners-clubs.json',
            'partners-leagues.json',
            'partners-ecosystem.json',
        ];
        $files = [];
        foreach ($preferred as $name) {
            $path = $dir . '/' . $name;
            if (is_file($path)) {
                $files[] = $path;
            }
        }
        // Any extra partners-*.json from research agents
        foreach (glob($dir . '/partners-*.json') ?: [] as $path) {
            if (!in_array($path, $files, true)) {
                $files[] = $path;
            }
        }
        return $files;
    }

    /**
     * @return array{upserted:int,files:int,file_names:array<int,string>,by_type:array<string,int>,with_email:int,verified:int}
     */
    public static function import(): array
    {
        PartnerLeadRepository::ensureSchema();
        $upserted = 0;
        $byType = [];
        $withEmail = 0;
        $verified = 0;
        $names = [];

        foreach (self::seedFiles() as $path) {
            $names[] = basename($path);
            $data = json_decode((string) file_get_contents($path), true);
            if (!is_array($data)) {
                continue;
            }
            foreach ($data as $row) {
                if (!is_array($row) || empty($row['name'])) {
                    continue;
                }
                $id = PartnerLeadRepository::upsert($row);
                if ($id) {
                    $upserted++;
                    $t = (string) ($row['type'] ?? 'media');
                    $byType[$t] = ($byType[$t] ?? 0) + 1;
                    if (!empty($row['email'])) {
                        $withEmail++;
                        if (($row['email_confidence'] ?? '') === 'verified') {
                            $verified++;
                        }
                    }
                }
            }
        }

        return [
            'upserted' => $upserted,
            'files' => count($names),
            'file_names' => $names,
            'by_type' => $byType,
            'with_email' => $withEmail,
            'verified' => $verified,
        ];
    }
}
