<?php

namespace App\Http\Controllers\Api\V1\User\Auth;


use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;

use App\Services\AuthService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Response;

class LogoutController extends Controller
{
    public function __construct(public AuthService $authService)
    {
    }

    public function __invoke(Request $request)
    {
        $this->authService->logout($request);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json([
            'message' => 'You are successfully logged out'
        ])->withCookie(
            Cookie::make(
                name: 'dorra_auth_token',
                value: null,
                minutes: -1, // expire immediately
                path: '/',
                domain: '.dorraprint.com',
                secure: false, // match how it was set
                httpOnly: false,

                sameSite: 'Lax' // match original
            )
        );


    }


}
