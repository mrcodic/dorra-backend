<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Mockup\TypeEnum;
use App\Http\Controllers\Base\DashboardController;


use App\Http\Resources\MockupResource;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\MockupService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Mockup\{StoreMockupRequest, UpdateMockupEditorRequest, UpdateMockupRequest};


class MockupController extends DashboardController
{
    public function __construct(
        public MockupService              $mockupService,
        public ProductRepositoryInterface $productRepository,


    )
    {
        parent::__construct($mockupService);
        $this->storeRequestClass = new StoreMockupRequest();
        $this->updateRequestClass = new UpdateMockupRequest();
        $this->indexView = 'mockups.index';
        $this->usePagination = true;
        $this->resourceTable = 'mockups';
        $this->assoiciatedData = [
            'index' => [
                'products' => $this->productRepository->query()->whereHasMockup(true)->get(['id', 'name']),
            ],
        ];
        $this->methodRelations = [
            'index' => ['product.saves','types'],
        ];
        $this->resourceClass = MockupResource::class;
    }

    public function index()
    {

        $data = $this->service->getAll($this->getRelations('index'), $this->usePagination, perPage: request('per_page', 16));

        $associatedData = $this->getAssociatedData('index');

        if (request()->ajax()) {
            $cards = view('dashboard.partials.filtered-mockups', compact('data','associatedData'))->render();

            $pagination = '';
            if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $pagination = '<div class="mt-2 px-1">' .
                    $data->withQueryString()->links('pagination::bootstrap-5')->render() .
                    '</div>';
            }

            return Response::api(data: [
                'cards' => $cards,
                'pagination' => $pagination,
                'total' => is_countable($data) ? count($data) : $data->total(),
            ]);
        }
        elseif (request()->expectsJson()) {
            return Response::api(data: MockupResource::collection($data->load('types'))->response()->getData(true));
        }
        else{
            return view("dashboard.mockups.index", get_defined_vars());
        }

    }

    public function mockupTypes()
    {
        return Response::api(data: TypeEnum::toArray());
    }

    public function recentMockups()
    {
        $recentMockups = $this->mockupService->recentMockups();
        return Response::api(data: MockupResource::collection($recentMockups));

    }

    public function showAndUpdateRecent($id)
    {
        $mockup = $this->mockupService->showAndUpdateRecent($id);
        return Response::api(data: MockupResource::make($mockup));

    }

    public function destroyRecentMockup($id)
    {
        $mockup = $this->mockupService->destroyRecentMockup($id);
        return Response::api(data: MockupResource::make($mockup));
    }

    public function updateEditorData(UpdateMockupEditorRequest $request, $id)
    {
        $mockup = $this->mockupService->updateResource($request->validated(), $id);
        return Response::api(data: MockupResource::make($mockup));

    }

}
