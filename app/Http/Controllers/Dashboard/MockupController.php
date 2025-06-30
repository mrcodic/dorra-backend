<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

use App\Http\Requests\Template\{
    StoreTemplateRequest,
    UpdateTemplateRequest,
    UpdateTranslatedTemplateRequest
};
use Mockery\Mock;
use MockupService;

class MockupController extends Controller
{
    public function __construct(
        public MockupService                         $mockupService,
        public ProductRepositoryInterface              $productRepository,
        public MockupRepositoryInterface             $templateRepository,
        public TagRepositoryInterface                  $tagRepository,
        public ProductSpecificationRepositoryInterface $productSpecificationRepository

    ) {
        parent::__construct($mockupService);
        $this->storeRequestClass = new StoreTemplateRequest();
        $this->updateRequestClass = new UpdateTranslatedTemplateRequest();
        $this->indexView = 'templates.index';
        $this->createView = 'templates.create';
        $this->editView = 'templates.edit';
        $this->showView = 'templates.show';
        $this->usePagination = true;
        $this->resourceTable = 'templates';
        $this->resourceClass = TemplateResource::class;
        $this->assoiciatedData = [
            'shared' => [
                'products' => $this->productRepository->all(),
            ],
            'index' => [
                'tags' => $this->tagRepository->all(),
            ],
        ];
        $this->methodRelations = [
            'index' => ["product.tags", "media"],
        ];
    }
}
