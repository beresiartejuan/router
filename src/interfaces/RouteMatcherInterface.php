<?php

/**
 * Interface for matching routes
 */
interface RouteMatcherInterface
{
    public function matches(Route $route, $uri);
    public function extractParameters(Route $route, $uri);
}
