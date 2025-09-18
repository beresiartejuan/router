<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/libs/RequestHandler.php';

class RequestHandlerTest extends TestCase
{
    private $requestHandler;

    protected function setUp(): void
    {
        $this->requestHandler = new RequestHandler();
        
        // Set up mock server environment
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_HOST'] = 'localhost';
    }

    public function testCanCreateRequestHandler()
    {
        $this->assertInstanceOf(RequestHandler::class, $this->requestHandler);
    }

    public function testGetCurrentRequestReturnsRequest()
    {
        $request = $this->requestHandler->getCurrentRequest();
        
        $this->assertInstanceOf(Request::class, $request);
        $this->assertEquals('GET', $request->getMethod());
    }

    public function testHandlesHeadRequestAsGet()
    {
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        
        $request = $this->requestHandler->getCurrentRequest();
        
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('HEAD', $request->getOriginalMethod());
    }

    public function testHandlesPostRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $request = $this->requestHandler->getCurrentRequest();
        
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('POST', $request->getOriginalMethod());
    }

    public function testHandlesCustomHeaders()
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        
        $request = $this->requestHandler->getCurrentRequest();
        $headers = $request->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('Accept', $headers);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('application/json', $headers['Accept']);
    }
}
