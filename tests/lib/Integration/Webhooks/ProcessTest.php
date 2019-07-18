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

namespace CloudCreativity\LaravelStripe\Tests\Webhooks;

use Carbon\Carbon;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use CloudCreativity\LaravelStripe\Jobs\ProcessWebhook;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use Illuminate\Support\Facades\Event;

class ProcessTest extends TestCase
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
                $this->assertNull($webhook->connection, "{$name}: connection");
                $this->assertNull($webhook->queue, "{$name}: queue");
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

        $job = new ProcessWebhook($event, $payload);
        $job->onConnection('sync')->onQueue('my_queue');

        dispatch($job);

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
                $this->assertSame('sync', $webhook->connection, "{$name}: connection");
                $this->assertSame('my_queue', $webhook->queue, "{$name}: queue");
                return true;
            });
        }
    }
}
