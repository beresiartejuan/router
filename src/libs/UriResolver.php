<?php

require_once __DIR__ . '/../interfaces/UriResolverInterface.php';

/**
 * Default URI resolver implementation
 */
class UriResolver implements UriResolverInterface
{
    private $basePath;

    public function getCurrentUri()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = substr(rawurldecode($requestUri), strlen($this->getBasePath()));

        // Remove query parameters
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return '/' . trim($uri, '/');
    }

    public function getBasePath()
    {
        if ($this->basePath === null) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $pathParts = explode('/', $scriptName);
            array_pop($pathParts); // Remove script filename
            $this->basePath = implode('/', $pathParts) . '/';
        }

        return $this->basePath;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }
}
