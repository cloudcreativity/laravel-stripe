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

namespace CloudCreativity\LaravelStripe\Events;

use Carbon\Carbon;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Stripe\Event;
use Stripe\StripeObject;

class Webhook
{

    use SerializesModels;

    /**
     * The stored webhook.
     *
     * @var StripeEvent
     */
    public $event;

    /**
     * The connected account the event relates to.
     *
     * @var AccountInterface|mixed|null
     */
    public $account;

    /**
     * The raw payload.
     *
     * @var array $payload
     */
    public $payload;

    /**
     * Webhook constructor.
     *
     * @param StripeEvent $event
     * @param AccountInterface|null $account
     * @param array $payload
     *      the raw Stripe event payload.
     * @todo PHP7 the account should be type-hinted as `?AccountInterface` and payload should not be optional.
     */
    public function __construct(StripeEvent $event, AccountInterface $account = null, array $payload = [])
    {
        $this->event = $event;
        $this->account = $account;
        $this->payload = $payload;
    }

    /**
     * The event id.
     *
     * @return string
     */
    public function id()
    {
        return $this->payload['id'];
    }

    /**
     * The id of the connected account that originated the event.
     *
     * @return string|null
     */
    public function accountId()
    {
        return $this->get('account');
    }

    /**
     * The stored connected account that originated the event.
     *
     * Note that even if there is an account id, this might return null
     * if the account is no longer in your application's storage.
     *
     * @return AccountInterface|null
     */
    public function account()
    {
        return $this->account;
    }

    /**
     * The Stripe API version used to render the data.
     *
     * @return string|null
     */
    public function apiVersion()
    {
        return $this->payload['api_version'];
    }

    /**
     * @return Carbon|null
     */
    public function created()
    {
        if ($created = $this->get('created')) {
            return Carbon::createFromTimestamp($created);
        }

        return null;
    }

    /**
     * @return StripeObject|mixed
     */
    public function data()
    {
        return Event::constructFrom($this->payload)->data;
    }

    /**
     * @return bool
     */
    public function liveMode()
    {
        return (bool) $this->get('livemode');
    }

    /**
     * @return int
     */
    public function pendingWebhooks()
    {
        return $this->get('pending_webhooks');
    }

    /**
     * @return bool
     */
    public function testMode()
    {
        return !$this->liveMode();
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->get('type');
    }

    /**
     * @param $type
     * @return bool
     */
    public function is($type)
    {
        return $this->type() === $type;
    }

    /**
     * Get a value from the payload using dot notation.
     *
     * @param string $path
     * @param null $default
     * @return mixed
     */
    public function get($path, $default = null)
    {
        return Arr::get($this->payload, $path, $default);
    }

    /**
     * Check if an item exists in the payload using dot notation.
     *
     * @param string $path
     * @return bool
     */
    public function has($path)
    {
        return Arr::has($this->payload, $path);
    }
}
