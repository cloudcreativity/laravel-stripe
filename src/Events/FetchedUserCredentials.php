<?php

namespace CloudCreativity\LaravelStripe\Events;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;
use Stripe\StripeObject;

class FetchedUserCredentials
{

    use SerializesModels;

    /**
     * @var AccountInterface
     */
    public $account;

    /**
     * @var StripeObject
     */
    public $token;

    /**
     * @var Authenticatable|mixed|null
     */
    public $user;

    /**
     * FetchedUserCredentials constructor.
     *
     * @param AccountInterface $account
     * @param StripeObject $token
     * @param Authenticatable|mixed|null
     */
    public function __construct(AccountInterface $account, StripeObject $token, $user)
    {
        $this->account = $account;
        $this->token = $token;
        $this->user = $user;
    }
}
