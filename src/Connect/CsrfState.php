<?php

namespace CloudCreativity\LaravelStripe\Connect;

use CloudCreativity\LaravelStripe\Contracts\Connect\StateProviderInterface;
use Illuminate\Contracts\Session\Session;

class CsrfState implements StateProviderInterface
{

    /**
     * @var Session
     */
    private $session;

    /**
     * CsrfState constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
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

}
