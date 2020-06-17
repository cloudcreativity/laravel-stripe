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

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;
use CloudCreativity\LaravelStripe\Models\StripeAccount;
use CloudCreativity\LaravelStripe\Models\StripeEvent;
use Illuminate\Database\Eloquent\Model;
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
     * Get the Stripe API version used by this application.
     *
     * @return string|null
     */
    public static function apiVersion()
    {
        return self::get('api_version') ?: null;
    }

    /**
     * Get the Stripe Connect client id.
     *
     * @return string|null
     */
    public static function clientId()
    {
        return self::get('client_id') ?: null;
    }

    /**
     * Get the Connect account model.
     *
     * @return StripeAccount|mixed
     */
    public static function connectModel()
    {
        $class = self::fqn('connect.model');

        return new $class;
    }

    /**
     * Get the Connect account owner model.
     *
     * @return AccountOwnerInterface|Model
     */
    public static function connectOwner()
    {
        $class = self::fqn('connect.owner');

        return new $class;
    }

    /**
     * Get the Connect queue config.
     *
     * @return array
     */
    public static function connectQueue()
    {
        return [
            'connection' => self::get('connect.queue_connection'),
            'queue' => self::get('connect.queue'),
        ];
    }

    /**
     * Get the view to render on OAuth success.
     *
     * @return string
     */
    public static function connectSuccessView()
    {
        return self::get('connect.views.success');
    }

    /**
     * @return string
     */
    public static function connectErrorView()
    {
        return self::get('connect.views.error');
    }

    /**
     * @return StripeEvent|mixed
     */
    public static function webhookModel()
    {
        $class = self::fqn('webhooks.model');

        return new $class;
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
     * @param string $type
     * @param bool $connect
     * @return array
     */
    public static function webhookQueue($type, $connect = false)
    {
        $path = sprintf(
            'webhooks.%s.%s',
            $connect ? 'connect' : 'account',
            str_replace('.', '_', $type)
        );

        return array_replace([
            'connection' => self::get('webhooks.default_queue_connection'),
            'queue' => self::get('webhooks.default_queue'),
            'job' => null,
        ], (array) self::get($path));
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
     * @param string $key
     * @return string
     */
    private static function fqn($key)
    {
        $fqn = self::get($key) ?: null;

        if (!class_exists($fqn)) {
            throw new RuntimeException("Configured class at 'stripe.{$key}' does not exist: {$fqn}");
        }

        return $fqn;
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
