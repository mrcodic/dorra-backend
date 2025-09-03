<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\FaqService;
use App\Http\Requests\Faq\{StoreFaqRequest, UpdateFaqRequest};
use App\Repositories\Interfaces\RoleRepositoryInterface;


class FaqController extends DashboardController
{
   public function __construct(public FaqService $faqService, public RoleRepositoryInterface $roleRepository)
   {
       parent::__construct($faqService);
       $this->storeRequestClass = new StoreFaqRequest();
       $this->updateRequestClass = new UpdateFaqRequest();
       $this->indexView = 'faqs.index';
       $this->usePagination = true;
       $this->resourceTable = 'faqs';
   }

    public function getData()
    {
        return $this->faqService->getData();
   }
}
