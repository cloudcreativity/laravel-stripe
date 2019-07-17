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

namespace CloudCreativity\LaravelStripe\Tests\Integration;

use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Tests\TestAccount;

class EloquentTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        config()->set('stripe.connect.model', TestAccount::class);
    }

    public function test()
    {
        /** @var TestAccount $model */
        $model = factory(TestAccount::class)->create();

        $this->assertSame($model->getKeyName(), $model->getStripeAccountKeyName(), 'key name');
        $this->assertSame($model->getKey(), $model->getStripeAccountId(), 'key');
        $this->assertTrue($model->stripe()->is($model), 'model connector');
        $this->assertTrue(Stripe::connect($model->id)->is($model), 'facade account connector');
    }

    public function testIncrementing()
    {
        /** @var TestAccount $model */
        $model = factory(TestAccount::class)->make();
        $model->incrementing = true;

        $this->assertSame('stripe_account_id', $model->getStripeAccountKeyName());
    }
}
