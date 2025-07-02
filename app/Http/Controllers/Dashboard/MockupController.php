<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;


use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Services\MockupService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Mockup\{
    StoreMockupRequest,
    UpdateMockupRequest,

};


class MockupController extends DashboardController
{
    public function __construct(
        public MockupService $mockupService,
        public ProductRepositoryInterface              $productRepository,


    )
    {
        parent::__construct($mockupService);
        $this->storeRequestClass = new StoreMockupRequest();
        $this->updateRequestClass = new UpdateMockupRequest();
        $this->indexView = 'mockups.index';
        $this->usePagination = true;
        $this->resourceTable = 'mockups';
        $this->assoiciatedData = [
            'index'=>[
                'products' => $this->productRepository->all(),
            ],
        ];
        $this->methodRelations = [
            'index' => ['product'],
        ];
    }
    public function index()
    {


        $data = $this->service->getAll($this->getRelations('index'), $this->usePagination ,perPage: request('per_page',16));
        $associatedData = $this->getAssociatedData('index');
        if (request()->ajax()) {
            $cards = view('dashboard.partials.filtered-mockups', compact('data'))->render();

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

        return view("dashboard.mockups.index", get_defined_vars());
    }
}
