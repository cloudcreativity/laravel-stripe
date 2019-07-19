<?php

namespace CloudCreativity\LaravelStripe\Events;

use CloudCreativity\LaravelStripe\Connect\Authorizer;
use Illuminate\Contracts\Auth\Authenticatable;

class OAuthSuccess extends AbstractConnectEvent
{

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $scope;

    /**
     * OAuthSuccess constructor.
     *
     * @param string $code
     * @param string $scope
     * @param Authenticatable|null $user
     * @param string $view
     * @param array $data
     */
    public function __construct($code, $scope, $user, $view, $data = [])
    {
        parent::__construct($user, $view, $data);
        $this->code = $code;
        $this->scope = $scope;
    }

    /**
     * Is the scope read only?
     *
     * @return bool
     */
    public function readOnly()
    {
        return Authorizer::SCOPE_READ_ONLY === $this->scope;
    }

    /**
     * Is the scope read/write?
     *
     * @return bool
     */
    public function readWrite()
    {
        return Authorizer::SCOPE_READ_WRITE === $this->scope;
    }

    /**
     * @inheritDoc
     */
    protected function defaults()
    {
        return ['scope' => $this->scope];
    }

}
