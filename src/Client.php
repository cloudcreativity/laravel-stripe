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

namespace CloudCreativity\LaravelStripe;

use CloudCreativity\LaravelStripe\Events\ClientReceivedResult;
use CloudCreativity\LaravelStripe\Events\ClientWillSend;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class Client
{

    /**
     * @var Dispatcher
     */
    private $events;

    /**
     * Client constructor.
     *
     * @param Dispatcher $events
     */
    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * @param string $class
     * @param string $method
     * @param mixed ...$args
     * @return mixed
     */
    public function __invoke($class, $method, ...$args)
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException('Expecting a valid class name.');
        }

        $callable = "{$class}::{$method}";
        $name = Str::snake(class_basename($class));

        if (!is_callable($callable)) {
            throw new RuntimeException(sprintf('Cannot class %s method %s', $class, $method));
        }

        $this->events->dispatch(new ClientWillSend($name, $method, $args));

        $result = call_user_func_array($callable, $args);

        $this->events->dispatch(new ClientReceivedResult($name, $method, $args, $result));

        return $result;
    }

}
