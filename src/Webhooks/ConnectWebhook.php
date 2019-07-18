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
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Stripe\Event;

class ConnectWebhook extends Webhook
{

    /**
     * The stored Stripe account.
     *
     * The account can be `null` if the webhook was received but for some
     * reason the account is no longer in storage.
     *
     * @var AccountInterface|StripeAccount|mixed|null
     */
    public $account;

    /**
     * ConnectWebhook constructor.
     *
     * @param Event $webhook
     * @param AccountInterface|StripeAccount|mixed|null $account
     * @param StripeEvent|mixed $model
     * @param array $config
     * @todo PHP7 account should be `?AccountInterface` and model not optional.
     */
    public function __construct(
        Event $webhook,
        AccountInterface $account = null,
        $model = null,
        array $config = []
    ) {
        if (!Arr::get($webhook, 'account')) {
            throw new InvalidArgumentException('Expecting a Stripe Connect webhook.');
        }

        parent::__construct($webhook, $model, $config);
        $this->account = $account;
    }

    /**
     * @inheritDoc
     */
    public function connect()
    {
        return true;
    }

    /**
     * @return string|null
     */
    public function account()
    {
        return $this->webhook['account'];
    }

    /**
     * Is the webhook for the supplied account?
     *
     * @param AccountInterface|string $account
     * @return bool
     */
    public function accountIs($account)
    {
        if ($account instanceof AccountInterface) {
            $account = $account->getStripeAccountId();
        }

        return $this->account() === $account;
    }

    /**
     * Is the webhook not for the specified account?
     *
     * @param AccountInterface|string $account
     * @return bool
     */
    public function accountIsNot($account)
    {
        return !$this->accountIs($account);
    }
}
