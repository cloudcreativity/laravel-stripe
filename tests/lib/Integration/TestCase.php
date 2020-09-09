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

namespace CloudCreativity\LaravelStripe\Tests\Integration;

use Carbon\Carbon;
use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\ServiceProvider;
use Illuminate\Database\Eloquent\Factory as ModelFactory;
use Illuminate\Foundation\Application;
use Laravel\Cashier\CashierServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use function collect;

abstract class TestCase extends BaseTestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('now');

        /** Setup the test database */
        $this->app['migrator']->path(__DIR__ . '/../../database/migrations');
        $this->app->make(ModelFactory::class)->load(__DIR__ . '/../../database/factories');

        if (method_exists($this, 'withoutMockingConsoleOutput')) {
            $this->withoutMockingConsoleOutput();
        }

        $this->app['view']->addNamespace('test', __DIR__ . '/../../resources/views');

        $this->artisan('migrate', ['--database' => 'testbench']);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    /**
     * Provider for all Stripe classes that are implemented via repositories.
     *
     * Balances are omitted because they do not have an id.
     *
     * @return array
     */
    public function classProvider(): array
    {
        return [
            'accounts' => [\Stripe\Account::class, 'accounts'],
            'charges' => [\Stripe\Charge::class, 'charges'],
            'events' => [\Stripe\Event::class, 'events'],
            'payment_intents' => [\Stripe\PaymentIntent::class, 'payment_intents'],
        ];
    }

    /**
     * Get package providers.
     *
     * To ensure this package works with Cashier, we also include
     * Cashier.
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            CashierServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    /**
     * Get facade aliases.
     *
     * @param Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Stripe' => Stripe::class,
        ];
    }

    /**
     * Setup the test environment.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        /** Include our default config. */
        $app['config']->set('stripe', require __DIR__ . '/../../../config/stripe.php');

        /** Override views to use our test namespace */
        $app['config']->set('stripe.connect.views', [
            'success' => 'test::oauth.success',
            'error' => 'test::oauth.error',
        ]);

        /** Setup a test database. */
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Load a stub.
     *
     * @param string $name
     * @return array
     */
    protected function stub($name)
    {
        return json_decode(
            file_get_contents(__DIR__ . '/../../stubs/' . $name . '.json'),
            true
        );
    }
}
