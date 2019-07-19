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

namespace CloudCreativity\LaravelStripe\Models;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Connector;
use CloudCreativity\LaravelStripe\Exceptions\AccountNotConnectedException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripeEvent extends Model
{

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'account_id',
        'api_version',
        'created',
        'livemode',
        'pending_webhooks',
        'type',
        'request',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'livemode' => 'boolean',
        'pending_webhooks' => 'integer',
        'request' => 'json',
    ];

    /**
     * @return BelongsTo
     */
    public function account()
    {
        $model = Config::connectModel();

        return new BelongsTo(
            $model->newQuery(),
            $this,
            $this->getAccountIdentifierName(),
            $model->getStripeAccountIdentifierName(),
            'account'
        );
    }

    /**
     * @return string
     */
    public function getAccountIdentifierName()
    {
        return 'account_id';
    }

    /**
     * Get the Stripe connector for the account that this belongs to.
     *
     * @return Connector
     * @throws AccountNotConnectedException
     */
    public function stripe()
    {
        if ($account = $this->account_id) {
            return app('stripe')->connect($account);
        }

        return app('stripe')->account();
    }
}
