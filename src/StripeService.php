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

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountInterface;
use CloudCreativity\LaravelStripe\Exceptions\AccountNotConnectedException;
use CloudCreativity\LaravelStripe\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

class StripeService
{

    /**
     * Register a webhook endpoint.
     *
     * @param string $uri
     * @param string $signingSecret
     *      the key of the signing secret in the `stripe.webhooks.signing_secrets` config.
     * @return \Illuminate\Routing\Route
     */
    public function webhook($uri, $signingSecret)
    {
        return Route::post($uri, '\\' . WebhookController::class)->middleware(
            "stripe.verify:{$signingSecret}"
        );
    }

    /**
     * Access the main application account.
     *
     * @return Connector
     */
    public function account()
    {
        return new Connector();
    }

    /**
     * Access a connected account.
     *
     * This method is a short-hand alias for `connectedAccount()`.
     *
     * @param AccountInterface|string $accountId
     * @return Connector
     * @throws AccountNotConnectedException
     */
    public function connect($accountId)
    {
        if ($accountId instanceof AccountInterface) {
            return new Connector($accountId);
        }

        if ($account = $this->connectAccount($accountId)) {
            return new Connector($account);
        }

        throw new AccountNotConnectedException($accountId);
    }

    /**
     * Get a Stripe Connect account by id.
     *
     * @param $accountId
     * @return AccountInterface|null
     */
    public function connectAccount($accountId)
    {
        Assert::id(Assert::ACCOUNT_ID_PREFIX, $accountId);

        return app('stripe.connect')->find($accountId);
    }

    /**
     * Log a Stripe object, sanitising any sensitive data.
     *
     * @param string $message
     * @param mixed $data
     * @param array $context
     */
    public function log($message, $data, array $context = [])
    {
        app('stripe.log')->encode($message, $data, $context);
    }

}
