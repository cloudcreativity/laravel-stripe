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

use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Stripe\Event;

class Webhook
{

    use SerializesModels;

    /**
     * The Stripe Event object.
     *
     * @var Event
     */
    public $webhook;

    /**
     * The stored webhook.
     *
     * @var StripeEvent|mixed
     */
    public $model;

    /**
     * @var array
     */
    public $config;

    /**
     * Webhook constructor.
     *
     * @param Event $webhook
     * @param StripeEvent|mixed $model
     * @param array $config
     */
    public function __construct(Event $webhook, $model, array $config = [])
    {
        $this->webhook = $webhook;
        $this->model = $model;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->webhook->id;
    }

    /**
     * Get the type of webhook.
     *
     * @return string
     */
    public function type()
    {
        return $this->webhook->type;
    }

    /**
     * Is this a Connect webhook?
     *
     * Useful for listeners or jobs that run on both account and Connect webhooks.
     *
     * @return bool
     */
    public function connect()
    {
        return false;
    }

    /**
     * Is the webhook the specified type?
     *
     * @param $type
     * @return bool
     */
    public function is($type)
    {
        return $this->type() === $type;
    }

    /**
     * Is the webhook not the specified type.
     *
     * @param string $type
     * @return bool
     */
    public function isNot($type)
    {
        return !$this->is($type);
    }

    /**
     * Get the configured queue for the webhook.
     *
     * @return string|null
     */
    public function queue()
    {
        return Arr::get($this->config, 'queue');
    }

    /**
     * Get the configured connection for the webhook.
     *
     * @return
     */
    public function connection()
    {
        return Arr::get($this->config, 'connection');
    }

    /**
     * Get the configured job for the webhook.
     *
     * @return string|null
     */
    public function job()
    {
        return Arr::get($this->config, 'job');
    }

}
