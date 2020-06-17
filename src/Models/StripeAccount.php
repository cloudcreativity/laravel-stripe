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

namespace CloudCreativity\LaravelStripe\Models;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Connect\ConnectedAccount;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StripeAccount extends Model implements AccountInterface
{

    use ConnectedAccount;
    use SoftDeletes;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'business_profile',
        'business_type',
        'capabilities',
        'charges_enabled',
        'country',
        'default_currency',
        'details_submitted',
        'email',
        'individual',
        'metadata',
        'payouts_enabled',
        'requirements',
        'settings',
        'tos_acceptance',
        'type',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'business_profile' => 'json',
        'capabilities' => 'json',
        'charges_enabled' => 'boolean',
        'details_submitted' => 'boolean',
        'individual' => 'json',
        'metadata' => 'json',
        'payouts_enabled' => 'boolean',
        'requirements' => 'json',
        'settings' => 'json',
        'tos_acceptance' => 'json',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * @return HasMany
     */
    public function events()
    {
        $model = Config::webhookModel();

        return $this->hasMany(
            get_class($model),
            $model->getAccountIdentifierName(),
            $this->getStripeAccountIdentifierName()
        );
    }

    /**
     * @return BelongsTo
     */
    public function owner()
    {
        $model = Config::connectOwner();

        return $this->belongsTo(
            get_class($model),
            $this->getStripeOwnerIdentifierName(),
            $model->getStripeIdentifierName(),
            'owner'
        );
    }
}
