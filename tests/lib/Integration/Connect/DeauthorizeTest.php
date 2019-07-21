<?php

namespace CloudCreativity\LaravelStripe\Tests\Integration\Connect;

use Carbon\Carbon;
use CloudCreativity\LaravelStripe\Events\AccountDeauthorized;
use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use Illuminate\Support\Facades\Event;
use Stripe\OAuth;
use Stripe\StripeObject;

class DeauthorizeTest extends TestCase
{


    public function test()
    {
        Event::fake();
        Stripe::fake($expected = new StripeObject());

        /** @var StripeAccount $account */
        $account = factory(StripeAccount::class)->create();

        $account->stripe()->deauthorize(['foo' => 'bar']);

        Stripe::assertInvoked(OAuth::class, 'deauthorize', function ($params, $options) use ($account) {
            $this->assertSame(['stripe_user_id' => $account->id], $params, 'params');
            $this->assertSame(['foo' => 'bar'], $options, 'options');
            return true;
        });

        Event::assertDispatched(AccountDeauthorized::class, function ($event) use ($account) {
            $this->assertTrue($account->is($event->account), 'event account');
            return true;
        });
    }

    public function testDeletesOnEvent()
    {
        Stripe::fake(new StripeObject());

        $account = factory(StripeAccount::class)->create([
            'refresh_token' => 'access_token',
            'token_scope' => 'read_write',
        ]);

        Stripe::connect($account)->deauthorize(['foo' => 'bar']);

        Stripe::assertInvoked(OAuth::class, 'deauthorize', function ($params, $options) use ($account) {
            $this->assertSame(['stripe_user_id' => $account->id], $params, 'params');
            $this->assertSame(['foo' => 'bar'], $options, 'options');
            return true;
        });

        $this->assertDatabaseHas('stripe_accounts', [
            $account->getKeyName() => $account->getKey(),
            'deleted_at' => Carbon::now()->toDateTimeString(),
            'refresh_token' => null,
            'token_scope' => null,
        ]);
    }
}
