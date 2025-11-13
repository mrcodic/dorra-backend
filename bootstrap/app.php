<?php

use App\Enums\HttpEnum;
use App\Http\Middleware\CountSiteVisitsMiddleware;
use App\Http\Middleware\TrackVisits;
use App\Models\Cart;
use App\Support\AclNavigator;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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
        $middleware->validateCsrfTokens(except: [
            'api/v1/user/payment/callback',
        ]);
        $middleware->encryptCookies(['dorra_auth_token','dorra_auth_cookie_id']);
        $middleware->api([
            EnsureFrontendRequestsAreStateful::class,
            ]);
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

                if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                    if ($request->expectsJson()) {
                        return Response::api(\App\Enums\HttpEnum::NOT_FOUND,
                            message: 'Something went wrong',
                            errors: [
                                ['message' => 'Resource not found.']
                            ]
                        );
                    }
                }
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

    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('shipping:sync shipblu')
            ->dailyAt(env('SHIPPING_SYNC_TIME', '03:15'))
            ->timezone('Africa/Cairo')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/shipping-sync-shipblu.log'));


        $schedule->call(function () {
            Cart::query()
                ->where('expires_at', '<', now())
                ->delete();
        })
            ->everyFifteenMinutes()
            ->name('cleanup-expired-carts');
    })->create();
