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

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountAdapterInterface;
use CloudCreativity\LaravelStripe\Contracts\Webhooks\ProcessorInterface;
use CloudCreativity\LaravelStripe\Eloquent\Adapter;
use CloudCreativity\LaravelStripe\Log\Logger;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Stripe\Stripe;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Boot services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        Stripe::setApiKey(Config::apiKey());

        if ($version = Config::apiVersion()) {
            Stripe::setApiVersion($version);
        }

        if (Config::logLevel()) {
            $this->enableLogging();
        }

        $this->publishes([
            __DIR__ . '/../config/stripe.php' => config_path('stripe.php'),
        ]);

        $this->commands(Console\Commands\StripeQuery::class);

        $router->aliasMiddleware('stripe.verify-webhook', Http\Middleware\VerifySignature::class);
    }

    /**
     * Bind services into the service container.
     *
     * @return void
     */
    public function register()
    {
        /** Service */
        $this->app->singleton(StripeService::class);
        $this->app->alias(StripeService::class, 'stripe');

        /** Connected Account Adapter */
        $this->app->singleton(AccountAdapterInterface::class, function (Application $app) {
            return $app->make(Config::connectedAccountAdapter());
        });

        /** Eloquent Adapter */
        $this->app->bind(Adapter::class, function (Application $app) {
            $model = Config::connectedAccountModel();
            return new Adapter(new $model);
        });

        /** Client */
        $this->app->bind(Client::class);

        /** Webhooks */
        $this->app->singleton(ProcessorInterface::class, function (Application $app) {
            return $app->make(Config::webhookProcessor());
        });

        /** Logger */
        $this->app->singleton(Logger::class, function (Application $app) {
            $level = Config::logLevel();

            return new Logger(
                $level ? $app->make(LoggerInterface::class) : new NullLogger(),
                $level,
                Config::logExclude()
            );
        });
    }

    /**
     * Boot logging implementation.
     *
     * @return void
     */
    private function enableLogging()
    {
        Stripe::setLogger(app(LoggerInterface::class));

        $this->app->afterResolving(StripeService::class, function () {
            logger()->log(Config::logLevel(), "Stripe: service booted.", [
                'api_key' => substr(Stripe::getApiKey(), 3, 4),
                'api_version' => Stripe::getApiVersion(),
            ]);
        });

        /** @var Dispatcher $events */
        $events = app('events');
        $events->listen(Events\ClientWillSend::class, Listeners\LogClientRequests::class);
        $events->listen(Events\ClientReceivedResult::class, Listeners\LogClientResults::class);
    }
}
