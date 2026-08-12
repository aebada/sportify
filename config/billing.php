<?php
/**
 * FIT-Pass billing — plan amounts (cents), payment methods, tax & provider keys.
 * Loaded via config.php; values can be overridden in .env.
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

return [
    'currency' => 'EUR',
    'currency_symbol' => '€',
    'tax_rate' => 0.19, // VAT (displayed at checkout)
    'annual_discount' => 0.17, // ~2 months free vs 12× monthly

    'plans' => [
        'bronze' => [
            'amount_monthly' => 900,
            'amount_yearly'  => 9000,
        ],
        'silver' => [
            'amount_monthly' => 1900,
            'amount_yearly'  => 19000,
        ],
        'gold' => [
            'amount_monthly' => 3900,
            'amount_yearly'  => 39000,
        ],
        'clubplus' => [
            'amount_monthly' => 14900,
            'amount_yearly'  => 149000,
        ],
        'digital' => [
            'amount_monthly' => 500,
            'amount_yearly'  => 5000,
        ],
        'youth' => [
            'amount_monthly' => 700,
            'amount_yearly'  => 7000,
        ],
        'family' => [
            'amount_monthly' => 2900,
            'amount_yearly'  => 29000,
        ],
        'scout' => [
            'amount_monthly' => 5900,
            'amount_yearly'  => 59000,
        ],
        'platinum' => [
            'amount_monthly' => 7900,
            'amount_yearly'  => 79000,
        ],
        'academy' => [
            'amount_monthly' => 9900,
            'amount_yearly'  => 99000,
        ],
        'enterprise' => [
            'amount_monthly' => 24900,
            'amount_yearly'  => 249000,
        ],
    ],

    'payment_methods' => [
        'card' => [
            'id' => 'card',
            'label' => 'Credit / Debit Card',
            'desc' => 'Visa, Mastercard, American Express',
            'icon' => '💳',
            'provider' => 'stripe',
            'instant' => true,
        ],
        'paypal' => [
            'id' => 'paypal',
            'label' => 'PayPal',
            'desc' => 'Pay with your PayPal balance or linked bank',
            'icon' => '🅿️',
            'provider' => 'paypal',
            'instant' => true,
        ],
        'apple_pay' => [
            'id' => 'apple_pay',
            'label' => 'Apple Pay',
            'desc' => 'Touch ID or Face ID on Apple devices',
            'icon' => '',
            'provider' => 'stripe',
            'instant' => true,
        ],
        'google_pay' => [
            'id' => 'google_pay',
            'label' => 'Google Pay',
            'desc' => 'Fast checkout on Android & Chrome',
            'icon' => '🔵',
            'provider' => 'stripe',
            'instant' => true,
        ],
        'sepa' => [
            'id' => 'sepa',
            'label' => 'SEPA Direct Debit',
            'desc' => 'EU bank account — debited each billing period',
            'icon' => '🏦',
            'provider' => 'stripe',
            'instant' => false,
        ],
        'bank_transfer' => [
            'id' => 'bank_transfer',
            'label' => 'Bank Transfer',
            'desc' => 'Wire transfer — membership activates after payment clears',
            'icon' => '🏛️',
            'provider' => 'manual',
            'instant' => false,
        ],
    ],

    'bank' => [
        'iban' => 'DE89 3704 0044 0532 0130 00',
        'bic' => 'COBADEFFXXX',
        'account_name' => 'Sportify FIT-Pass GmbH',
        'reference_prefix' => 'FIT-',
    ],

    'stripe' => [
        'secret_key' => $get('STRIPE_SECRET_KEY', ''),
        'publishable_key' => $get('STRIPE_PUBLISHABLE_KEY', ''),
        'webhook_secret' => $get('STRIPE_WEBHOOK_SECRET', ''),
    ],

    'paypal' => [
        'client_id' => $get('PAYPAL_CLIENT_ID', ''),
        'client_secret' => $get('PAYPAL_CLIENT_SECRET', ''),
        'mode' => $get('PAYPAL_MODE', 'sandbox'),
    ],
];
