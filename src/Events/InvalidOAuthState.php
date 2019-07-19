<?php

namespace CloudCreativity\LaravelStripe\Events;

class InvalidOAuthState extends AbstractConnectEvent
{

    /**
     * @inheritDoc
     */
    protected function defaults()
    {
        return ['code' => null, 'message' => 'Unexpected authorization credentials.'];
    }

}
