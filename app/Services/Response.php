<?php

namespace App\Services;

/**
 *
 */
class Response
{
    /**
     * @param       $code
     * @param       $data
     * @param int   $status
     * @param array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function make($code, $data = [], int $status = 200, array $headers = [])
    {
        return response(trim($code ?? 0),$status,$headers);
        // return response()->json([
        //                             ...array_wrap($data),
        //                             'code' => $code ?? 0,
        //                         ], $status, [
        //                             'Content-Type' => 'application/json',
        //                             ...$headers,
        //                         ]);
    }

    /**
     * @param $data
     * @param $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = [],$code = 1)
    {
        return static::make($code,$data,200);
    }

    /**
     * @param $data
     * @param $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($data = [], $code = 0)
    {
        return static::make($code,$data,401);
    }
}
