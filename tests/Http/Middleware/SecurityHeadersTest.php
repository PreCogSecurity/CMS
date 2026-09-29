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

use GrahamCampbell\Tests\BootstrapCMS\AbstractTestCase;
use Illuminate\Contracts\Console\Kernel;

/**
 * This is the security headers middleware test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class SecurityHeadersTest extends AbstractTestCase
{
    /**
     * @before
     */
    public function runInstallCommand()
    {
        $this->app->make(Kernel::class)->call('app:install');
    }

    public function testHeadersAreSetOnFullResponses()
    {
        $this->get('pages/home');

        $this->assertResponseOk();

        $this->assertSame('nosniff', $this->response->headers->get('X-Content-Type-Options'));
        $this->assertSame('SAMEORIGIN', $this->response->headers->get('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $this->response->headers->get('Referrer-Policy'));
    }

    public function testHeadersAreSetOnRedirects()
    {
        $this->get('pages');

        $this->assertRedirectedTo('pages/home');

        $this->assertSame('nosniff', $this->response->headers->get('X-Content-Type-Options'));
        $this->assertSame('SAMEORIGIN', $this->response->headers->get('X-Frame-Options'));
    }
}
