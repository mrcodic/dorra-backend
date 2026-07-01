<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class AiAssetController extends Controller
{
    public ?string $activeGuard;
    public function __construct()
    {
        $this->activeGuard = getActiveGuard();

    }

    public function index(Request $request)
   {
       $media = Media::query()->whereMorphedTo('model',$request->user())
           ->whereCollectionName("ai_assets")
           ->latest()
           ->paginate();
       return Response::api(data: MediaResource::collection($media)->response()->getData(true));
   }

    public function store(Request $request)
    {
        $request->validate(['file' => ['required','file',  'mimetypes:image/jpeg,image/png,image/svg+xml',
            'mimes:jpg,jpeg,png,svg',
            ]]);
        $media = handleMediaUploads($request->file('file'),$request->user(),'ai_assets');
        return Response::api(data: MediaResource::make($media)->response()->getData(true));

   }
}
