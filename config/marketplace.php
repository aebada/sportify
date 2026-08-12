<?php
/**
 * Sportify Marketplace — categories, payment methods, gateway stub.
 * Set MARKETPLACE_PAYMENT_GATEWAY=true in .env when Stripe/PayPal are wired.
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
    'auto_approve_vendors' => filter_var($get('MARKETPLACE_AUTO_APPROVE', 'true'), FILTER_VALIDATE_BOOLEAN),

    /** When false, checkout uses COD / bank transfer / contact seller (MVP). */
    'payment_gateway_enabled' => filter_var($get('MARKETPLACE_PAYMENT_GATEWAY', 'false'), FILTER_VALIDATE_BOOLEAN),

    'payment_methods' => [
        'cod' => [
            'id' => 'cod',
            'label' => 'Cash on delivery',
            'desc' => 'Pay when you receive the item',
            'icon' => '💵',
            'provider' => 'manual',
            'instant' => false,
        ],
        'bank_transfer' => [
            'id' => 'bank_transfer',
            'label' => 'Bank transfer',
            'desc' => 'Transfer to seller — order confirmed after payment',
            'icon' => '🏦',
            'provider' => 'manual',
            'instant' => false,
        ],
        'contact_seller' => [
            'id' => 'contact_seller',
            'label' => 'Contact seller',
            'desc' => 'Arrange payment and pickup directly with the vendor',
            'icon' => '💬',
            'provider' => 'manual',
            'instant' => false,
        ],
    ],

    'bank' => [
        'iban' => $get('MARKETPLACE_BANK_IBAN', 'DE89 3704 0044 0532 0130 00'),
        'bic' => $get('MARKETPLACE_BANK_BIC', 'COBADEFFXXX'),
        'account_name' => $get('MARKETPLACE_BANK_NAME', 'Sportify Marketplace'),
        'reference_prefix' => 'MP-',
    ],

    /** Future Stripe/PayPal integration — see MarketplacePaymentService. */
    'stripe' => [
        'secret_key' => $get('MARKETPLACE_STRIPE_SECRET_KEY', $get('STRIPE_SECRET_KEY', '')),
        'publishable_key' => $get('MARKETPLACE_STRIPE_PUBLISHABLE_KEY', $get('STRIPE_PUBLISHABLE_KEY', '')),
        'webhook_secret' => $get('MARKETPLACE_STRIPE_WEBHOOK_SECRET', ''),
    ],

    'paypal' => [
        'client_id' => $get('MARKETPLACE_PAYPAL_CLIENT_ID', $get('PAYPAL_CLIENT_ID', '')),
        'client_secret' => $get('MARKETPLACE_PAYPAL_CLIENT_SECRET', $get('PAYPAL_CLIENT_SECRET', '')),
        'mode' => $get('MARKETPLACE_PAYPAL_MODE', $get('PAYPAL_MODE', 'sandbox')),
    ],
];
