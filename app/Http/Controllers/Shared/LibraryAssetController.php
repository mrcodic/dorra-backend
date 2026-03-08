<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Admin;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class LibraryAssetController extends Controller
{
    public ?string $activeGuard;

    public function __construct()
    {
        $this->activeGuard = getActiveGuard();

    }

    public function index(Request $request)
    {
        $notAuth = request()->is('api/v1/admin/*');
        $model = $notAuth ? Admin::first() : getAuthOrGuest();
        $media = Media::query()
            ->withCount([
                'templates as templates_assets_count' => function   ($q) {
                    $q->whereNull('mediable.type');
                }, 'templates as templates_fonts_count' => function   ($q) {
                    $q->where('mediable.type','font');
                },
                'designs'])
            ->where(function ($query) use ($model) {
                $query->whereMorphedTo('model', $model)
                    ->whereCollectionName(($this->activeGuard ?? 'guest') . '_assets');
                if ($this->activeGuard === 'sanctum') {
                    $query->orWhere('collection_name', 'web_assets');
                }
            })
            ->orderByRaw("
            CASE
                WHEN collection_name = 'sanctum_assets' THEN 1
                WHEN collection_name = 'web_assets' THEN 2
                ELSE 3
            END
        ")
            ->latest()
            ->paginate($request->query('per_page', 10));
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
