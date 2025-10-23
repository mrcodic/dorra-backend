<?php

namespace App\Services;


use Yajra\DataTables\DataTables;
use Illuminate\Http\{JsonResponse, Request};
use App\Repositories\Interfaces\{InventoryRepositoryInterface,};

class InventoryService extends BaseService
{
    public function __construct(InventoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $inventories = $this->repository
            ->query()
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                    $query->where("name", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })
            ->whereNull('parent_id')
            ->latest();
        return DataTables::of($inventories)
            ->addColumn('available_places_count', function ($inventory) {
                return $inventory->children()->available()->count();
            })
            ->addColumn('action', function () {
                return [
                    'can_show' => (bool) auth()->user()->hasPermissionTo('discount-codes_show'),
                    'can_delete' => (bool) auth()->user()->hasPermissionTo('discount-codes_delete'),
                ];
            })
            ->make(true);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($validatedData, $relationsToStore, $relationsToLoad) {
            $inventory = $this->repository->create($validatedData);
            $now = now();
            $rows = [];
            for ($i = 1; $i <= $validatedData['number']; $i++) {
                $rows[] = [
                    'name' => $validatedData['name'] . $i,
                    'number' => $i,
                    'parent_id' => $inventory->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->repository->query()->insert($rows);


            return $inventory;
        });
    }

    public function availablePlaces($id)
    {
        return $this->repository->query()
            ->where('parent_id', $id)
            ->available()
            ->get();
    }
}
