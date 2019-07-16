[![Build Status](https://travis-ci.org/cloudcreativity/laravel-stripe.svg?branch=master)](https://travis-ci.org/cloudcreativity/laravel-stripe)

# Laravel Stripe

A Laravel integration for [Stripe's official PHP package.](https://github.com/stripe/stripe-php)
 
This package allows you to fluently query the Stripe API via repositories.
Repositories can be for either your application's Stripe account, or connected Stripe accounts.

### Example

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

This package is meant to be used *in addition* to [Laravel Cashier](https://laravel.com/docs/billing),
not instead of it.

Our primary use-case is Stripe Connect. We needed a package that provided really easy access to data from
connected Stripe accounts. We wanted to make interacting with the entire Stripe API fluent,
easily testable and highly debuggable.

In contrast, Cashier does not provide full Stripe API coverage, and provides
[no support for Stripe Connect.](https://github.com/laravel/cashier/pull/519)
So if you need to do more than just Cashier's billing functionality, install this package as well.

## Installation

Install the package using Composer:

```bash
$ composer require cloudcreativity/laravel-stripe:1.x-dev
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

## Repositories

Access to Stripe objects is provided via repositories. These are accessed either on your
application's Stripe account, or on a Connected account. You can get these via our
facade, the `stripe` container binding, or via dependency injection of the
`\CloudCreativity\LaravelStripe\StripeService` class.

### Application Account

To access your application' Stripe account, use the `app()` method as follows:

```php
// Using the facade...
/** @var \Stipe\Account $account */
$account = Stripe::app()->account();

// Using the container alias...
app('stripe')->app()->account();

// Using dependency injection
/** @var \CloudCreativity\LaravelStripe\StripeService $service */
$service->app()->account();
```

### Connected Accounts

To access connected accounts, you will need to have stored the Stripe account id.
See [Connected Accounts](#connected-accounts) below for details as to how to integrate models
or other storage with this package.

Once you've done that, you can access the connected Stripe account via the facade or service:

```php
// Using the facade...
/** @var string $accountId the Stripe account id (starts with acct_) */
/** @var \Stipe\Account $account */
$account = Stripe::account($accountId)->account();

// Using the container alias...
app('stripe')->account($accountId)->account();

// Using dependency injection
/** @var \CloudCreativity\LaravelStripe\StripeService $service */
$service->app()->account();
```

### Accessing Repositories

The object returned from the Stripe `app()` and `account($accountId)` methods provides access to
repositories for Stripe resources. The method name is the camel-case name of the Stripe object
type, for example `payment_intents` are accessible via the `paymentIntents()` method:

```php
/** @var \CloudCreativity\LaravelStripe\Repositories\PaymentIntentRepository $intents */
$intents = Stripe::app()->paymentIntents();
```

The available repositories are as follows (classes are in the `Repositories` namespace):

| Stripe Object | Method | Class |
| :-- | :-- | :-- |
| `account` | `accounts()` | `AccountRepository` |
| `charge` | `charges()` | `ChargeRepository` |
| `event` | `events()` | `EventRepository` |
| `payment_intent` | `paymentIntents()` | `PaymentIntentRepository` |
| `refund` | `refunds()` | `RefundRepository` |

> Need a missing repository? Repositories are really easy to write, so are ideal for quick PRs.
Our aim is to get full coverage of the Stripe API, so if it is missing we will accept a PR to add
a missing repository. See [Contributing](#contributing) below.

#### Using Repositories

Each repository will implement the following methods, depending on whether they are available in
the Stripe API:

| Method | Description |
| :-- | :-- |
| `all` | List resources (returns a Stripe collection). |
| `collect` | List resources (returns a Laravel collection). |
| `retrieve` | Retrieve a specific resource by ID. |
| `update` | Update a specific resource. |

> Note that there is no `delete` method as this is available on the Stripe object returned
by the `retrieve` method.

The method signature may vary according to the resource, but the general pattern is that
all required parameters are type-hinted, and then the final argument will be an iterable
of *optional* parameters.

For example, `payment_intents` require a `currency` and `amount` to create, so the method
signature is `create($currency, $amount, $params = [])`.

> Optional `$params` can be provided as any value that will be accepted by Laravel's `collect()`
method. This allows you to pass collections, with this package taking care of converting them
to arrays before passing them on.

#### Request Options and Helper Methods

Each repository has methods for additional request options, and helper methods for common
parameters. These helpers must be called *before* calling any of the `all`,
`create`, `retrieve` and `update` methods. For example:

```php
$charges = Stripe::account($accountId)
    ->charges()
    ->expand('application', 'application_fee')
    ->all();
```

Available methods are:

| Method | Description |
| :-- | :-- |
| `expand(string ...$keys)` | Paths to [expand objects](https://stripe.com/docs/api/expanding_objects). |
| `idempotent(string $value)` | Use an [idempotent request](https://stripe.com/docs/api/idempotent_requests). |
| `metadata(iterable $meta)` | Add [metadata](https://stripe.com/docs/api/metadata) (if the resource supports it). |
| `option(string $key, $value)` | Add an option. |
| `options(iterable $values)` | Add multiple options. |
| `param(string $key, $value)` | Add an optional parameter. |
| `params(iterable $values)` | Add multiple optional parameters. |

## Connected Accounts

### Eloquent

To use connected accounts, we expect you to have an Eloquent model in which you are storing the
details of your connected accounts, including the Stripe ID. Set the `stripe.connected_accounts.model`
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

### Stripe ID Column

If your model does not use an incrementing primary key, we assume that the primary key is also the Stripe ID.

If your model does use incrementing primary keys, we default to `stripe_account_id` as the column name.

If you use a different name, implement the `getStripeAccountKeyName()` on your model:

```php
<?php

namespace App;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Eloquent\ConnectedAccount;
use Illuminate\Database\Eloquent\Model;

class StripeAccount extends Model implements AccountInterface
{
    use ConnectedAccount;

    public function getStripeAccountKeyName()
    {
        return 'account_id';
    }
}
```

### Not Using Eloquent?

You can easily integrate alternative storage implementions by writing an adapter that 
[implements this interface.](./src/Contracts/Connect/AccountAdapterInterface.php)
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

> This console command is provided for debugging data in your Stripe API.

## Contributing

We have only implemented the repositories for the Stripe resources we are using in our application.
Repositories are very easy to implement - for example, the 
[payment intent repository](./src/Repositories/PaymentIntentRepository.php) -
because they are predominantly composed of traits. Then they just need to be added to
[the connector class](./src/Connector.php).

If you find this package is missing a resource you need in your application, an ideal way to contribute
is to submit a pull request to add the missing repository.
