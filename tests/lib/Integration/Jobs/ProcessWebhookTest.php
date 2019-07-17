<?php

namespace CloudCreativity\LaravelStripe\Tests\Jobs;

use Carbon\Carbon;
use CloudCreativity\LaravelStripe\Events\Webhook;
use CloudCreativity\LaravelStripe\Jobs\ProcessWebhook;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use Illuminate\Support\Facades\Event;

class ProcessWebhookTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        Event::fake();
        Carbon::setTestNow('now');
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    public function test()
    {
        $event = factory(StripeEvent::class)->create([
            'updated_at' => Carbon::now()->subMinute(),
        ]);

        $payload = [
            'id' => $event->getKey(),
            'type' => 'charge.failed',
        ];

        dispatch(new ProcessWebhook($event, $payload));

        $expected = [
            'stripe.webhooks',
            'stripe.webhooks:charge.*',
            'stripe.webhooks:charge.failed',
        ];

        foreach ($expected as $name) {
            Event::assertDispatched($name, function ($ev, Webhook $webhook) use ($name, $event, $payload) {
                $this->assertTrue($event->is($webhook->event), "{$name}: model");
                $this->assertNull($webhook->account, "{$name}: account");
                $this->assertEquals($payload, $webhook->payload, "{$name}: payload");
                return true;
            });
        }

        /** Ensure the model had its timestamp updated. */
        $this->assertDatabaseHas('stripe_events', [
            $event->getKeyName() => $event->getKey(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function testConnect()
    {
        $event = factory(StripeEvent::class)->states('connect')->create();

        $payload = [
            'id' => $event->getKey(),
            'account' => $event->account_id,
            'type' => 'payment_intent.succeeded',
        ];

        dispatch(new ProcessWebhook($event, $payload));

        $expected = [
            'stripe.webhooks',
            'stripe.webhooks:payment_intent.*',
            'stripe.webhooks:payment_intent.succeeded',
        ];

        foreach ($expected as $name) {
            Event::assertDispatched($name, function ($ev, Webhook $webhook) use ($name, $event, $payload) {
                $this->assertTrue($event->is($webhook->event), "{$name}: model");
                $this->assertTrue($event->account->is($webhook->account), "{$name}: account");
                $this->assertEquals($payload, $webhook->payload, "{$name}: payload");
                return true;
            });
        }
    }
}
