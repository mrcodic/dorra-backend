<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\HttpEnum;
use App\Models\CountryCode;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\View\{Factory, View};
use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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
        $this->methodRelations = [
            'show' => ['ownerTeams', 'reviews.reviewable', 'addresses.state.country','addresses.user','orders.orderItems'],
            'edit' => ['ownerTeams'],
        ];
        $this->relationsToStore = ['addresses'];
        $this->indexView = 'users.index';
        $this->showView = 'users.show';
        $this->createView = 'users.create';
        $this->editView = 'users.edit';
        $this->usePagination = true;
        $this->resourceTable = 'users';

    }

    public function getData(): JsonResponse
    {
        return $this->userService->getData();
    }

    public function changePassword(Request $request,$id)
    {
        $isChanged = $this->userService->changePassword($request,$id);
        return $isChanged ? Response::api() : Response::api(status:HttpEnum::NOT_MODIFIED ,message:"something went wrong",errors: [
            'password' => 'password not changed',
        ]);
    }
    public function billing(User $user): View|Factory|Application
    {
        $countries = $this->countryRepository->all(columns : ['id', 'name']);

        return view('dashboard.users.billing', get_defined_vars());
    }

    public function search(Request $request): JsonResponse
    {
        return Response::api(data: $this->userService->search($request));

    }
}

