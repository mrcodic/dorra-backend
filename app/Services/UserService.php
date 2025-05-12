<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;
use App\Repositories\{Interfaces\UserRepositoryInterface, Base\BaseRepositoryInterface};

class UserService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
        parent::__construct($repository);

    }

    public function getData(): JsonResponse
    {
        $users = $this->repository
            ->query(['id', 'first_name', 'last_name', 'email', 'status', 'created_at'])
            ->when(request()->filled('search_value'), function ($query) {
                $search = request('search_value');
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                });
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })->latest();
        return DataTables::of($users)
            ->addColumn('name', function ($user) {
                return $user->name;
            })
            ->addColumn('joined_date', function ($user) {
                return $user->created_at?->format('j/n/Y');
            })
            ->addColumn('orders_count', function ($user) {
                return 0;
            })->make();
    }

}
