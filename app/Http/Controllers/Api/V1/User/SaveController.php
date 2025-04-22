<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SavedItems\DeleteSaveRequest;
use App\Http\Requests\User\SavedItems\ToggleSaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SaveController extends Controller
{
    public function toggleSave(ToggleSaveRequest $request)
    {
        $relation = "saved".ucfirst($request->savable_type)."s";
        $request->user()->$relation()->toggle($request->savable_id);
        $isNowSaved = $request->user()->$relation()->whereKey($request->savable_id)->exists();
        return Response::api(data: [
            'status' => $isNowSaved ? 'saved' : 'unsaved',
            'saveable_type' => $request->savable_type,
            'saveable_id' => $request->savable_id
        ]);
    }

    public function destroyBulk(DeleteSaveRequest $request)
    {
        $relation = "saved".ucfirst($request->savable_type)."s";
        $request->user()->$relation()->detach($request->savable_ids);

        return Response::api(data: [
            'status' => 'unsaved',
            'saveable_type' => $request->savable_type,
            'saveable_ids' => $request->savable_ids
        ]);
    }
}
