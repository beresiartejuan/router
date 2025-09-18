<?php

require_once __DIR__ . '/../Router.php';

/**
 * RouterFactory - Factory for creating Router instances
 */
class RouterFactory
{
    /**
     * Create a new Router instance with default dependencies
     */
    public static function create()
    {
        return new Router();
    }

    /**
     * Create a Router instance for testing with custom dependencies
     */
    public static function createWithDependencies(
        RequestHandlerInterface $requestHandler = null,
        UriResolverInterface $uriResolver = null,
        RouteMatcherInterface $routeMatcher = null
    ) {
        return new Router($requestHandler, $uriResolver, $routeMatcher);
    }
}
