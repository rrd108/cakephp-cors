<?php
declare(strict_types=1);

namespace Rrd108\Cors\Tests\TestCase\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rrd108\Cors\Routing\Middleware\CorsMiddleware;
use TypeError;

class CorsMiddlewareTest extends TestCase
{
    private $server = [];

    public const BASE_ORIGIN = 'http://test.com';

    public function setUp(): void
    {
        parent::setUp();
        $this->server = [
            'REQUEST_URI' => '/test',
            'HTTP_ORIGIN' => 'http://test.com',
            'REQUEST_METHOD' => 'OPTIONS',
        ];
    }

    private function _setServer(array $server)
    {
        $this->server = array_merge($this->server, $server);
    }

    private function _sendRequest()
    {
        $request = ServerRequestFactory::fromGlobals($this->server);
        $handler = new class implements RequestHandlerInterface
        {
            public $callable;

            public function __construct(?callable $callable = null)
            {
                $this->callable = $callable ?: function ($request) {
                    return new Response();
                };
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return ($this->callable)($request);
            }
        };
        $middleware = new CorsMiddleware();
        $response = $middleware->process($request, $handler);

        return $response;
    }

    public function testNoExposeAllCorsHeadersIfNotAOptionsRequest()
    {
        $this->_setServer(['REQUEST_METHOD' => 'GET']);
        $response = $this->_sendRequest();
        $headersKeys = array_keys($response->getHeaders());

        $this->assertContains('Access-Control-Allow-Origin', $headersKeys);
        $this->assertContains('Access-Control-Allow-Credentials', $headersKeys);
        $this->assertContains('Access-Control-Max-Age', $headersKeys);
        $this->assertNotContains('Access-Control-Allow-Headers', $headersKeys);
        $this->assertNotContains('Access-Control-Allow-Methods', $headersKeys);
        $this->assertNotContains('Access-Control-Expose-Headers', $headersKeys);
    }

    public function testDefaultValuesIfNotAOptionsRequest()
    {
        $this->_setServer(['REQUEST_METHOD' => 'GET']);
        $response = $this->_sendRequest();
        $headers = $response->getHeaders();

        $this->assertEquals(self::BASE_ORIGIN, current($headers['Access-Control-Allow-Origin']));
        $this->assertEquals('true', current($headers['Access-Control-Allow-Credentials']));
        $this->assertEquals(Configure::read('Cors.MaxAge'), current($headers['Access-Control-Max-Age']));
    }

    public function testExposeAllCorsHeadersIfIsAOptionsRequest()
    {
        $response = $this->_sendRequest();
        $headersKeys = array_keys($response->getHeaders());

        $this->assertContains('Access-Control-Allow-Origin', $headersKeys);
        $this->assertContains('Access-Control-Allow-Credentials', $headersKeys);
        $this->assertContains('Access-Control-Max-Age', $headersKeys);
        $this->assertContains('Access-Control-Allow-Headers', $headersKeys);
        $this->assertContains('Access-Control-Allow-Methods', $headersKeys);
        $this->assertContains('Access-Control-Expose-Headers', $headersKeys);
    }

    public function testDefaultValuesIfIsAOptionsRequest()
    {
        $response = $this->_sendRequest();
        $headers = $response->getHeaders();

        $this->assertEquals('', current($headers['Access-Control-Allow-Headers']));
        $this->assertEquals('GET, POST, PUT, PATCH, DELETE, OPTIONS', current($headers['Access-Control-Allow-Methods']));
        $this->assertEquals('', current($headers['Access-Control-Expose-Headers']));
    }

    private function _sendRequestForOriginTest($originUrl, $allowUrl)
    {
        Configure::write('Cors.AllowOrigin', $allowUrl);
        $this->_setServer(['HTTP_ORIGIN' => $originUrl]);

        return $this->_sendRequest()->getHeaderLine('Access-Control-Allow-Origin');
    }

    public function testOriginDifferentAllKey()
    {
        $responseAllowOrigin = $this->_sendRequestForOriginTest(self::BASE_ORIGIN, '*');
        $this->assertEquals(self::BASE_ORIGIN, $responseAllowOrigin);

        $responseAllowOrigin = $this->_sendRequestForOriginTest('https://google.com', '*');
        $this->assertEquals('https://google.com', $responseAllowOrigin);

        $responseAllowOrigin = $this->_sendRequestForOriginTest('https://google.com', true);
        $this->assertEquals('https://google.com', $responseAllowOrigin);
    }

    public function testOriginIsForbidden()
    {
        $responseAllowOrigin = $this->_sendRequestForOriginTest('https://google.com', false);
        $this->assertEquals('', $responseAllowOrigin);
    }

