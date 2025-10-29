<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\IndustryRepositoryInterface;
use App\Http\Requests\Industry\{StoreIndustryRequest, UpdateIndustryRequest};
use App\Services\IndustryService;
use Illuminate\Http\JsonResponse;


class IndustryController extends DashboardController
{
    public function __construct(public IndustryService $industryService,
    public IndustryRepositoryInterface $industryRepository,)
    {

        parent::__construct($industryService);
        $this->storeRequestClass = new StoreIndustryRequest();
        $this->updateRequestClass = new UpdateIndustryRequest();
        $this->indexView = 'industries.index';
        $this->usePagination = true;
        $this->resourceTable = 'industries';
        $this->assoiciatedData = [
          'industries' =>   $industryRepository->query()->whereNull('parent_id')->get(),
        ];

    }
    public function getData(): JsonResponse
    {
        return $this->industryService->getData();
    }
}
