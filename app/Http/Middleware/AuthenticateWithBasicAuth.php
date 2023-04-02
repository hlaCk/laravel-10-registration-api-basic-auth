<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 *
 */
class AuthenticateWithBasicAuth extends \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param string|null              $guard
     * @param string|null              $field
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function handle($request, Closure $next, $guard = null, $field = null)
    {
        $auth = $this->auth->guard($guard);
        $auth->basic($field ?: 'email', [ fn($q) => $q->whereNotNull('email_verified_at') ]);
        $user = $auth->user();

        if(
            $user &&
            (
                !$request->routeIs([ 'logout', 'login' ]) ||
                $user->hasSessionHash()
            ) &&
            !$user->hasValidSessionHash()
        ) {
            // $session = $user->session;
            $auth->logout();
            $user->setSessionHash();
            // if( $user->session = $session ) {
            //     $user->save();
            // }

            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.', headers: [ 'WWW-Authenticate' => 'Basic realm="REALM"' ]);
        }

        return $next($request);
    }

}
