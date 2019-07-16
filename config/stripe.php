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
    | Connected Account
    |--------------------------------------------------------------------------
    |
    | The adapter provides access to the stored connected account details.
    | By default we provide an Eloquent adapter, which will be configured to
    | query the model class specified below.
    */
    'connected_accounts' => [
        'adapter' => \CloudCreativity\LaravelStripe\Eloquent\Adapter::class,
        'model' => \App\StripeAccount::class,
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
    | Webhook Events
    |--------------------------------------------------------------------------
    |
    | This package allows you to dispatch Stripe webhooks as events.
    | These events will have your Stripe account model attached automatically
    | if the webhook relates to a connected account.
    |
    | You can customise the event implementation by changing the
    | dispatcher class here. Your custom implementation will need to
    | implement our `Contracts\Webhooks\DispatcherInterface`.
    |
    | If want to disable webhook events, set the value here to null.
    |
    */
    'webhooks' => \CloudCreativity\LaravelStripe\Webhooks\Dispatcher::class,

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
            'payment_intent' => 'client_secret',
        ],
    ],
];
