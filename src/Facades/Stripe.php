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

namespace CloudCreativity\LaravelStripe\Facades;

use CloudCreativity\LaravelStripe\Client;
use CloudCreativity\LaravelStripe\Connect\AuthorizeUrl;
use CloudCreativity\LaravelStripe\Connector;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Testing\ClientFake;
use CloudCreativity\LaravelStripe\Testing\StripeFake;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Facade;
use Stripe\StripeObject;

/**
 * Class Stripe
 *
 * @package CloudCreativity\LaravelStripe
 *
 * @method static Route webhook(string $uri, string $signingSecret)
 * @method static Route oauth(string $uri)
 * @method static Connector account()
 * @method static Connector connect(string $accountId)
 * @method static AccountInterface connectAccount(string $accountId)
 * @method static AuthorizeUrl authorizeUrl(array $options = null)
 * @method static void log(string $message, StripeObject|mixed $data, array $context = [])
 *
 * @method static void assertInvoked(string $class, string $method, \Closure $args = null)
 * @method static void assertInvokedAt(int $index, string $class, string $method, \Closure $args = null)
 */
class Stripe extends Facade
{

    /**
     * Fake static calls to Stripe.
     *
     * @param StripeObject ...$queue
     * @return void
     */
    public static function fake(StripeObject ...$queue)
    {
        /**
         * Swapping the client stubs static calls to Stripe. This allows the entire Laravel
         * Stripe package to operate, with only the static calls to the Stripe package being
         * removed.
         */
        static::$app->instance(
            Client::class,
            $client = new ClientFake(static::$app->make('events'))
        );

        /**
         * We then swap in a Stripe service fake, that has our test assertions on it.
         * This extends the real Stripe service and doesn't overload anything on it,
         * so the service will operate exactly as expected.
         */
        static::swap($fake = new StripeFake($client));

        $fake->withQueue(...$queue);
    }

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'stripe';
    }
}
