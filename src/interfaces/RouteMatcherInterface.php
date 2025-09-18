<?php

/**
 * Interface for matching routes
 */
interface RouteMatcherInterface
{
    public function matches(Route $route, string $uri): bool;
    public function extractParameters(Route $route, string $uri): array;
}
