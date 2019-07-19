<?php

namespace CloudCreativity\LaravelStripe\Jobs;

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
     * @param string $state
     * @param Authenticatable|null $user
     *      the signed in user when the code was acquired, if any.
     */
    public function __construct($code, $scope, $state, $user)
    {
        $this->code = $code;
        $this->scope = $scope;
        $this->state = $state;
        $this->user = $user;
    }
}
