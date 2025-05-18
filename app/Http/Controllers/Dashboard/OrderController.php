<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\OrderService;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};
use App\Services\AdminService;


class OrderController extends DashboardController
{
   public function __construct(OrderService $orderService,
                               public CategoryRepositoryInterface $categoryRepository,
                               public TagRepositoryInterface $tagRepository,)
   {
       parent::__construct($orderService);
       $this->storeRequestClass = new StoreAdminRequest();
       $this->updateRequestClass = new UpdateAdminRequest();
       $this->indexView = 'orders.index';
       $this->createView = 'orders.create';
       $this->editView = 'orders.edit';
       $this->showView = 'orders.show';
       $this->usePagination = true;
       $this->assoiciatedData = [
           'create' => [
               'categories' => $this->categoryRepository->query(['id','name'])->whereNull('parent_id')->get(),
               'tags' => $this->tagRepository->all(columns: ['id','name']),
           ],
       ];
   }
}
