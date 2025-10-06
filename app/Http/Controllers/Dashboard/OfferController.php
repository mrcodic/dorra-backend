<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\OfferService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Offer\{StoreOfferRequest, UpdateOfferRequest};


class OfferController extends DashboardController
{
    public function __construct(public OfferService $offerService)
    {
        parent::__construct($offerService);
        $this->storeRequestClass = new StoreOfferRequest();
        $this->updateRequestClass = new UpdateOfferRequest();
        $this->indexView = 'offers.index';
        $this->usePagination = true;
        $this->resourceTable = 'offers';
    }

    public function getData(): JsonResponse
    {
        return $this->offerService->getData();
    }


}
