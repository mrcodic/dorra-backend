<?php

namespace App\Http\Controllers\Api\V1\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Profile\UpdatePasswordRequest;
use Illuminate\Support\Facades\Response;

class PasswordController extends Controller
{
    public function __invoke(UpdatePasswordRequest $request)
    {
        if (!empty($request->new_password))
        {
            $request->user()->update([
                'password' => $request->new_password,
                'password_updated_at' => now(),
            ]);
        }
        return Response::api();
    }
}
