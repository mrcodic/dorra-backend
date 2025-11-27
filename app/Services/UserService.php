<?php

namespace App\Services;

use App\Jobs\SendSmsMessageJob;
use App\Services\SMS\SmsInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rules\Password;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        if (isset($validatedData['image_id'])) {
            Media::where('id', $validatedData['image_id'])
                ->update([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'collection_name' => 'users',
                ]);
        }
        return $model->load($relationsToLoad);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        if (isset($validatedData['image_id'])) {
            $model->getMedia('users')
                ->where('id', '!=', $validatedData['image_id'])
                ->each->delete();

            Media::where('id', $validatedData['image_id'])
                ->update([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'collection_name' => 'users',
                ]);

        }

        return $model;
    }

    public function getData(): JsonResponse
    {
        $users = $this->repository
            ->query(['id', 'first_name', 'last_name', 'email', 'status', 'created_at'])
            ->withCount('orders')
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
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })->latest();
        return DataTables::of($users)
            ->addColumn('name', function ($user) {
                return $user->name;
            })
            ->addColumn('image', function ($admin) {
                return $admin->getFirstMediaUrl('users') ?: asset("images/default-user.png");
            })
            ->addColumn('joined_date', function ($user) {
                return $user->created_at?->format('j/n/Y');
            })
            ->addColumn('action', function () {
                return [
                    'can_show' => (bool)auth()->user()->hasPermissionTo('users_show'),
                    'can_edit' => (bool)auth()->user()->hasPermissionTo('users_update'),
                    'can_delete' => (bool)auth()->user()->hasPermissionTo('users_delete'),
                ];
            })
            ->make();
    }

    public function getCampaignData(): JsonResponse
    {
        $users = $this->repository
            ->query(['id', 'first_name', 'last_name', 'phone_number'])
           ->latest();
        return DataTables::of($users)
            ->addColumn('name', function ($user) {
                return $user->name;
            })
            ->addColumn('image', function ($admin) {
                return $admin->getFirstMediaUrl('users') ?: asset("images/default-user.png");
            })
            ->make();
    }

    public function sendSms($validatedData): void
    {
        dd("DSfsd");
        $users = $this->repository->query()
            ->whereIn('id', $validatedData['numbers'])
            ->get();
        $sms = app(SmsInterface::class);
        $chunkSize = 20;
        $delaySeconds = 2;
        $chunks = $users->chunk($chunkSize);
        foreach ($chunks as $index => $chunk) {

            $numbers = $chunk->pluck('phone_number')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
            Log::info("numbers: " . json_encode($numbers));

            if (empty($numbers)) {
                continue;
            }

dump($numbers);
            $sms->send($numbers, $validatedData['message'], ['language' => 1]);
            if ($index < $chunks->count() - 1) {
                sleep($delaySeconds);
            }
//        SendSmsMessageJob::dispatch($users, $validatedData['message']);
        }
    }
    public function changePassword($request, $id): bool
    {
        $request->validate([
            'password' => ['required', 'string', 'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols(),],
        ]);
        $user = $this->repository->find($id);
        $user = $user->update([
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
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'image_url' => $user->image?->getUrl() ?? asset("images/default-user.png"),
            ]);
    }

    public function campaigns()
    {
        return $this->repository->query()
            ->select(['first_name', 'last_name', 'phone_number'])
            ->get();
    }
}
