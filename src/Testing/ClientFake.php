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

namespace CloudCreativity\LaravelStripe\Testing;

use ArrayIterator;
use CloudCreativity\LaravelStripe\Client;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use IteratorAggregate;
use LogicException;
use Stripe\StripeObject;

class ClientFake extends Client implements IteratorAggregate
{

    /**
     * @var Collection
     */
    private $queue;

    /**
     * @var Collection
     */
    private $history;

    /**
     * @var int
     */
    private $counter;

    /**
     * ClientFake constructor.
     *
     * @param Dispatcher $events
     */
    public function __construct(Dispatcher $events)
    {
        parent::__construct($events);
        $this->queue = collect();
        $this->history = collect();
        $this->counter = 0;
    }

    /**
     * Queue results.
     *
     * @param StripeObject ...$results
     * @return void
     */
    public function queue(StripeObject ...$results)
    {
        $this->queue = $this->queue->merge($results);
    }

    /**
     * Get the call history index.
     *
     * @return int
     */
    public function index()
    {
        return $this->counter;
    }

    /**
     * Get the current index, then increment it.
     *
     * @return int
     */
    public function increment()
    {
        $index = $this->index();

        ++$this->counter;

        return $index;
    }

    /**
     * @param int $index
     * @return array|null
     */
    public function at($index)
    {
        return $this->history->get($index);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->history->getIterator();
    }

    /**
     * @param $class
     * @param $method
     * @param array $args
     * @return mixed
     */
    protected function execute($class, $method, array $args)
    {
        if (!$result = $this->queue->shift()) {
            throw new LogicException("Unexpected Stripe call: {$class}::{$method}");
        }

        $this->history->push([
            'class' => $class,
            'method' => $method,
            'args' => $args,
            'result' => $result
        ]);

        return $result;
    }
}
