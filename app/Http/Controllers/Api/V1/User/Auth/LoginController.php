<?php

namespace App\Http\Controllers\Api\V1\User\Auth;

use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function __construct(public AuthService $authService)
    {
    }

    public function __invoke(LoginRequest $request)
    {
        $user = $this->authService->login($request->validated());
        return Response::api(message: "You are logged in successfully", data: UserResource::make($user->load('countryCode')));
    }

    public function loginWithGoogle(Request $request)
    {
        $user = $this->authService->loginWithGoogle($request);
        if (!$user) {
            return Response::api(HttpEnum::UNAUTHORIZED, message: "Google authentication failed",
                errors: [
                    'token' => ['Invalid or expired Google token. Please try again.']
                ]);

        }
        return Response::api(message: "You are logged in successfully", data: UserResource::make($user->load('countryCode')));
    }
}
