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

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountAdapterInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Adapter implements AccountAdapterInterface
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
        return $this->model->newQuery()->where(
            $this->model->getStripeAccountKeyName(),
            $accountId
        )->first();
    }

}
