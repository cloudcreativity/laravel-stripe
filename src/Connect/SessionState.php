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
