<?php

namespace App\Http\Controllers\Api\V1\User\Variant;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use App\Repositories\Interfaces\VariantRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VariantController extends Controller
{
    public function index(Request $request, VariantRepositoryInterface $variantRepository)
    {
        $variant = $variantRepository->query()->where('key', $request->key)
            ->whereVariantableId($request->variantable_id)
            ->whereVariantableType($request->variantable_type)
            ->first();
        $media = $variant->media()->first();

        return Response::api(
            data: MediaResource::make($media)
        );
    }

}
