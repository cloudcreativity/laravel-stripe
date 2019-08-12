[![Build Status](https://travis-ci.org/cloudcreativity/laravel-stripe.svg?branch=master)](https://travis-ci.org/cloudcreativity/laravel-stripe)

# Laravel Stripe

## Overview

A Laravel integration for [Stripe's official PHP package.](https://github.com/stripe/stripe-php)
 
This package allows you to fluently query the Stripe API via repositories.
Repositories can be for either your application's Stripe account, or connected Stripe accounts.

### Example

```php
// For your application's account:
/** @var \Stripe\PaymentIntent $intent */
$intent = Stripe::account()
    ->paymentIntents()
    ->create('gbp', 1500);

// For a Stripe Connect account model:
$account->stripe()->paymentIntents()->create('gbp', 999);
```

### What About Cashier?

This package is meant to be used *in addition* to [Laravel Cashier](https://laravel.com/docs/billing),
not instead of it.

Our primary use-case is Stripe Connect. We needed a package that provided really easy access to data from
connected Stripe accounts. We wanted to make interacting with the entire Stripe API fluent,
easily testable and highly debuggable.

In contrast, Cashier does not provide full Stripe API coverage, and provides
[no support for Stripe Connect.](https://github.com/laravel/cashier/pull/519)
So if you need to do more than just Cashier's billing functionality, install this package as well.

## Installation

Installation is via Composer. Refer to the [Installation Guide](./docs/installation.md) for
instructions.

## Documentation

1. [Installation](./docs/installation.md)
2. [Accessing the Stripe API](./docs/repositories.md)
3. [Receiving Webhooks](./docs/webhooks.md)
4. [Stripe Connect](./docs/connect.md)
5. [Artisan Commands](./docs/console.md)
6. [Testing](./docs/testing.md)

## Version Compatibility

The following table shows which version to install. We have provided the Stripe API version that we
developed against as guide. You may find the package works with older versions of the API.

| Laravel | Stripe PHP | Stripe API | Laravel-Stripe | Cashier |
| :-- | :-- | :-- | :-- |:-- |
| `5.4.x` | `^6.40` | `>=2019-05-16` | `^0.1` | n/a |

> Installing version `0.x` of this package and Cashier is not supported, because Cashier required
an older version of the `stripe/stripe-php` package for Laravel 5.4.

## Contributing

We have only implemented the repositories for the Stripe resources we are using in our application.
Repositories are very easy to implement - for example, the 
[payment intent repository](./src/Repositories/PaymentIntentRepository.php) -
because they are predominantly composed of traits. Then they just need to be added to
[the connector class](./src/Connector.php).

If you find this package is missing a resource you need in your application, an ideal way to contribute
is to submit a pull request to add the missing repository.
