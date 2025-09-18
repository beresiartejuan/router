<?php

/**
 * HTTP Request representation
 */
class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $originalMethod,
        private readonly string $uri,
        private readonly array $headers = []
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getOriginalMethod(): string
    {
        return $this->originalMethod;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }
}
