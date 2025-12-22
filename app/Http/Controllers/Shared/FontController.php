<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
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
        $isAdminRoute = request()->is('api/v1/admin/*');

        if ($isAdminRoute) {
            $model = Admin::first() ?? Admin::find(8);
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
            'name' => ['required', 'string', 'max:255',],
            'font_styles' => ['required', 'array'],
            'font_styles.*.id' => ['required', 'integer', 'exists:font_styles,id'],
            'font_styles.*.file' => ['required', 'integer', 'exists:font_styles,id'],
        ]);



        $isAdminRoute = request()->is('api/v1/admin/*');

        if ($isAdminRoute) {
            $model = Admin::first() ?? Admin::find(8);
            $collection = "web_fonts";
        } else {
            $model = auth($this->activeGuard)->user();
            $collection = "{$this->activeGuard}_fonts";
        }

        $media = handleMediaUploads($request->file('file'), $model, $collection);

        return Response::api(data: MediaResource::make($media)->response()->getData(true));
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
