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

use Carbon\Carbon;
use CloudCreativity\LaravelStripe\Events\SignatureVerificationFailed;
use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\Jobs\ProcessWebhook;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Webhooks\Verifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\Error\SignatureVerification;

class ReceiveTest extends TestCase
{

    /**
     * @var MockObject
     */
    private $verifier;

    /**
     * @var array
     */
    private $event;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Event::fake();

        $this->instance(Verifier::class, $this->verifier = $this->createMock(Verifier::class));
        $this->event = $this->stub('webhook');

        Stripe::webhook('/test/webhook', 'foobar');

        config()->set('stripe.webhooks.default_queue_connection', 'stripe_connection');
        config()->set('stripe.webhooks.default_queue', 'stripe_queue');
    }

    public function test()
    {
        unset($this->event['account']);

        $this->verifier
            ->expects($this->once())
            ->method('verify')
            ->with($this->isInstanceOf(Request::class), 'foobar');

        $this->withoutExceptionHandling()
            ->postJson('/test/webhook', $this->event)
            ->assertStatus(204);

        $this->assertDatabaseHas('stripe_events', [
            'id' => $this->event['id'],
            'account_id' => null,
            'api_version' => $this->event['api_version'],
            'created' => Carbon::createFromTimestamp($this->event['created'])->toDateTimeString(),
            'livemode' => $this->event['livemode'],
            'pending_webhooks' => $this->event['pending_webhooks'],
            'type' => $this->event['type'],
            'request' => $this->event['request'] ? json_encode($this->event['request']) : null,
        ]);

        $expected = StripeEvent::findOrFail($this->event['id']);

        Queue::assertPushedOn('stripe_queue', ProcessWebhook::class, function ($job) use ($expected) {
            $this->assertSame('stripe_connection', $job->connection, 'job connection');
            $this->assertTrue($expected->is($job->event), 'job event');
            $this->assertEquals($this->event, $job->payload, 'job payload');
            return true;
        });

        Event::assertNotDispatched(SignatureVerificationFailed::class);
    }

    public function testConnect()
    {
        factory(StripeAccount::class)->create([
            'id' => $this->event['account'],
        ]);

        $this->withoutExceptionHandling()
            ->postJson('/test/webhook', $this->event)
            ->assertStatus(204);

        $this->assertDatabaseHas('stripe_events', [
            'id' => $this->event['id'],
            'account_id' => $this->event['account'],
            'api_version' => $this->event['api_version'],
            'created' => Carbon::createFromTimestamp($this->event['created'])->toDateTimeString(),
            'livemode' => $this->event['livemode'],
            'pending_webhooks' => $this->event['pending_webhooks'],
            'type' => $this->event['type'],
            'request' => $this->event['request'] ? json_encode($this->event['request']) : null,
        ]);
    }

    public function testSpecificQueue()
    {
        unset($this->event['account']);

        config()->set('stripe.webhooks.account', [
            /** Use the underscore version */
            'charge_failed' => [
                'connection' => null,
                'queue' => 'high_queue',
            ],
        ]);

        $this->withoutExceptionHandling()
            ->postJson('/test/webhook', $this->event)
            ->assertStatus(204);

        $expected = StripeEvent::findOrFail($this->event['id']);

        Queue::assertPushedOn('high_queue', ProcessWebhook::class, function ($job) use ($expected) {
            $this->assertNull($job->connection, 'job connection');
            $this->assertTrue($expected->is($job->event), 'job event');
            $this->assertEquals($this->event, $job->payload, 'job payload');
            return true;
        });
    }

    public function testConnectSpecificQueue()
    {
        config()->set('stripe.webhooks.connect', [
            /** Use the underscore version */
            'charge_failed' => [
                'connection' => null,
                'queue' => 'high_queue',
            ],
        ]);

        $this->withoutExceptionHandling()
            ->postJson('/test/webhook', $this->event)
            ->assertStatus(204);

        $expected = StripeEvent::findOrFail($this->event['id']);

        Queue::assertPushedOn('high_queue', ProcessWebhook::class, function ($job) use ($expected) {
            $this->assertNull($job->connection, 'job connection');
            $this->assertTrue($expected->is($job->event), 'job event');
            $this->assertEquals($this->event, $job->payload, 'job payload');
            return true;
        });
    }

    public function testInvalidSignature()
    {
        $this->verifier->method('verify')->willThrowException(
            new SignatureVerification('Invalid.', 'Header')
        );

        $this->postJson('/test/webhook', $this->event)->assertStatus(400)->assertExactJson([
            'error' => 'Invalid signature.',
        ]);

        $this->assertDatabaseMissing('stripe_events', ['id' => $this->event['id']]);

        Queue::assertNotPushed(ProcessWebhook::class);

        Event::assertDispatched(SignatureVerificationFailed::class, function ($event) {
            $this->assertSame('Invalid.', $event->message, 'message');
            $this->assertSame('Header', $event->header, 'header');
            $this->assertSame('foobar', $event->signingSecret, 'signing secret');
            return true;
        });
    }

    public function testInvalidPayload()
    {
        $this->postJson('/test/webhook', ['object' => 'blah'])->assertStatus(400)->assertExactJson([
            'error' => 'Invalid JSON payload.',
        ]);

        $this->assertDatabaseMissing('stripe_events', ['id' => $this->event['id']]);

        Queue::assertNotPushed(ProcessWebhook::class);
    }

}
