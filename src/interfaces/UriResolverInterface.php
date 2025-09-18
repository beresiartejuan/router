<?php

/**
 * Interface for resolving URIs
 */
interface UriResolverInterface
{
    public function getCurrentUri();
    public function getBasePath();
}
