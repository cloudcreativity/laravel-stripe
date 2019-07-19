<?php

namespace CloudCreativity\LaravelStripe\Events;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
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
     * FetchedUserCredentials constructor.
     *
     * @param AccountInterface $account
     * @param StripeObject $token
     */
    public function __construct(AccountInterface $account, StripeObject $token)
    {
        $this->account = $account;
        $this->token = $token;
    }
}
