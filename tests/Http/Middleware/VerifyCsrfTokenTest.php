<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\Tests\BootstrapCMS\Http\Middleware;

use GrahamCampbell\BootstrapCMS\Http\Middleware\VerifyCsrfToken;
use GrahamCampbell\Tests\BootstrapCMS\AbstractTestCase;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\Store;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

/**
 * This is the verify csrf token middleware test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class VerifyCsrfTokenTest extends AbstractTestCase
{
    /**
     * The token that our fake sessions are carrying.
     *
     * @var string
     */
    const TOKEN = 'a-very-secret-token';

    public function testMiddlewareIsRegisteredGlobally()
    {
        $kernel = $this->app->make(Kernel::class);

        $this->assertTrue($kernel->hasMiddleware(VerifyCsrfToken::class));
    }

    public function testReadRequestIsAllowed()
    {
        $middleware = $this->getMiddleware();

        $response = $middleware->handle($this->getRequest('GET'), $this->getHandler());

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testStateChangingRequestWithoutTokenIsRejected()
    {
        $middleware = $this->getMiddleware();

        $this->setExpectedException('Illuminate\Session\TokenMismatchException');

        $middleware->handle($this->getRequest('POST'), $this->getHandler());
    }

    public function testStateChangingRequestWithWrongTokenIsRejected()
    {
        $middleware = $this->getMiddleware();

        $this->setExpectedException('Illuminate\Session\TokenMismatchException');

        $middleware->handle($this->getRequest('POST', ['_token' => 'not-the-token']), $this->getHandler());
    }

    public function testStateChangingRequestWithTokenIsAllowed()
    {
        $middleware = $this->getMiddleware();

        $request = $this->getRequest('POST', ['_token' => self::TOKEN]);

        $response = $middleware->handle($request, $this->getHandler());

        $this->assertInstanceOf(Response::class, $response);

        $cookies = $response->headers->getCookies();

        $this->assertCount(1, $cookies);
        $this->assertSame('XSRF-TOKEN', $cookies[0]->getName());
        $this->assertSame(self::TOKEN, $cookies[0]->getValue());
    }

    public function testStateChangingRequestWithHeaderTokenIsAllowed()
    {
        $middleware = $this->getMiddleware();

        $request = $this->getRequest('POST', [], ['HTTP_X_CSRF_TOKEN' => self::TOKEN]);

        $this->assertInstanceOf(Response::class, $middleware->handle($request, $this->getHandler()));
    }

    public function testTokenIsNotRequiredWhileTesting()
    {
        $middleware = $this->getMiddleware('testing');

        $response = $middleware->handle($this->getRequest('POST'), $this->getHandler());

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Get the middleware under test.
     *
     * @param string $environment
     *
     * @return \GrahamCampbell\BootstrapCMS\Http\Middleware\VerifyCsrfToken
     */
    protected function getMiddleware($environment = 'production')
    {
        // the middleware reads the environment through the application, and we
        // resolve it out of the container so that the wiring is under test too
        $this->app->instance('env', $environment);

        return $this->app->make(VerifyCsrfToken::class);
    }

    /**
     * Get a request that is carrying a session with a known token.
     *
     * @param string $method
     * @param array  $parameters
     * @param array  $server
     *
     * @return \Illuminate\Http\Request
     */
    protected function getRequest($method, array $parameters = [], array $server = [])
    {
        $session = new Store('laravel_session', new NullSessionHandler(), 'session-id');
        $session->put('_token', self::TOKEN);

        $request = Request::create('/', $method, $parameters, [], [], $server);
        $request->setSession($session);

        return $request;
    }

    /**
     * Get a stand in for the rest of the middleware stack.
     *
     * @return \Closure
     */
    protected function getHandler()
    {
        return function () {
            return new Response('ok', 200);
        };
    }
}
