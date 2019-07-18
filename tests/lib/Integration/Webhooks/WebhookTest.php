<?php

namespace CloudCreativity\LaravelStripe\Tests\Webhooks;

use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Tests\Integration\TestCase;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Illuminate\Contracts\Database\ModelIdentifier;
use Stripe\Event;

class WebhookTest extends TestCase
{

    public function test()
    {
        $webhook = Event::constructFrom($this->stub('webhook'));
        $model = factory(StripeEvent::class)->create();

        $event = new Webhook($webhook, $model);

        $serialized = unserialize(serialize($event));

        $this->assertInstanceOf(ModelIdentifier::class, $event->model, 'event was serialized');
        $this->assertEquals($event->webhook, $webhook, 'same webhook');
        $this->assertTrue($model->is($serialized->model), 'same model');
    }
}
