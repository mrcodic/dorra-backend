<?php

namespace App\Http\Controllers\Api\V1\User\Template;


use App\Actions\Template\StoreTemplate;
use App\DTOs\TemplateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Template\StoreTemplateRequest;
use App\Http\Resources\TemplateResource;
use App\Services\TemplateService;
use Illuminate\Http\Response;


class TemplateController extends Controller
{
    public function __construct(public TemplateService $templateService)
    {
    }

    public function store(StoreTemplateRequest $request, StoreTemplate $action)
    {
        $templateData = TemplateData::fromRequest($request);
        $template = $action->handle($templateData);
        return Response::api(data: TemplateResource::make($template));
    }

    public function index($productId)
    {
        $templates = $this->templateService->getProductTemplates($productId);
        return Response::api(data: TemplateResource::collection($templates));

    }


}