    public function testOriginStringIsOk()
    {
        $responseAllowOrigin = $this->_sendRequestForOriginTest('https://google.com', 'https://google.com');
        $this->assertEquals('https://google.com', $responseAllowOrigin);
    }

    public function testOriginStringIsForbidden()
    {
        $responseAllowOrigin = $this->_sendRequestForOriginTest('https://google.com', 'https://bing.com');
        $this->assertEquals('https://bing.com', $responseAllowOrigin);
    }

    public function testOriginArrayIsOk()
    {
        $responseAllowOrigin = $this->_sendRequestForOriginTest('https://google.com', ['https://bing.com', 'https://google.com']);
        $this->assertEquals('https://google.com', $responseAllowOrigin);
    }

    public function testOriginArrayIsForbidden()
    {
        $responseAllowOrigin = $this->_sendRequestForOriginTest('https://duckduckgo.com', ['https://bing.com', 'https://google.com']);
        $this->assertEquals('', $responseAllowOrigin);
    }

    public function testCredentialsTrue()
    {
        Configure::write('Cors.AllowCredentials', true);
        $responseAllowCredentials = $this->_sendRequest()->getHeaderLine('Access-Control-Allow-Credentials');
        $this->assertEquals('true', $responseAllowCredentials);
    }

    public function testCredentialsFalse()
    {
        Configure::write('Cors.AllowCredentials', false);
        $responseAllowCredentials = $this->_sendRequest()->getHeaderLine('Access-Control-Allow-Credentials');
        $this->assertEquals('false', $responseAllowCredentials);
    }

    public function testMethodString()
    {
        Configure::write('Cors.AllowMethods', 'GET');
        $this->expectException(TypeError::class);
        $this->_sendRequest()->getHeaderLine('Access-Control-Allow-Methods');
    }

    public function testMethodArray()
    {
        Configure::write('Cors.AllowMethods', ['GET', 'POST']);
        $responseAllowMethods = $this->_sendRequest()->getHeaderLine('Access-Control-Allow-Methods');
        $this->assertEquals('GET, POST', $responseAllowMethods);
    }

    public function testAllowHeadersString()
    {
        Configure::write('Cors.AllowHeaders', 'authorization');
        $responseRequestHeaders = $this->_sendRequest()->getHeaderLine('Access-Control-Allow-Headers');
        $this->assertEquals('authorization', $responseRequestHeaders);
    }

    public function testAllowHeadersArray()
    {
        Configure::write('Cors.AllowHeaders', ['authorization', 'Content-Type']);
        $responseRequestHeaders = $this->_sendRequest()->getHeaderLine('Access-Control-Allow-Headers');
        $this->assertEquals('authorization, Content-Type', $responseRequestHeaders);
    }

    public function testAllowHeadersAllReturnSendedHeaders()
    {
        Configure::write('Cors.AllowHeaders', true);
        $this->_setServer(['HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'authorization']);
        $responseRequestHeaders = $this->_sendRequest()->getHeaderLine('Access-Control-Allow-Headers');
        $this->assertEquals('authorization', $responseRequestHeaders);
    }

    public function testExposeHeadersString()
    {
        Configure::write('Cors.ExposeHeaders', 'X-My-Custom-Header');
        $responseExposeHeaders = $this->_sendRequest()->getHeaderLine('Access-Control-Expose-Headers');
        $this->assertEquals('X-My-Custom-Header', $responseExposeHeaders);
    }

    public function testExposeHeadersArray()
    {
        Configure::write('Cors.ExposeHeaders', ['X-My-Custom-Header', 'X-Another-Custom-Header']);
        $responseExposeHeaders = $this->_sendRequest()->getHeaderLine('Access-Control-Expose-Headers');
        $this->assertEquals('X-My-Custom-Header, X-Another-Custom-Header', $responseExposeHeaders);
    }

    public function testExposeHeadersFalse()
    {
        Configure::write('Cors.ExposeHeaders', false);
        $responseExposeHeaders = $this->_sendRequest()->getHeaderLine('Access-Control-Expose-Headers');
        $this->assertEquals('', $responseExposeHeaders);
    }

    public function testMaxAge1Hour()
    {
        Configure::write('Cors.MaxAge', 3600);
        $responseMaxAge = $this->_sendRequest()->getHeaderLine('Access-Control-Max-Age');
        $this->assertEquals(3600, $responseMaxAge);
    }

    public function testMaxAgeFalse()
    {
        Configure::write('Cors.MaxAge', false);
        $responseMaxAge = $this->_sendRequest()->getHeaderLine('Access-Control-Max-Age');
        $this->assertEquals(0, $responseMaxAge);
    }
}
