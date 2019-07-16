<?php

namespace CloudCreativity\LaravelStripe\Tests\Integration;

use CloudCreativity\LaravelStripe\Facades\Stripe;
use CloudCreativity\LaravelStripe\ServiceProvider;
use CloudCreativity\LaravelStripe\Tests\TestAccount;
use Illuminate\Database\Eloquent\Factory as ModelFactory;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->counter = 0;

        /** Setup the test database */
        $this->app['migrator']->path(__DIR__ . '/../../database/migrations');
        $this->app->make(ModelFactory::class)->load(__DIR__ . '/../../database/factories');

        if (method_exists($this, 'withoutMockingConsoleOutput')) {
            $this->withoutMockingConsoleOutput();
        }

        $this->artisan('migrate', ['--database' => 'testbench']);

        /** Fake Stripe so that we do not make any static calls to it. */
        Stripe::fake();
    }

    /**
     * Provider for all Stripe classes that are implemented via repositories.
     *
     * To support Laravel 5.4, we have to use an old version of the Stripe PHP
     * library. We therefore filter out any classes that do not exist.
     *
     * @todo filtering needs to be removed once we drop Laravel 5.4. The version
     * constraint for the stripe/stripe-php should always be set to support all
     * classes that are available on the latest version.
     *
     * @return array
     * @todo remove filtering once we are only on Stripe ^6.0.
     */
    public function classProvider()
    {
        return collect([
            'accounts' => [\Stripe\Account::class, 'accounts'],
            'charges' => [\Stripe\Charge::class, 'charges'],
            'events' => [\Stripe\Event::class, 'events'],
            'payment_intents' => [\Stripe\PaymentIntent::class, 'payment_intents'],
        ])->filter(function (array $values) {
            return class_exists($values[0]);
        })->all();
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
