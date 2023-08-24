<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountLockMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->username;
        if (!User::query()->where('username', $request->username)->exists()) {
            return response()->json([
                'message' => 'username not found'
            ], 404);
        }
        $user = User::where('username', $username)->first();

        if ($user->flag == 1)
            return response()->json([
                'message' => 'your account is locked'
            ], 401);
        return $next($request);
    }
}
