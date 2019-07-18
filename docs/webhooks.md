# Webhooks

This package provides all the tools you need to handle [Stripe webhooks](https://stripe.com/docs/webhooks),
including [Connect webhooks.](https://stripe.com/docs/connect/webhooks)

Our implementation verifies the webhook signature and follows 
[Stripe's best practices](https://stripe.com/docs/webhooks/best-practices) for webhooks. This includes
storing a record that the webhook has been received to avoid handling duplicate events, and pushing
processing of the webhook onto the Laravel queue.

You can integrate your application's handling of webhooks by either:

- Listening to the webhook events that we dispatch; and/or
- Configuring jobs to process specific webhook events.

## Endpoints

### Signing Secrets

You will need to create [webhook endpoints in your Stripe Dashboard.](https://dashboard.stripe.com/test/webhooks)
Stripe provides a signing secret for each endpoint. You will need to add to the `stripe.webhooks.signing_secrets`
config array. Give each signing secret a *name* in the array key, for example:

```php
return [
    // ...
    
    'webhooks' => [
        // ...
        'signing_secrets' => [
            'app' => env('STRIPE_WEBHOOKS_SIGNING_SECRET'),
            'connect' => env('STRIPE_WEBHOOKS_CONNECT_SIGNING_SECRET'),         
        ],
    ],
];
```

> You can add any number of signing secrets, and give them any name you want.

### Routing

You then need to add the endpoint to your application's routing, via our `Stripe::webhook()`
helper method. Provide the URI as the first argument, and the name of the signing secret to use.
For example:

```php
\Stripe::webhook('/stripe/webhooks/connect', 'connect');
``` 

> You need to disable [CSRF Protection](https://laravel.com/docs/csrf#csrf-excluding-uris)
for your Stripe webhook endpoints.

## Models

When a webhook is received, we store it in the database. This is to prevent processing of duplicate
events. To do this, we provide a `\CloudCreativity\LaravelStripe\Models\StripeEvent` model.

This model stores all the attributes *about* a webhook - i.e. all the attributes provided by Stripe
except for the `data` attribute. The model's attributes are:

| Attribute | Type | Description |
| :-- | :-- | :-- |
| `id` | `string` | The Stripe event id. |
| `account_id` | `string` | The connected account that originated the event. |
| `api_version` | `string` | The Stripe API version of the event payload. |
| `created` | `datetime` | When the webhook was created by Stripe. | 
| `created_at` | `datetime` | When the model was created by your application. |
| `livemode` | `boolean` | Whether object exists in live mode or test mode. |
| `pending_webhooks` | `integer` | Number of webhooks that were yet to be successfully delivered at the time of the webhook. |
| `type` | `string` | Description of the event, e.g. `charge.refunded`. |
| `updated_at` | `datetime` | When the model was last updated by your application. |

Note that Stripe sends the Connect account id as `account`, but we store it as `account_id`. This
is to follow Eloquent's conventions for foreign keys, and allow you to do `$event->account` to
retrieve your Connect account model. E.g.:

```php
use CloudCreativity\LaravelStripe\Models\StripeEvent;

/** @var \CloudCreativity\LaravelStripe\Models\StripeAccount|null $account */
$account = StripeEvent::find('evt_0000000000')->account;

// Eager loading example...
StripeEvent::with('account')->paginate(50);
```

### Custom Model

If you want to use your own model, set the `stripe.webhooks.model` config value to the fully-qualified
class name of the model. You will also need to follow the instructions about
[package migrations.](./installation.md#migrations)

To store a webhook from stripe, we use the `fill()` method on the model, passing in the array version
of the `\Stripe\Event` object. If this is not suitable for your model, refer to the
[custom implementation](#custom-implementation) instructions below.

## Webhook Processing

Webhook processing is pushed onto the Laravel queue. This follows Stripe's recommendation that a response
to their webhook requests should be returned immediately, and any complex logic is executed separately.

The queue and connection that is used for webhook processing can be configured in the
`stripe.webhooks.default_queue_connection` and `stripe.webhooks.default_queue` values.

You can also configure a queue and connection for specific events in the `stripe.webhooks.queues`
config array. This is useful if you want to push specific webhooks onto a priority queue. 

For example, if our application wanted to prioritise `payment_intent.succeeded` events, our
config would look like this:

```php
return [
    // ...
    
    'webhooks' => [
        // ...
        'default_queue_connection' => env('STRIPE_WEBHOOKS_QUEUE_CONNECTION'),
        'default_queue' => env('STRIPE_WEBHOOKS_QUEUE'),
        'queues' => [
            'payment_intent_succeeded' => [
                'connection' => env('QUEUE_HIGH_PRIORITY_CONNECTION'),
                'queue' => env('QUEUE_HIGH_PRIORITY'),
            ],
        ],    
    ],
];
``` 

> Note that we use the snake case version of the event name, so `payment_intent.succeeded` becomes
`payment_intent_succeeded`.

## Application Logic

To implement your application logic, you can either:

- Add listeners for the webhook events that we dispatch during queued processing; and/or
- Configure jobs to be dispatched on the Laravel queue for a named webhook.

### Events

Add listeners by binding to any of the following events:

| Event Name | Description |
| :-- | :-- | 
| `stripe.webhooks` | Bind to every webhook. |
| `stripe.webhooks:<object>.*` | Listen for webhooks for the specified Stripe object. |
| `stripe.webhooks:<event_name>` | Listen for a specific webhook. |

For example, when processing a `payment_intent.succeeded` webhook, the following three events will be
fired in this order:

- `stripe.webhooks`
- `stripe.webhooks:payment_intent.*`
- `stripe.webhooks:payment_intent.succeeded`

Listeners receive an instance of `\CloudCreativity\LaravelStripe\Webhooks\Webhook` as their first
and only argument.

### Jobs

Alternatively you can choose for a job to be queued when a webhook is processed. The job will
be constructed with the webhook, giving you access to all the information from Stripe. For example:


```php
namespace App\Jobs;

use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FulfillOrder implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;
    
    public $webhook;
    
    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;
    }
    
    public function handle()
    {
        // ...fulfill the order.
    }
}
```

Then add the job to your `stripe.webhooks.jobs` configuration:

```php
return [
    // ...
    
    'webhooks' => [
        // ...
        'jobs' => [
            'payment_intent_succeeded' => \App\Jobs\FulfillOrder::class,
        ],    
    ],
];
``` 

> Note that we use the snake case version of the event name, so `payment_intent.succeeded` becomes
`payment_intent_succeeded`.

#### Queues and Connections

Jobs are dispatched to the same queue and connection that the webhook was processed on. See
[webhook processing above](#webhook-processing) for how to configure queue/connections
for specific webhooks.

## Custom Implementation

If you need to customise the handling of webhooks, create your own class that implements our
[processor interface](../src/Contracts/Webhooks/ProcessorInterface.php), or extend
[our processor implementation](../src/Webhooks/Processor.php).

You will then need to register your processor in the `register()` method of your application's
service provider:

```php
namespace App\Providers;

use CloudCreativity\LaravelStripe\LaravelStripe;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

  public function register()
  {
        LaravelStripe::webhooks(\App\Stripe\Processor::class);
  }
}
```
