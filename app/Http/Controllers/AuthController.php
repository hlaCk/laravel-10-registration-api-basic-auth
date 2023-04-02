<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 *
 */
class AuthController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->merge($_REQUEST);

        User::query()
            // ->whereNotNull('created_at')
            ->where('created_at', "<", Carbon::now()->subMinutes(Config::get('auth.verification.expire', 60)))
            ->where(fn($q)=>$q->whereNull('email_verified_at')->orWhereNull('email2_verified_at'))
            ->delete();

        $data = $request->validate([
                                       'email' => [
                                           'required',
                                           'string',
                                           'email:fqn',
                                           Rule::unique('users', 'email')
                                               ->where(fn($q) => $q->where('created_at', "<", Carbon::now()->subMinutes(Config::get('auth.verification.expire', 60)))),
                                       ],
                                       'email2' => [ 'nullable', 'string', 'email:fqn' ],
                                       'password' => [ 'required', 'string' ],
                                   ]);

        $data[ 'password' ] = bcrypt($data[ 'password' ]);

        User::query()->where(fn($q)=>$q->where('email', $data[ 'email' ])->orWhere('email2', $data[ 'email2' ]))
                     ->where(fn($q)=>$q->whereNull('email_verified_at')->orWhereNull('email2_verified_at'))
                     ->delete();

        /** @var User $user */
        $user = User::create($data);
        $user->sendEmailVerificationNotification();

        return Response::success([ 'id' => $user->id ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return Response::success($request->user());
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->clearSessionHash();
        Auth::logout();
        Auth::guard()->logout();
        Auth::forgetGuards();

        session()->invalidate();
        session()->regenerate(true);
        $request->session()->regenerate(true);
        if( Auth::guard()->check() ) {
            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.', headers: [ 'WWW-Authenticate' => 'Basic realm="REALM"' ]);
        }

        return \App\Services\Response::make(1, status: 401, headers: [ 'WWW-Authenticate' => 'Basic realm="REALM"' ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        // dd($user,$user->hasSessionHash());
        $user?->setSessionHash();

        return Response::make(
            1,
            $user?->only('id','app_token')
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(\App\Http\Requests\EmailVerificationRequest $request)
    {
        if( !$request->user() ) {
            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.');
        }

        if( $request->user()
            ->created_at
            ->addMinutes(Config::get('auth.verification.expire', 60))
            ->isPast()
        ) {
            $this->logout($request);

            throw new UnauthorizedHttpException('Basic', 'Invalid credentials.');
        }

        $request->fulfill();

        return 'Success!';
    }
}
