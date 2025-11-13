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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        $year = (int)request('year', now()->year);

        $format = function ($row, string $valueKey = 'c') {
            if (!$row) return null;


            $label = Carbon::createFromFormat('m-Y', $row->ym)->translatedFormat('F Y');

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
            ->whereStatus(StatusEnum::DELIVERED)
            ->groupBy('ym');

        $topRevenue    = $monthlyRevenue->orderByDesc('amount')->first();
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


            'orders_revenue'      => $format($topRevenue, 'amount'),
            'orders_revenue_lowest' => $format($lowestRevenue, 'amount'),

            'orders_refunded'     => $format($topRefunded, 'c'),


            'categories'          => $categories,
            'products'            => $products,
            'templates'           => $topTemplates,
            'published_templates' => $topPublished,
            'live_templates'      => $topLive,
            'draft_templates'     => $topDraft,


            'visits'              => $format($topVisits, 'c'),
            'visits_lowest'       => $format($lowestVisits, 'c'),
        ];

        return view('dashboard.index', compact('bestMonths'));
    }
    public function chart(Request $request)
    {
        $year = (int) $request->query('year', now()->year);

        $payload = Cache::remember("dashboard.chart.$year", now()->addMinutes(10), function () use ($year) {
            $salesRaw = Order::whereYear('created_at', $year)
                ->whereStatus(StatusEnum::DELIVERED)
                ->selectRaw('MONTH(created_at) AS m, SUM(total_price) AS amount')
                ->groupBy('m')
                ->pluck('amount', 'm')
                ->all();

            $salesMonthly = array_replace(array_fill_keys(range(1, 12), 0.0), $salesRaw);

            $visitsRaw = DB::table('visits')
                ->selectRaw('MONTH(COALESCE(`date`, `created_at`)) AS m, SUM(hits) AS views')
                ->whereYear(DB::raw('COALESCE(`date`, `created_at`)'), $year)
                ->groupBy('m')
                ->pluck('views', 'm')
                ->all();

            $visitsMonthly = array_replace(array_fill_keys(range(1, 12), 0), $visitsRaw);

            $categories = array_map(
                fn ($m) => Carbon::createFromDate($year, $m, 1)->translatedFormat('M'),
                range(1, 12)
            );


            return [
                'year'       => $year,
                'categories' => $categories,
                'series'     => [
                    ['name' => 'Visits', 'data' => array_values($visitsMonthly)],
                    ['name' => 'Sales',  'data' => array_values($salesMonthly)],
                ],
            ];
        });
        return response()->json($payload);
    }
}
