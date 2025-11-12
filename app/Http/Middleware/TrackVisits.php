<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('get') && $response->isSuccessful()) {
            $ip = $request->ip() ?? '0.0.0.0';
            $today = now()->toDateString();
            $query = DB::table('visits')
                ->where('date', $today)
                ->where('ip', $ip);
            $existed = $query
                ->exists();
            if ($existed) {
                $query->increment('hits');
            }

            if (!$existed) {
                DB::table('visits')->insert([
                    'date' => $today,
                    'ip' => $ip,
                    'hits' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $response;
    }


}
