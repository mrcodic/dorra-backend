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
        $variant = $variantRepository->query()
            ->when($request->key, fn($query, $key) => $query->where('key', $key))
            ->when($request->variantable_id, fn($query, $id) => $query->where('variantable_id', $id))
            ->when($request->variantable_type, fn($query, $type) => $query->where('variantable_type', $type))
            ->firstOrFail();
        $media = $variant->media()->first();

        return Response::api(
            data: MediaResource::make($media)
        );
    }

}
