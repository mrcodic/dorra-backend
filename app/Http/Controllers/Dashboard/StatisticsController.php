<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Order\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Template;


class StatisticsController extends Controller
{
  public function index()
  {
      $totalOrders = Order::count();
      $refundedOrders = Order::status(StatusEnum::REFUNDED)->count();
      $totalOrdersPries = Order::sum('total_price');
      $categoriesCount = Category::count();
      $templatesCount = Template::count();
      return view('dashboard.index',get_defined_vars());
  }
}
