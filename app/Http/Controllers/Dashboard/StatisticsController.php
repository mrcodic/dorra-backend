<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Order\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
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


        $topCategories = Category::selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, COUNT(*) AS c')
            ->whereYear('created_at', $year)
            ->groupBy('ym')
            ->orderByDesc('c')
            ->first();


        $topTemplates = Template::selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, COUNT(*) AS c')
            ->whereYear('created_at', $year)
            ->groupBy('ym')
            ->orderByDesc('c')
            ->first();
        $topPublished = Template::selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, COUNT(*) AS c')
            ->whereYear('created_at', $year)
            ->whereStatus(\App\Enums\Template\StatusEnum::PUBLISHED)
            ->groupBy('ym')
            ->orderByDesc('c')
            ->first();
        $topDraft = Template::selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, COUNT(*) AS c')
            ->whereYear('created_at', $year)
            ->whereStatus(\App\Enums\Template\StatusEnum::DRAFTED)
            ->groupBy('ym')
            ->orderByDesc('c')
            ->first();

        $bestMonths = [
            'orders' => $format($topOrders, 'c'),
            'orders_revenue' => $format($topRevenue, 'amount'),
            'orders_refunded' => $format($topRefunded, 'c'),
            'categories' => $format($topCategories, 'c'),
            'templates' => $format($topTemplates, 'c'),
            'published_templates' => $format($topPublished, 'c'),
            'draft_templates' => $format($topDraft, 'c'),
        ];

        return view('dashboard.index', compact('bestMonths'));
    }
}
