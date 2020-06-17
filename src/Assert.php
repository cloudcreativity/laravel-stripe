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

namespace CloudCreativity\LaravelStripe;

use CloudCreativity\LaravelStripe\Exceptions\UnexpectedValueException;
use Illuminate\Support\Str;

class Assert
{

    const ACCOUNT_ID_PREFIX = 'acct_';
    const CHARGE_ID_PREFIX = 'ch_';

    /**
     * @param string $expected
     *      the expected prefix.
     * @param $id
     *      the id.
     */
    public static function id($expected, $id)
    {
        if (!is_string($id)) {
            throw new UnexpectedValueException('Expecting a string id.');
        }

        if (!Str::startsWith($id, $expected)) {
            throw new UnexpectedValueException("Expecting a Stripe id with prefix '{$expected}', received '{$id}'.");
        }
    }

    /**
     * Assert that the currency is supported by the application.
     *
     * @param $currency
     * @return void
     */
    public static function supportedCurrency($currency)
    {
        if (!is_string($currency) || empty($currency)) {
            throw new UnexpectedValueException('Expecting a non-empty string.');
        }

        if (!Config::currencies()->containsStrict($currency)) {
            throw new UnexpectedValueException("Expecting a valid currency, received: {$currency}");
        }
    }

    /**
     * Assert that the currency and amount are chargeable.
     *
     * @param $currency
     * @param $amount
     * @return void
     * @see https://stripe.com/docs/currencies#minimum-and-maximum-charge-amounts
     */
    public static function chargeAmount($currency, $amount)
    {
        self::supportedCurrency($currency);

        if (!is_int($amount)) {
            throw new UnexpectedValueException('Expecting an integer.');
        }

        $minimum = Config::minimum($currency);

        if ($minimum > $amount) {
            throw new UnexpectedValueException("Expecting a charge amount that is greater than {$minimum}.");
        }
    }

    /**
     * Assert that the value is a zero-decimal amount.
     *
     * @param $amount
     * @return void
     * @see https://stripe.com/docs/currencies#zero-decimal
     */
    public static function zeroDecimal($amount)
    {
        if (!is_int($amount) || 0 > $amount) {
            throw new UnexpectedValueException('Expecting a positive integer.');
        }
    }
}
