<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Thêm dòng này

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sử dụng Auth facade thay vì helper auth()
        if (Auth::check() && Auth::user()->role == 1) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện yêu cầu này.'
        ], 403);
    }
}
