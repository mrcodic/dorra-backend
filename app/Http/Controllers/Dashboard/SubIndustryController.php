<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\IndustryRepositoryInterface;
use App\Http\Requests\SubIndustry\{StoreSubIndustryRequest, UpdateSubIndustryRequest};
use App\Services\IndustryService;
use Illuminate\Http\JsonResponse;


class SubIndustryController extends DashboardController
{
    public function __construct(public IndustryService $industryService,
    public IndustryRepositoryInterface $industryRepository)
    {

        parent::__construct($industryService);
        $this->storeRequestClass = new StoreSubIndustryRequest();
        $this->updateRequestClass = new UpdateSubIndustryRequest();
        $this->indexView = 'sub-industries.index';
        $this->usePagination = true;
        $this->resourceTable = 'industries';
        $this->assoiciatedData = [
            'index' =>[
                'industries' =>   $industryRepository->query()->whereNull('parent_id')->get(),
            ]

        ];
    }
    public function getData(): JsonResponse
    {
        return $this->industryService->getSubIndustryData();
    }
}
