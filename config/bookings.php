<?php
/**
 * Book-a-Talk — per-minute player session booking.
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
    'currency'         => 'EUR',
    'currency_symbol'  => '€',
    'default_minutes'  => 5,
    'default_max_minutes' => 60,
    'duration_options' => [5, 10, 15, 30, 45, 60],

    'payment_methods' => [
        'bank_transfer' => [
            'id'       => 'bank_transfer',
            'label'    => 'Bank transfer',
            'desc'     => 'Transfer the total — player confirms after payment',
            'icon'     => '🏦',
            'provider' => 'manual',
            'instant'  => false,
        ],
        'contact' => [
            'id'       => 'contact',
            'label'    => 'Contact to arrange',
            'desc'     => 'Player will reach out to confirm details',
            'icon'     => '✉',
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

    'statuses' => ['pending', 'confirmed', 'completed', 'cancelled'],
];
