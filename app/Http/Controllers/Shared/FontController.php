<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class FontController extends Controller
{
    public ?string $activeGuard;
    public function __construct()
    {
        $this->activeGuard = getActiveGuard();

    }

    public function index()
    {
        $isAdminRoute = request()->is('api/v1/admin/*');

        if ($isAdminRoute) {
            $model = Admin::find(1) ?? Admin::find(7);
            $collection = "web_fonts";
        } else {
            $model = auth($this->activeGuard)->user();
            $collection = "{$this->activeGuard}_fonts";
        }

        $media = Media::query()
            ->whereMorphedTo('model', $model)
            ->whereCollectionName($collection)
            ->latest()
            ->get();

        return Response::api(data: MediaResource::collection($media)->response()->getData(true));
    }


    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file',
                'mime' => 'mimetypes:font/ttf,
                font/otf,font/woff,font/woff2,
                application/vnd.ms-fontobject,image/svg+xml',
            ],
        ]);

        $isAdminRoute = request()->is('api/v1/admin/*');

        if ($isAdminRoute) {
            $model = Admin::find(1) ?? Admin::find(7);
            $collection = "web_fonts";
        } else {
            $model = auth($this->activeGuard)->user();
            $collection = "{$this->activeGuard}_fonts";
        }

        $media = handleMediaUploads($request->file('file'), $model, $collection);

        return Response::api(data: MediaResource::make($media)->response()->getData(true));
    }

}
