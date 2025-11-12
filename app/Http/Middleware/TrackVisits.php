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

            DB::statement(
                'INSERT INTO visits (`ip`,`hits`,`created_at`,`updated_at`)
     VALUES (?, 1, NOW(), NOW())
     ON DUPLICATE KEY UPDATE `hits` = `hits` + 1, `updated_at` = VALUES(`updated_at`)',
                [$request->ip() ?? '0.0.0.0']
            );

        }

        return $response;
    }


}
