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


            $updated = DB::table('visits')
                ->where('ip', $ip)
                ->increment('hits');

            if ($updated === 0) {
                DB::table('visits')->insert([
                    'ip'         => $ip,
                    'hits'       => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $response;
    }


}
