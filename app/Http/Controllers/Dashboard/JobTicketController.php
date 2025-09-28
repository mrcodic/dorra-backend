<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\StationRepositoryInterface;


class JobTicketController extends Controller
{


    public function __invoke(StationRepositoryInterface $station)
    {
        if (request()->wantsJson() || request()->boolean('json')) {
            $stations = $station->query()
                ->with('jobTickets')
                ->get()
                ->map(fn ($b) => [
                    'id'    => (string) $b->id,
                    'title' => e($b->name),
                    'item'  => $b->jobTickets->map(fn ($it) => [
                        'id'    => (string) $it->id,
                        'title' => "<span class='kanban-text'>".e($it->code)."</span>",
                    ])->all(),
                ])->all();

            return response()->json($stations);
        }

        return view('dashboard.board', get_defined_vars());
    }

}
