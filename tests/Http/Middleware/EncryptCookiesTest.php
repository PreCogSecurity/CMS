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

use GrahamCampbell\BootstrapCMS\Http\Middleware\EncryptCookies;
use GrahamCampbell\Tests\BootstrapCMS\AbstractTestCase;
use Illuminate\Encryption\Encrypter;

/**
 * This is the encrypt cookies middleware test class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class EncryptCookiesTest extends AbstractTestCase
{
    public function testXsrfTokenCookieIsNotEncrypted()
    {
        // javascript has to be able to read this one so that ajax requests
        // can echo it back, so it is the one cookie we leave in the clear
        $this->assertTrue($this->getMiddleware()->isDisabled('XSRF-TOKEN'));
    }

    public function testSessionCookieIsStillEncrypted()
    {
        $this->assertFalse($this->getMiddleware()->isDisabled('laravel_session'));
    }

    /**
     * Get the middleware under test.
     *
     * @return \GrahamCampbell\BootstrapCMS\Http\Middleware\EncryptCookies
     */
    protected function getMiddleware()
    {
        return new EncryptCookies(new Encrypter(str_repeat('a', 32), 'AES-256-CBC'));
    }
}
