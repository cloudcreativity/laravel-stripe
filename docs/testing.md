# Testing

Test calls to the Stripe API via our test helpers.

## Usage

You may use the Stripe facade's `fake()` method to prevent all static calls to the
`stripe/stripe-php` library from executing. This prevents any requests being sent to Stripe
in your tests.

You can then assert that static calls were made and even inspect the arguments they received.
For this to work, you need to tell the fake what objects to return (and in what order)
**before** the code under test is executed, and then make the assertions **after** the
test code is executed.

For example:

```php
namespace Tests\Feature;

use Tests\TestCase;
use CloudCreativity\LaravelStripe\Facades\Stripe;

class StripeTest extends TestCase
{
    
    public function test()
    {
        Stripe::fake(
            $expected = new \Stripe\PaymentIntent()
        );
        
        $account = factory(StripeAccount::class)->create();
        $actual = $account->stripe()->paymentIntents()->create('gbp', 999);
        
        $this->assertSame($expected, $actual);
        
        Stripe::assertInvoked(
            \Stripe\PaymentIntent::class, 
            'create', 
            function ($params, $options) use ($account) {
                $this->assertEquals(['currency' => 'gbp', 'amount' => 999], $params);
                $this->assertEquals(['stripe_account' => $account->id], $options);
                return true;
            }
        );
    }
}
```

If you are expecting multiple calls, you can queue up multiple return results:

```php
Stripe::fake(
    new \Stripe\Account(),
    new \Stripe\Charge()
)
```

In this scenario, you need to call `assertInvoked()` in *exactly* the same order
as you were expecting the static calls to be made.

## Asserting No Calls

The Stripe fake fails the test if it is called when it no longer has any queued
results. This means that if you expect Stripe to never be called, all you need
to do is:

```php
Stripe::fake()
```

In this scenario, if there is an unexpected call the test will fail.

## Non-Static Methods

Calling `Stripe::fake()` only prevents **static** methods in Stripe's PHP package from being
called. This means you will need to mock any non-static methods.

For example, it is possible to cancel a payment intent by calling the `cancel()` method
on a `\Stripe\PaymentIntent` instance. To test this, we will need to provide a mock
as the static return result:

```php
// Example using PHPUnit mock...
$mock = $this
    ->getMockBuilder(\Stripe\PaymentIntent::class)
    ->setMethods(['cancel'])
    ->getMock();
    
$mock->expects($this->once())->method('cancel');

Stripe::fake($mock);

// ...run test code.
```
