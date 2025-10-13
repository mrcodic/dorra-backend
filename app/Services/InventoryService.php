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
            ->whereNull('parent_id')
            ->latest();
        return DataTables::of($inventories)
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
        dd( $this->repository->query()
            ->where('parent_id', $id)->available()->get());
        return $this->repository->query()
            ->where('parent_id', $id)
            ->available()
            ->get();
    }
}
