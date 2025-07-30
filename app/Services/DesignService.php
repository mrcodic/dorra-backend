<?php

namespace App\Services;


use App\Jobs\ProcessBase64Image;

use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Implementations\ProductSpecificationOptionRepository;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\GuestRepositoryInterface;
use App\Repositories\Interfaces\TeamRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;


class DesignService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(
        DesignRepositoryInterface                   $repository,
        public TemplateRepositoryInterface          $templateRepository,
        public ProductSpecificationOptionRepository $optionRepository,
        public UserRepositoryInterface              $userRepository,
        public GuestRepositoryInterface             $guestRepository,
        public TeamRepositoryInterface                 $teamRepository,

    )
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        if (!empty($validatedData['template_id'])) {
            $design = $this->handleTransaction(function () use ($validatedData) {
                if (!empty($validatedData['user_id'])) {
                    $design = $this->repository->query()->firstOrCreate(
                        ['template_id' => $validatedData['template_id'],
                            'user_id' => $validatedData['user_id']]
                        , $validatedData);
                }
                if (!empty($validatedData['guest_id'])) {
                    $design = $this->repository->query()->firstOrCreate(
                        ['template_id' => $validatedData['template_id'],
                            'guest_id' => $validatedData['guest_id']]
                        , $validatedData);
                }

                $this->templateRepository
                    ->find($validatedData['template_id'])
                    ->getMedia('templates')
                    ->last()
                    ?->copy($design, 'designs');


                return $design->load([
                    'product.prices',
                    'media',
                    'template:id',
                    'product.specifications.options',
                ]);
            });

        } else {
            $design = $this->repository->query()->create($validatedData);
        }
        if ($validatedData['user_id']) {
            $design->users()->attach(
                $this->userRepository->find($validatedData['user_id'])
            );
        }


        return $design->load([
            'media',
            'product.prices',
            'template:id',
            'product.specifications.options',
        ]);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        if (isset($validatedData['base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model);
        }
        return $model->load($relationsToLoad);
    }


    public function getDesigns()
    {
        $userId = auth('sanctum')->id();
        $cookieValue = request()->cookie('cookie_id');
        $guestId = null;

        if (!$userId && $cookieValue) {
            $guestId = $this->guestRepository->query()
                ->where('cookie_value', $cookieValue)
                ->value('id');
        }

        if (!$userId && !$guestId) {
            return new LengthAwarePaginator(
                collect([]),
                0,
                10,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );
        }

        $query = $this->repository->query()
            ->with([
                'product.category:id,name',
                'product.saves:id',
                'saves' => fn($q) => $q->where('user_id', $userId),
                'owner:id,first_name,last_name',
                'template:id,name,description',
                'template.products.saves',
            ])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->when(request()->filled('owner_id'), fn($q) => $q->where('user_id', request('owner_id')))
            ->when(request()->filled('category_id'), fn($q) =>
            $q->whereHas('product.category', fn($cat) =>
            $cat->where('id', request('category_id'))
            )
            )
            ->orderBy('created_at', request('date', 'desc'));

        $shouldPaginate = filter_var(request('paginate', true), FILTER_VALIDATE_BOOLEAN);

        return $shouldPaginate
            ? $query->paginate()
            : $query->get();
    }


    public function assignToTeam($designId, $teamId)
    {
        $design = $this->repository->find($designId);
        $team = $this->teamRepository->find($teamId);
        $design->teams()->attach($team);
    }
    public function getDesignVersions($designId)
    {
        return $this->repository->find($designId)->versions()->paginate();
    }

    public function designFinalization($request)
    {
        $validatedData = $request->validated();


        return $this->handleTransaction(function () use ($validatedData) {
            $this->repository->update($validatedData, $validatedData['design_id']);
            $design = $this->repository->query()->find($validatedData['design_id']);
            if (!empty($validatedData['specs'])) {
                $syncData = collect($validatedData['specs'])->mapWithKeys(function ($spec) {
                    return [
                        $spec['id'] => ['spec_option_id' => $spec['option']]
                    ];
                })->toArray();

                $design->specifications()->sync($syncData);

                $optionTotal = collect($validatedData['specs'])
                    ->map(function ($spec) {
                        return $this->optionRepository->find($spec['option'])->price;
                    })
                    ->sum();
            }

            $productPrice = optional($design->productPrice)->price;
            $subTotal = $optionTotal ?? 0 + ($productPrice ?? ($design->product->base_price * $design->quantity));
            return [
                'sub_total' => $subTotal,
                'quantity' => $design->productPrice?->quantity ?? $design->quantity,
                'syncData' => $design->specifications->load(['options']),
            ];
        });
    }

    public function owners()
    {

        return auth('sanctum')->user()
            ->designs()
            ->with('owner:id,first_name,last_name')
            ->get()
            ->pluck('owner')
            ->filter()
            ->unique('id')
            ->values();

    }

    public function bulkForceResources($ids)
    {
        return $this->repository->query()->withTrashed()->whereIn('id', $ids)->forceDelete();
    }

    public function trash()
    {
        return $this->repository->query()
            ->onlyTrashed()
            ->with(['product.category', 'owner'])
            ->whereHas('users', function ($query) {
                $query->where('user_id', auth('sanctum')->id());
            })
            ->get();
    }

}
