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

namespace CloudCreativity\LaravelStripe\Tests\Integration\Webhooks;

use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Tests\TestWebhookJob;
use Illuminate\Support\Facades\Queue;

class ListenersTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        Queue::fake();

        config()->set('stripe.webhooks.jobs', [
            'payment_intent_succeeded' => TestWebhookJob::class,
        ]);
    }

    /**
     * If there are any configured webhook jobs, we expect them to be dispatched
     * when the `stripe.webhooks` event is fired. They should be dispatched on the
     * same queue and connection as the webhook itself.
     */
    public function test()
    {
        event('stripe.webhooks', $webhook = new Webhook(
            factory(StripeEvent::class)->create(),
            factory(StripeAccount::class)->create(),
            ['id' => 'evt_00000000', 'object' => 'event', 'type' => 'payment_intent.succeeded'],
            'my_queue',
            'my_connection'
        ));

        Queue::assertPushedOn('my_queue', TestWebhookJob::class, function ($job) use ($webhook) {
            $this->assertSame($webhook, $job->webhook, 'webhook');
            $this->assertSame('my_queue', $job->queue, 'queue');
            $this->assertSame('my_connection', $job->connection, 'connection');
            return true;
        });
    }

    /**
     * Test it does not dispatch a job if the webhook is not for the specified event type.
     */
    public function testDoesNotDispatch()
    {
        event('stripe.webhooks', $webhook = new Webhook(
            factory(StripeEvent::class)->create(),
            factory(StripeAccount::class)->create(),
            ['id' => 'evt_00000000', 'object' => 'event', 'type' => 'charge.refunded']
        ));

        Queue::assertNotPushed(TestWebhookJob::class);
    }
}
