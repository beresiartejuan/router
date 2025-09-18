<?php

/**
 * HTTP Request representation
 */
class Request
{
    private $method;
    private $originalMethod;
    private $uri;
    private $headers;

    public function __construct($method, $originalMethod, $uri, array $headers = [])
    {
        $this->method = $method;
        $this->originalMethod = $originalMethod;
        $this->uri = $uri;
        $this->headers = $headers;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getOriginalMethod()
    {
        return $this->originalMethod;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name)
    {
        return $this->headers[$name] ?? null;
    }
}
