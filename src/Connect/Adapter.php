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

use CloudCreativity\LaravelStripe\Contracts\Connect\AdapterInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use RuntimeException;
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
    public function store($accountId, $refreshToken, $user)
    {
        $account = $this->find($accountId) ?: $this->newInstance($accountId, $user);
        $account->{$this->model->getStripeRefreshTokenKeyName()} = $refreshToken;
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
            $this->model->getStripeAccountKeyName(),
            $accountId
        );
    }

    /**
     * Make a new instance.
     *
     * @param string|null $accountId
     * @param mixed|null $user
     * @return Model|ConnectedAccount
     */
    protected function newInstance($accountId, $user)
    {
        $account = $this->model->newInstance();
        $account->{$this->model->getStripeAccountKeyName()} = $accountId;

        $userKey = $this->model->getUserIdKeyName();

        if ($userKey && $userId = $this->userId($user)) {
            $account->{$userKey} = $userId;
        }

        return $account;
    }

    /**
     * Get the user id.
     *
     * @param mixed|null $user
     * @return string|int|null
     */
    protected function userId($user)
    {
        if (is_null($user)) {
            return null;
        }

        if ($user instanceof Model) {
            return $user->getKey();
        }

        if ($user instanceof Authenticatable) {
            return $user->getAuthIdentifier();
        }

        throw new RuntimeException('Cannot determine user id to store with Stripe account.');
    }

}
