<?php

namespace CloudCreativity\LaravelStripe\Jobs;

use CloudCreativity\LaravelStripe\Connect\Authorizer;
use CloudCreativity\LaravelStripe\Contracts\Connect\AdapterInterface;
use CloudCreativity\LaravelStripe\Events\FetchedUserCredentials;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
     * @var string
     */
    public $state;

    /**
     * The signed in user when the code was acquired, if any.
     *
     * @var Authenticatable|null
     */
    public $user;

    /**
     * FetchUserCredentials constructor.
     *
     * @param string $code
     * @param string $scope
     * @param Authenticatable|mixed|null $user
     *      the signed in user when the code was acquired, if any.
     */
    public function __construct($code, $scope, $user)
    {
        $this->code = $code;
        $this->scope = $scope;
        $this->user = $user;
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
            $this->user
        );

        event(new FetchedUserCredentials($account, $token, $this->user));
    }
}
