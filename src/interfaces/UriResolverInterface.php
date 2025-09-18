<?php

/**
 * Interface for resolving URIs
 */
interface UriResolverInterface
{
    public function getCurrentUri(): string;
    public function getBasePath(): string;
}
