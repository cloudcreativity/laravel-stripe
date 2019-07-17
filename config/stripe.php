<?php
/**
 * This file is part of cloudcreativity/laravel-stripe
 *
 * (c) Christopher Gammie <info@cloudcreativity.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | The default API version that this application uses. Not providing
    | this value will mean no version is sent to Stripe.
    |
    */
    'api_version' => env('STRIPE_API_VERSION'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Connect
    |--------------------------------------------------------------------------
    |
    | Settings for your Stripe Connect integration.
    */
    'connect' => [
        'model' => \CloudCreativity\LaravelStripe\Models\StripeAccount::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Currencies
    |--------------------------------------------------------------------------
    |
    | If your application only supports specific currencies, you can list
    | them here. An empty array indicates that ALL currencies are supported.
    |
    */
    'currencies' => [
        'GBP',
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum Charge Amounts
    |--------------------------------------------------------------------------
    |
    | The minimum charge amounts.
    |
    | @see https://stripe.com/docs/currencies#minimum-and-maximum-charge-amounts
    */
    'minimum_charge_amounts' => [
        'USD' => 50,
        'AUD' => 50,
        'BRL' => 50,
        'CAD' => 50,
        'CHF' => 50,
        'DKK' => 250,
        'EUR' => 50,
        'GBP' => 30,
        'HKD' => 400,
        'JPY' => 50,
        'MXN' => 10,
        'NOK' => 300,
        'NZD' => 50,
        'SEK' => 300,
        'SGD' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | This package provides a webhook implementation. Webhooks will be
    | stored as the model below and then dispatched for asynchronous processing
    | on by the Laravel queue.
    |
    | You need to add your endpoint signing secrets in the `signing_secrets`
    | array below. The key of the secret is the name you pass to the
    | `stripe.verify` middleware, e.g. `stripe.verify:default`.
    |
    | Webhook process jobs will be pushed to the default queue (and connection)
    | specified below. You can also set queue/connections for specific
    | Stripe webhooks - which allows you to prioritise some events over
    | others. The example below shows how to set a different connection/queue
    | for the `payment_intent.succeeded` webhook. (Note that the key in
    | config is `payment_intent_succeeded`.)
    |
    */
    'webhooks' => [
        'model' => \CloudCreativity\LaravelStripe\Models\StripeEvent::class,
        'signature_tolerance' => env('STRIPE_WEBHOOKS_SIGNATURE_TOLERANCE', \Stripe\Webhook::DEFAULT_TOLERANCE),
        'signing_secrets' => [
            'default' => env('STRIPE_WEBHOOKS_SIGNING_SECRET'),
        ],
        'default_queue_connection' => env('STRIPE_WEBHOOKS_QUEUE_CONNECTION'),
        'default_queue' => env('STRIPE_WEBHOOKS_QUEUE'),
        'queues' => [
            'payment_intent_succeeded' => [
                'connection' => env('STRIPE_WEBHOOKS_QUEUE_CONNECTION'),
                'queue' => env('STRIPE_WEBHOOKS_QUEUE'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | The log level used for logging calls to Stripe. Setting to an empty
    | value disables logging.
    |
    | The excluded list contains a key or list of keys (dot notation) that
    | must not be logged for the named Stripe object. For example, the payment
    | intent's client secret must not be logged.
    |
    */
    'log' => [
        'level' => env('STRIPE_LOG_LEVEL'),
        'exclude' => [
            'payment_intent' => ['client_secret'],
        ],
    ],
];
