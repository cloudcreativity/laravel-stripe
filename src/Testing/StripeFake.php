<?php

namespace CloudCreativity\LaravelStripe\Testing;

use CloudCreativity\LaravelStripe\Contracts\Connect\AccountAdapterInterface;
use CloudCreativity\LaravelStripe\Contracts\Webhooks\DispatcherInterface;
use CloudCreativity\LaravelStripe\StripeService;
use CloudCreativity\LaravelStripe\Testing\Concerns\MakesStripeAssertions;

class StripeFake extends StripeService
{

    use MakesStripeAssertions;

    /**
     * StripeFake constructor.
     *
     * @param AccountAdapterInterface $accounts
     * @param DispatcherInterface $webhooks
     * @param ClientFake $client
     */
    public function __construct(
        AccountAdapterInterface $accounts,
        DispatcherInterface $webhooks,
        ClientFake $client
    ) {
        parent::__construct($accounts, $webhooks);
        $this->stripeClient = $client;
    }
}
