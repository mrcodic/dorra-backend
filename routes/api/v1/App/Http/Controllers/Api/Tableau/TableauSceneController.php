<?php

namespace App\Http\Controllers\Api\Tableau;

use App\Http\Controllers\Controller;
use App\Http\Resources\TableauSceneResource;
use App\Models\Template;
use Illuminate\Http\JsonResponse;

class TableauSceneController extends Controller
{
    public function index(Template $template): JsonResponse
    {
        $template->load([
            'tableauScenes.media',
        ]);
        return response()->api(data: TableauSceneResource::collection($template->tableauScenes));
    }
}
