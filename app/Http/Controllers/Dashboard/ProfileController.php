<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Models\CountryCode;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\Services\AdminService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;

class ProfileController extends Controller
{
    public function index(): View|Factory|Application
    {
        $countryCodes = CountryCode::all();
        return view('dashboard.profile.show', get_defined_vars());
    }

    public function update(UpdateAdminRequest $request,$id,AdminRepositoryInterface $adminRepository)
    {
        $admin = $adminRepository->update($request->validated(),$id);

        return Response::api(data: AdminResource::make($admin));
    }
}
