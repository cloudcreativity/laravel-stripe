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
use CloudCreativity\LaravelStripe\Jobs\ProcessWebhook;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Webhooks\ConnectWebhook;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Illuminate\Support\Facades\Event;

class ProcessTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    public function test()
    {
        $model = factory(StripeEvent::class)->create([
            'updated_at' => Carbon::now()->subMinute(),
        ]);

        $payload = [
            'id' => $model->getKey(),
            'type' => 'charge.failed',
        ];

        dispatch(new ProcessWebhook($model, $payload));

        $expected = [
            'stripe.webhooks',
            'stripe.webhooks:charge',
            'stripe.webhooks:charge.failed',
        ];

        foreach ($expected as $name) {
            Event::assertDispatched(
                $name,
                function ($ev, Webhook $webhook) use ($name, $model, $payload) {
                    $this->assertNotInstanceOf(ConnectWebhook::class, $webhook, 'not connect');
                    $this->assertEquals(\Stripe\Event::constructFrom($payload), $webhook->webhook, "{$name}: webhook");
                    $this->assertTrue($model->is($webhook->model), "{$name}: model");
                    return true;
                }
            );
        }

        /** Ensure the model had its timestamp updated. */
        $this->assertDatabaseHas('stripe_events', [
            $model->getKeyName() => $model->getKey(),
            'updated_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    public function testConnect()
    {
        $model = factory(StripeEvent::class)->states('connect')->create();

        $payload = [
            'id' => $model->getKey(),
            'account' => $model->account_id,
            'type' => 'payment_intent.succeeded',
        ];

        $job = new ProcessWebhook($model, $payload);
        $job->onConnection('sync')->onQueue('my_queue');

        dispatch($job);

        $expected = [
            'stripe.connect.webhooks',
            'stripe.connect.webhooks:payment_intent',
            'stripe.connect.webhooks:payment_intent.succeeded',
        ];

        foreach ($expected as $name) {
            Event::assertDispatched(
                $name,
                function ($ev, ConnectWebhook $webhook) use ($name, $model, $payload) {
                    $this->assertEquals(\Stripe\Event::constructFrom($payload), $webhook->webhook, "{$name}: webhook");
                    $this->assertTrue($model->account->is($webhook->account), "{$name}: account");
                    $this->assertTrue($model->is($webhook->model), "{$name}: model");
                    return true;
                }
            );
        }
    }
}
