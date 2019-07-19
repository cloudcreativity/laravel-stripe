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

namespace CloudCreativity\LaravelStripe\Connect;

use CloudCreativity\LaravelStripe\Connector;

trait ConnectedAccount
{

    /**
     * @return Connector
     */
    public function stripe()
    {
        return app('stripe')->connect(
            $this->getStripeAccountId()
        );
    }

    /**
     * @return string
     */
    public function getStripeAccountId()
    {
        return $this->{$this->getStripeAccountKeyName()};
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
    public function getStripeAccountKeyName()
    {
        if (!$this->incrementing) {
            return $this->getKeyName();
        }

        return 'stripe_account_id';
    }

    /**
     * Get the Stripe refresh token.
     *
     * @return string|null
     */
    public function getStripeRefreshToken()
    {
        return $this->{$this->getStripeRefreshTokenKeyName()};
    }

    /**
     * Get the Stripe refresh token column name.
     *
     * If your model is using the key name as the Stripe account key name,
     * we assume the refresh token is stored as `refresh_token`.
     *
     * Otherwise we assume it is stored as `stripe_refresh_token`.
     *
     * If you use a different name, just implement this method yourself.
     *
     * @return string
     */
    public function getStripeRefreshTokenKeyName()
    {
        if ($this->getKeyName() === $this->getStripeAccountKeyName()) {
            return 'refresh_token';
        }

        return 'stripe_refresh_token';
    }

    /**
     * Get the user id that the account is associated to.
     *
     * @return mixed|null
     */
    public function getUserId()
    {
        return $this->{$this->getUserIdKeyName()};
    }

    /**
     * Get the user id column name.
     *
     * If this method returns null, the user will not be stored
     * when an access token is fetched.
     *
     * @return string|null
     */
    public function getUserIdKeyName()
    {
        return 'user_id';
    }

}
