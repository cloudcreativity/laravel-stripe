<?php
/**
 * Copyright 2020 Cloud Creativity Limited
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

declare(strict_types=1);

namespace CloudCreativity\LaravelStripe;

use CloudCreativity\LaravelStripe\Exceptions\UnexpectedValueException;
use CloudCreativity\LaravelStripe\Repositories\AbstractRepository;
use Illuminate\Support\Str;
use Stripe\Account;

class Connector
{

    /**
     * Get a resource repository by resource type.
     *
     * @param string $resource
     * @return AbstractRepository
     */
    public function __invoke($resource): AbstractRepository
    {
        $method = Str::camel($resource);
        $repository = null;

        if (method_exists($this, $method) && !in_array($method, ['retrieve'])) {
            return call_user_func([$this, $method]);
        }

        throw new UnexpectedValueException("Invalid resource type: {$resource}");
    }

    /**
     * Retrieve the Stripe account object that this connector belongs to.
     *
     * @return Account
     */
    public function retrieve(): Account
    {
        return $this->accounts()->retrieve();
    }

    /**
     * @return Repositories\AccountRepository
     */
    public function accounts(): Repositories\AccountRepository
    {
        return new Repositories\AccountRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * @return Repositories\BalanceRepository
     */
    public function balances(): Repositories\BalanceRepository
    {
        return new Repositories\BalanceRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * @return Repositories\ChargeRepository
     */
    public function charges(): Repositories\ChargeRepository
    {
        return new Repositories\ChargeRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * @return Repositories\EventRepository
     */
    public function events(): Repositories\EventRepository
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
    public function paymentIntents(): Repositories\PaymentIntentRepository
    {
        return new Repositories\PaymentIntentRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * @return Repositories\RefundRepository
     */
    public function refunds(): Repositories\RefundRepository
    {
        return new Repositories\RefundRepository(
            app(Client::class),
            $this->accountId()
        );
    }

    /**
     * Get the account id to use when creating a repository.
     *
     * @return string|null
     */
    protected function accountId(): ?string
    {
        return null;
    }

}
