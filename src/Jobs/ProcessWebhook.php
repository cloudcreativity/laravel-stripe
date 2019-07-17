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

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountAdapterInterface;
use CloudCreativity\LaravelStripe\Events\Webhook;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
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
     * For each webhook, we dispatch three events to give applications options
     * as to how they want to bind.
     *
     * E.g. if the Stripe event name is `payment_intent.succeeded`, we dispatch:
     *
     * - `stripe.webhooks`
     * - `stripe.webhooks:payment_intent.*`
     * - `stripe.webhooks:payment_intent.succeeded`
     *
     * @param Dispatcher $events
     * @param AccountAdapterInterface $accounts
     * @return void
     */
    public function handle(Dispatcher $events, AccountAdapterInterface $accounts)
    {
        /**
         * We look the account up via the adapter rather than the Stripe Event
         * model, in case the developer is using their own event model (which
         * may not have the account relationship).
         */
        $accountId = isset($this->payload['account']) ? $this->payload['account'] : null;
        $account = $accountId ? $accounts->find($accountId) : null;

        /** Dispatch a generic event that anything can bind to. */
        $events->dispatch('stripe.webhooks', $event = new Webhook(
            $this->event,
            $account,
            $this->payload
        ));

        $parts = explode('.', $event->type());

        /** Dispatch a namespaced event, e.g. `payment_intent` */
        $events->dispatch(sprintf('stripe.webhooks:%s.*', $parts[0]), $event);

        /** Dispatch a specific event. */
        $events->dispatch(
            sprintf('stripe.webhooks:%s', $event->type()),
            $event
        );

        /** Update the timestamps on the stored webhook */
        $this->event->touch();
    }
}
