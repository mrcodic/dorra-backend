<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Template\StatusEnum;
use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\TemplateResource;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ProductSpecificationRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Template\{StoreTemplateRequest,
    StoreTranslatedTemplateRequest,
    UpdateTemplateRequest,
    UpdateTranslatedTemplateRequest};


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
        $this->updateRequestClass = new UpdateTranslatedTemplateRequest();
        $this->indexView = 'templates.index';
        $this->createView = 'templates.create';
        $this->editView = 'templates.edit';
        $this->showView = 'templates.show';
        $this->usePagination = true;
        $this->resourceTable = 'templates';
        $this->resourceClass = TemplateResource::class;
        $this->assoiciatedData = [
            'shared'=>[
                'products' => $this->productRepository->all(),
            ],
            'index' => [
                'tags' => $this->tagRepository->all(),
            ],
        ];
        $this->methodRelations = [
            'index' => ["product.tags" ,"media"],
        ];

    }

    public function index()
    {
        $data = $this->service->getAll($this->getRelations('index'), $this->usePagination ,perPage: request('per_page',16));
        $associatedData = $this->getAssociatedData('index');
        if (request()->ajax()) {
            $cards = view('dashboard.templates.partials.filtered-templates', compact('data'))->render();

            $pagination = '';
            if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $pagination = '<div class="mt-2 px-1">' .
                    $data->withQueryString()->links('pagination::bootstrap-5')->render() .
                    '</div>';
            }

            return Response::api(data: [
                'cards'      => $cards,
                'pagination' => $pagination,
                'total'      => is_countable($data) ? count($data) : $data->total(),
            ]);
        }
        return view("dashboard.templates.index", get_defined_vars());
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
        return Response::api(data: TemplateResource::make($this->templateService->showResource($id,['specifications.options','product.prices'])));
    }

    public function getProductTemplates()
    {
        $productId = request()->input('productId');
        $templates = $this->templateService->getProductTemplates($productId);
        return Response::api(data: TemplateResource::collection($templates));

    }

    public function templateCustomizations(Request $request)
    {
        $templates = $this->templateService->templateCustomizations($request->validated());
        return Response::api(data: TemplateResource::collection($templates));

    }
    public function changeStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required','in:'.StatusEnum::getValuesAsString()]);
       $template =  $this->templateService->updateResource(['status'=> $request->status],$id);
        return Response::api(data: TemplateResource::make($template));
    }
}
