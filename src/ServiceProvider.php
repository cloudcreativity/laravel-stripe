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
use CloudCreativity\LaravelStripe\Connect\Adapter;
use CloudCreativity\LaravelStripe\Log\Logger;
use CloudCreativity\LaravelStripe\Webhooks\Processor;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factory as ModelFactory;
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
        ], 'stripe');

        $this->commands(Console\Commands\StripeQuery::class);

        $router->aliasMiddleware('stripe.verify', Http\Middleware\VerifySignature::class);

        /**
         * If this package is running migrations, we load them. Otherwise we
         * make them publishable so that the developer can publish them and modify
         * them as needed.
         */
        if (LaravelStripe::$runMigrations) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->app->afterResolving(ModelFactory::class, function (ModelFactory $factory) {
                $factory->load(__DIR__ . '/../database/factories');
            });
        } else {
            $this->publishes([
                __DIR__ . '/../database/factories' => database_path('factories'),
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'stripe.models');
        }
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

        /** Connect */
        $this->bindConnect();

        /** Webhooks */
        $this->bindWebhooks();

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
     * Bind the Stripe Connect implementation into the service container.
     *
     * @return void
     */
    private function bindConnect()
    {
        $this->app->singleton(AccountAdapterInterface::class, function (Application $app) {
            return $app->make(LaravelStripe::$accounts);
        });

        $this->app->alias(AccountAdapterInterface::class, 'stripe.connect');

        $this->app->bind(Adapter::class, function (Application $app) {
            $model = Config::connectModel();
            return new Adapter(new $model);
        });
    }

    /**
     * Bind the webhook implementation into the service container.
     *
     * @return void
     */
    private function bindWebhooks()
    {
        $this->app->singleton(ProcessorInterface::class, function (Application $app) {
            return $app->make(LaravelStripe::$webhooks);
        });

        $this->app->alias(ProcessorInterface::class, 'stripe.webhooks');

        $this->app->bind(Processor::class, function (Application $app) {
            $model = Config::webhookModel();
            return new Processor($app->make(Dispatcher::class), new $model);
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

        $events = app('events');
        $events->listen(Events\ClientWillSend::class, Listeners\LogClientRequests::class);
        $events->listen(Events\ClientReceivedResult::class, Listeners\LogClientResults::class);
    }
}
