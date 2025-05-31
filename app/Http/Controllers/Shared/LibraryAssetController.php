<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
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
       $media = Media::query()->whereMorphedTo('model',auth($this->activeGuard)->user())
           ->get();
       return Response::api(data: MediaResource::collection($media));
   }

    public function store(Request $request)
    {
        $media = handleMediaUploads($request->file('file'),auth($this->activeGuard)->user(),"$this->activeGuard.'_assets'");
        return Response::api(data: MediaResource::make($media));

   }
}
