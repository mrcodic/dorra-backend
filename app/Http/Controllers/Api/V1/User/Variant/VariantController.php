<?php

namespace App\Http\Controllers\Api\V1\User\Variant;

use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VariantController extends Controller
{
    public function index()
    {
        return Response::api(data: MediaResource::make(Media::first()));
    }

}
