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

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Contracts\Webhooks\DispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher as EventsContract;
use Stripe\Event;

class Dispatcher implements DispatcherInterface
{

    /**
     * @var EventsContract
     */
    private $events;

    /**
     * Dispatcher constructor.
     *
     * @param EventsContract $events
     */
    public function __construct(EventsContract $events)
    {
        $this->events = $events;
    }

    /**
     * @inheritDoc
     */
    public function dispatch(Event $event, AccountInterface $account = null)
    {
        // TODO
    }

}