<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;


class StatisticsController extends Controller
{
  public function index()
  {
      $totalOrders = Order::count();
      $totalOrdersPries = Order::sum('total_price');
      return view('dashboard.index',get_defined_vars());
  }
}
