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

use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Stripe\Webhook;

class Config
{

    /**
     * Get the Stripe API Key.
     *
     * The default Laravel installation has this in the `services.stripe` config,
     * so we expect it to be there.
     *
     * @return string|null
     */
    public static function apiKey()
    {
        return config('services.stripe.secret') ?: null;
    }

    /**
     * @return string|null
     */
    public static function apiVersion()
    {
        return self::get('api_version') ?: null;
    }

    /**
     * Get the class for the connected account adapter.
     *
     * @return string
     */
    public static function connectedAccountAdapter()
    {
        return self::get('connected_accounts.adapter');
    }

    /**
     * Get the class for the connected account model.
     *
     * @return string
     */
    public static function connectedAccountModel()
    {
        $fqn = self::get('connected_accounts.model') ?: null;

        if (!class_exists($fqn)) {
            throw new RuntimeException("Connected account class {$fqn} does not exist.");
        }

        return $fqn;
    }

    /**
     * Get the class for dispatching webhooks.
     *
     * A null value disables webhook dispatching.
     *
     * @return string
     */
    public static function webhookProcessor()
    {
        $fqn = self::get('webhooks.processor');

        if (!class_exists($fqn)) {
            throw new RuntimeException("Webhook dispatcher class {$fqn} does not exist.");
        }

        return $fqn;
    }

    /**
     * Get a webhook signing secret by key.
     *
     * @param string $name
     * @return string
     */
    public static function webhookSigningSecrect($name)
    {
        if (!$secret = self::get("webhooks.signing_secrets.{$name}")) {
            throw new RuntimeException("Webhook signing secret does not exist: {$name}");
        }

        if (!is_string($secret)|| empty($secret)) {
            throw new RuntimeException("Invalid webhook signing secret: {$name}");
        }

        return $secret;
    }

    /**
     * Get the webhook signature tolerance.
     *
     * @return int
     */
    public static function webhookTolerance()
    {
        return self::get('webhooks.signature_tolerance', Webhook::DEFAULT_TOLERANCE);
    }

    /**
     * Get the queue config for the named webhook event.
     *
     * @param string $eventName
     * @return array
     */
    public static function webhookQueueDetails($eventName)
    {
        if (self::has($path = "webhooks.queues.{$eventName}")) {
            return array_replace(['connection' => null, 'queue' => null], (array) self::get($path));
        }

        return [
            'connection' => self::get('webhooks.queues.default_queue_connection'),
            'queue' => self::get('webhooks.queues.default_queue'),
        ];
    }

    /**
     * Get the currencies the application supports.
     *
     * We lowercase the currencies because Stripe returns currencies from the API
     * in lowercase.
     *
     * @return Collection
     */
    public static function currencies()
    {
        $currencies = collect(self::get('currencies'))->map(function ($currency) {
            return strtolower($currency);
        });

        if ($currencies->isEmpty()) {
            throw new RuntimeException('Expecting to support at least one currency.');
        }

        return $currencies;
    }

    /**
     * Get the minimum charge amount for the specified currency.
     *
     * @param string $currency
     * @return int
     */
    public static function minimum($currency)
    {
        if (!is_string($currency) || empty($currency)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        $currency = strtoupper($currency);
        $min = collect(self::get('minimum_charge_amounts'))->get($currency);

        if (!is_int($min) || 1 > $min) {
            throw new RuntimeException("Invalid minimum {$currency} charge amount for currency.");
        }

        return $min;
    }

    /**
     * @return string
     */
    public static function logLevel()
    {
        return self::get('log.level');
    }

    /**
     * Get the exclusion paths for logging a particular type.
     *
     * @return array
     */
    public static function logExclude()
    {
        return (array) self::get("log.exclude");
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    private static function get($key, $default = null)
    {
        return config("stripe.{$key}", $default);
    }

    /**
     * @param $key
     * @return bool
     */
    private static function has($key)
    {
        return config()->has("stripe.{$key}");
    }
}
