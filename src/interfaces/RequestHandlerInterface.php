<?php

/**
 * Interface for handling HTTP requests
 */
interface RequestHandlerInterface
{
    public function getCurrentRequest();
    public function getRequestHeaders();
}
