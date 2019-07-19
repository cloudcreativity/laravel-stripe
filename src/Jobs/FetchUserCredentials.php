<?php
/**
 * Copyright 2019 Cloud Creativity Limited
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

namespace CloudCreativity\LaravelStripe\Jobs;

use CloudCreativity\LaravelStripe\Connect\Authorizer;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;
use CloudCreativity\LaravelStripe\Contracts\Connect\AdapterInterface;
use CloudCreativity\LaravelStripe\Events\FetchedUserCredentials;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchUserCredentials implements ShouldQueue
{

    use InteractsWithQueue, SerializesModels, Queueable;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $scope;

    /**
     * @var AccountOwnerInterface|Model
     */
    public $owner;

    /**
     * FetchUserCredentials constructor.
     *
     * @param string $code
     * @param string $scope
     * @param AccountOwnerInterface $owner
     */
    public function __construct($code, $scope, AccountOwnerInterface $owner)
    {
        $this->code = $code;
        $this->scope = $scope;
        $this->owner = $owner;
    }

    /**
     * Execute the job.
     *
     * @param Authorizer $authorizer
     * @param AdapterInterface $adapter
     * @return void
     */
    public function handle(Authorizer $authorizer, AdapterInterface $adapter)
    {
        $token = $authorizer->authorize($this->code);

        $account = $adapter->store(
            $token['stripe_user_id'],
            $token['refresh_token'],
            $this->owner
        );

        event(new FetchedUserCredentials($account, $this->owner, $token));
    }
}
