<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Services\FlagService;
use App\Http\Requests\Flag\{StoreFlagRequest, UpdateFlagRequest};



class FlagController extends DashboardController
{
   public function __construct(
       public FlagService $flagService,
       public ProductRepositoryInterface              $productRepository,
       public TemplateRepositoryInterface             $templateRepository,
   )
   {
       parent::__construct($flagService);
       $this->storeRequestClass = new StoreFlagRequest();
       $this->updateRequestClass = new UpdateFlagRequest();
       $this->indexView = 'flags.index';
       $this->usePagination = true;
       $this->resourceTable = 'flags';
       $this->assoiciatedData = [

           'index' => [
               'products' => $this->productRepository->query()->get(),
               'templates' => $this->templateRepository->query()->get(),
           ],

       ];
   }

    public function getData()
    {
        return $this->flagService->getData();
   }
}
