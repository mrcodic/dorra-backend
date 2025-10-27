<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Models\Admin;
use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
        $request->validate([
            'file' => [
                'required','file','max:4096',
                'mimetypes:image/jpeg,image/png,image/svg+xml',
                'mimes:jpg,jpeg,png,svg',
            ],
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType();
        $model = \App\Models\Admin::find(1) ?? \App\Models\Admin::find(7); // حسب منطقك

        if ($mime === 'image/svg+xml') {
            // 1) عقّم SVG
            $original = file_get_contents($file->getRealPath()) ?: '';
            $sanitizer = new Sanitizer();
            $clean = $sanitizer->sanitize($original) ?? '';

            if (! str_contains(strtolower($clean), '<svg')) {
                return response()->json(['message' => 'الملف بعد التعقيم لم يعد SVG صالحاً.'], 422);
            }

            // 2) اكتب إلى ملف مؤقت داخل storage/app/tmp
            $tmpDir = 'tmp/svg';
            $filename = Str::uuid().'.svg';
            $relativePath = $tmpDir.'/'.$filename;                 // داخل storage/app
            Storage::put($relativePath, $clean);

            $absolutePath = storage_path('app/'.$relativePath);

            // 3) لفّه كـ UploadedFile علشان أي هيلبر عندك يتعامل معاه بسهولة
            $uploaded = new UploadedFile(
                $absolutePath,
                $filename,
                'image/svg+xml',
                null,
                true // test mode: يمنع move_uploaded_file errors
            );

            // 4) استخدم نفس الهيلبر بتاعك (لو بيقبل UploadedFile)
            $media = handleMediaUploads($uploaded, $model, 'web_assets');

            // 5) نظّف الملف المؤقت
            Storage::delete($relativePath);
        } else {
            // صور عادية: سلّم الملف زي ما هو
            $media = handleMediaUploads($file, $model, 'web_assets');
        }

        return Response::api(data: \App\Http\Resources\MediaResource::make($media)->response()->getData(true));
    }

}
