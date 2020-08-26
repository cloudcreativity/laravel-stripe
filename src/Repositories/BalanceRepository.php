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

namespace CloudCreativity\LaravelStripe\Repositories;

use Stripe\Balance;

class BalanceRepository extends AbstractRepository
{
    /**
     * Retrieve the balance object of an account.
     *
     * @return Balance
     * @link https://stripe.com/docs/api/balance/balance_retrieve
     */
    public function retrieve()
    {
        return $this->send('retrieve', $this->options ?: null);
    }

    /**
     * @inheritDoc
     */
    protected function fqn()
    {
        return Balance::class;
    }


}
