<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

declare(strict_types=1);

namespace CloudCreativity\LaravelStripe\Repositories;

use CloudCreativity\LaravelStripe\Assert;
use Stripe\PaymentIntent;

class PaymentIntentRepository extends AbstractRepository
{

    use Concerns\All;
    use Concerns\Retrieve;
    use Concerns\Update;

    /**
     * Create a payment intent.
     *
     * Both currency and amount are required parameters.
     *
     * @param string $currency
     * @param int $amount
     * @param iterable|array $params
     *      additional optional parameters.
     * @return PaymentIntent
     */
    public function create(string $currency, int $amount, iterable $params = [])
    {
        Assert::chargeAmount($currency, $amount);

        $this->params($params)->params(
            compact('currency', 'amount')
        );

        return $this->send('create', $this->params, $this->options);
    }

    /**
     * @inheritDoc
     */
    protected function fqn(): string
    {
        return PaymentIntent::class;
    }

}
