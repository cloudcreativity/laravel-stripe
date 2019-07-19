<?php

namespace CloudCreativity\LaravelStripe\Connect;

use CloudCreativity\LaravelStripe\Contracts\Connect\StateProviderInterface;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

class SessionState implements StateProviderInterface
{

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Request
     */
    private $request;

    /**
     * SessionState constructor.
     *
     * @param Session $session
     * @param Request $request
     */
    public function __construct(Session $session, Request $request)
    {
        $this->session = $session;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        return $this->session->token();
    }

    /**
     * @inheritDoc
     */
    public function check($value)
    {
        return $this->get() === $value;
    }

    /**
     * @inheritDoc
     */
    public function user()
    {
        return $this->request->user();
    }

}
