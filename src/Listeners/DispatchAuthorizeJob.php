<?php

namespace CloudCreativity\LaravelStripe\Listeners;

use CloudCreativity\LaravelStripe\Config;
use CloudCreativity\LaravelStripe\Events\OAuthSuccess;
use CloudCreativity\LaravelStripe\Jobs\FetchUserCredentials;

class DispatchAuthorizeJob
{

    /**
     * Handle the event.
     *
     * @param OAuthSuccess $event
     * @return void
     */
    public function handle(OAuthSuccess $event)
    {
        $config = Config::connectQueue();

        $job = new FetchUserCredentials(
            $event->code,
            $event->scope,
            $event->user
        );

        $job->onQueue($config['queue'])->onConnection($config['connection']);

        dispatch($job);
    }
}
