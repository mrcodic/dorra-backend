<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\CountryCode;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\View\{Factory, View};
use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Http\Requests\User\{StoreUserRequest, UpdateUserRequest};


class UserController extends DashboardController
{
    public function __construct(public UserService $userService, public CountryRepositoryInterface $countryRepository)
    {
        parent::__construct($userService);
        $this->storeRequestClass = new StoreUserRequest();
        $this->updateRequestClass = new UpdateUserRequest();
        $this->mergeSharedVariables = true;
        $this->assoiciatedData = [
            'shared' => [
                'country_codes' => CountryCode::all(),
                'countries' => $this->countryRepository->all(),
            ],
        ];

        $this->relationsToStore = ['addresses'];
        $this->indexView = 'users.index';
        $this->showView = 'users.show';
        $this->createView = 'users.create';
        $this->editView = 'users.edit';
        $this->usePagination = true;
    }

    public function getData(): JsonResponse
    {
        return $this->userService->getData();
    }

    public function billing(User $user): View|Factory|Application
    {
        $countries = $this->countryRepository->all(columns : ['id', 'name']);

        return view('dashboard.users.billing', get_defined_vars());
    }
}
