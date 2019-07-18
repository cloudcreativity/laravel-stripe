# Repositories

Repositories provide fluent and testable access to objects in the Stripe API, including
Stripe Connect accounts.

## Usage

You can access repositories via:

- The `Stripe` facade; or
- The `stripe` container binding; or
- Type-hinting `\CloudCreativity\LaravelStripe\StripeService` for dependency injection; or
- The `stripe()` method on a Stripe account model.

Repositories are always scoped to either your application's Stripe account, or to a Stripe Connect
account. They return objects from the `stripe/stripe-php` package.

### Application

To access your application's Stripe account, use the `account()` method.
For example, to list `charge` resources:

```php
// Facade
$charges = Stripe::account()->charges()->all(); 

// Container
$charges = app('stripe')->account()->charges()->all();

// Dependency injection
/** @var \CloudCreativity\LaravelStripe\StripeService $service */
$charges = $service->account()->charges()->all();
```

> In this example `$charges` will be an instance of `\Stripe\Collection`.

### Stripe Connect

To access repositories for a [Stripe Connect](./connect.md) account, you will need either
the Stripe `id` (starting with `acct_`) for the account, or the model representing that account.

Then use the `connect()` method, for example to create a payment intent via the facade or model:

```php
// Facade
$intent = Stripe::connect($accountId)
    ->paymentIntents()
    ->create($currency, $amount);

$intent = $model
    ->stripe()
    ->paymentIntents()
    ->create($currency, $amount);
```

> In this example, `$intent` will be an instance of `\Stripe\PaymentIntent`.

## Available Repositories

The object returned from the Stripe `account()` and `connect()` methods provides access to
repositories for Stripe resources. The method name is the camel-case name of the Stripe object
type, for example `payment_intents` are accessible via the `paymentIntents()` method.

The available repositories are as follows:

| Stripe Resource | Stripe Class | Method | Repository Class |
| :-- | :-- | :-- | :-- |
| `account` | `\Stripe\Account` | `accounts()` | `AccountRepository` |
| `charge` | `\Stripe\Charge` | `charges()` | `ChargeRepository` |
| `event` | `\Stripe\Event` | `events()` | `EventRepository` |
| `payment_intent` | `\Stripe\PaymentIntent` | `paymentIntents()` | `PaymentIntentRepository` |
| `refund` | `\Stripe\Refund` | `refunds()` | `RefundRepository` |

Repository classes are in the [`\CloudCreativity\LaravelStripe\Repositories`](../src/Repositories)
namespace.

> Need a missing repository? Repositories are really easy to write, so are ideal for quick PRs.
Our aim is to get full coverage of the Stripe API, so if it is missing we will accept a PR to add
a missing repository. See [Contributing](../README.md#contributing) below.

## Using Repositories

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

For example, `payment_intent` resources require a `currency` and `amount` to create,
so the method signature is `create($currency, $amount, $params = [])`.

> Optional `$params` can be provided as any value that will be accepted by Laravel's `collect()`
method. This allows you to pass collections, with this package taking care of converting them
to arrays before passing them on.

### Request Options and Helper Methods

Each repository has methods for additional request options, and helper methods for common
parameters. These helpers must be called *before* calling any of the `all`,
`create`, `retrieve` and `update` methods. For example:

```php
$charges = Stripe::connect($accountId)
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

## Testing

Repositories are fully testable, allow you to ensure that the correct arguments are passed to
the Stripe API. See the [Testing Chapter](./testing.md) for details.
