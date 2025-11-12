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
        dd(  $request->isMethod('get') &&
            $request->is('user/api/*') &&
            $response->isSuccessful());
        if (
            $request->isMethod('get') &&
            $request->is('user/api/*') &&
            $response->isSuccessful()
        ) {

            $ua = strtolower($request->userAgent() ?? '');
            if (!Str::contains($ua, ['bot', 'spider', 'crawl'])) {

                $fingerprint = sha1(($request->ip() ?? '0.0.0.0') . '|' . $ua);
                $cacheKey = "visit:dedupe:{$fingerprint}";
                if (Cache::add($cacheKey, true, now()->addMinutes(30))) {
                    $today = now()->toDateString();


                    $updated = DB::table('visits')
                        ->where('date', $today)
                        ->increment('total');

                    if ($updated === 0) {
                        DB::table('visits')->insert([
                            'date' => $today,
                            'total' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        return $response;
    }


}
