<?php

namespace CloudCreativity\LaravelStripe\Tests\Integration;

use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\ServiceProvider;
use CloudCreativity\LaravelStripe\Tests\TestAccount;
use Illuminate\Database\Eloquent\Factory as ModelFactory;
use Illuminate\Foundation\Application;
use Laravel\Cashier\CashierServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        /** Setup the test database */
        $this->app['migrator']->path(__DIR__ . '/../../database/migrations');
        $this->app->make(ModelFactory::class)->load(__DIR__ . '/../../database/factories');
        $this->artisan('migrate', ['--database' => 'testbench']);
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
        $app['config']->set('stripe.connected_accounts.model', TestAccount::class);

        /** Setup a test database. */
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
