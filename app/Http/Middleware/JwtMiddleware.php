<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $allowedPaths=[
                "api/user/signup",
                "api/user/login",
                "api/user/send-otp",
                "api/user/verify-otp",
                "api/admin/total-users"
            ];
            if(!in_array($request->path(),$allowedPaths))
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['message' => 'Unauthorized. Please login to continue'], 401);
        }
        return $next($request);
    }
}
