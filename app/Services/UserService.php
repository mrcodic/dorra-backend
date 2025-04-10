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
        $users = $this->repository->query(['id', 'first_name' , 'last_name', 'email', 'status', 'created_at']);
        return DataTables::of($users)
            ->addColumn('name',function ($user){
                return $user->name;
            })
            ->addColumn('joined_date',function ($user){
                return $user->created_at?->format('j/n/Y');
            })
            ->addColumn('orders_count', function ($user) {
                return 5;
            })->make();
    }

}
