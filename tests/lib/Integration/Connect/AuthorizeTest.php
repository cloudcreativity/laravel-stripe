<?php

namespace CloudCreativity\LaravelStripe\Tests\Integration\Connect;

use CloudCreativity\LaravelStripe\Events\FetchedUserCredentials;
use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Jobs\FetchUserCredentials;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Stripe\OAuth;
use Stripe\StripeObject;

class AuthorizeTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        Event::fake();
    }

    public function test()
    {
        $user = factory(User::class)->create();

        Stripe::fake($token = StripeObject::constructFrom([
            'access_token' => 'secret_token',
            'scope' => 'read_write',
            'livemode' => true,
            'token_type' => 'bearer',
            'refresh_token' => 'storable_refresh_token',
            'stripe_user_id' => 'acct_1234567890',
            'stripe_publishable_key' => 'publishable_key',
        ]));

        dispatch(new FetchUserCredentials('auth_code', 'read_write', $user));

        Stripe::assertInvoked(OAuth::class, 'token', function ($params) {
            $this->assertEquals([
                'code' => 'auth_code',
                'grant_type' => 'authorization_code',
            ], $params, 'params');

            return true;
        });

        $this->assertDatabaseHas('stripe_accounts', [
            'id' => 'acct_1234567890',
            'refresh_token' => 'storable_refresh_token',
            'user_id' => $user->getKey(),
        ]);

        /** @var StripeAccount $model */
        $model = StripeAccount::find('acct_1234567890');

        /**
         * @TODO need to investigate why this isn't working.
         *
         * For some reason the connection is `null` on the created model, which means it does not
         * pass the `is()` check. My best guess is it is related to having to fake all events
         * (so the model boot isn't happening) but need to investigate further.
         */
        $this->markTestIncomplete("@todo assert model on event");

        Event::assertDispatched(FetchedUserCredentials::class, function ($event) use ($model, $token) {
            $this->assertTrue($model->is($event->account), 'event account');
            $this->assertSame($token, $event->token, 'event token');
            return true;
        });
    }

    public function testAlreadyExists()
    {
        /** @var StripeAccount $model */
        $model = factory(StripeAccount::class)->create([
            'refresh_token' => 'blahblah',
        ]);

        Stripe::fake($token = StripeObject::constructFrom([
            'access_token' => 'secret_token',
            'scope' => 'read_write',
            'livemode' => true,
            'token_type' => 'bearer',
            'refresh_token' => 'storable_refresh_token',
            'stripe_user_id' => $model->getStripeAccountId(),
            'stripe_publishable_key' => 'publishable_key',
        ]));

        dispatch(new FetchUserCredentials('auth_code', 'read_write', null));

        Stripe::assertInvoked(OAuth::class, 'token', function ($params) {
            $this->assertEquals([
                'code' => 'auth_code',
                'grant_type' => 'authorization_code',
            ], $params, 'params');

            return true;
        });

        $this->assertDatabaseHas('stripe_accounts', [
            $model->getKeyName() => $model->getKey(),
            'refresh_token' => 'storable_refresh_token',
        ]);

        Event::assertDispatched(FetchedUserCredentials::class, function ($event) use ($model, $token) {
            $this->assertTrue($model->is($event->account), 'event account');
            $this->assertSame($token, $event->token, 'event token');
            return true;
        });
    }
}
