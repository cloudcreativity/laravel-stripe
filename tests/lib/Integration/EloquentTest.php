<?php

namespace CloudCreativity\LaravelStripe\Tests\Integration;

use CloudCreativity\LaravelStripe\Connector;
use CloudCreativity\LaravelStripe\Tests\TestAccount;

class EloquentTest extends TestCase
{

    public function test()
    {
        /** @var TestAccount $model */
        $model = factory(TestAccount::class)->create();

        $this->assertSame($model->getKeyName(), $model->getStripeAccountKeyName(), 'key name');
        $this->assertSame($model->getKey(), $model->getStripeAccountId(), 'key');
        $this->assertInstanceOf(Connector::class, $model->stripe(), 'stripe');
    }

    public function testIncrementing()
    {
        /** @var TestAccount $model */
        $model = factory(TestAccount::class)->make();
        $model->incrementing = true;

        $this->assertSame('stripe_account_id', $model->getStripeAccountKeyName());
    }
}
