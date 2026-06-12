<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = (string) config('stockroom.api_key');
        $provided = (string) $request->header('X-API-Key');

        if ($configured === '' || ! hash_equals($configured, $provided)) {
            return response()->json([
                'message' => 'A valid X-API-Key header is required for this action.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
