<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\IndustryResource;
use App\Repositories\Interfaces\IndustryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Industry\{StoreIndustryRequest, UpdateIndustryRequest};
use App\Services\IndustryService;
use Illuminate\Http\JsonResponse;


class IndustryController extends DashboardController
{
    public function __construct(public IndustryService             $industryService,
                                public IndustryRepositoryInterface $industryRepository,)
    {

        parent::__construct($industryService);
        $this->storeRequestClass = new StoreIndustryRequest();
        $this->updateRequestClass = new UpdateIndustryRequest();
        $this->indexView = 'industries.index';
        $this->usePagination = true;
        $this->resourceTable = 'industries';
        $this->assoiciatedData = [
            'industries' => $industryRepository->query()->whereNull('parent_id')->get(),
        ];

    }

    public function getData(): JsonResponse
    {
        return $this->industryService->getData();
    }

    public function getSubIndustries(Request $request): JsonResponse
    {
        $validated = $request->validate(['industry_ids' => 'required', 'exists:industries,id']);
        $subIndustries = $this->industryService->getSubIndustriesByIndustries($validated['industry_ids']);
        return Response::api(data: IndustryResource::collection($subIndustries));
    }
}
