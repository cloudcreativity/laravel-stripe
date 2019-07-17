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

namespace CloudCreativity\LaravelStripe\Webhooks;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Contracts\Webhooks\ProcessorInterface;
use CloudCreativity\LaravelStripe\Jobs\ProcessWebhook;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Illuminate\Contracts\Bus\Dispatcher;
use Stripe\Event;

class Processor implements ProcessorInterface
{

    /**
     * @var Dispatcher
     */
    private $queue;

    /**
     * Processor constructor.
     *
     * @param Dispatcher $queue
     */
    public function __construct(Dispatcher $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @inheritDoc
     */
    public function process(Event $event)
    {
        $queue = Config::webhookQueueDetails($event->type);
        $model = StripeEvent::create($event->jsonSerialize());

        $job = new ProcessWebhook($model, $event->jsonSerialize());
        $job->onConnection($queue['connection'])->onQueue($queue['queue']);

        $this->queue->dispatch($job);
    }

    /**
     * @inheritDoc
     */
    public function didProcess(Event $event)
    {
        return StripeEvent::query()->whereKey($event->id)->exists();
    }

}
