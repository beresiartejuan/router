<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/libs/UriResolver.php';

class UriResolverTest extends TestCase
{
    private $uriResolver;

    protected function setUp(): void
    {
        $this->uriResolver = new UriResolver();
        
        // Mock server environment
        $_SERVER['REQUEST_URI'] = '/test/path';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    public function testCanCreateUriResolver()
    {
        $this->assertInstanceOf(UriResolver::class, $this->uriResolver);
    }

    public function testGetCurrentUriBasic()
    {
        $_SERVER['REQUEST_URI'] = '/test';
        
        $uri = $this->uriResolver->getCurrentUri();
        $this->assertEquals('/test', $uri);
    }

    public function testGetCurrentUriWithQueryParameters()
    {
        $_SERVER['REQUEST_URI'] = '/test?param=value&other=123';
        
        $uri = $this->uriResolver->getCurrentUri();
        $this->assertEquals('/test', $uri);
    }

    public function testGetCurrentUriRoot()
    {
        $_SERVER['REQUEST_URI'] = '/';
        
        $uri = $this->uriResolver->getCurrentUri();
        $this->assertEquals('/', $uri);
    }

    public function testGetCurrentUriWithTrailingSlash()
    {
        $_SERVER['REQUEST_URI'] = '/test/';
        
        $uri = $this->uriResolver->getCurrentUri();
        $this->assertEquals('/test', $uri);
    }

    public function testGetCurrentUriWithMultipleSlashes()
    {
        $_SERVER['REQUEST_URI'] = '//test//path//';
        
        $uri = $this->uriResolver->getCurrentUri();
        $this->assertEquals('/test/path', $uri);
    }

    public function testGetCurrentUriWithEncodedCharacters()
    {
        $_SERVER['REQUEST_URI'] = '/test%20path/with%2Bencoded';
        
        $uri = $this->uriResolver->getCurrentUri();
        $this->assertEquals('/test path/with+encoded', $uri);
    }

    public function testGetBasePath()
    {
        $_SERVER['SCRIPT_NAME'] = '/app/public/index.php';
        
        $basePath = $this->uriResolver->getBasePath();
        $this->assertEquals('/app/public/', $basePath);
    }

    public function testSetBasePath()
    {
        $customBasePath = '/custom/path/';
        $this->uriResolver->setBasePath($customBasePath);
        
        $this->assertEquals($customBasePath, $this->uriResolver->getBasePath());
    }

    public function testGetCurrentUriWithCustomBasePath()
    {
        $_SERVER['REQUEST_URI'] = '/app/public/test/route';
        $this->uriResolver->setBasePath('/app/public/');
        
        $uri = $this->uriResolver->getCurrentUri();
        $this->assertEquals('/test/route', $uri);
    }

    public function testEmptyRequestUri()
    {
        $_SERVER['REQUEST_URI'] = '';
        
        $uri = $this->uriResolver->getCurrentUri();
        $this->assertEquals('/', $uri);
    }
}
