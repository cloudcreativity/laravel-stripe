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

namespace CloudCreativity\LaravelStripe;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountAdapterInterface;
use CloudCreativity\LaravelStripe\Contracts\Webhooks\DispatcherInterface;
use CloudCreativity\LaravelStripe\Exceptions\AccountNotConnected;
use Stripe\Event;

class StripeService
{

    /**
     * @var AccountAdapterInterface
     */
    private $accounts;

    /**
     * @var DispatcherInterface
     */
    private $webhooks;

    /**
     * StripeService constructor.
     *
     * @param AccountAdapterInterface $accounts
     * @param DispatcherInterface $webhooks
     */
    public function __construct(AccountAdapterInterface $accounts, DispatcherInterface $webhooks)
    {
        $this->accounts = $accounts;
        $this->webhooks = $webhooks;
    }

    /**
     * Access the main application account.
     *
     * @return Connector
     */
    public function app()
    {
        return new Connector();
    }

    /**
     * Access a connected account.
     *
     * This method is a short-hand alias for `connectedAccount()`.
     *
     * @param AccountInterface|string $accountId
     * @return Connector
     */
    public function account($accountId)
    {
        return $this->connectedAccount($accountId);
    }

    /**
     * Access a connected account.
     *
     * @param AccountInterface|string $accountId
     *      the Stripe account id.
     * @return Connector
     * @throws AccountNotConnected
     */
    public function connectedAccount($accountId)
    {
        if ($accountId instanceof AccountInterface) {
            return new Connector($accountId);
        }

        Assert::id(Assert::ACCOUNT_ID_PREFIX, $accountId);

        if ($account = $this->accounts->find($accountId)) {
            return new Connector($account);
        }

        throw new AccountNotConnected($accountId);
    }

    /**
     * Dispatch a Stripe webhook.
     *
     * @param Event $event
     * @return void
     */
    public function dispatch(Event $event)
    {
        $account = null;

        if (isset($event['account'])) {
            $account = $this->accounts->find($event['account']);
        }

        $this->webhooks->dispatch($event, $account);
    }
}
