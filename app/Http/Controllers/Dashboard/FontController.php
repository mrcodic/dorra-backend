<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\FontResource;
use App\Models\Font;
use App\Models\FontStyle;
use App\Rules\ValidFontFile;
use App\Services\FontService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class FontController extends Controller
{

    public function __construct(public FontService $fontService)
    {
    }

    public function index()
    {
        $fonts = $this->fontService->getAll(['fontStyles.media', 'fontStyles.font'],true,
            perPage: request('per_page'));
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
                new ValidFontFile(),
                'max:10240',
            ],


        ]);

        $font = $this->fontService->storeResource($validated);
        return Response::api(data: FontResource::make($font));
    }

    public function show($id)
    {
        $font = $this->fontService->showResource($id, ['fontStyles.media', 'fontStyles.font']);
        return Response::api(data: FontResource::make($font));
    }

    public function update(Request $request, Font $font)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255',],
            'font_styles' => ['required', 'array'],
            'font_styles.*.id' => ['sometimes', 'integer', 'exists:font_styles,id'],
            'font_styles.*.name' => ['required', 'string', 'max:255'],
            'font_styles.*.file' => ['required_without:font_styles.*.id', 'file',
                new ValidFontFile(),
                'max:10240',

            ],
        ]);
        $this->fontService->update($validated, $font);
        return Response::api();
    }

    public function destroy(Font $font)
    {
        $font->delete();
        return Response::api(message: "Font deleted successfully.");


    }


    public function destroyFontStyle(FontStyle $fontStyle)
    {
        DB::transaction(function () use ($fontStyle) {
            $font = $fontStyle->font;
            $fontStyle->delete();
            if ($font && $font->fontStyles()->count() === 0) {
                $font->delete();
            }
        });
        return Response::api(message: "Font Style deleted successfully.");
    }


}
