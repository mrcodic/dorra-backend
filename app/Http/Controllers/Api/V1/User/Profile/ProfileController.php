<?php

namespace App\Http\Controllers\Api\V1\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return Response::api(data: UserResource::make($request->user()->load('countryCode','socialAccounts')));
    }

    public function update()
    {

    }
}
