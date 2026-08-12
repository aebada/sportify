<?php

return [
    'services' => [
        ['icon' => 'dumbbell', 'title' => 'Training Plans', 'desc' => 'Position-specific programmes built by professional coaches.'],
        ['icon' => 'building', 'title' => 'Partner Gyms', 'desc' => 'Access a network of partner gyms and performance centres.'],
        ['icon' => 'shield', 'title' => 'Sports Clubs', 'desc' => 'Train with affiliated clubs and academies near you.'],
        ['icon' => 'play', 'title' => 'Online Courses', 'desc' => 'Tactical, technical and mindset courses on demand.'],
        ['icon' => 'heart', 'title' => 'Wellness Services', 'desc' => 'Nutrition, recovery, physio and mental performance.'],
        ['icon' => 'chart', 'title' => 'Player Development', 'desc' => 'Track progress with measurable performance milestones.'],
    ],

    /* Ordered low → high price */
    'plans' => [
        [
            'name' => 'Digital', 'price' => '€5', 'period' => '/mo', 'tier' => 'digital', 'popular' => false,
            'amount_monthly' => 500, 'amount_yearly' => 5000,
            'tagline' => 'Online-only access',
            'features' => ['Online courses', 'Wellness content', 'Community access', 'Mobile-first learning'],
        ],
        [
            'name' => 'Youth', 'price' => '€7', 'period' => '/mo', 'tier' => 'youth', 'popular' => false,
            'amount_monthly' => 700, 'amount_yearly' => 7000,
            'tagline' => 'For players under 18',
            'features' => ['Age-appropriate training', 'Parent dashboard', 'Academy finder', '5 highlight uploads', 'Safeguarding tools'],
        ],
        [
            'name' => 'Bronze', 'price' => '€9', 'period' => '/mo', 'tier' => 'bronze', 'popular' => false,
            'amount_monthly' => 900, 'amount_yearly' => 9000,
            'tagline' => 'Start your journey',
            'features' => ['Basic training plans', 'Community access', 'Profile listing', 'Limited highlight uploads'],
        ],
        [
            'name' => 'Silver', 'price' => '€19', 'period' => '/mo', 'tier' => 'silver', 'popular' => true,
            'amount_monthly' => 1900, 'amount_yearly' => 19000,
            'tagline' => 'Most popular for players',
            'features' => ['All Bronze features', 'Partner gym access', 'Unlimited highlights', 'Performance tracking', 'Priority in search'],
        ],
        [
            'name' => 'Family', 'price' => '€29', 'period' => '/mo', 'tier' => 'family', 'popular' => false,
            'amount_monthly' => 2900, 'amount_yearly' => 29000,
            'tagline' => 'Up to 4 linked profiles',
            'features' => ['4 family member profiles', 'Shared gym passes', 'Family progress dashboard', 'All Silver features', 'Parent controls'],
        ],
        [
            'name' => 'Gold', 'price' => '€39', 'period' => '/mo', 'tier' => 'gold', 'popular' => false,
            'amount_monthly' => 3900, 'amount_yearly' => 39000,
            'tagline' => 'Elite development',
            'features' => ['All Silver features', 'Online courses library', 'Wellness & nutrition', '1:1 coaching credits', 'Verified profile badge'],
        ],
        [
            'name' => 'Scout', 'price' => '€59', 'period' => '/mo', 'tier' => 'scout', 'popular' => false,
            'amount_monthly' => 5900, 'amount_yearly' => 59000,
            'tagline' => 'For scouts & agents',
            'features' => ['Advanced player search', 'Export scouting reports', 'Watchlist alerts', '50 contact credits/mo', 'AI fit scores'],
        ],
        [
            'name' => 'Platinum', 'price' => '€79', 'period' => '/mo', 'tier' => 'platinum', 'popular' => false,
            'amount_monthly' => 7900, 'amount_yearly' => 79000,
            'tagline' => 'Semi-pro & pro pathway',
            'features' => ['All Gold features', 'Agent introduction credits', 'Media pack export', 'Priority club matching', 'Dedicated support line'],
        ],
        [
            'name' => 'Academy', 'price' => '€99', 'period' => '/mo', 'tier' => 'academy', 'popular' => false,
            'amount_monthly' => 9900, 'amount_yearly' => 99000,
            'tagline' => 'Youth academies & schools',
            'features' => ['Squad management (30 players)', 'Parent communication hub', 'Trial day tools', 'Progress reports', 'Coach assignment'],
        ],
        [
            'name' => 'Club+', 'price' => '€149', 'period' => '/mo', 'tier' => 'clubplus', 'popular' => false,
            'amount_monthly' => 14900, 'amount_yearly' => 149000,
            'tagline' => 'For clubs & academies',
            'features' => ['AI recommendations', 'Unlimited shortlists', 'Squad analytics', 'Recruitment dashboard', 'Direct player contact'],
        ],
        [
            'name' => 'Enterprise', 'price' => '€249', 'period' => '/mo', 'tier' => 'enterprise', 'popular' => false,
            'amount_monthly' => 24900, 'amount_yearly' => 249000,
            'tagline' => 'Federations & multi-club groups',
            'features' => ['Unlimited seats', 'White-label reports', 'API access', 'Multi-site dashboard', 'Dedicated success manager'],
        ],
    ],

    /* Feature matrix for compare table — tier slugs that include each feature */
    'comparison' => [
        ['feature' => 'Training plans',       'tiers' => ['youth','bronze','silver','family','gold','scout','platinum','academy','clubplus','enterprise']],
        ['feature' => 'Partner gyms',         'tiers' => ['silver','family','gold','platinum','academy','clubplus','enterprise']],
        ['feature' => 'Online courses',       'tiers' => ['digital','gold','platinum','academy','clubplus','enterprise']],
        ['feature' => 'AI scouting tools',    'tiers' => ['scout','clubplus','enterprise']],
        ['feature' => 'Priority search',      'tiers' => ['silver','family','gold','platinum','clubplus','enterprise']],
        ['feature' => 'Squad management',     'tiers' => ['academy','clubplus','enterprise']],
        ['feature' => 'Multi-profile / seats','tiers' => ['family','academy','clubplus','enterprise']],
        ['feature' => 'API & integrations',   'tiers' => ['enterprise']],
    ],
];
