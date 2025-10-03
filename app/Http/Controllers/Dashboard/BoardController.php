<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Repositories\Interfaces\StationRepositoryInterface;


class BoardController extends Controller
{


    public function __invoke(StationRepositoryInterface $station)
    {
//        if (request()->wantsJson() || request()->boolean('json')) {
//            $stations = $station->query()
//                ->with('jobTickets')
//                ->get()
//                ->map(fn ($b) => [
//                    'id'    => (string) $b->id,
//                    'title' => e($b->name),
//                    'item'  => $b->jobTickets->map(fn ($it) => [
//                        'id'    => (string) $it->id,
//                        'title' => "<span class='kanban-text'>".e($it->code)."</span>",
//                    ])->all(),
//                ])->all();
//
//            return response()->json($stations);
//        }
        $stations = $station->query()
            ->with(['jobTickets.orderItem.orderable','jobTickets.currentStatus','jobTickets.station'])
            ->get();

        return view('dashboard.board', get_defined_vars());
    }

}
