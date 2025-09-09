<?php

namespace App\Services;


use App\Models\Role;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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
        if (Arr::get($validatedData, 'role_id')) {
            $model->roles()->attach($validatedData['role_id'], [
                'model_type' => get_class($model)
            ]);

        }
        if (isset($validatedData['image_id'])) {
            Media::where('id', $validatedData['image_id'])
                ->update([
                    'model_type' => get_class($model),
                    'model_id'   => $model->id,
                    'collection_name' => 'admins',
                ]);
        }

        return $model->load($relationsToLoad);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        if (empty($validatedData['password'])) {
            unset($validatedData['password'], $validatedData['password_confirmation']);
        }
        $model = $this->repository->update($validatedData, $id);


        if (!empty($validatedData['role_id'])) {
            $role = Role::find($validatedData['role_id']);
            if ($role) {
                $model->syncRoles([$role]);
            }
        }



        if (empty($validatedData['image_id'])) {
            $model->clearMediaCollection('admins');

        }

        if (isset($validatedData['image_id'])) {
            Media::where('id', $validatedData['image_id'])
                ->update([
                    'model_type' => get_class($model),
                    'model_id'   => $model->id,
                    'collection_name' => 'admins',
                ]);
        }

        return $model;
    }


    public function getData()
    {
        $admins = $this->repository
            ->query(['id', 'first_name', 'last_name', 'email', 'phone_number', 'status', 'created_at'])
            ->with(['roles', 'media'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
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
                } else {
                    $query->whereRaw('1 = 0');
                }
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
                return $admin->created_at->format('d/m/Y');
            })
            ->addColumn('role', function ($admin) {
                return $admin->roles->first()?->getTranslation('name',app()->getLocale()) ?? '-';
            })
            ->editColumn('status', function ($admin) {
                return $admin->status == 1 ? "active" : "blocked";
            })
            ->addColumn('image', function ($admin) {
                return $admin->getFirstMediaUrl('admins') ?: asset("images/default-user.png");
            })->addColumn('image_id', function ($admin) {
                return $admin->getFirstMedia('admins')?->id;
            })
            ->addColumn('status_value', function ($admin) {
                return $admin->status;
            })
            ->make();
    }

}
