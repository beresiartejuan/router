<?php

/**
 * Route representation
 */
class Route
{
    private $pattern;
    private $callback;

    public function __construct($pattern, $callback)
    {
        $this->pattern = $pattern;
        $this->callback = $callback;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function getCallback()
    {
        return $this->callback;
    }
}
