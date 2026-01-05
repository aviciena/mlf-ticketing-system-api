<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateXFunction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $xApiKey = $request->header('x-functions');

        if (empty($xApiKey)) {
            return response()->json(['message' => 'x-functions is missing'], 401);
        }

        if ($xApiKey !== env('X_FUNCTIONS_KEY')) {
            return response()->json(['message' => 'invalid x-functions'], 401);
        }

        return $next($request);
    }
}
