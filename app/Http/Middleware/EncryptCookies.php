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

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncrypter;

/**
 * This is the encrypt cookies middleware class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class EncryptCookies extends BaseEncrypter
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * The xsrf token cookie is deliberately left in the clear: javascript has
     * to be able to read it so that our ajax calls can echo it back in the
     * X-CSRF-TOKEN header, which is where laravel 5.1 looks for it. The token
     * is a per session nonce, and the session cookie itself stays encrypted
     * and http only, so this does not weaken the session itself.
     *
     * @var string[]
     */
    protected $except = ['XSRF-TOKEN'];
}
