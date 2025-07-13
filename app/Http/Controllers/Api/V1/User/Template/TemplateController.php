<?php

namespace App\Http\Controllers\Api\V1\User\Template;


use App\Actions\Template\StoreTemplate;
use App\DTOs\Template\TemplateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Template\StoreTemplateRequest;
use App\Http\Requests\Template\UpdateTemplateRequest;
use App\Http\Resources\TemplateResource;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Response;


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
    public function update($id, UpdateTemplateRequest $request)
    {
        $template = $this->templateService->updateResource($request->validated(),$id);

        return Response::api(data: TemplateResource::make($template));
    }

    public function show($id)
    {
        return Response::api(data: TemplateResource::make($this->templateService->showResource($id)));


    }

    public function index()
    {
        $templates = $this->templateService->getAll();
        return Response::api(data: TemplateResource::collection($templates));
    }


}
