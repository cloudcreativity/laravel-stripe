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
use CloudCreativity\LaravelStripe\Exceptions\InvalidArgumentException;
use CloudCreativity\LaravelStripe\Repositories\AbstractRepository;
use Illuminate\Support\Str;
use Stripe\Account;

class Connector
{

    /**
     * @var AccountInterface
     */
    private $account;

    /**
     * RepositoryManager constructor.
     *
     * @param AccountInterface $account
     */
    public function __construct(AccountInterface $account = null)
    {
        $this->account = $account;
    }

    /**
     * Get a resource repository by resource type.
     *
     * @param string $resource
     * @return AbstractRepository
     */
    public function __invoke($resource)
    {
        $method = Str::camel($resource);
        $repository = null;

        if (method_exists($this, $method) && !in_array($method, ['account'])) {
            $repository = call_user_func([$this, $method]);
        }

        if (!$repository instanceof AbstractRepository) {
            throw new InvalidArgumentException("Invalid resource type: {$resource}");
        }

        return $repository;
    }

    /**
     * Get the account id of the connected account.
     *
     * @return string|null
     */
    public function accountId()
    {
        return $this->account ? $this->account->getStripeAccountId() : null;
    }

    /**
     * Get the Stripe Account object for this connector's account.
     *
     * @return Account
     */
    public function account()
    {
        return $this->accounts()->retrieve();
    }

    /**
     * @return Repositories\AccountRepository
     */
    public function accounts()
    {
        return new Repositories\AccountRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * @return Repositories\ChargeRepository
     */
    public function charges()
    {
        return new Repositories\ChargeRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * @return Repositories\EventRepository
     */
    public function events()
    {
        return new Repositories\EventRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * Create a payment intents client for the provided account.
     *
     * @return Repositories\PaymentIntentRepository
     */
    public function paymentIntents()
    {
        return new Repositories\PaymentIntentRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * @return Repositories\RefundRepository
     */
    public function refunds()
    {
        return new Repositories\RefundRepository(
            app(Client::class),
            $this->accountId()
        );
    }
}
