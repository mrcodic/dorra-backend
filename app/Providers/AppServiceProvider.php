<?php

namespace App\Providers;

use App\Enums\HttpEnum;
use App\Models\Admin;
use App\Models\Product;
use App\Services\SMS\SmsInterface;
use App\Services\SMS\SmsMisrService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsInterface::class,SmsMisrService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::authenticateUsing(function (Request $request) {
        $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string','min:6'],
        ]);

        $user = Admin::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => trans('auth.user_not_found'),
            ]);
        }
        if ($user->status == 0)
        {
            throw ValidationException::withMessages([
                'email' => 'your account is blocked',
            ]);
        }
        if ($user->roles->isEmpty()) {
            throw ValidationException::withMessages([
                'role' => 'contact administrator your role deleted',
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => trans('auth.password_incorrect'),
            ]);
        }



        return $user;
    });

        Fortify::loginView(fn () => view('dashboard.auth.login'));

        Response::macro('api', function (
            $statusCode = HttpEnum::OK,
            $message = "Request completed successfully",
            $data = [],
            $errors = [],
            $forgetToken = false
        ) {

            $response = [
                'status'  => $statusCode->value,
                'success' => $statusCode->value < HttpEnum::BAD_REQUEST->value,
                'message' => $message,
            ];

            if (!empty($errors)) {
                $response['errors'] = $errors;
            }

            if (!empty($data)) {

                // ✅ PAGINATED RESOURCE COLLECTION
                if ($data instanceof ResourceCollection && $data->resource instanceof LengthAwarePaginator) {
                    $paginator = $data->resource;

                    $response['data'] = $data->collection;
                    $response['pagination'] = [
                        'total'         => $paginator->total(),
                        'count'         => $paginator->count(),
                        'per_page'      => $paginator->perPage(),
                        'current_page'  => $paginator->currentPage(),
                        'last_page'     => $paginator->lastPage(),
                        'next_page_url' => $paginator->nextPageUrl(),
                        'prev_page_url' => $paginator->previousPageUrl(),
                    ];

                }

                // ✅ RAW PAGINATOR (NO RESOURCE)
                elseif ($data instanceof LengthAwarePaginator) {
                    $response['data'] = $data->items();
                    $response['pagination'] = [
                        'total'         => $data->total(),
                        'count'         => $data->count(),
                        'per_page'      => $data->perPage(),
                        'current_page'  => $data->currentPage(),
                        'last_page'     => $data->lastPage(),
                        'next_page_url' => $data->nextPageUrl(),
                        'prev_page_url' => $data->previousPageUrl(),
                    ];
                }

                // ✅ NORMAL COLLECTION / ARRAY
                else {
                    $response['data'] = $data;
                }
            }

            $jsonResponse = response()->json($response, $statusCode->value);

            if ($forgetToken) {
                $jsonResponse->withCookie(
                    cookie()->forget('token', '/', '.dorraprint.com')
                );
            }

            return $jsonResponse;
        });
         Model::preventLazyLoading(!app()->isProduction());
    }
}
