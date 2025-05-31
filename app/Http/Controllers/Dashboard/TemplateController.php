<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\TemplateResource;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductSpecificationRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Template\{StoreTemplateRequest, StoreTranslatedTemplateRequest, UpdateTemplateRequest};


class TemplateController extends DashboardController
{
    public function __construct(
        public TemplateService                         $templateService,
        public ProductRepositoryInterface              $productRepository,
        public TemplateRepositoryInterface             $templateRepository,
        public TagRepositoryInterface                  $tagRepository,
        public ProductSpecificationRepositoryInterface $productSpecificationRepository

    )
    {
        parent::__construct($templateService);
        $this->storeRequestClass = new StoreTemplateRequest();
        $this->updateRequestClass = new UpdateTemplateRequest();
        $this->indexView = 'templates.index';
        $this->createView = 'templates.create';
        $this->editView = 'templates.edit';
        $this->showView = 'templates.show';
        $this->usePagination = true;
        $this->resourceTable = 'templates';
        $this->assoiciatedData = [
            'index' => [
                'products' => $this->productRepository->all(),
                'tags' => $this->tagRepository->all(),
                'templates' => $this->templateRepository->query()
                    ->with(['product.tags'])
                    ->paginate(16),
            ],
            'create' => [
                'products' => $this->productRepository->all(),
            ]
        ];
    }


    public function getProductTemplates()
    {
        $productId = request()->input('productId');
        $templates = $this->templateService->getProductTemplates($productId);
        return Response::api(data: TemplateResource::collection($templates));

    }

    public function storeAndRedirect(StoreTranslatedTemplateRequest $request)
    {
      $template  = $this->templateService->storeResource($request->validated());
      return Response::api(data: [
         "redirect_url" => config('services.editor_url') . 'templates/' . $template->id
      ]);
    }
    public function show($id)
    {
        return Response::api(data: TemplateResource::make($this->templateService->showResource($id)));
    }
}
