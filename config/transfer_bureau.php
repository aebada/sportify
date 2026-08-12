<?php
/**
 * Sportify Transfer Bureau — amateur football transfer marketplace.
 */

return [
    'free_contact_limit' => 3,
    'contact_limit_period' => 'month',

    'team_levels' => [
        '1_herren'       => ['label' => '1. Herren', 'group' => 'senior'],
        '2_herren'       => ['label' => '2. Herren', 'group' => 'senior'],
        'a_jugend'       => ['label' => 'A-Jugend', 'group' => 'youth'],
        'b_jugend'       => ['label' => 'B-Jugend', 'group' => 'youth'],
        'c_jugend'       => ['label' => 'C-Jugend', 'group' => 'youth'],
        'frauen'         => ['label' => 'Frauen', 'group' => 'women'],
        'frauen_2'       => ['label' => 'Frauen 2', 'group' => 'women'],
        'vorstand'       => ['label' => 'Vorstand', 'group' => 'staff'],
        'sportlicher_leiter' => ['label' => 'Sportlicher Leiter', 'group' => 'staff'],
    ],

    'positions' => [
        'GK' => 'Torwart',
        'CB' => 'Innenverteidiger',
        'LB' => 'Linksverteidiger',
        'RB' => 'Rechtsverteidiger',
        'DM' => 'Defensives Mittelfeld',
        'CM' => 'Zentrales Mittelfeld',
        'AM' => 'Offensives Mittelfeld',
        'LW' => 'Linksaußen',
        'RW' => 'Rechtsaußen',
        'ST' => 'Stürmer',
        'TRAINER' => 'Trainer',
    ],

    'league_levels' => [
        'kreisliga'     => 'Kreisliga',
        'bezirksliga'   => 'Bezirksliga',
        'landesliga'    => 'Landesliga',
        'verbandsliga'  => 'Verbandsliga',
        'oberliga'      => 'Oberliga',
        'regionalliga'  => 'Regionalliga',
        'amateur'       => 'Amateur / Hobby',
    ],

    'genders' => [
        'men'   => 'Herren',
        'women' => 'Frauen',
    ],

    'availability' => [
        'immediate'   => 'Sofort verfügbar',
        'next_season' => 'Ab nächster Saison',
    ],

    'cities' => [
        'Berlin'   => ['lat' => 52.5200, 'lng' => 13.4050],
        'Munich'   => ['lat' => 48.1372, 'lng' => 11.5761],
        'Hamburg'  => ['lat' => 53.5511, 'lng' => 9.9937],
        'Cologne'  => ['lat' => 50.9375, 'lng' => 6.9603],
        'Dortmund' => ['lat' => 51.5136, 'lng' => 7.4653],
    ],

    'premium_plans' => [
        'club_monthly' => [
            'id'           => 'club_monthly',
            'role'         => 'club',
            'name'         => 'Verein Premium',
            'price_cents'  => 1999,
            'period'       => 'monthly',
            'period_label' => '€19,99 / Monat',
            'features'     => [
                'Unbegrenzte Kontaktaufnahmen',
                'Hervorgehobene Stellenanzeigen',
                'Verifiziertes Vereins-Badge',
                'Priorität in der Spielersuche',
            ],
        ],
        'player_weekly' => [
            'id'           => 'player_weekly',
            'role'         => 'player',
            'name'         => 'Spieler Premium',
            'price_cents'  => 499,
            'period'       => 'weekly',
            'period_label' => '€4,99 / Woche',
            'features'     => [
                'Unbegrenzte Kontaktaufnahmen',
                'Hervorgehobenes Profil',
                'Oben in den Suchergebnissen',
                'Probetraining-Einladungen',
            ],
        ],
        'trainer_monthly' => [
            'id'           => 'trainer_monthly',
            'role'         => 'trainer',
            'name'         => 'Trainer Premium',
            'price_cents'  => 999,
            'period'       => 'monthly',
            'period_label' => '€9,99 / Monat',
            'features'     => [
                'Unbegrenzte Kontaktaufnahmen',
                'Hervorgehobenes Trainerprofil',
                'Benachrichtigung bei Vereinsbedarf',
            ],
        ],
    ],

    'payment_methods' => [
        'demo' => [
            'id'       => 'demo',
            'label'    => 'Demo-Zahlung (MVP)',
            'desc'     => 'Premium wird sofort aktiviert — keine echte Abbuchung',
            'icon'     => '🎟️',
            'provider' => 'manual',
            'instant'  => true,
        ],
        'card' => [
            'id'       => 'card',
            'label'    => 'Kredit- / Debitkarte',
            'desc'     => 'Sicher bezahlen mit Stripe',
            'icon'     => '💳',
            'provider' => 'stripe',
            'instant'  => false,
        ],
        'paypal' => [
            'id'       => 'paypal',
            'label'    => 'PayPal',
            'desc'     => 'Mit PayPal-Konto bezahlen',
            'icon'     => '🅿️',
            'provider' => 'paypal',
            'instant'  => false,
        ],
    ],

    'trial_statuses' => ['pending', 'accepted', 'declined', 'completed'],
];
