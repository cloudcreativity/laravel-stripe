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
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Stripe\Event;

class WebhookTest extends TestCase
{

    public function test()
    {
        $webhook = Event::constructFrom($this->stub('webhook'));
        $model = factory(StripeEvent::class)->create();

        $event = new Webhook($webhook, $model);

        $serialized = unserialize(serialize($event));

        $this->assertEquals($event->webhook, $webhook, 'same webhook');
        $this->assertTrue($model->is($serialized->model), 'same model');
    }
}
