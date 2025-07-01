<?php

namespace App\Providers;

use App\Enums\HttpEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

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
        Fortify::loginView(fn () => view('dashboard.auth.login'));

        Response::macro('api', function (
            $statusCode = HttpEnum::OK,
            $message = "Request completed successfully",
            $data = [],
            $errors = [],
            $meta = []
        ) {
            $response = [
                'status' => $statusCode->value,
                'success' => $statusCode->value < HttpEnum::BAD_REQUEST->value,
                'message' => $message,
            ];
            if (!empty($errors)) {
                $response['errors'] = $errors;
            }
            if (!empty($data)) {
                if ($data instanceof LengthAwarePaginator) {
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
                    if ($data instanceof ResourceCollection) {
                        return $data->response()->setStatusCode($statusCode->value);
                    }
                } else {
                    $response['data'] = $data;
                }
            }

            if (!empty($meta)) {
                $response['meta'] = $meta;
            }

            return response()->json($response, $statusCode->value);
        });

        Model::preventLazyLoading(! app()->isProduction());
    }
}
