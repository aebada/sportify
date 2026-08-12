<?php
/**
 * Sportify FIT-Pass — role-based membership tiers (Free → Elite).
 * Plans are seeded into membership_plans; this config drives features, limits & pricing.
 */

$tierNames = ['free' => 'Free', 'basic' => 'Basic', 'pro' => 'Pro', 'elite' => 'Elite'];

$prices = [
    'player'  => ['basic' => 999,  'pro' => 1999,  'elite' => 4999],
    'club'    => ['basic' => 999,  'pro' => 1999,  'elite' => 4999],
    'scout'   => ['basic' => 999,  'pro' => 1999,  'elite' => 4999],
    'trainer' => ['basic' => 999,  'pro' => 1999,  'elite' => 4999],
    'fan'     => ['basic' => 499,  'pro' => 999,   'elite' => 1999],
];

$annualDiscount = 0.17;

$buildPlan = static function (string $role, int $tierLevel, string $slug, array $features, array $limits, bool $popular = false) use ($prices, $tierNames, $annualDiscount): array {
    $tierKey = match ($tierLevel) {
        0 => 'free',
        1 => 'basic',
        2 => 'pro',
        3 => 'elite',
        default => 'free',
    };
    $monthly = $tierLevel === 0 ? 0 : ($prices[$role][$tierKey] ?? 999);
    $yearly = $tierLevel === 0 ? 0 : (int) round($monthly * 12 * (1 - $annualDiscount));

    return [
        'role'           => $role,
        'slug'           => $slug,
        'name'           => $tierNames[$tierKey],
        'tier_level'     => $tierLevel,
        'tier_key'       => $tierKey,
        'price_monthly'  => $monthly,
        'price_yearly'   => $yearly,
        'currency'       => 'EUR',
        'features'       => $features,
        'limits'         => $limits,
        'popular'        => $popular,
        'sort_order'     => $tierLevel,
    ];
};

