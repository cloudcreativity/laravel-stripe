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

namespace CloudCreativity\LaravelStripe\Connect;

use CloudCreativity\LaravelStripe\Connector as BaseConnector;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Events\AccountDeauthorized;
use Illuminate\Database\Eloquent\Model;

class Connector extends BaseConnector
{


    /**
     * @var AccountInterface|Model
     */
    private $account;

    /**
     * @var Authorizer
     */
    private $authorizer;

    /**
     * Connector constructor.
     *
     * @param AccountInterface $account
     */
    public function __construct(AccountInterface $account)
    {
        $this->account = $account;
    }

    /**
     * Is the connector for the provided account?
     *
     * @param AccountInterface|string $accountId
     * @return bool
     */
    public function is($accountId)
    {
        if ($accountId instanceof AccountInterface) {
            $accountId = $accountId->getStripeAccountIdentifier();
        }

        return $this->id() === $accountId;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->account->getStripeAccountIdentifier();
    }

    /**
     * Deauthorize the connected account.
     *
     * @param iterable|array|null $options
     * @return void
     */
    public function deauthorize($options = null)
    {
        app(Authorizer::class)->deauthorize(
            $this->accountId(),
            collect($options)->all() ?: null
        );

        event(new AccountDeauthorized($this->account));
    }

    /**
     * @return string
     */
    protected function accountId(): string
    {
        return $this->id();
    }
}
