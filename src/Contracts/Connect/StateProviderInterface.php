<?php

namespace CloudCreativity\LaravelStripe\Contracts\Connect;

use Illuminate\Contracts\Auth\Authenticatable;

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

    /**
     * Get the user associated with the state, if there is one.
     *
     * @return Authenticatable|mixed|null
     */
    public function user();
}
