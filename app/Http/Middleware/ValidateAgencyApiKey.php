<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Agency;

class ValidateAgencyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY') ?? $request->input('api_key');

        if (!$apiKey) {
            return response()->json([
                'message' => 'API Key is required'
            ], 401);
        }

        $agency = Agency::where('api_key', $apiKey)->first();

        if (!$agency) {
            return response()->json([
                'message' => 'Invalid API Key'
            ], 401);
        }

        // Store the agency in the request for later use
        $request->merge(['authenticated_agency' => $agency]);

        return $next($request);
    }
}
