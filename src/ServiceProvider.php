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

use CloudCreativity\LaravelStripe\Connect\Adapter;
use CloudCreativity\LaravelStripe\Contracts\Connect\AdapterInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\StateProviderInterface;
use CloudCreativity\LaravelStripe\Contracts\Webhooks\ProcessorInterface;
use CloudCreativity\LaravelStripe\Events\AccountDeauthorized;
use CloudCreativity\LaravelStripe\Events\ClientReceivedResult;
use CloudCreativity\LaravelStripe\Events\ClientWillSend;
use CloudCreativity\LaravelStripe\Events\OAuthSuccess;
use CloudCreativity\LaravelStripe\Listeners\DispatchAuthorizeJob;
use CloudCreativity\LaravelStripe\Listeners\DispatchWebhookJob;
use CloudCreativity\LaravelStripe\Listeners\RemoveAccountOnDeauthorize;
use CloudCreativity\LaravelStripe\Log\Logger;
use CloudCreativity\LaravelStripe\Webhooks\Processor;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Events\Dispatcher as Events;
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
     * @param Router $router
     * @param Events $events
     * @return void
     */
    public function boot(Router $router, Events $events)
    {
        Stripe::setApiKey(Config::apiKey());
        Stripe::setClientId(Config::clientId());

        if ($version = Config::apiVersion()) {
            Stripe::setApiVersion($version);
        }

        $this->bootLogging($events);
        $this->bootWebhooks($events);
        $this->bootConnect($events);

        /** Config */
        $this->publishes([
            __DIR__ . '/../config/stripe.php' => config_path('stripe.php'),
        ], 'stripe');

        /** Brand Assets */
        $this->publishes([
            __DIR__ . '/../resources' => public_path('vendor/stripe'),
        ], 'stripe-brand');

        /** Commands */
        $this->commands(Console\Commands\StripeQuery::class);

        /** Middleware */
        $router->aliasMiddleware('stripe.verify', Http\Middleware\VerifySignature::class);

        /** Migrations and Factories */
        $this->loadStripeMigrations();
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

        $this->app->alias(Logger::class, 'stripe.log');
    }

    /**
     * Bind the Stripe Connect implementation into the service container.
     *
     * @return void
     */
    private function bindConnect()
    {
        $this->app->singleton(AdapterInterface::class, function (Application $app) {
            return $app->make(LaravelStripe::$connect);
        });

        $this->app->alias(AdapterInterface::class, 'stripe.connect');

        $this->app->bind(Adapter::class, function () {
            return new Adapter(Config::connectModel());
        });

        $this->app->bind(StateProviderInterface::class, function (Application $app) {
            return $app->make(LaravelStripe::$oauthState);
        });
    }

    /**
     * Boot the Connect implementation.
     *
     * @param Events $events
     * @return void
     */
    private function bootConnect(Events $events)
    {
        $events->listen(OAuthSuccess::class, DispatchAuthorizeJob::class);
        $events->listen(AccountDeauthorized::class, RemoveAccountOnDeauthorize::class);
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
            return new Processor(
                $app->make(Dispatcher::class),
                $app->make(Events::class),
                $app->make('stripe.connect'),
                Config::webhookModel()
            );
        });
    }

    /**
     * Boot the webhook implementation.
     *
     * @param Events $events
     * @return void
     */
    private function bootWebhooks(Events $events)
    {
        $events->listen('stripe.webhooks', DispatchWebhookJob::class);
        $events->listen('stripe.connect.webhooks', DispatchWebhookJob::class);
    }

    /**
     * Boot the logging implementation.
     *
     * @param Events $events
     * @return void
     */
    private function bootLogging(Events $events)
    {
        Stripe::setLogger(app(LoggerInterface::class));

        $this->app->afterResolving(StripeService::class, function () {
            app(Logger::class)->log("Service booted.", [
                'api_key' => substr(Stripe::getApiKey(), 3, 4),
                'api_version' => Stripe::getApiVersion(),
            ]);
        });

        $events->listen(ClientWillSend::class, Listeners\LogClientRequests::class);
        $events->listen(ClientReceivedResult::class, Listeners\LogClientResults::class);
    }

    /**
     * Load or publish package migrations and factories.
     *
     * If this package is running migrations, we load them. Otherwise we
     * make them publishable so that the developer can publish them and modify
     * them as needed.
     *
     * @return void
     */
    private function loadStripeMigrations()
    {
        if (LaravelStripe::$runMigrations) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->app->afterResolving(ModelFactory::class, function (ModelFactory $factory) {
                $factory->load(__DIR__ . '/../database/factories');
            });
        } else {
            $this->publishes([
                __DIR__ . '/../database/factories' => database_path('factories'),
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'stripe-migrations');
        }
    }
}
