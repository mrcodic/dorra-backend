<?php

namespace App\Http\Controllers\Api\V1\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Repositories\Interfaces\SocialAccountRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return Response::api(data: UserResource::make($request->user()->load('countryCode','socialAccounts','notificationTypes')));
    }

    public function update(UpdateProfileRequest $request)
    {
        $validated = $request->validated();
        $request->user()->update($validated);
        if (!empty($validated['notification_types']))
        {
            $request->user()->notificationTypes()->sync($validated['notification_types']);
        }
        return Response::api(data: UserResource::make($request->user()->load('countryCode','socialAccounts','notificationTypes')));

    }

    public function disconnectAccount($accountId, SocialAccountRepositoryInterface $socialAccountRepository)
    {
        $socialAccountRepository->delete($accountId);
        return Response::api();
    }
}
