<?php

namespace App\Services;

use App\Enums\Permission\PermissionAction;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PermissionService extends BaseService
{
    public function __construct(PermissionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        return collect(PermissionAction::values())->each(function ($item) use ($validatedData) {
            return $this->repository->create([
                'group' => $validatedData['group'],
                'name' => $validatedData['group']['en'] . $item,
            ]);

        });

    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $search = request('search_value');
        $order = request('created_at', 'desc');

        // Step 1: Get latest permission IDs per unique translated group
        $latestPermissionIds = DB::table('permissions')
            ->select(DB::raw('MAX(id) as id'))
            ->when($search, function ($query) use ($locale, $search) {
                $query->where("group->{$locale}", 'LIKE', "%{$search}%");
            })
            ->groupBy(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(`group`, '$.\"$locale\"'))"))
            ->pluck('id');

        // Step 2: Get full permission records with roles
        $permissions = $this->repository
            ->query(['id', 'group', 'created_at'])
            ->with('roles')
            ->whereIn('id', $latestPermissionIds)
            ->orderBy('created_at', $order);

        // Step 3: Return DataTables response
        return DataTables::of($permissions)
            ->editColumn('created_at', function ($permission) {
                return $permission->created_at->format('d/m/Y');
            })
            ->make(true);
    }



}
