<?php

namespace App\Services;

use App\Models\PartnerLeadRepository;

/**
 * Merges partners-*.json seed files (research agents + curated) into partner_leads.
 * Deduplicates by lowercase email, then by name+website.
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
            'partners-de-expand.json',
            'partners-eu-intl-expand.json',
            'partners-biz-expand.json',
            'partners-directory-extra.json',
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
     * @return array{upserted:int,skipped_dupes:int,files:int,file_names:array<int,string>,by_type:array<string,int>,with_email:int,verified:int,unique_rows:int}
     */
    public static function import(): array
    {
        PartnerLeadRepository::ensureSchema();
        $byType = [];
        $withEmail = 0;
        $verified = 0;
        $names = [];
        $seenEmail = [];
        $seenNameWeb = [];
        $unique = [];
        $skipped = 0;

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
                $email = isset($row['email']) && $row['email'] !== ''
                    ? strtolower(trim((string) $row['email']))
                    : '';
                $website = isset($row['website']) && $row['website'] !== ''
                    ? rtrim(strtolower(trim((string) $row['website'])), '/')
                    : '';
                $nameKey = mb_strtolower(trim((string) $row['name']));
                $nwKey = $nameKey . '|' . $website;

                if ($email !== '' && isset($seenEmail[$email])) {
                    $skipped++;
                    continue;
                }
                if ($website !== '' && isset($seenNameWeb[$nwKey])) {
                    $skipped++;
                    continue;
                }
                // Also skip pure name dupes when no website on either side and same email absence
                if ($website === '' && isset($seenNameWeb[$nameKey . '|'])) {
                    $skipped++;
                    continue;
                }

                if ($email !== '') {
                    $seenEmail[$email] = true;
                    $row['email'] = $email;
                }
                $seenNameWeb[$nwKey] = true;
                $unique[] = $row;
            }
        }

        $upserted = 0;
        foreach ($unique as $row) {
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

        return [
            'upserted' => $upserted,
            'unique_rows' => count($unique),
            'skipped_dupes' => $skipped,
            'files' => count($names),
            'file_names' => $names,
            'by_type' => $byType,
            'with_email' => $withEmail,
            'verified' => $verified,
        ];
    }
}
