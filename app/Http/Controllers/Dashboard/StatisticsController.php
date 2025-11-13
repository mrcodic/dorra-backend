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
use function Awssat\Visits\visits;


class StatisticsController extends Controller
{
    public function index()
    {
        $year = (int) request('year', now()->year);

        // --- Sales: monthly revenue (SUM total_price)
        $salesRaw = Order::selectRaw('MONTH(created_at) AS m, SUM(total_price) AS amount')
            ->whereYear('created_at', $year)
            ->groupBy('m')
            ->pluck('amount', 'm')
            ->all();

        $salesMonthly = array_replace(array_fill_keys(range(1, 12), 0.0), $salesRaw);
        $salesTotal   = array_sum($salesMonthly);
        $salesMaxM    = array_search(max($salesMonthly), $salesMonthly) ?: 1;

        // if you want the "lowest non-zero", use the next 2 lines; otherwise keep min()
        $salesNZ      = array_filter($salesMonthly, fn ($v) => $v > 0);
        $salesMinM    = $salesNZ ? array_search(min($salesNZ), $salesMonthly) : array_search(min($salesMonthly), $salesMonthly);

        // --- Visits: monthly hits (SUM hits) using date OR created_at
        $visitsRaw = DB::table('visits')
            ->selectRaw('MONTH(COALESCE(`date`, `created_at`)) AS m, SUM(hits) AS hits')
            ->whereYear(DB::raw('COALESCE(`date`, `created_at`)'), $year)
            ->groupBy('m')
            ->pluck('hits', 'm')
            ->all();

        $visitsMonthly = array_replace(array_fill_keys(range(1, 12), 0), $visitsRaw);
        $visitsTotal   = array_sum($visitsMonthly);
        $visitsMaxM    = array_search(max($visitsMonthly), $visitsMonthly) ?: 1;

        $visitsNZ      = array_filter($visitsMonthly, fn ($v) => $v > 0);
        $visitsMinM    = $visitsNZ ? array_search(min($visitsNZ), $visitsMonthly) : array_search(min($visitsMonthly), $visitsMonthly);

        $monthName = function (int $y, int $m) {
            return Carbon::createFromDate($y, $m, 1)
                ->locale(app()->getLocale())
                ->translatedFormat('F');
        };

        $salesCard = [
            'total'   => $salesTotal,
            'year'    => $year,
            'highest' => $monthName($year, $salesMaxM),
            'lowest'  => $monthName($year, $salesMinM),
        ];

        $visitsCard = [
            'total'   => $visitsTotal,
            'year'    => $year,
            'highest' => $monthName($year, $visitsMaxM),
            'lowest'  => $monthName($year, $visitsMinM),
        ];

        return view('dashboard.index', compact('salesCard', 'visitsCard'));
    }

}
