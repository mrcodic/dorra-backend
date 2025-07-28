<?php

namespace App\Http\Controllers\Api\V1\User\Auth;


use App\Enums\HttpEnum;
use App\Http\Controllers\Controller;

use App\Services\AuthService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LogoutController extends Controller
{
    public function __construct(public AuthService $authService)
    {
    }

    public function __invoke(Request $request)
    {
        $this->authService->logout($request);
        return Response::api(
            message: 'You are successfully logged out',
            forgetToken: true
        );


    }


}
