<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\FontResource;
use App\Http\Resources\MediaResource;
use App\Models\Admin;
use App\Models\Font;
use App\Models\FontStyle;
use App\Services\FontService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class FontController extends Controller
{

    public function __construct(public FontService $fontService)
    {
    }

    public function index()
    {
        $fonts = $this->fontService->getAll(['fontStyles.media']);
        return Response::api(data: FontResource::collection($fonts)->response()->getData(true));

    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',],
            'font_styles' => ['required', 'array'],
            'font_styles.*.name' => ['required', 'string', 'max:255'],
            'font_styles.*.file' => [
                'required',
                'file',
                'mimetypes:font/ttf,font/otf,font/woff,font/woff2,
                application/x-font-ttf,application/x-font-otf,application/x-font-woff,application/font-sfnt,application/vnd.ms-fontobject,
                application/octet-stream,application/vnd.ms-opentype',
                'max:10240',
            ],

        ]);
        $font = $this->fontService->storeResource($validated);
        return Response::api(data: FontResource::make($font));
    }

    public function show($id)
    {
        $font = $this->fontService->showResource($id,['fontStyles.media']);
        return Response::api(data: FontResource::make($font));
    }
    public function update(Request $request,Font $font)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',],
            'font_styles' => ['required', 'array'],
            'font_styles.*.id' => ['sometimes', 'integer', 'exists:font_styles,id'],
            'font_styles.*.name' => ['required', 'string', 'max:255'],
            'font_styles.*.file' => ['required_without:font_styles.*.id', 'file',
                'mimetypes:font/ttf,font/otf,font/woff,font/woff2,
                application/x-font-ttf,application/x-font-otf,application/x-font-woff,application/font-sfnt,application/vnd.ms-fontobject,
                application/octet-stream,application/vnd.ms-opentype',
                ],
        ]);
        $this->fontService->update($validated,$font);
        return Response::api();
    }

    public function destroy(Font $font)
    {
        $font->delete();
        return Response::api(message: "Font deleted successfully.");


    }

    public function destroyFontStyle(FontStyle $fontStyle)
    {
        $fontStyle->delete();
        return Response::api(message: "Font Style deleted successfully.");

    }

}