return [
    'product_name' => 'Sportify FIT-Pass',
    'currency'     => 'EUR',
    'currency_symbol' => '€',
    'annual_discount' => $annualDiscount,
    'trial_days'   => 14,
    'trial_tier'   => 'pro',
    'trial_roles'  => ['club'],

    /** Map users.role → membership role key */
    'role_map' => [
        'player'   => 'player',
        'club'     => 'club',
        'scout'    => 'scout',
        'agent'    => 'scout',
        'trainer'  => 'trainer',
        'provider' => 'trainer',
        'coach'    => 'trainer',
        'fan'      => 'fan',
    ],

    'roles' => [
        'player'  => ['label' => 'Player',  'icon' => '⚽', 'description' => 'Develop your profile, get AI insights, and stand out to scouts.'],
        'club'    => ['label' => 'Club',    'icon' => '🛡️', 'description' => 'Discover talent, manage contacts, and run your transfer window.'],
        'scout'   => ['label' => 'Scout',   'icon' => '🔍', 'description' => 'Build shortlists, export data, and use AI scouting tools.'],
        'trainer' => ['label' => 'Trainer', 'icon' => '🏋️', 'description' => 'Offer Pro Coaches tiers, bookings, and analytics.'],
        'fan'     => ['label' => 'Fan',     'icon' => '🎉', 'description' => 'Predictions, fan pages, Book-a-Talk, and marketplace perks.'],
    ],

    'feature_labels' => [
        'profile'               => 'membership.features.profile',
        'browse'                => 'membership.features.browse',
        'ai_analysis'           => 'membership.features.ai_analysis',
        'predictions_limited'   => 'membership.features.predictions_limited',
        'unlimited_predictions' => 'membership.features.unlimited_predictions',
        'priority_listing'      => 'membership.features.priority_listing',
        'book_a_talk_receive'   => 'membership.features.book_a_talk_receive',
        'featured_profile'      => 'membership.features.featured_profile',
        'pro_coaches_discount'  => 'membership.features.pro_coaches_discount',
        'browse_players'        => 'membership.features.browse_players',
        'contacts_limited'      => 'membership.features.contacts_limited',
        'listings_limited'      => 'membership.features.listings_limited',
        'unlimited_contacts'    => 'membership.features.unlimited_contacts',
        'scout_tools'           => 'membership.features.scout_tools',
        'crm_lite'              => 'membership.features.crm_lite',
        'ai_recommendations'    => 'membership.features.ai_recommendations',
        'featured_club_badge'   => 'membership.features.featured_club_badge',
        'shortlist_limited'     => 'membership.features.shortlist_limited',
        'add_players_limited'   => 'membership.features.add_players_limited',
        'unlimited_players'     => 'membership.features.unlimited_players',
        'bulk_export'           => 'membership.features.bulk_export',
        'ai_scout'              => 'membership.features.ai_scout',
        'priority_support'      => 'membership.features.priority_support',
        'verified_scout_badge'  => 'membership.features.verified_scout_badge',
        'pro_coaches_1_tier'    => 'membership.features.pro_coaches_1_tier',
        'pro_coaches_3_tiers'   => 'membership.features.pro_coaches_3_tiers',
        'session_booking'       => 'membership.features.session_booking',
        'featured_coach'        => 'membership.features.featured_coach',
        'analytics'             => 'membership.features.analytics',
        'chat_groups'           => 'membership.features.chat_groups',
        'predictions'           => 'membership.features.predictions',
        'fan_page'              => 'membership.features.fan_page',
        'book_a_talk'           => 'membership.features.book_a_talk',
        'marketplace_discount'  => 'membership.features.marketplace_discount',
        'all_access'            => 'membership.features.all_access',
    ],

    'limit_labels' => [
        'contacts_per_month'  => 'membership.limits.contacts_per_month',
        'shortlist_max'       => 'membership.limits.shortlist_max',
        'players_add_max'     => 'membership.limits.players_add_max',
        'predictions_per_month' => 'membership.limits.predictions_per_month',
        'listings_max'        => 'membership.limits.listings_max',
        'pro_coach_tiers'     => 'membership.limits.pro_coach_tiers',
    ],

    'plans' => [
        // Player × 4
        $buildPlan('player', 0, 'player-free',  ['profile', 'browse'], ['predictions_per_month' => 0]),
        $buildPlan('player', 1, 'player-basic', ['profile', 'browse', 'ai_analysis', 'predictions_limited'], ['predictions_per_month' => 5]),
        $buildPlan('player', 2, 'player-pro',   ['profile', 'browse', 'ai_analysis', 'unlimited_predictions', 'priority_listing', 'book_a_talk_receive'], [], true),
        $buildPlan('player', 3, 'player-elite', ['profile', 'browse', 'ai_analysis', 'unlimited_predictions', 'priority_listing', 'book_a_talk_receive', 'featured_profile', 'pro_coaches_discount'], []),

        // Club × 4
        $buildPlan('club', 0, 'club-free',  ['browse_players'], ['contacts_per_month' => 0, 'listings_max' => 0]),
        $buildPlan('club', 1, 'club-basic', ['browse_players', 'contacts_limited', 'listings_limited'], ['contacts_per_month' => 10, 'listings_max' => 1]),
        $buildPlan('club', 2, 'club-pro',   ['browse_players', 'unlimited_contacts', 'scout_tools', 'crm_lite'], [], true),
        $buildPlan('club', 3, 'club-elite', ['browse_players', 'unlimited_contacts', 'scout_tools', 'crm_lite', 'ai_recommendations', 'featured_club_badge'], []),

        // Scout × 4
        $buildPlan('scout', 0, 'scout-free',  ['shortlist_limited'], ['shortlist_max' => 10]),
        $buildPlan('scout', 1, 'scout-basic', ['shortlist_limited', 'add_players_limited'], ['shortlist_max' => 25, 'players_add_max' => 25]),
        $buildPlan('scout', 2, 'scout-pro',   ['unlimited_players', 'bulk_export', 'ai_scout'], [], true),
        $buildPlan('scout', 3, 'scout-elite', ['unlimited_players', 'bulk_export', 'ai_scout', 'priority_support', 'verified_scout_badge'], []),

        // Trainer × 4
        $buildPlan('trainer', 0, 'trainer-free',  ['profile'], []),
        $buildPlan('trainer', 1, 'trainer-basic', ['profile', 'pro_coaches_1_tier'], ['pro_coach_tiers' => 1]),
        $buildPlan('trainer', 2, 'trainer-pro',   ['profile', 'pro_coaches_3_tiers', 'session_booking'], ['pro_coach_tiers' => 3], true),
        $buildPlan('trainer', 3, 'trainer-elite', ['profile', 'pro_coaches_3_tiers', 'session_booking', 'featured_coach', 'analytics'], []),

        // Fan × 4
        $buildPlan('fan', 0, 'fan-free',  ['browse', 'chat_groups'], []),
        $buildPlan('fan', 1, 'fan-basic', ['browse', 'chat_groups', 'predictions', 'fan_page'], []),
        $buildPlan('fan', 2, 'fan-pro',   ['browse', 'chat_groups', 'predictions', 'fan_page', 'book_a_talk', 'marketplace_discount'], [], true),
        $buildPlan('fan', 3, 'fan-elite', ['browse', 'chat_groups', 'predictions', 'fan_page', 'book_a_talk', 'marketplace_discount', 'all_access'], []),
    ],
];
