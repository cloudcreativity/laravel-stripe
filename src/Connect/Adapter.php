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

namespace CloudCreativity\LaravelStripe\Connect;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AdapterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Stripe\Account;

class Adapter implements AdapterInterface
{

    /**
     * @var Model|ConnectedAccount
     */
    private $model;

    /**
     * ConnectedAccounts constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        if (!$model instanceof AccountInterface) {
            throw new InvalidArgumentException('Expecting a connected account model.');
        }

        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function find($accountId)
    {
        return $this->query($accountId)->first();
    }

    /**
     * @inheritDoc
     */
    public function store($accountId, $refreshToken, AccountOwnerInterface $owner)
    {
        $account = $this->find($accountId) ?: $this->model->newInstance();
        $account->{$this->model->getStripeAccountIdentifierName()} = $accountId;
        $account->{$this->model->getStripeOwnerIdentifierName()} = $owner->getStripeIdentifier();
        $account->{$this->model->getStripeRefreshTokenName()} = $refreshToken;
        $account->save();

        return $account;
    }

    /**
     * @inheritDoc
     */
    public function update(Account $account)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $accountId
     * @return Builder
     */
    protected function query($accountId)
    {
        return $this->model->newQuery()->where(
            $this->model->getStripeAccountIdentifierName(),
            $accountId
        );
    }

}
