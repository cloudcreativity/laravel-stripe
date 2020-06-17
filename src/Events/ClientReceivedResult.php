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

use Illuminate\Contracts\Support\Arrayable;
use Stripe\StripeObject;

class ClientReceivedResult implements Arrayable
{

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $args;

    /**
     * @var StripeObject
     */
    public $result;

    /**
     * ClientReceivedResult constructor.
     *
     * @param string $name
     * @param string $method
     * @param array $args
     * @param StripeObject $result
     */
    public function __construct($name, $method, array $args, StripeObject $result)
    {
        $this->name = $name;
        $this->method = $method;
        $this->args = $args;
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'method' => $this->method,
            'args' => $this->args,
            'result' => $this->result,
        ];
    }
}
