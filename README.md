# Laravel Stripe

A Laravel integration for [Stripe's official PHP package.](https://github.com/stripe/stripe-php)
 
This package allows you to fluently query the Stripe API via repositories.
Repositories can be for either your application's Stripe account, or connected Stripe accounts.

For example:

```php
// For your application's account:
/** @var \Stripe\PaymentIntent $intent */
$intent = Stripe::app()
    ->paymentIntents()
    ->create('gbp', 1500);

// For a connected Stripe account:
Stripe::account($accountId)
    ->paymentIntents()
    ->create('gbp', 999);

// Or if you have the account model:
$account->stripe()->paymentIntents()->create('gbp', 999);
```

See [this class](./src/Connector.php) for the resources that we have already implemented.

> If you need to add a resource that is currently not catered for, see the [Contributing](#Contributing)
section below.

## Why not just use Cashier?

This package is meant to be used *in addition* to [Laravel Cashier](https://laravel.com/docs/billing)
- not instead of it.

Our primary use-case is Stripe Connect. We needed a package that provided really easy access to data from
connected Stripe accounts. We wanted to make interacting with the entire Stripe API fluent,
easily testable and highly debuggable.

In contrast, Cashier does not provide full Stripe API coverage, and provides
[no support for Stripe Connect.](https://github.com/laravel/cashier/pull/519)
So if you need to do more than just Cashier's billing functionality, install this package as well.

## Installation

Install the package using Composer:

```bash
$ composer require cloudcreativity/laravel-stripe
```

Then publish the package config:

```bash
$ php artisan vendor:publish --provider="CloudCreativity\LaravelStripe\ServiceProvider"
```

This will create a `stripe.php` config file. Refer to the documentation in the file for details
of each configuration option.

Note that by default Laravel puts your Stripe keys in the `services` config file. We expect
them to be there too. Here's an example from Laravel 5.8:

```php
return [
    // ...other service config
    
    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],
];
```

## Connected Accounts

### Eloquent

To use connected accounts, we expect you to have an Eloquent model in which you are storing the
details of your connected accounts, including the Stripe id. Set the `stripe.connected_accounts.model`
to your model class, then add the following interface and trait:

```php
<?php

namespace App;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Eloquent\ConnectedAccount;
use Illuminate\Database\Eloquent\Model;

class StripeAccount extends Model implements AccountInterface
{

    use ConnectedAccount;

    // ...
}
```

### Not Using Eloquent?

You can easily integrate alternative storage implementions but writing an adapter that 
[implements this interface.](./src/Contracts/ConnectedAccountAdapter.php)
Then set the `stripe.connected_accounts.adapter` config option to your custom class.

## Console

You can also query Stripe via a console command. For example, to query charges on your application's
account:

```bash
$ php artisan stripe charge
```

Or to query a specific charge on a connected account:

```bash
$ php artisan stripe charge ch_4X8JtIYiSwHJ0o --account=acct_hrGMqodSZxqRuTM1
```

The options available are:

```
Usage:
  stripe [options] [--] <resource> [<id>]

Arguments:
  resource                 The resource name
  id                       The resource id

Options:
  -A, --account[=ACCOUNT]  The connected account
  -e, --expand[=EXPAND]    The paths to expand (multiple values allowed)
```

## Contributing

We have only implemented the repositories for the Stripe resources we are using in our application.
Repositories are very easy to implement - for example, the 
[payment intent repository](./src/Repositories/PaymentIntentRepository.php) -
because they are predominantly composed of traits. Then they just need to be added to
[the connector class](./src/Connector.php).

If you find this package is missing a resource you need in your application, an ideal way to contribute
is to submit a pull request to add the missing repository.
