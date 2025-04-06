<?php

namespace App\Http\Controllers\Api\V1\User\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationTypeResource;
use App\Models\NotificationType;
use Illuminate\Support\Facades\Response;


class UserNotificationTypeController extends Controller
{
    public function __invoke()
    {
        return Response::api(data: NotificationTypeResource::collection(NotificationType::all()));

    }
}
