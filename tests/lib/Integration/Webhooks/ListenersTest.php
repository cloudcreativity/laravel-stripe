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

namespace CloudCreativity\LaravelStripe\Tests\Integration\Webhooks;

use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Tests\TestWebhookJob;
use CloudCreativity\LaravelStripe\Webhooks\ConnectWebhook;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Illuminate\Support\Facades\Queue;
use Stripe\Event;

class ListenersTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * If there are any configured webhook jobs, we expect them to be dispatched
     * when the `stripe.webhooks` event is fired. They should be dispatched on the
     * same queue and connection as the webhook itself.
     */
    public function test()
    {
        $event = Event::constructFrom([
            'id' => 'evt_00000000',
            'type' => 'payment_intent.succeeded',
        ]);

        event('stripe.webhooks', $webhook = new Webhook(
            $event,
            factory(StripeEvent::class)->create(),
            ['queue' => 'my_queue', 'connection' => 'my_connection', 'job' => TestWebhookJob::class]
        ));

        Queue::assertPushedOn('my_queue', TestWebhookJob::class, function ($job) use ($webhook) {
            $this->assertSame($webhook, $job->webhook);
            $this->assertSame('my_queue', $job->queue, 'queue');
            $this->assertSame('my_connection', $job->connection, 'connection');
            return true;
        });
    }

    /**
     * If there are any configured webhook jobs, we expect them to be dispatched
     * when the `stripe.connect.webhooks` event is fired. They should be dispatched on the
     * same queue and connection as the webhook itself.
     */
    public function testConnect()
    {
        $event = Event::constructFrom([
            'id' => 'evt_00000000',
            'type' => 'payment_intent.succeeded',
            'account' => 'acct_0000000000',
        ]);

        $model = factory(StripeEvent::class)->states('connect')->create();

        event('stripe.connect.webhooks', $webhook = new ConnectWebhook(
            $event,
            $model->account,
            $model,
            ['job' => TestWebhookJob::class]
        ));

        Queue::assertPushed(TestWebhookJob::class, function ($job) use ($webhook) {
            $this->assertSame($webhook, $job->webhook);
            $this->assertNull($job->queue, 'queue');
            $this->assertNull($job->connection, 'connection');
            return true;
        });
    }

    /**
     * Test it does not dispatch a job if the webhook is not for the specified event type.
     */
    public function testDoesNotDispatch()
    {
        event('stripe.webhooks', $webhook = new Webhook(
            Event::constructFrom(['id' => 'evt_00000000', 'type' => 'charge.refunded']),
            factory(StripeEvent::class)->create(),
            ['job' => null]
        ));

        Queue::assertNotPushed(TestWebhookJob::class);
    }
}
