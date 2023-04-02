<?php

use App\Http\Controllers\AuthController;
use App\Services\Response;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::prefix('mobile')
     ->group(function(): void {
         Route::middleware([ 'guest', 'web' ])
              ->group(function(): void {
                  Route::any('register', [ AuthController::class, 'register' ])->name('register');
              });

         Route::middleware([ 'auth.basic' ])
              ->group(function(): void {
                  Route::middleware('guest')
                       ->get('logout', [ AuthController::class, 'logout' ])->name('logout');

                  Route::get('login', [ AuthController::class, 'login' ])->name('login');

                  Route::get('user', [ AuthController::class, 'user' ])->name('user');

              });

         Route::get('email/verify/{id}/{hash}', [ AuthController::class, 'verify' ])->middleware([ 'throttle:6,1','signed' ])->name('verification.verify');
     });


Route::fallback(function() {
    return Response::success([ 'message' => config('app.name') ]);
});
