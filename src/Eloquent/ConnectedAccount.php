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

namespace CloudCreativity\LaravelStripe\Eloquent;

use CloudCreativity\LaravelStripe\Connector;

trait ConnectedAccount
{

    /**
     * @return Connector
     */
    public function stripe()
    {
        return app('stripe')->connectedAccount(
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

}
