<?php

namespace CloudCreativity\LaravelStripe;

class LaravelStripe
{

    /**
     * Stripe on storage of their ids:
     *
     * You can safely assume object IDs we generate will never exceed 255 characters, but you
     * should be able to handle IDs of up to that length. If for example you’re using MySQL,
     * you should store IDs in a `VARCHAR(255) COLLATE utf8_bin` column
     * (the COLLATE configuration ensures case-sensitivity in lookups).
     *
     * @see https://stripe.com/docs/upgrades#what-changes-does-stripe-consider-to-be-backwards-compatible
     */
    const ID_DATABASE_COLLATION = 'utf8_bin';

    /**
     * @var bool
     */
    public static $runMigrations = true;

    /**
     * The class name of the Stripe Connect adapter.
     *
     * @var string
     */
    public static $connect = Connect\Adapter::class;

    /**
     * The class name of the webhook processor.
     *
     * @var string
     */
    public static $webhooks = Webhooks\Processor::class;

    /**
     * Do not run package migrations.
     *
     * If package migrations are not run, they will be publishable instead.
     *
     * @return LaravelStripe
     */
    public static function withoutMigrations()
    {
        self::$runMigrations = false;

        return new self();
    }

    /**
     * Set the fully-qualified class name of the Connect accounts adapter.
     *
     * @param string $fqn
     * @return LaravelStripe
     */
    public static function connect($fqn)
    {
        self::$connect = $fqn;

        return new self();
    }

    /**
     * Set the fully-qualified class name of the Webhook processor.
     *
     * @param string $fqn
     * @return LaravelStripe
     */
    public static function webhooks($fqn)
    {
        self::$webhooks = $fqn;

        return new self();
    }
}
