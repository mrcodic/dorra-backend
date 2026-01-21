<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class LibraryAssetController extends Controller
{
    public ?string $activeGuard;

    public function __construct()
    {
        $this->activeGuard = getActiveGuard();

    }

    public function index()
    {
        $notAuth = request()->is('api/v1/admin/*');
        $model = $notAuth ? Admin::first() : getAuthOrGuest();
        $media = Media::query()->whereMorphedTo('model', $model)
            ->whereCollectionName(($this->activeGuard ?? 'guest') . '_assets')
            ->latest()
            ->get();
        return Response::api(data: MediaResource::collection($media)->response()->getData(true));
    }

    public function store(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/svg+xml',
            'mimes:jpg,jpeg,png,svg',
        ]]);
        $notAuth = request()->is('api/v1/admin/*');
        $model = $notAuth ? Admin::first() : getAuthOrGuest();
        $media = handleMediaUploads($request->file('file'), $model, ($this->activeGuard ?? 'guest') . '_assets');
        return Response::api(data: MediaResource::make($media)->response()->getData(true));

    }
}
