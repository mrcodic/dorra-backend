<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Response;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api/v1/user.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api/v1/user',
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

        $exceptions->render(function (Throwable $e, $request) {
            if (
                $e instanceof ModelNotFoundException ||
                $e instanceof NotFoundHttpException
            ) {
                if ($request->is('api/v1/*')) {
                    return Response::api(\App\Enums\HttpEnum::NOT_FOUND,errors: [
                            ['message' => 'Resource not found.']
                        ]
                   );
                }
                abort(404);
            }

            return null;
        });

    })->create();
