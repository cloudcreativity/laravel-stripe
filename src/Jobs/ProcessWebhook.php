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

namespace CloudCreativity\LaravelStripe\Jobs;

use CloudCreativity\LaravelStripe\Contracts\Connect\AdapterInterface;
use CloudCreativity\LaravelStripe\Contracts\Webhooks\ProcessorInterface;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
     * @param AdapterInterface $accounts
     * @param ProcessorInterface $processor
     * @return void
     * @throws \Throwable
     */
    public function handle(AdapterInterface $accounts, ProcessorInterface $processor)
    {
        /**
         * We look the account up via the adapter rather than the Stripe Event
         * model, in case the developer is using their own event model (which
         * may not have the account relationship).
         */
        $accountId = isset($this->payload['account']) ? $this->payload['account'] : null;
        $account = $accountId ? $accounts->find($accountId) : null;

        $webhook = new Webhook(
            $this->event,
            $account,
            $this->payload,
            $this->queue,
            $this->connection
        );

        $this->event->getConnection()->transaction(function () use ($processor, $webhook) {
            $processor->dispatch($webhook);
        });
    }

}
