<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Order\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Template;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $topOrders = Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym')
            ->whereYear('created_at', $year)
            ->groupBy('ym')
            ->first();



        $monthlyRevenue = Order::whereYear('created_at', $year)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, SUM(total_price) AS amount')
            ->groupBy('ym');

        $topRevenue    = DB::query()->fromSub($monthlyRevenue, 'm')->orderByDesc('amount')->first();
        $lowestRevenue = DB::query()->fromSub($monthlyRevenue, 'm')->orderBy('amount')->first();

        $monthlyVisits = Visit::whereYear(DB::raw('COALESCE(`date`, `created_at`)'), $year)
            ->selectRaw('DATE_FORMAT(COALESCE(`date`,`created_at`), "%Y-%m") AS ym, SUM(hits) AS c')
            ->groupBy('ym');

        $topVisits    = DB::query()->fromSub($monthlyVisits, 'v')->orderByDesc('c')->first();
        $lowestVisits = DB::query()->fromSub($monthlyVisits, 'v')->orderBy('c')->first();

        $topRefunded = Order::status(StatusEnum::REFUNDED)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS ym, COUNT(*) AS c')
            ->whereYear('created_at', $year)
            ->groupBy('ym')
            ->orderByDesc('c')
            ->first();


        $categories = Category::count();
        $products = Product::count();

        $topTemplates = Template::count();
        $topPublished = Template::whereStatus(\App\Enums\Template\StatusEnum::PUBLISHED)->count();
        $topLive = Template::whereStatus(\App\Enums\Template\StatusEnum::LIVE)->count();
        $topDraft = Template::
            whereStatus(\App\Enums\Template\StatusEnum::DRAFTED)
            ->count();

        $bestMonths = [
            'orders'              => $format($topOrders, 'c'),

            // Revenue extremes (keep existing key for highest; add *_lowest)
            'orders_revenue'      => $format($topRevenue, 'amount'),
            'orders_revenue_lowest' => $format($lowestRevenue, 'amount'),

            'orders_refunded'     => $format($topRefunded, 'c'),

            // Counts
            'categories'          => $categories,
            'products'            => $products,
            'templates'           => $topTemplates,
            'published_templates' => $topPublished,
            'live_templates'      => $topLive,
            'draft_templates'     => $topDraft,

            // Visits extremes (keep existing key for highest; add *_lowest)
            'visits'              => $format($topVisits, 'c'),
            'visits_lowest'       => $format($lowestVisits, 'c'),
        ];

        return view('dashboard.index', compact('bestMonths'));
    }
}
