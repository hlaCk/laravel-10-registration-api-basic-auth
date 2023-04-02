<?php

namespace App\Services\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Timebox;

/**
 *
 */
class CustomGuard extends SessionGuard implements Guard
{
    /**
     * Create a new authentication guard.
     *
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param \Illuminate\Http\Request                $request
     *
     * @return void
     */
    public function __construct(
        $guard_name,
        UserProvider $provider,
        Session $session,
        Request $request = null,
        Timebox $timebox = null
    ) {
        parent::__construct($guard_name, $provider, $session, $request, $timebox);
    }

    /**
     * Attempt to authenticate using basic authentication.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $field
     * @param array                                     $extraConditions
     *
     * @return bool
     */
    protected function attemptBasic(\Symfony\Component\HttpFoundation\Request $request, $field, $extraConditions = [])
    {
        if( !$request->getUser() ) {
            return false;
        }

        return $this->attempt(
            array_merge(
                $this->basicCredentials($request, $field),
                $extraConditions
            )
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse|void
     */
    protected function failedBasicResponse()
    {
        return $this->userResponse(0);
    }

    /**
     * @param     $code
     * @param     $data
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userResponse($code, $data = [], int $status = 200) {
        return response()->json([
                                    ...array_wrap($data),
                                    'code' => $code ?? 0,
                                ], $status, [
                                    'Content-Type' => 'application/json',
                                ]);
    }

    /**
     * Attempt to authenticate using HTTP Basic Auth.
     *
     * @param string $field
     * @param array  $extraConditions
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function basic($field = 'email', $extraConditions = [])
    {
        if( $this->check() ) {
            return $this->userResponse(1,['id' => $this->user->id]);
        }

        // If a username is set on the HTTP basic request, we will return out without
        // interrupting the request lifecycle. Otherwise, we'll need to generate a
        // request indicating that the given credentials were invalid for login.
        if( $this->attemptBasic($this->getRequest(), $field, $extraConditions) ) {
            return $this->userResponse(1,['id' => $this->user->id]);
        }

        return $this->failedBasicResponse();
    }
}
