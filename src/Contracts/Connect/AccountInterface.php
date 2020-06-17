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

namespace CloudCreativity\LaravelStripe\Contracts\Connect;

interface AccountInterface
{

    /**
     * Get the Stripe account id for the connected account.
     *
     * @return string
     */
    public function getStripeAccountIdentifier();

    /**
     * Get the name for the Stripe account id.
     *
     * @return string
     */
    public function getStripeAccountIdentifierName();

    /**
     * Get the token scope for the Stripe account.
     *
     * @return string|null
     */
    public function getStripeTokenScope();

    /**
     * Get the name for the Stripe token scope.
     *
     * @return string
     */
    public function getStripeTokenScopeName();

    /**
     * Get the Stripe refresh token for the connected account.
     *
     * @return string|null
     */
    public function getStripeRefreshToken();

    /**
     * Get the name for the Stripe refresh token.
     *
     * @return string
     */
    public function getStripeRefreshTokenName();

    /**
     * Get the unique identifier for the Stripe account owner.
     *
     * @return string|int
     */
    public function getStripeOwnerIdentifier();

    /**
     * Get the name for the Stripe account owner identifier.
     *
     * @return string
     */
    public function getStripeOwnerIdentifierName();
}
