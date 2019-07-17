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
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Stripe\Event;

class Processor implements ProcessorInterface
{

    /**
     * @var Dispatcher
     */
    private $queue;

    /**
     * @var Model
     */
    private $model;

    /**
     * Processor constructor.
     *
     * @param Dispatcher $queue
     * @param Model $model
     */
    public function __construct(Dispatcher $queue, Model $model)
    {
        $this->queue = $queue;
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function process(Event $event)
    {
        $data = $event->jsonSerialize();

        /**
         * Create the model, giving it the option of filling the account as `account_id`.
         */
        $model = $this->model->create(
            collect($data)->put('account_id', Arr::get($data, 'account'))->all()
        );

        /** Get the queue config for this specific event.  */
        $queue = Config::webhookQueue($event->type);

        /** Dispatch a job to asynchronously process the webhook. */
        $job = new ProcessWebhook($model, $data);
        $job->onConnection($queue['connection'])->onQueue($queue['queue']);

        $this->queue->dispatch($job);
    }

    /**
     * @inheritDoc
     */
    public function didProcess(Event $event)
    {
        return $this->model->newQuery()->whereKey($event->id)->exists();
    }

}
