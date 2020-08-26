<?php
/**
 * Copyright 2020 Cloud Creativity Limited
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

namespace CloudCreativity\LaravelStripe\Tests\Integration\Console;

use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use Stripe\Balance;
use Stripe\Charge;
use Stripe\Collection;

class StripeQueryTest extends TestCase
{

    /**
     * @param string $fqn
     * @param string $resource
     * @dataProvider classProvider
     */
    public function testAll(string $fqn, string $resource): void
    {
        Stripe::fake(new Collection());

        $result = $this->artisan('stripe:query', compact('resource'));

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked($fqn, 'all', function ($params, $options) {
            $this->assertNull($params, 'params');
            $this->assertNull($options, 'options');
            return true;
        });
    }

    /**
     * The console command should handle the singular of the resource name.
     */
    public function testAllSingular(): void
    {
        Stripe::fake(new Collection());

        $result = $this->artisan('stripe:query', ['resource' => 'charge']);

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked(Charge::class, 'all', function ($params, $options) {
            $this->assertNull($params, 'params');
            $this->assertNull($options, 'options');
            return true;
        });
    }

    /**
     * @param string $fqn
     * @param string $resource
     * @dataProvider classProvider
     */
    public function testAllConnect(string $fqn, string $resource): void
    {
        /** @var StripeAccount $account */
        $account = factory(StripeAccount::class)->create();

        Stripe::fake(new Collection());

        $result = $this->artisan('stripe:query', [
            'resource' => $resource,
            '--account' => $account->getStripeAccountIdentifier(),
        ]);

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked($fqn, 'all', function ($params, $options) use ($account) {
            $this->assertNull($params, 'params');

            $this->assertSame([
                'stripe_account' => $account->getStripeAccountIdentifier()
            ], $options, 'options');

            return true;
        });
    }

    /**
     * @param string $fqn
     * @param string $resource
     * @dataProvider classProvider
     */
    public function testRetrieveAndExpand(string $fqn, string $resource): void
    {
        Stripe::fake(new $fqn($id = 'foo_bazbat'));

        $result = $this->artisan('stripe:query', [
            'resource' => $resource,
            'id' => $id,
            '--expand' => ['foo', 'bar'],
        ]);

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked($fqn, 'retrieve', function ($params, $options) use ($id) {
            $this->assertEquals([
                'id' => $id,
                'expand' => ['foo', 'bar'],
            ], $params, 'params');
            $this->assertNull($options, 'options');
            return true;
        });
    }

    /**
     * The console command should handle the singular version of the resource name.
     */
    public function testRetrieveSingular(): void
    {
        Stripe::fake(new Charge($id = 'foo_bazbat'));

        $result = $this->artisan('stripe:query', [
            'resource' => 'charge',
            'id' => $id,
            '--expand' => ['foo', 'bar'],
        ]);

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked(Charge::class, 'retrieve', function ($params, $options) use ($id) {
            $this->assertEquals([
                'id' => $id,
                'expand' => ['foo', 'bar'],
            ], $params, 'params');
            $this->assertNull($options, 'options');
            return true;
        });
    }

    /**
     * @param $fqn
     * @param $resource
     * @dataProvider classProvider
     */
    public function testRetrieveConnect($fqn, $resource)
    {
        /** @var StripeAccount $account */
        $account = factory(StripeAccount::class)->create();

        Stripe::fake(new $fqn($id = 'foo_bazbat'));

        $result = $this->artisan('stripe:query', [
            'resource' => $resource,
            'id' => $id,
            '--account' => $account->getStripeAccountIdentifier(),
        ]);

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked($fqn, 'retrieve', function ($params, $options) use ($id, $account) {
            $this->assertSame(compact('id'), $params, 'params');

            $this->assertSame([
                'stripe_account' => $account->getStripeAccountIdentifier()
            ], $options, 'options');

            return true;
        });
    }

    /**
     * The 'balance' resource does not have an id - it is a singleton.
     */
    public function testBalance(): void
    {
        Stripe::fake(new Balance());

        $result = $this->artisan('stripe:query', ['resource' => 'balances']);

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked(Balance::class, 'retrieve', function ($options) {
            $this->assertNull($options, 'options');
            return true;
        });
    }
}
