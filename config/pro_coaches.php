<?php
/**
 * Sportify Pro Coaches — training subscriptions & session booking.
 * Stripe keys are stubs until payment integration is enabled.
 */

$root = dirname(__DIR__);
$env = [];
$envFile = $root . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $env[trim($key)] = trim(trim($value), "\"'");
    }
}
$get = static fn(string $k, $default = '') => $env[$k] ?? (getenv($k) !== false ? getenv($k) : $default);

$stripeSecret = trim((string) $get('STRIPE_SECRET_KEY', ''));
$paymentsLive = $stripeSecret !== '' || (trim((string) $get('PAYPAL_CLIENT_ID', '')) !== '' && trim((string) $get('PAYPAL_CLIENT_SECRET', '')) !== '');

return [
    'brand'           => 'Sportify Pro Coaches',
    'slug_prefix'     => 'pro-coaches',
    'tagline'         => 'Train with the pros — subscribe, book online or in-person.',
    'currency'        => 'EUR',
    'currency_symbol' => '€',
    'demo_mode'       => !$paymentsLive,

    'categories' => [
        'football_training' => ['label' => 'Football training', 'icon' => '⚽'],
        'gym'               => ['label' => 'Gym & strength', 'icon' => '🏋️'],
        'hiit'              => ['label' => 'HIIT', 'icon' => '🔥'],
        'yoga'              => ['label' => 'Yoga & flexibility', 'icon' => '🧘'],
        'nutrition'         => ['label' => 'Nutrition', 'icon' => '🥗'],
        'rehab'             => ['label' => 'Rehab & recovery', 'icon' => '🩹'],
        'youth_coaching'    => ['label' => 'Youth coaching', 'icon' => '🌱'],
    ],

    'session_durations' => [30, 60],

    'payment_methods' => [
        'demo' => [
            'id'       => 'demo',
            'label'    => 'Demo mode (instant access)',
            'desc'     => 'Payments are stubbed — subscription/session activates immediately for testing.',
            'icon'     => '🧪',
            'provider' => 'manual',
            'instant'  => true,
        ],
        'bank_transfer' => [
            'id'       => 'bank_transfer',
            'label'    => 'Bank transfer',
            'desc'     => 'Coach confirms after payment received',
            'icon'     => '🏦',
            'provider' => 'manual',
            'instant'  => false,
        ],
        'card' => [
            'id'       => 'card',
            'label'    => 'Credit / debit card',
            'desc'     => 'Pay securely with Stripe',
            'icon'     => '💳',
            'provider' => 'stripe',
            'instant'  => false,
        ],
        'paypal' => [
            'id'       => 'paypal',
            'label'    => 'PayPal',
            'desc'     => 'Pay with your PayPal account',
            'icon'     => '🅿️',
            'provider' => 'paypal',
            'instant'  => false,
        ],
    ],

    'stripe' => [
        'secret_key'      => $get('STRIPE_SECRET_KEY', ''),
        'publishable_key' => $get('STRIPE_PUBLISHABLE_KEY', ''),
        'webhook_secret'  => $get('STRIPE_WEBHOOK_SECRET', ''),
        'enabled'         => $stripeSecret !== '',
    ],

    'subscription_statuses' => ['active', 'cancelled', 'expired'],
    'session_statuses'      => ['pending', 'confirmed', 'completed', 'cancelled'],
];
