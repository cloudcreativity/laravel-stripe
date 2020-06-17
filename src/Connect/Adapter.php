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

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AdapterInterface;
use CloudCreativity\LaravelStripe\Exceptions\UnexpectedValueException;
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
    public function store($accountId, $refreshToken, $scope, AccountOwnerInterface $owner)
    {
        $account = $this->findWithTrashed($accountId) ?: $this->newInstance($accountId);
        $account->{$this->model->getStripeRefreshTokenName()} = $refreshToken;
        $account->{$this->model->getStripeTokenScopeName()} = $scope;
        $account->{$this->model->getStripeOwnerIdentifierName()} = $owner->getStripeIdentifier();

        if ($account->exists && $this->softDeletes()) {
            $account->restore();
        } else {
            $account->save();
        }

        return $account;
    }

    /**
     * @inheritDoc
     */
    public function update(AccountInterface $account, Account $resource)
    {
        if (!$account instanceof $this->model) {
            throw new UnexpectedValueException('Unexpected Stripe account model.');
        }

        if ($account->getStripeAccountIdentifier() !== $resource->id) {
            throw new UnexpectedValueException('Unexpected Stripe account resource.');
        }

        $account->update($resource->jsonSerialize());
    }

    /**
     * @inheritDoc
     */
    public function remove(AccountInterface $account)
    {
        if (!$account instanceof $this->model) {
            throw new UnexpectedValueException('Unexpected Stripe account model.');
        }

        $account->{$this->model->getStripeRefreshTokenName()} = null;
        $account->{$this->model->getStripeTokenScopeName()} = null;
        $account->save();

        if ($this->softDeletes()) {
            $account->delete();
        }
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

    /**
     * @param $accountId
     * @return Model|null
     */
    protected function findWithTrashed($accountId)
    {
        $query = $this->query($accountId);

        if ($this->softDeletes()) {
            $query->withTrashed();
        }

        return $query->first();
    }

    /**
     * Make a new instance of the account model.
     *
     * @param $accountId
     * @return Model
     */
    protected function newInstance($accountId)
    {
        $account = $this->model->newInstance();
        $account->{$this->model->getStripeAccountIdentifierName()} = $accountId;

        return $account;
    }

    /**
     * Does the model soft-delete?
     *
     * @return bool
     */
    protected function softDeletes()
    {
        return method_exists($this->model, 'forceDelete');
    }

}
