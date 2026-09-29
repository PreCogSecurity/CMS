<?php

/*
 * This file is part of Bootstrap CMS.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\BootstrapCMS\Http\Middleware;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

/**
 * This is the verify csrf token middleware class.
 *
 * Every state changing request has to carry the session token, either as a
 * _token field or as an X-CSRF-TOKEN header for our ajax calls. Without this,
 * any third party site could make a logged in editor create, rewrite or delete
 * content, and any logged in user could post comments on their behalf.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * The application is injected so that the automated test suite can drive
     * the http kernel without having to scrape a token out of every form, in
     * exactly the same way that laravel itself does in later versions.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Contracts\Encryption\Encrypter    $encrypter
     *
     * @return void
     */
    public function __construct(Application $app, Encrypter $encrypter)
    {
        $this->app = $app;

        parent::__construct($encrypter);
    }

    /**
     * Determine if the request should pass through csrf verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        return $this->runningUnitTests() || parent::shouldPassThrough($request);
    }

    /**
     * Determine if the application is currently running the test suite.
     *
     * This is the same check that laravel performs internally, expressed
     * against the application contract so that the middleware can be built by
     * the container without depending on the concrete application class.
     *
     * @return bool
     */
    protected function runningUnitTests()
    {
        return $this->app->environment() == 'testing';
    }
}
