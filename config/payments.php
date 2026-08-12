<?php
/**
 * Unified payment gateway configuration (Stripe + PayPal).
 * Loaded via config.php; override keys in .env (never commit live secrets).
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
if ($stripeSecret === '') {
    $stripeSecret = trim((string) $get('STRIPE_RESTRICTED_KEY', ''));
}
$stripePublishable = trim((string) $get('STRIPE_PUBLISHABLE_KEY', ''));
$paypalClientId = trim((string) $get('PAYPAL_CLIENT_ID', ''));
$paypalSecret = trim((string) $get('PAYPAL_CLIENT_SECRET', ''));
$gatewayEnabled = filter_var($get('PAYMENT_GATEWAY_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN);

return [
    'brand' => 'Sportify-Payments',
    'default' => $get('PAYMENT_DEFAULT', 'stripe'),
    'currency' => 'EUR',
    'currency_symbol' => '€',
    'gateway_enabled' => $gatewayEnabled,

    'stripe' => [
        'enabled' => $gatewayEnabled && $stripeSecret !== '' && $stripePublishable !== '',
        'secret_key' => $stripeSecret,
        'publishable_key' => $stripePublishable,
        'webhook_secret' => trim((string) $get('STRIPE_WEBHOOK_SECRET', '')),
        'webhook_path' => '/webhooks/stripe',
        'webhook_url' => rtrim((string) $get('APP_URL', 'https://sportifyplus.de'), '/') . '/webhooks/stripe',
        'uses_restricted_key' => trim((string) $get('STRIPE_SECRET_KEY', '')) === ''
            && trim((string) $get('STRIPE_RESTRICTED_KEY', '')) !== '',
    ],

    'paypal' => [
        'enabled' => $gatewayEnabled && $paypalClientId !== '' && $paypalSecret !== '',
        'mode' => $get('PAYPAL_MODE', 'sandbox'),
        'client_id' => $paypalClientId,
        'client_secret' => $paypalSecret,
        'webhook_id' => trim((string) $get('PAYPAL_WEBHOOK_ID', '')),
    ],

    'methods' => [
        'card' => [
            'id' => 'card',
            'label' => 'Credit / Debit Card',
            'desc' => 'Visa, Mastercard, American Express',
            'icon' => '💳',
            'gateway' => 'stripe',
        ],
        'paypal' => [
            'id' => 'paypal',
            'label' => 'PayPal',
            'desc' => 'Pay with PayPal balance or linked bank',
            'icon' => '🅿️',
            'gateway' => 'paypal',
        ],
    ],

    'payable_types' => [
        'marketplace_order',
        'pro_subscription',
        'session_booking',
        'booking',
        'transfer_premium',
        'fitpass_subscription',
    ],
];
