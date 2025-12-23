<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\FontResource;
use App\Http\Resources\MediaResource;
use App\Models\Admin;
use App\Services\FontService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class FontController extends Controller
{
    public ?string $activeGuard;
    public function __construct(public FontService $fontService)
    {
        $this->activeGuard = getActiveGuard();

    }

    public function index()
    {
        $fonts = $this->fontService->getAll(['fontStyles.media']);
        return Response::api(data: FontResource::collection($fonts)->response()->getData(true));

    }


    public function store(Request $request)
    {
       $validated =  $request->validate([
            'name' => ['required', 'string', 'max:255',],
            'font_style_name' => ['required', 'string', 'max:255'],
            'font_style_file' => ['required', 'file'],
            'font_id' => ['sometimes', 'integer', 'exists:fonts,id'],
        ]);
       $font = $this->fontService->storeResource($validated);
        return Response::api(data: FontResource::make($font)->response()->getData(true));
    }
    public function destroy($mediaId)
    {
        $media = Media::findOrFail($mediaId);

        $isAdminRoute = request()->is('api/v1/admin/*');

        if ($isAdminRoute) {
            $model = Admin::first() ?? Admin::find(8);
            $collection = "web_fonts";
        } else {
            $model = auth($this->activeGuard)->user();
            $collection = "{$this->activeGuard}_fonts";
        }

//        if (
//            $media->model_id !== $model?->id ||
//            $media->model_type !== get_class($model) ||
//            $media->collection_name !== $collection
//        ) {
//            return Response::api(message: "Unauthorized to delete this font.", status: 403);
//        }
        $media->forceDelete();

        return Response::api(message: "Font deleted successfully.");
    }

}
