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

use CloudCreativity\LaravelStripe\Log\Logger;
use CloudCreativity\LaravelStripe\Webhooks\Webhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;

class DispatchWebhookJob
{

    /**
     * @var Dispatcher
     */
    private $queue;

    /**
     * @var Logger
     */
    private $log;

    /**
     * DispatchWebhookJob constructor.
     *
     * @param Dispatcher $queue
     * @param Logger $log
     */
    public function __construct(Dispatcher $queue, Logger $log)
    {
        $this->queue = $queue;
        $this->log = $log;
    }

    /**
     * Handle the event.
     *
     * @param Webhook $webhook
     * @return void
     */
    public function handle(Webhook $webhook)
    {
        if (!$job = $webhook->job()) {
            return;
        }

        /** @var Queueable $job */
        $job = new $job($webhook);
        $job->onConnection($webhook->connection());
        $job->onQueue($webhook->queue());

        $this->log->log("Dispatching job for webhook '{$webhook->type()}'.", [
            'id' => $webhook->id(),
            'connection' => $webhook->connection(),
            'queue' => $webhook->queue(),
            'job' => $webhook->job(),
        ]);

        $this->queue->dispatch($job);
    }
}
