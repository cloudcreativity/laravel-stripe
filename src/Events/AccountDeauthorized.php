<?php

namespace CloudCreativity\LaravelStripe\Events;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use Illuminate\Queue\SerializesModels;

class AccountDeauthorized
{

    use SerializesModels;

    /**
     * @var AccountInterface
     */
    public $account;

    /**
     * AccountDeauthorized constructor.
     *
     * @param AccountInterface $account
     */
    public function __construct(AccountInterface $account)
    {
        $this->account = $account;
    }
}
