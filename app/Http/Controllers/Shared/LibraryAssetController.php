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
       $media = Media::query()->whereMorphedTo('model',Admin::find(1) ?? Admin::find(7))
           ->whereCollectionName("web_assets")
           ->latest()
           ->paginate();
//       $media = Media::query()->whereMorphedTo('model',auth($this->activeGuard)->user())
//           ->whereCollectionName("{$this->activeGuard}_assets")
//           ->latest()
//           ->get();
       return Response::api(data: MediaResource::collection($media)->response()->getData(true));
   }

    public function store(Request $request)
    {
        $request->validate(['file' => ['required','file']]);
        $media = handleMediaUploads($request->file('file'),Admin::find(1) ?? Admin::find(7),"web_assets");
//        $media = handleMediaUploads($request->file('file'),auth($this->activeGuard)->user(),"{$this->activeGuard}_assets");
        return Response::api(data: MediaResource::make($media)->response()->getData(true));

   }
}
