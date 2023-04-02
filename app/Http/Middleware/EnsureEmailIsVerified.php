<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 *
 */
class EnsureEmailIsVerified extends \Illuminate\Auth\Middleware\EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if ( $request->user() &&
            ($request->user() instanceof MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {

            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.', headers: [ 'WWW-Authenticate' => 'Basic realm="REALM"' ]);
        }

        return $next($request);
    }
}
