<?php

/**
 * Route representation
 */
class Route
{
    public function __construct(
        private readonly string $pattern,
        private readonly mixed $callback
    ) {
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getCallback(): mixed
    {
        return $this->callback;
    }
}
