<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [ null ] : $guards;

        foreach( $guards as $guard ) {
            if( ($g = Auth::guard($guard))->check() ) {
                if( !$request->routeIs('login') ) {
                    $g->user()->clearSessionHash();
                }

                if( !$request->routeIs('logout') ) {
                    session()->invalidate();
                    session()->regenerate(true);
                    $request->session()->regenerate(true);
                }
            }
        }

        return $next($request);
    }
}
