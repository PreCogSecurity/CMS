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

use Closure;

/**
 * This is the security headers middleware class.
 *
 * These are pure defence in depth headers: none of them change the behaviour
 * of the application, they only stop a browser from doing something dangerous
 * with a response it should not have been trusted to do anything with.
 *
 * A content security policy is deliberately not set here because pages, posts
 * and comments are allowed to carry their own css and js by design. See
 * SECURITY.md for how to lock that down if you do not need it.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class SecurityHeaders
{
    /**
     * The security headers to attach to every response.
     *
     * @var string[]
     */
    protected $headers = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        foreach ($this->headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
