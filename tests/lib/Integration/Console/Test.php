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
use Stripe\Collection;
use Stripe\StripeObject;

class Test extends TestCase
{

    /**
     * @param $fqn
     * @param $resource
     * @dataProvider classProvider
     */
    public function testAll($fqn, $resource)
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
     * @param $fqn
     * @param $resource
     * @dataProvider classProvider
     */
    public function testAllConnect($fqn, $resource)
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
     * @param $fqn
     * @param $resource
     * @dataProvider classProvider
     */
    public function testRetrieveAndExpand($fqn, $resource)
    {
        Stripe::fake(new StripeObject($id = 'foo_bazbat'));

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
     * @param $fqn
     * @param $resource
     * @dataProvider classProvider
     */
    public function testRetrieveConnect($fqn, $resource)
    {
        /** @var StripeAccount $account */
        $account = factory(StripeAccount::class)->create();

        Stripe::fake(new StripeObject($id = 'foo_bazbat'));

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
}
