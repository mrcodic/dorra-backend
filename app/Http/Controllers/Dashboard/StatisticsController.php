<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Order\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Template;
use Carbon\Carbon;
use function Awssat\Visits\visits;


class StatisticsController extends Controller
{
    public function index()
    {


        $year = (int)request('year', now()->year);


        $format = function ($row, string $valueKey = 'c') {
            if (!$row) return null;


            $label = Carbon::createFromFormat('Y-m', $row->ym)->translatedFormat('F Y');

            return [
                'ym' => $row->ym,
                'label' => $label,
                'value' => (float)$row->{$valueKey},
            ];
        };

        $topOrders = Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, COUNT(*) AS c')
            ->whereYear('created_at', $year)
            ->groupBy('ym')
            ->orderByDesc('c')
            ->first();


        $topRevenue = Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, SUM(total_price) AS amount')
            ->whereYear('created_at', $year)
            ->groupBy('ym')
            ->orderByDesc('amount')
            ->first();


        $topRefunded = Order::status(StatusEnum::REFUNDED)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, COUNT(*) AS c')
            ->whereYear('created_at', $year)
            ->groupBy('ym')
            ->orderByDesc('c')
            ->first();


        $categories = Category::count();
        $products = Product::count();

        $topTemplates = Template::selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, COUNT(*) AS c')
            ->whereYear('created_at', $year)
            ->groupBy('ym')
            ->orderByDesc('c')
            ->first();
        $topPublished = Template::whereStatus(\App\Enums\Template\StatusEnum::PUBLISHED)
            ->count();
        $topDraft = Template::
            whereStatus(\App\Enums\Template\StatusEnum::DRAFTED)
            ->count();

        $bestMonths = [
            'orders' => $format($topOrders, 'c'),
            'orders_revenue' => $format($topRevenue, 'amount'),
            'orders_refunded' => $format($topRefunded, 'c'),
            'categories' => $categories,
            'products' => $products,
            'templates' => $format($topTemplates, 'c'),
            'published_templates' => $topPublished,
            'draft_templates' => $topDraft,
        ];

        return view('dashboard.index', compact('bestMonths'));
    }
}
