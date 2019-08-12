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
