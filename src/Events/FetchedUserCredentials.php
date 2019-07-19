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

namespace CloudCreativity\LaravelStripe\Events;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;
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
     * @var AccountOwnerInterface
     */
    public $owner;

    /**
     * @var StripeObject
     */
    public $token;

    /**
     * FetchedUserCredentials constructor.
     *
     * @param AccountInterface $account
     * @param AccountOwnerInterface $owner
     * @param StripeObject $token
     */
    public function __construct(AccountInterface $account, AccountOwnerInterface $owner, StripeObject $token)
    {
        $this->account = $account;
        $this->owner = $owner;
        $this->token = $token;
    }
}
