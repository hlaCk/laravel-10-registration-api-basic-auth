<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 *
 */
class UserLogoutListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        // if( !$event->user?->hasValidSessionHash() && $event->user?->hasSessionHash() )
        if( request()->routeIs('logout') )
        {
            $event->user?->clearSessionHash();
            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.', headers: [ 'WWW-Authenticate' => 'Basic realm="REALM"' ],code: 401);
        }

    }
}
