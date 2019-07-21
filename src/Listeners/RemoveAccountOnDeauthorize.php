<?php

namespace CloudCreativity\LaravelStripe\Listeners;

use CloudCreativity\LaravelStripe\Contracts\Connect\AdapterInterface;
use CloudCreativity\LaravelStripe\Events\AccountDeauthorized;

class RemoveAccountOnDeauthorize
{

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * RemoveAccountOnDeauthorize constructor.
     *
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Handle the event.
     *
     * @param AccountDeauthorized $event
     * @return void
     */
    public function handle(AccountDeauthorized $event)
    {
        $this->adapter->remove($event->account);
    }
}
