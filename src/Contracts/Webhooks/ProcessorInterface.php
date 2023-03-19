<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

namespace CloudCreativity\LaravelStripe\Contracts\Webhooks;

use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Stripe\Event;

interface ProcessorInterface
{

    /**
     * Receive a Stripe webhook.
     *
     * If your webhook processor performs complex logic, or makes network calls,
     * it’s possible that the request would time out before Stripe sees its complete execution.
     * Ideally, your webhook handler code is separate of any other logic you do for that event.
     *
     * Therefore the process method should do the minimum required and delay timely
     * processing to a later point. E.g. adding a job to an asynchronous queue.
     *
     * @param Event $event
     * @return void
     * @see https://stripe.com/docs/webhooks/best-practices#acknowledge-events-immediately
     */
    public function receive(Event $event);

    /**
     * Has the Stripe webhook been received?
     *
     * Webhook endpoints might occasionally receive the same event more than once.
     * Stripe advise to guard against duplicated event receipts by making event
     * processing idempotent.
     *
     * @param Event $event
     * @return bool
     * @see https://stripe.com/docs/webhooks/best-practices#duplicate-events
     */
    public function didReceive(Event $event);

    /**
     * Dispatch a processed webhook.
     *
     * @param Event $event
     * @param StripeEvent|mixed $model
     *      the stored webhook.
     * @return void
     */
    public function dispatch(Event $event, $model);

}
