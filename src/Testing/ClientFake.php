<?php

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
