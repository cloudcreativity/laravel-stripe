<?php

namespace CloudCreativity\LaravelStripe\Http\Controllers;

use CloudCreativity\LaravelStripe\Connect\Authorizer;
use CloudCreativity\LaravelStripe\Http\Requests\AuthorizeConnect;
use CloudCreativity\LaravelStripe\Log\Logger;
use Illuminate\Routing\Controller;

class OAuthController extends Controller
{

    /**
     * @var Logger
     */
    private $log;

    /**
     * OAuthController constructor.
     *
     * @param Logger $log
     */
    public function __construct(Logger $log)
    {
        $this->log = $log;
    }

    public function __invoke(AuthorizeConnect $request, Authorizer $authorizer)
    {
        
    }
}
