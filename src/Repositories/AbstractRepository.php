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

namespace CloudCreativity\LaravelStripe\Repositories;

use CloudCreativity\LaravelStripe\Client;
use InvalidArgumentException;

abstract class AbstractRepository
{

    const PARAM_EXPAND = 'expand';
    const PARAM_ID = 'id';
    const PARAM_METADATA = 'metadata';
    const OPT_IDEMPOTENCY_KEY = 'idempotency_key';
    const OPT_STRIPE_ACCOUNT = 'stripe_account';

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Client
     */
    private $client;

    /**
     * Get the fully qualified class name that the client handles.
     *
     * @return string
     */
    abstract protected function fqn();

    /**
     * AbstractRepository constructor.
     *
     * @param Client $client
     * @param string|null $accountId
     */
    public function __construct(Client $client, $accountId = null)
    {
        $this->client = $client;
        $this->params = [];
        $this->options = [];

        if ($accountId) {
            $this->option(self::OPT_STRIPE_ACCOUNT, $accountId);
        }
    }

    /**
     * Get the account id.
     *
     * @return string|null
     */
    public function accountId()
    {
        return isset($this->options[self::OPT_STRIPE_ACCOUNT]) ?
            $this->options[self::OPT_STRIPE_ACCOUNT] : null;
    }

    /**
     * Make the next request idempotent.
     *
     * @param string $value
     * @return $this
     */
    public function idempotent($value)
    {
        if (!is_string($value) || empty($value)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        $this->option(self::OPT_IDEMPOTENCY_KEY, $value);

        return $this;
    }

    /**
     * Set a parameter.
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function param($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Set many parameters.
     *
     * @param iterable $values
     * @return $this
     */
    public function params($values)
    {
        foreach ($values as $key => $value) {
            $this->param($key, $value);
        }

        return $this;
    }

    /**
     * Set an option.
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function option($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set many options.
     *
     * @param iterable $values
     * @return $this
     */
    public function options($values)
    {
        foreach ($values as $key => $value) {
            $this->option($key, $value);
        }

        return $this;
    }

    /**
     * Set keys to expand.
     *
     * @param string ...$keys
     * @return $this
     */
    public function expand(...$keys)
    {
        $this->param(self::PARAM_EXPAND, $keys ?: null);

        return $this;
    }

    /**
     * Call the static Stripe method with the provided arguments.
     *
     * We call everything via this method so that:
     *
     * - The static call can be stubbed out in tests.
     * - Events are dispatched.
     *
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    protected function send($method, ...$args)
    {
        $result = call_user_func(
            $this->client,
            $this->fqn(),
            $method,
            ...$args
        );

        $this->reset();

        return $result;
    }

    /**
     * @return void
     */
    protected function reset()
    {
        $this->params = [];
        $this->options = collect($this->options)->only(self::OPT_STRIPE_ACCOUNT)->all();
    }

}
