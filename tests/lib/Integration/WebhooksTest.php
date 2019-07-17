<?php

namespace CloudCreativity\LaravelStripe\Tests\Integration;

use Carbon\Carbon;
use CloudCreativity\LaravelStripe\Events\SignatureVerificationFailed;
use CloudCreativity\LaravelStripe\Http\Controllers\WebhookController;
use CloudCreativity\LaravelStripe\Jobs\ProcessWebhook;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Webhooks\Verifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Stripe\Error\SignatureVerification;

class WebhooksTest extends TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $verifier;

    /**
     * @var array
     */
    private $event;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Queue::fake();
        Event::fake();

        $this->instance(Verifier::class, $this->verifier = $this->createMock(Verifier::class));
        $this->event = $this->stub('webhook');

        Route::post('/test/webhook', WebhookController::class)->middleware('stripe.verify:foobar');

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
        config()->set('stripe.webhooks.queues', [
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
