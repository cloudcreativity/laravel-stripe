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

namespace CloudCreativity\LaravelStripe\Connect;

trait ConnectedAccount
{

    /**
     * @return Connector
     */
    public function stripe()
    {
        return app('stripe')->connect(
            $this->getStripeAccountIdentifier()
        );
    }

    /**
     * @return string
     */
    public function getStripeAccountIdentifier()
    {
        return $this->{$this->getStripeAccountIdentifierName()};
    }

    /**
     * Get the Stripe account ID column name.
     *
     * If your model does not use an incrementing primary key, we assume
     * that the primary key is also the Stripe ID.
     *
     * If your model does use incrementing primary keys, we default to
     * `stripe_account_id` as the column name.
     *
     * If you use a different name, just implement this method yourself.
     *
     * @return string
     */
    public function getStripeAccountIdentifierName()
    {
        if (!$this->incrementing) {
            return $this->getKeyName();
        }

        return 'stripe_account_id';
    }

    /**
     * @return string|null
     */
    public function getStripeTokenScope()
    {
        return $this->{$this->getStripeTokenScopeName()};
    }

    /**
     * Get the name for the Stripe token scope.
     *
     * @return string
     */
    public function getStripeTokenScopeName()
    {
        return $this->hasStripeKey() ? 'token_scope' : 'stripe_token_scope';
    }

    /**
     * Get the Stripe refresh token.
     *
     * @return string|null
     */
    public function getStripeRefreshToken()
    {
        return $this->{$this->getStripeRefreshTokenName()};
    }

    /**
     * Get the Stripe refresh token column name.
     *
     * @return string
     */
    public function getStripeRefreshTokenName()
    {
        return $this->hasStripeKey() ? 'refresh_token' : 'stripe_refresh_token';
    }

    /**
     * Get the user id that the account is associated to.
     *
     * @return mixed|null
     */
    public function getStripeOwnerIdentifier()
    {
        return $this->{$this->getStripeOwnerIdentifierName()};
    }

    /**
     * Get the user id column name.
     *
     * If this method returns null, the user will not be stored
     * when an access token is fetched.
     *
     * @return string|null
     */
    public function getStripeOwnerIdentifierName()
    {
        return 'owner_id';
    }

    /**
     * Is the model using the Stripe account identifier as its key?
     *
     * @return bool
     */
    protected function hasStripeKey()
    {
        return $this->getKeyName() === $this->getStripeAccountIdentifierName();
    }

}
