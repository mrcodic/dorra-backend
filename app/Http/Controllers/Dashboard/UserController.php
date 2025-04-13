<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Models\CountryCode;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\{StoreUserRequest, UpdateUserRequest};
use App\Services\UserService;


class UserController extends DashboardController
{
    public function __construct(public UserService $userService, public CountryRepositoryInterface $countryRepository)
    {
        parent::__construct($userService);
        $this->storeRequestClass = new StoreUserRequest();
        $this->updateRequestClass = new UpdateUserRequest();
        $this->assoiciatedData['index'] = [
            'country_codes' => CountryCode::all(),
            'countries' => $this->countryRepository->all(),
            ];
        $this->relationsToStore = ['addresses'];
        $this->indexView = 'users.index';
        $this->createView = 'users.create';
        $this->editView = 'users.edit';
        $this->showView = 'users.show';
        $this->usePagination = true;
        $this->successMessage = 'Process success';
    }

    public function getData(): JsonResponse
    {
        return $this->userService->getData();
    }
}
