<?php
/**
 * FIT-Pass AI Wellness Assistant configuration (Phase 1 MVP).
 *
 * Phase 2+ roadmap (not implemented yet):
 * - Apple Calendar & Outlook OAuth + busy/free sync
 * - Real Google Calendar read (free/busy only; never write without confirm)
 * - Weather & traffic-aware scheduling
 * - Push notifications for daily plan & booking reminders
 * - Write confirmed bookings back to user calendar
 * - ML ranking beyond rule-based WellnessAiService
 */

$root = dirname(__DIR__);
$get = static function (string $key, $default = '') {
    $v = getenv($key);
    return $v !== false && $v !== '' ? $v : $default;
};

return [
    'goals' => [
        'weight_loss'        => ['label' => 'Weight management',     'icon' => '⚖️', 'sports' => ['gym', 'yoga', 'pilates']],
        'muscle_gain'        => ['label' => 'Strength & muscle',     'icon' => '💪', 'sports' => ['gym', 'sports_club']],
        'flexibility'        => ['label' => 'Flexibility & mobility','icon' => '🧘', 'sports' => ['yoga', 'pilates']],
        'stress_relief'      => ['label' => 'Stress relief',         'icon' => '🌿', 'sports' => ['yoga', 'massage', 'wellness']],
        'cardio_fitness'     => ['label' => 'Cardio fitness',        'icon' => '🏃', 'sports' => ['gym', 'sports_club']],
        'recovery'           => ['label' => 'Recovery & rehab',      'icon' => '❄️', 'sports' => ['physio', 'recovery', 'massage']],
        'nutrition'          => ['label' => 'Sports nutrition',      'icon' => '🥗', 'sports' => ['nutrition']],
        'mental_performance' => ['label' => 'Mental performance',    'icon' => '🧠', 'sports' => ['wellness', 'yoga']],
        'general_wellness'   => ['label' => 'General wellness',      'icon' => '✨', 'sports' => ['wellness', 'gym', 'yoga']],
    ],

    'sports' => [
        'gym'         => 'Gym & fitness',
        'massage'     => 'Massage',
        'physio'      => 'Physiotherapy',
        'nutrition'   => 'Nutrition',
        'recovery'    => 'Recovery',
        'yoga'        => 'Yoga',
        'pilates'     => 'Pilates',
        'wellness'    => 'Wellness & spa',
        'sports_club' => 'Sports club',
    ],

    'time_of_day' => [
        'morning'   => ['label' => 'Morning',   'hours' => [6, 12]],
        'afternoon' => ['label' => 'Afternoon', 'hours' => [12, 17]],
        'evening'   => ['label' => 'Evening',   'hours' => [17, 22]],
    ],

    'modality' => [
        'online'  => 'Online',
        'offline' => 'In-person',
        'hybrid'  => 'Hybrid',
    ],

    'google_calendar' => [
        'client_id'     => $get('GOOGLE_CALENDAR_CLIENT_ID', $get('GOOGLE_CLIENT_ID', '')),
        'client_secret' => $get('GOOGLE_CALENDAR_CLIENT_SECRET', $get('GOOGLE_CLIENT_SECRET', '')),
        'redirect_uri'  => $get('WELLNESS_GOOGLE_REDIRECT_URI', ''),
        'scopes'        => 'https://www.googleapis.com/auth/calendar.readonly',
    ],

    'default_location' => [
        'lat' => 52.52,
        'lng' => 13.405,
        'city' => 'Berlin',
    ],

    'mock_free_slots' => [
        ['day' => 'monday',    'start' => '07:00', 'end' => '08:30'],
        ['day' => 'monday',    'start' => '18:00', 'end' => '19:30'],
        ['day' => 'wednesday', 'start' => '12:00', 'end' => '13:00'],
        ['day' => 'friday',    'start' => '17:30', 'end' => '19:00'],
        ['day' => 'saturday',  'start' => '09:00', 'end' => '11:00'],
    ],
];
