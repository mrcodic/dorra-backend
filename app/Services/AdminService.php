<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class AdminService extends BaseService
{

    public function __construct(AdminRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
       $model->assignRole(Role::findById($validatedData['role_id']));
       if (isset($validatedData['image'])) {
           handleMediaUploads($validatedData['image'], $model);
       }

        return $model->load($relationsToLoad);
    }

    public function getData()
    {
        $admins = $this->repository
            ->query(['id', 'first_name', 'last_name', 'email','phone_number', 'status', 'created_at'])
            ->with(['roles','media'])
            ->when(request()->filled('search_value'), function ($query) {
                $search = request('search_value');
                $words = preg_split('/\s+/', $search);
                $query->where(function ($query) use ($words) {
                    foreach ($words as $word) {
                        $query->where(function ($q) use ($word) {
                            $q->where('first_name', 'like', '%' . $word . '%')
                                ->orWhere('last_name', 'like', '%' . $word . '%');
                        });
                    }
                });
            })
            ->when(request()->filled('role_id'), function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('id', request('role_id'));
                });
            })
            ->when(request()->filled('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->orderBy('created_at', request('created_at', 'desc'));

        return DataTables::of($admins)
            ->addColumn('name', function ($admin) {
                return $admin->first_name . ' ' . $admin->last_name;
            })
            ->editColumn('created_at', function ($admin) {
                return $admin->created_at->format('d/m/Y') ;
            })
            ->editColumn('status', function ($admin) {
                return $admin->status == 1 ? "active" : "blocked";
            })
            ->addColumn('status_value', function ($admin) {
                return $admin->status;
            })
            ->make();
    }

}
