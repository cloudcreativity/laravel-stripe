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

declare(strict_types=1);

namespace CloudCreativity\LaravelStripe\Repositories;

use CloudCreativity\LaravelStripe\Assert;
use Stripe\InvoiceItem;

class InvoiceItemRepository extends AbstractRepository
{

    use Concerns\All;
    use Concerns\Retrieve;
    use Concerns\Update;

    /**
     * Create an invoice item.
     *
     * Only customer is a required parameter,
     * currency and amount should be sent as parameter data.
     *
     * @param string $customer
     * @param iterable|array $params
     *      additional optional parameters.
     * @return InvoiceItem
     */
    public function create(string $customer, iterable $params = []): InvoiceItem
    {
        if(isset($params['currency']) && isset($params['amount'])) {
            Assert::chargeAmount($params['currency'], $params['amount']);
        }

        $this->params($params)->params(
            compact('customer')
        );

        return $this->send(
            'create',
            $this->params ?: null,
            $this->options ?: null
        );
    }

    /**
     * @inheritDoc
     */
    protected function fqn(): string
    {
        return InvoiceItem::class;
    }
}
