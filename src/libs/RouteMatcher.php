<?php

require_once __DIR__ . '/../interfaces/RouteMatcherInterface.php';
require_once __DIR__ . '/Route.php';

/**
 * Default route matcher implementation
 */
class RouteMatcher implements RouteMatcherInterface
{
    public function matches(Route $route, string $uri): bool
    {
        $pattern = $this->convertToRegex($route->getPattern());
        return (bool) preg_match('#^' . $pattern . '$#', $uri);
    }

    public function extractParameters(Route $route, string $uri): array
    {
        $pattern = $this->convertToRegex($route->getPattern());
        
        if (!preg_match_all('#^' . $pattern . '$#', $uri, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        // Remove the full match, keep only captured groups
        $matches = array_slice($matches, 1);

        return array_map(function ($match, $index) use ($matches) {
            // Handle multiple parameters properly
            if (isset($matches[$index + 1]) && isset($matches[$index + 1][0]) && is_array($matches[$index + 1][0])) {
                if ($matches[$index + 1][0][1] > -1) {
                    return trim(substr($match[0][0], 0, $matches[$index + 1][0][1] - $match[0][1]), '/');
                }
            }

            return isset($match[0][0]) && $match[0][1] != -1 ? trim($match[0][0], '/') : null;
        }, $matches, array_keys($matches));
    }

    private function convertToRegex(string $pattern): string
    {
        // Convert Laravel-style {param} to regex capture groups
        // Use [^/]+ to match one or more characters that are not slashes
        return preg_replace('/\/{([^}]+)}/', '/([^/]+)', $pattern);
    }
}
