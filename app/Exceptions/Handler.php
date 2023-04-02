<?php

namespace App\Exceptions;

use App\Services\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // $this->reportable(function(Throwable $e) {
        //    dd($e);
        // });
        $this->renderable(function(\Illuminate\Validation\ValidationException $e) {
            return \response(trim(0),$e->getCode() ?: 401, method_exists($e, 'getHeaders') ? $e->getHeaders() : []);
        });
        $this->renderable(function(\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $e) {
            return \response(trim(0),$e->getStatusCode() ?: 401, method_exists($e, 'getHeaders') ? $e->getHeaders() : []);
        });
        $this->renderable(function(Throwable $e) {
            // if( request()->has('s-r') )
            // {
            //     return ;
            // }

            return \response(trim(0),401, headers: method_exists($e, 'getHeaders') ? $e->getHeaders() : []);

            if( ( !($e instanceof AuthenticationException) && !request()->routeIs([ 'login' ])) ) {
                return Response::make(0, [ 'message' => $e->getMessage() ], headers: method_exists($e, 'getHeaders') ? $e->getHeaders() : []);
            }
        });
    }
}
