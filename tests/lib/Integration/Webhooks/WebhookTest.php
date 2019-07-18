<?php

namespace CloudCreativity\LaravelStripe\Tests\Webhooks;

use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Illuminate\Contracts\Database\ModelIdentifier;

class WebhookTest extends TestCase
{

    public function test()
    {
        $this->markTestIncomplete('@todo add tests for helper methods');
    }

    public function testSerialize()
    {
        $event = factory(StripeEvent::class)->states('connect')->create();

        $webhook = new Webhook(
            $event,
            $event->account,
            $this->stub('webhook'),
            'my_queue',
            'my_connection'
        );

        $serialized = unserialize(serialize($webhook));

        $this->assertInstanceOf(ModelIdentifier::class, $webhook->event, 'event was serialized');
        $this->assertInstanceOf(ModelIdentifier::class, $webhook->account, 'account was serialized');
        $this->assertTrue($event->is($serialized->event), 'same event');
        $this->assertTrue($event->account->is($serialized->account), 'same account');
    }
}
