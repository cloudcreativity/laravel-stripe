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

namespace CloudCreativity\LaravelStripe;

use Closure;

class LaravelStripe
{

    /**
     * Stripe on storage of their ids:
     *
     * You can safely assume object IDs we generate will never exceed 255 characters, but you
     * should be able to handle IDs of up to that length. If for example you’re using MySQL,
     * you should store IDs in a `VARCHAR(255) COLLATE utf8_bin` column
     * (the COLLATE configuration ensures case-sensitivity in lookups).
     *
     * @see https://stripe.com/docs/upgrades#what-changes-does-stripe-consider-to-be-backwards-compatible
     */
    const ID_DATABASE_COLLATION = 'utf8_bin';

    /**
     * @var bool
     */
    public static $runMigrations = true;

    /**
     * The class name of the Stripe Connect adapter.
     *
     * @var string
     */
    public static $connect = Connect\Adapter::class;

    /**
     * The class name of the OAuth state provider.
     *
     * @var string
     */
    public static $oauthState = Connect\SessionState::class;

    /**
     * The resolver for the Stripe account owner for the current request.
     *
     * @var \Closure|null
     */
    public static $currentOwnerResolver;

    /**
     * The class name of the webhook processor.
     *
     * @var string
     */
    public static $webhooks = Webhooks\Processor::class;

    /**
     * Do not run package migrations.
     *
     * If package migrations are not run, they will be publishable instead.
     *
     * @return LaravelStripe
     */
    public static function withoutMigrations()
    {
        self::$runMigrations = false;

        return new self();
    }

    /**
     * Set the fully-qualified class name of the Connect accounts adapter.
     *
     * @param string $fqn
     * @return LaravelStripe
     */
    public static function connect($fqn)
    {
        self::$connect = $fqn;

        return new self();
    }

    /**
     * Set the fully-qualified class name of the OAuth state parameter provider.
     *
     * @param string $fqn
     * @return LaravelStripe
     */
    public static function oauthState($fqn)
    {
        self::$oauthState = $fqn;

        return new self();
    }

    /**
     * Set the fully-qualified class name of the Webhook processor.
     *
     * @param string $fqn
     * @return LaravelStripe
     */
    public static function webhooks($fqn)
    {
        self::$webhooks = $fqn;

        return new self();
    }

    /**
     * Set the resolver for the Stripe owner of the current request.
     *
     * @param Closure|null $closure
     * @return LaravelStripe
     */
    public static function currentOwnerResolver(Closure $closure = null)
    {
        self::$currentOwnerResolver = $closure;

        return new self();
    }
}
