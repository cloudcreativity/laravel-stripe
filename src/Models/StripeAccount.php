<?php

namespace CloudCreativity\LaravelStripe\Models;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Connect\ConnectedAccount;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use Illuminate\Database\Eloquent\Model;
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
        'country',
        'default_currency',
        'details_submitted',
        'email',
        'individual',
        'metadata',
        'payouts_enabled',
        'requirements',
        'tos_acceptance',
        'type',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'business_profile' => 'json',
        'capabilities' => 'json',
        'details_submitted' => 'boolean',
        'individual' => 'json',
        'metadata' => 'json',
        'payouts_enabled' => 'boolean',
        'requirements' => 'json',
        'tos_acceptance' => 'json',
    ];

    /**
     * @return HasMany
     */
    public function events()
    {
        $model = Config::webhookModel();

        return new HasMany(
            $model->newQuery(),
            $this,
            $model->getQualifiedAccountIdKeyName(),
            $this->getKeyName()
        );
    }
}
