<?php

require_once __DIR__ . '/../interfaces/RequestHandlerInterface.php';
require_once __DIR__ . '/Request.php';

/**
 * Default request handler implementation
 */
class RequestHandler implements RequestHandlerInterface
{
    public function getCurrentRequest()
    {
        $originalMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $method = $this->resolveMethod($originalMethod);
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $headers = $this->getRequestHeaders();

        return new Request($method, $originalMethod, $uri, $headers);
    }

    public function getRequestHeaders()
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers !== false) {
                return $headers;
            }
        }

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_' || in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerName = str_replace(
                    [' ', 'Http'], 
                    ['-', 'HTTP'], 
                    ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                );
                $headers[$headerName] = $value;
            }
        }

        return $headers;
    }

    private function resolveMethod($originalMethod)
    {
        if ($originalMethod === 'HEAD') {
            ob_start();
            return 'GET';
        }

        if ($originalMethod === 'POST') {
            $headers = $this->getRequestHeaders();
            $override = $headers['X-HTTP-Method-Override'] ?? null;
            
            if ($override && in_array($override, ['PUT', 'DELETE', 'PATCH'])) {
                return $override;
            }
        }

        return $originalMethod;
    }
}
