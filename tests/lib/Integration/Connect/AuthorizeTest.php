<?php
/**
 * Copyright 2019 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CloudCreativity\LaravelStripe\Tests\Integration\Connect;

use CloudCreativity\LaravelStripe\Events\FetchedUserCredentials;
use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Jobs\FetchUserCredentials;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Tests\TestUser;
use Illuminate\Support\Facades\Event;
use Stripe\OAuth;
use Stripe\StripeObject;

class AuthorizeTest extends TestCase
{

    /**
     * @var TestUser
     */
    private $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake(FetchedUserCredentials::class);

        $this->user = factory(TestUser::class)->create();
    }

    public function test()
    {
        Stripe::fake($token = StripeObject::constructFrom([
            'access_token' => 'secret_token',
            'scope' => 'read_write',
            'livemode' => true,
            'token_type' => 'bearer',
            'refresh_token' => 'storable_refresh_token',
            'stripe_user_id' => 'acct_1234567890',
            'stripe_publishable_key' => 'publishable_key',
        ]));

        dispatch(new FetchUserCredentials('auth_code', 'read_write', $this->user));

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
            'owner_id' => $this->user->getKey(),
        ]);

        /** @var StripeAccount $model */
        $model = StripeAccount::find('acct_1234567890');

        Event::assertDispatched(FetchedUserCredentials::class, function ($event) use ($model, $token) {
            $this->assertTrue($model->is($event->account), 'event account');
            $this->assertSame($token, $event->token, 'event token');
            $this->assertTrue($this->user->is($event->owner), 'event owner');
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
            'stripe_user_id' => $model->getStripeAccountIdentifier(),
            'stripe_publishable_key' => 'publishable_key',
        ]));

        dispatch(new FetchUserCredentials('auth_code', 'read_write', $this->user));

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
            'owner_id' => $this->user->getKey(),
        ]);

        Event::assertDispatched(FetchedUserCredentials::class, function ($event) use ($model, $token) {
            $this->assertTrue($model->is($event->account), 'event account');
            $this->assertSame($token, $event->token, 'event token');
            $this->assertTrue($this->user->is($event->owner), 'event owner');
            return true;
        });
    }
}
