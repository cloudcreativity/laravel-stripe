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

namespace CloudCreativity\LaravelStripe\Jobs;

use CloudCreativity\LaravelStripe\Contracts\Webhooks\ProcessorInterface;
use CloudCreativity\LaravelStripe\Log\Logger;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\Event;

class ProcessWebhook implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, Queueable;

    /**
     * @var StripeEvent
     */
    public $event;

    /**
     * @var array
     */
    public $payload;

    /**
     * ProcessWebhook constructor.
     *
     * @param StripeEvent|Model $event
     *      the stored Stripe event model.
     * @param array $payload
     *      the payload received from Stripe
     */
    public function __construct($event, array $payload)
    {
        $this->event = $event;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @param ProcessorInterface $processor
     * @param Logger $log
     * @return void
     * @throws \Throwable
     */
    public function handle(ProcessorInterface $processor, Logger $log)
    {
        $webhook = Event::constructFrom($this->payload);

        $log->log(
            "Processing webhook {$webhook->id}.",
            collect($this->payload)->only('account', 'type')->all()
        );

        $this->event->getConnection()->transaction(function () use ($processor, $webhook) {
            $processor->dispatch($webhook, $this->event);
        });
    }

}
