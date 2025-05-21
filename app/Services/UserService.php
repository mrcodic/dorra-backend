<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rules\Password;
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

    public function changePassword($request, $id): bool
    {
        $request->validate([
            'password' => ['required', 'string','confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),],
        ]);
        $user = $this->repository->find($id);
        $user =  $user->update([
            'password' => $request->password,
        ]);
        return (bool)$user;
    }

    public function search($request)
    {
        $search = request('search');
        $all = $request->boolean('all');

        return $this->repository->query(['id', 'first_name', 'last_name'])
            ->when(filled($search), function ($query) use ($search) {
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
            ->limit($all ? 100 : 5)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'image_url' => $user->image?->getUrl() ?? asset("images/default-user.png"),
            ]);
    }


}
