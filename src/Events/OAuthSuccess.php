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

namespace CloudCreativity\LaravelStripe\Events;

use CloudCreativity\LaravelStripe\Connect\Authorizer;
use CloudCreativity\LaravelStripe\Contracts\Connect\AccountOwnerInterface;

class OAuthSuccess extends AbstractOAuthEvent
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
     * @param AccountOwnerInterface $owner
     * @param string $view
     * @param array $data
     */
    public function __construct($code, $scope, $owner, $view, $data = [])
    {
        parent::__construct($owner, $view, $data);
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
