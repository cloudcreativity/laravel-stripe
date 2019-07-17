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

namespace CloudCreativity\LaravelStripe\Listeners;

use CloudCreativity\LaravelStripe\Events\ClientWillSend;
use CloudCreativity\LaravelStripe\Log\Logger;

class LogClientRequests
{

    /**
     * @var Logger
     */
    private $log;

    /**
     * LogClientRequests constructor.
     *
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->log = $log;
    }

    /**
     * Handle the event.
     *
     * @param ClientWillSend $event
     * @return void
     */
    public function handle(ClientWillSend $event)
    {
        $this->log->log(
            "Stripe: sending {$event->name}.{$event->method}",
            $event->toArray()
        );
    }
}
