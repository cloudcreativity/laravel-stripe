<?php

namespace CloudCreativity\LaravelStripe\Testing\Concerns;

use Closure;
use CloudCreativity\LaravelStripe\Testing\ClientFake;
use PHPUnit\Framework\Assert;
use Stripe\StripeObject;

trait MakesStripeAssertions
{

    /**
     * @var ClientFake
     */
    protected $stripeClient;

    /**
     * Queue Stripe responses.
     *
     * @param StripeObject ...$objects
     * @return void
     */
    public function withQueue(StripeObject ...$objects)
    {
        $this->stripeClient->queue(...$objects);
    }

    /**
     * Assert the next Stripe call in the history.
     *
     * @param $class
     *      the expected fully qualified class name.
     * @param $method
     *      the expected static method.
     * @param Closure|null $args
     *      an optional closure to assert that the call received the correct arguments.
     */
    public function assertInvoked($class, $method, Closure $args = null)
    {
        $index = $this->stripeClient->increment();

        $this->assertInvokedAt($index, $class, $method, $args);
    }

    /**
     * Assert the next Stripe call in the history.
     *
     * @param int $index
     *      the index in the history of Stripe calls.
     * @param $class
     *      the expected fully qualified class name.
     * @param $method
     *      the expected static method.
     * @param Closure|null $args
     *      an optional closure to assert that the call received the correct arguments.
     */
    public function assertInvokedAt($index, $class, $method, Closure $args = null)
    {
        if (!$history = $this->stripeClient->at($index)) {
            Assert::fail("No Stripe call at index {$index}.");
        }

        Assert::assertSame(
            $class . '::' . $method,
            $history['class'] . '::' . $history['method'],
            "Stripe {$index}: class and method"
        );

        if ($args) {
            Assert::assertTrue(
                $args(...$history['args']),
                "Stripe {$index}: arguments"
            );
        }
    }

}
