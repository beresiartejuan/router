<?php

/**
 * Interface for handling HTTP requests
 */
interface RequestHandlerInterface
{
    public function getCurrentRequest(): Request;
    public function getRequestHeaders(): array;
}
