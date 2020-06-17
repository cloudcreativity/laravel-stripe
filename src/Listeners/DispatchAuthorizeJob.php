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
            $event->owner
        );

        $job->onQueue($config['queue'])->onConnection($config['connection']);

        dispatch($job);
    }
}
