<?php

namespace CloudCreativity\LaravelStripe\Jobs;

use Illuminate\Bus\Queueable;
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
     * The signed in user when the code was acquired, if any.
     *
     * @var mixed|null
     */
    public $user;

    /**
     * FetchUserCredentials constructor.
     *
     * @param string $code
     * @param string $scope
     * @param mixed|null $user
     *      the signed in user when the code was acquired, if any.
     */
    public function __construct($code, $scope, $user)
    {
        $this->code = $code;
        $this->scope = $scope;
        $this->user = $user;
    }
}
