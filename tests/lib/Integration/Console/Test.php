<?php

namespace CloudCreativity\LaravelStripe\Tests\Integration\Console;

use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Tests\TestAccount;
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
        Stripe::withQueue(new Collection());

        $result = $this->artisan('stripe', compact('resource'));

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
        /** @var TestAccount $account */
        $account = factory(TestAccount::class)->create();

        Stripe::withQueue(new Collection());

        $result = $this->artisan('stripe', [
            'resource' => $resource,
            '--account' => $account->getStripeAccountId(),
        ]);

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked($fqn, 'all', function ($params, $options) use ($account) {
            $this->assertNull($params, 'params');

            $this->assertSame([
                'stripe_account' => $account->getStripeAccountId()
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
        Stripe::withQueue(new StripeObject($id = 'foo_bazbat'));

        $result = $this->artisan('stripe', [
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
        /** @var TestAccount $account */
        $account = factory(TestAccount::class)->create();

        Stripe::withQueue(new StripeObject($id = 'foo_bazbat'));

        $result = $this->artisan('stripe', [
            'resource' => $resource,
            'id' => $id,
            '--account' => $account->getStripeAccountId(),
        ]);

        $this->assertSame(0, $result, 'success');

        Stripe::assertInvoked($fqn, 'retrieve', function ($params, $options) use ($id, $account) {
            $this->assertSame(compact('id'), $params, 'params');

            $this->assertSame([
                'stripe_account' => $account->getStripeAccountId()
            ], $options, 'options');

            return true;
        });
    }
}
