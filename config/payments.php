<?php

return [
    'mercadopago' => [
        'access_token'   => (string) env('MP_ACCESS_TOKEN', ''),
        'webhook_secret' => (string) env('MP_WEBHOOK_SECRET', ''),
        'currency'       => env('MP_CURRENCY', 'ARS'),
        'api'            => 'https://api.mercadopago.com',
    ],
    'paypal' => [
        'client_id'  => (string) env('PAYPAL_CLIENT_ID', ''),
        'secret'     => (string) env('PAYPAL_SECRET', ''),
        'webhook_id' => (string) env('PAYPAL_WEBHOOK_ID', ''),
        'currency'   => env('PAYPAL_CURRENCY', 'USD'),
        'api'        => env('PAYPAL_MODE', 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com',
    ],
];
