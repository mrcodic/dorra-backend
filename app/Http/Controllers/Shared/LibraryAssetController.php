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
        $model = \App\Models\Admin::find(1) ?? \App\Models\Admin::find(7);

        if ($mime === 'image/svg+xml') {
            // 1) sanitize
            $original   = file_get_contents($file->getRealPath()) ?: '';
            $sanitizer  = new Sanitizer();
            $clean      = $sanitizer->sanitize($original) ?? '';

            if (! str_contains(strtolower($clean), '<svg')) {
                return response()->json(['message' => 'الملف بعد التعقيم لم يعد SVG صالحاً.'], 422);
            }

            // 2) اكتب على disk local (وليس الافتراضي)
            $tmpDir       = 'tmp/svg';
            Storage::disk('local')->makeDirectory($tmpDir);
            $filename     = Str::uuid().'.svg';
            $relativePath = $tmpDir.'/'.$filename;

            // مهم: disk local
            Storage::disk('local')->put($relativePath, $clean);

            // 3) هات absolute path الصحيح من نفس الـdisk
            $absolutePath = Storage::disk('local')->path($relativePath);

            try {
                // لو عندك هيلبرك
                // لفّه كـ UploadedFile (المسار لازم يكون موجود فعلاً)
                $uploaded = new UploadedFile(
                    $absolutePath,
                    $filename,
                    'image/svg+xml',
                    null,
                    true // test mode
                );

                $media = handleMediaUploads($uploaded, $model, 'web_assets');

            } finally {
                // 4) احذف المؤقت بعد المحاولة (حتى لو فشلت)
                Storage::disk('local')->delete($relativePath);
            }
        } else {
            // صور عادية
            $media = handleMediaUploads($file, $model, 'web_assets');
        }

        return Response::api(data: \App\Http\Resources\MediaResource::make($media)->response()->getData(true));
    }


}
