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
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Stripe\Charge;
use Stripe\Refund;

class RefundRepository extends AbstractRepository
{

    use Concerns\All;
    use Concerns\Retrieve;
    use Concerns\HasMetadata;

    /**
     * Create a full refund.
     *
     * @param Charge|string $charge
     * @param iterable $params
     * @return Refund
     */
    public function full($charge, iterable $params = []): Refund
    {
        if (isset($params['amount'])) {
            throw new InvalidArgumentException('Not expecting an amount for a full refund.');
        }

        return $this->create($charge, $params);
    }

    /**
     * Create a partial refund.
     *
     * @param Charge|string $charge
     * @param int $amount
     * @param iterable|array $params
     * @return Refund
     */
    public function partial($charge, int $amount, iterable $params = []): Refund
    {
        Assert::zeroDecimal($amount);

        $params['amount'] = $amount;

        return $this->create($charge, $params);
    }

    /**
     * Create a refund.
     *
     * @param Charge|string $charge
     * @param iterable|array $params
     * @return Refund
     */
    public function create($charge, iterable $params = []): Refund
    {
        if ($charge instanceof Charge) {
            $charge = $charge->id;
        } else {
            Assert::id(Assert::CHARGE_ID_PREFIX, $charge);
        }

        $this->params($params)->param('charge', $charge);

        return $this->send(
            'create',
            $this->params ?: null,
            $this->options ?: null
        );
    }

    /**
     * Update a refund.
     *
     * This request only accepts the `metadata` as an argument.
     *
     * @param string $id
     * @param Collection|iterable|array $metadata
     * @return Refund
     */
    public function update(string $id, iterable $metadata): Refund
    {
        $this->metadata($metadata);

        return $this->send(
            'update',
            $id,
            $this->params ?: null,
            $this->options ?: null
        );
    }

    /**
     * @inheritDoc
     */
    protected function fqn(): string
    {
        return Refund::class;
    }

}
