<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/libs/Request.php';

class RequestTest extends TestCase
{
    public function testCanCreateRequest()
    {
        $request = new Request('GET', 'GET', '/test');

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testCanGetMethod()
    {
        $request = new Request('GET', 'GET', '/test');

        $this->assertEquals('GET', $request->getMethod());
    }

    public function testCanGetOriginalMethod()
    {
        $request = new Request('GET', 'HEAD', '/test');

        $this->assertEquals('HEAD', $request->getOriginalMethod());
    }

    public function testCanGetUri()
    {
        $uri = '/test/path';
        $request = new Request('GET', 'GET', $uri);

        $this->assertEquals($uri, $request->getUri());
    }

    public function testCanGetHeaders()
    {
        $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
        $request = new Request('GET', 'GET', '/test', $headers);

        $this->assertEquals($headers, $request->getHeaders());
    }

    public function testCanGetSpecificHeader()
    {
        $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
        $request = new Request('GET', 'GET', '/test', $headers);

        $this->assertEquals('application/json', $request->getHeader('Content-Type'));
        $this->assertEquals('application/json', $request->getHeader('Accept'));
    }

    public function testReturnsNullForNonExistentHeader()
    {
        $request = new Request('GET', 'GET', '/test');

        $this->assertNull($request->getHeader('Non-Existent'));
    }

    public function testCanCreateRequestWithoutHeaders()
    {
        $request = new Request('POST', 'POST', '/api/test');

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('POST', $request->getOriginalMethod());
        $this->assertEquals('/api/test', $request->getUri());
        $this->assertEquals([], $request->getHeaders());
    }
}
