<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 *
 */

/**
 *
 */
class FixMiddleware extends \Illuminate\Auth\Middleware\Authenticate
{
    /**
     * The guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'application/json');

        if( empty($guards) ) {
            $guards = [ null ];
        }

        foreach( $guards as $guard ) {
            if( $this->auth->guard($guard)->check() ) {
                $this->auth->shouldUse($guard);
                $user = $this->auth->guard($guard)->user();
                if(
                    ( !$user && ($request->getUser() && $request->getPassword())) ||
                    ($user && !$request->routeIs('logout') && !$user->hasValidSessionHash() )
                ) {
                    throw new UnauthorizedHttpException('Basic');
                }

                return $next($request);
            }
        }

        return $next($request);
    }
}
