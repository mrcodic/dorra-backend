<?php

namespace App\Providers;

use App\Enums\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('api',function ($statusCode = Http::OK , $message = "success", $data = [], $errors = []) {
            $response = [
                'status' => $statusCode->value,
                'success' => $statusCode->value < Http::BAD_REQUEST->value,
                'message' => $message,
            ];
            if (!empty($errors)) {
                $response['errors'] = $errors;
            }
            if (!empty($data)) {
                if ($data instanceof LengthAwarePaginator)
                {
                    $response['data'] = $data->items();
                    $response['pagination'] = [
                        'total' => $data->total(),
                        'count' => $data->count(),
                        'per_page' => $data->perPage(),
                        'current_page' => $data->currentPage(),
                        'last_page' => $data->lastPage(),
                        'next_page_url' => $data->nextPageUrl(),
                        'prev_page_url' => $data->previousPageUrl(),
                    ];

                }
                else{
                    $response['data'] = $data;
                }
            }
            return response()->json($response, $statusCode->value);
        });
    }
}
