<?php

namespace CloudCreativity\LaravelStripe\Exceptions;

class InvalidOAuthStateException extends UnexpectedValueException
{

    /**
     * @var string|null
     */
    private $expected;

    /**
     * @var string|null
     */
    private $actual;

    /**
     * InvalidOAuthStateException constructor.
     *
     * @param string|null $expected
     * @param string|null $actual
     */
    public function __construct($expected, $actual)
    {
        parent::__construct('Invalid OAuth state parameter.');
        $this->expected = $expected;
        $this->actual = $actual;
    }

    /**
     * Get the expected state value.
     *
     * @return string|null
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @return string|null
     */
    public function getActual()
    {
        return $this->actual;
    }
}
