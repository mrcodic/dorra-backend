<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\SubIndustry\{StoreSubIndustryRequest, UpdateSubIndustryRequest};
use App\Services\IndustryService;
use Illuminate\Http\JsonResponse;


class SubIndustryController extends DashboardController
{
    public function __construct(public IndustryService $industryService)
    {

        parent::__construct($industryService);
        $this->storeRequestClass = new StoreSubIndustryRequest();
        $this->updateRequestClass = new UpdateSubIndustryRequest();
        $this->indexView = 'sub-industries.index';
        $this->usePagination = true;
        $this->resourceTable = 'industries';

    }
    public function getData(): JsonResponse
    {
        return $this->industryService->getSubIndustryData();
    }
}
