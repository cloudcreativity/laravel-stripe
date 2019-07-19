<?php

namespace CloudCreativity\LaravelStripe\Contracts\Connect;

interface StateProviderInterface
{

    /**
     * Get the state value.
     *
     * @return string
     */
    public function get();

    /**
     * Is the provided state valid?
     *
     * @param string $value
     * @return bool
     */
    public function check($value);
}
