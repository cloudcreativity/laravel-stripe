# Stripe Connect

## Models

We provide a `\CloudCreativity\LaravelStripe\Models\StripeAccount` model to store connected accounts.
We store the majority of attributes provided by Stripe on their
[account resource.](https://stripe.com/docs/api/accounts)
The model's attributes are:

| Attribute | Type | Description |
| :-- | :-- | :-- |
| `id` | `string` | The Stripe id for the account resource. |
| `business_profile` | `json` | Information related to the business. |
| `business_type` | `string` | The business type, `individual` or `company`. |
| `capabilities` | `json` | The capabilities requested for the connected account. |
| `charges_enabled` | `bool` | Whether the account can create live charges. |
| `company` | `json` | Information about the company or business. |
| `country` | `string` | The account's country. |
| `created` | `datetime` | When the account was created with Stripe. |
| `default_currency` | `string` | Three-letter ISO currency code representing the default currency for the account. |
| `details_submitted` | `bool` | Whether account details have been submitted. |
| `email` | `string` | The primary user's email address. |
| `individual` | `json` | Information about the person represented by the account. |
| `metadata` | `json` | Set of key-value pairs that you have attached to the Stripe account resource. |
| `payouts_enabled` | `bool` | Whether Stripe can send payouts to this account. |
| `requirements` | `json` | Information about the requirements for the account, including what information needs to be collected, and by when. |
| `settings` | `json` | Account options for customizing how the account functions within Stripe. |
| `tos_acceptance` | `json` | Details on the acceptance of the Stripe Services Agreement. |
| `type` | `string` | The Stripe account type. Can be `standard`, `express`, or `custom`. |

> Some columns will only have values depending on the type of account. You should refer to the
[Stripe account documentation](https://stripe.com/docs/api/accounts) for more information.

### Custom Model

If you want to use your own model, you have two choices:

- Extend our [model class](../src/Models/StripeAccount.php) and customise it.
- Use your own model class and apply our [interface](../src/Contracts/Connect/AccountInterface.php) and
[connected account trait.](../src/Connect/ConnectedAccount.php)

Whichever approach you use, you will need to set the `stripe.webhooks.model` config value to the
fully-qualified class name of your custom model. You will also need to follow the instructions
about [package migrations.](./installation.md#migrations)

#### Extension

This is an example of extending our own class to change the database connection:

```php
<?php

namespace App\Stripe;

use CloudCreativity\LaravelStripe\Models\StripeAccount;

class Account extends StripeAccount
{
    
    protected $connection = 'stripe';
}
```

#### Without Extension

If you do not want to extend our model class, apply our interface and trait to your own class.
For example:

```php
<?php

namespace App;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Connect\ConnectedAccount;
use Illuminate\Database\Eloquent\Model;

class StripeAccount extends Model implements AccountInterface
{
    use ConnectedAccount;

    // ...
}
```

If your model **does not** use an incrementing primary key, we assume that the primary key is also the Stripe ID.
If your model **uses** incrementing primary keys, we default to `stripe_account_id` as the column name.

If you neither of these assumptions are correct, implement the `getStripeAccountKeyName()`. 
For example:

```php
<?php

namespace App;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Connect\ConnectedAccount;
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

## Repositories

You can fluently access the Stripe API for the connected account using our 
[repositories](./repositories.md#stripe-connect) implementation.

For example:

```php
$charges = $model->stripe()->charges()->all();
```

Refer to the [Repositories](./repositories.md) chapter for more details.

## Custom Implementation

If you need to customise the Stripe Connect implementation, create your own class that implements our
[adapter interface](../src/Contracts/Connect/AdapterInterface.php), or extend
[our adapter implementation](../src/Connect/Adapter.php).

You will then need to register your adapter in the `register()` method of your application's
service provider:

```php
namespace App\Providers;

use CloudCreativity\LaravelStripe\LaravelStripe;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

  public function register()
  {
        LaravelStripe::connect(\App\Stripe\Connect\Adapter::class);
  }
}
```
