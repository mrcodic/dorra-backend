<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;

class ProfileController extends Controller
{
  public function index(): View|Factory|Application
  {
    return view('dashboard.profile.show');
  }
}
