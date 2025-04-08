<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\User\{StoreCategoryRequest, UpdateCategoryRequest};
use App\Services\UserService;


class UserController extends DashboardController
{
   public function __construct(UserService $userService)
   {
       parent::__construct($userService);
       $this->storeRequestClass = new StoreCategoryRequest();
       $this->updateRequestClass = new UpdateCategoryRequest();
       $this->indexView = 'users.index';
       $this->createView = 'users.create';
       $this->editView = 'users.edit';
       $this->showView = 'users.show';
       $this->usePagination = true;
       $this->successMessage = 'Process success';
   }
}
