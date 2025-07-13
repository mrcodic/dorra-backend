<?php

use App\Enums\HttpEnum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api/v1/user.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1/user',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1/admin')
                ->group(base_path('routes/api/v1/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo('/');
        $middleware->api([EnsureFrontendRequestsAreStateful::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/v1/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
    });
        $exceptions->render(function( ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return Response::api(
                    HttpEnum::UNPROCESSABLE_ENTITY,
                    message: 'Validation error',
                    errors: $e->errors()
                );
            }
        });



        $exceptions->render(function (Throwable $e, $request) {
            if ($e instanceof ModelNotFoundException) {
                if ($request->expectsJson()) {
                    return Response::api(\App\Enums\HttpEnum::NOT_FOUND,
                        message: 'Something went wrong',
                        errors: [
                            ['message' => 'Resource not found.']
                        ]
                   );
                }
            }
//            if ($e instanceof NotFoundHttpException) {
//                if ($request->expectsJson()) {
//                    return Response::api(\App\Enums\HttpEnum::NOT_FOUND,
//                        message: 'Something went wrong',
//                        errors: [
//                            ['message' => 'Route not found.']
//                        ]
//                    );
//                }
//            }
            if ($e instanceof InvalidArgumentException) {
                if ($request->expectsJson()) {
                    return Response::api(\App\Enums\HttpEnum::BAD_REQUEST,
                        message: 'Something went wrong',
                        errors: [
                            ['message' =>  $e->getMessage()]
                        ]
                    );
                }
            }
        });

    })->create();
