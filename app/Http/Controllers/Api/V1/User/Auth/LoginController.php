<?php

namespace App\Http\Controllers\Api\V1\User\Auth;

use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LoginController extends Controller
{
    public function __construct(public AuthService $authService)
    {
    }

    public function __invoke(LoginRequest $request)
    {
        $user = $this->authService->login($request);

        return Response::api(message: "You are logged in successfully", data: UserResource::make($user->load('countryCode', 'socialAccounts', 'notificationTypes')));
    }

    public function redirectToGoogle(Request $request)
    {
        return $this->authService->redirectToGoogle($request);
    }

    public function handleGoogleCallback()
    {
        $data = $this->authService->handleGoogleCallback();

        if ($data === false || empty($data['user'])) {
            return Response::api(
                HttpEnum::BAD_REQUEST,
                message: "Bad Request",
                errors: ['message' => 'something went wrong, please try again.']
            );
        }

        return redirect()->away($data['redirectUrl'])->withCookie(cookie(
            name: 'dorra_auth_token',
            value: $data['user']->token,
            minutes: 60 * 24 * 30,
            path: '/',
            domain: '.dorraprint.com',
            secure: app()->environment('production'),
            httpOnly: true,
            sameSite: 'Lax'
        ));
    }

}
