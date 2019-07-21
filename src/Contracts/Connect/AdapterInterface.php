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

namespace CloudCreativity\LaravelStripe\Contracts\Connect;

use Stripe\Account;

interface AdapterInterface
{

    /**
     * Find a connected account by its Stripe id.
     *
     * @param $accountId
     * @return AccountInterface|null
     */
    public function find($accountId);

    /**
     * Store an account id and refresh access token.
     *
     * Called when an authorization token is fetched at the end of the OAuth
     * process. The Stripe documentation states that the following should
     * be stored:
     *
     * - The `stripe_user_id`: $accountId
     * - The `refresh_token`: $refreshToken
     *
     * We also pass the token scope, so that you can store whether you have
     * read_only or read_write access to the Stripe account.
     *
     * @param string $accountId
     * @param string $refreshToken
     * @param string $scope
     * @param AccountOwnerInterface|null $owner
     *      the user associated with the OAuth process.
     * @return AccountInterface
     * @see https://stripe.com/docs/connect/standard-accounts#token-request
     */
    public function store($accountId, $refreshToken, $scope, AccountOwnerInterface $owner);

    /**
     * Update an account from a Stripe account resource.
     *
     * @param AccountInterface $account
     * @param Account $resource
     *      the Stripe account resource.
     * @return void
     */
    public function update(AccountInterface $account, Account $resource);

    /**
     * Remove an account when it is de-authorized.
     *
     * @param AccountInterface $account
     * @return void
     */
    public function remove(AccountInterface $account);
}
