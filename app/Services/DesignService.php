<?php

namespace App\Services;


use App\Jobs\ProcessBase64Image;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Implementations\ProductSpecificationOptionRepository;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
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
        public TeamRepositoryInterface              $teamRepository,
        public CategoryRepositoryInterface          $categoryRepository,

    )
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        dd($validatedData);
        if (!empty($validatedData['template_id'])) {
            $design = $this->handleTransaction(function () use ($validatedData) {
                if (!empty($validatedData['user_id'])) {
                    $design = $this->repository->query()->create(
                        ['template_id' => $validatedData['template_id'],
                            'user_id' => $validatedData['user_id']]
                        , $validatedData);
                }
                if (!empty($validatedData['guest_id'])) {
                    $design = $this->repository->query()->create(
                        ['template_id' => $validatedData['template_id'],
                            'guest_id' => $validatedData['guest_id']]
                        , $validatedData);
                }

                $this->templateRepository
                    ->find($validatedData['template_id'])
                    ->getMedia('templates')
                    ->last()
                    ?->copy($design, 'designs');

                $this->templateRepository
                    ->find($validatedData['template_id'])
                    ->getMedia('back_templates')
                    ->last()
                    ?->copy($design, 'back_designs');


                return $design->load([
                    'designable.prices',
                    'media',
                    'template:id',
                    'designable.specifications.options',
                    'specifications.options',
                    'productPrice'
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
        if (isset($validatedData['specs'])) {
            collect($validatedData['specs'])->each(function ($spec) use ($design) {
                $design->specifications()->attach([$design->id => [
                    'product_spec_id' => $spec['id'],
                    'option_id' => $spec['option'],
                ]]);
            });
        }

        return $design->load([
            'media',
            'designable.prices',
            'template:id',
            'designable.specifications.options',
            'specifications.options',
            'productPrice'
        ]);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        if (isset($validatedData['specs'])) {
            collect($validatedData['specs'])->each(function ($spec) use ($model) {
                $model->specifications()->syncWithoutDetaching([$model->id => [
                    'product_spec_id' => $spec['id'],
                    'option_id' => $spec['option'],
                ]]);
            });
        }
        if (isset($validatedData['base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model, 'designs');
        }
        if (isset($validatedData['back_base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['back_base64_preview_image'], $model, 'back_designs');
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
        $category = $this->categoryRepository->query()->find(request('category_id'));
        $query = $this->repository->query()
            ->with([
                'designable',
                'dimension',
                'productPrice',
                'specifications.options',
                'saves' => fn($q) => $q->where('user_id', $userId),
                'owner:id,first_name,last_name',
                'template:id,name,description,orientation',
                'template.products.saves',
            ])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
            ->when(request()->filled('owner_id'), fn($q) => $q->where('user_id', request('owner_id')))
            ->when(request()->filled('category_id'), fn($q) => $q->whereIn('designable_id', $category?->is_has_category ?
                $category?->products->pluck('id') :
                [request('category_id')])
                ->where('designable_type', $category?->is_has_category ? Product::class : Category::class))
            ->orderBy('created_at', request('date', 'desc'));

        $shouldPaginate = filter_var(request('paginate', true), FILTER_VALIDATE_BOOLEAN);

        return $shouldPaginate
            ? $query->paginate()
            : $query->get();
    }


    public function assignToTeam($designId)
    {
        $design = $this->repository->find($designId);
        $design->teams()->syncWithoutDetaching(request()->teams);
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
            $subTotal = $optionTotal ?? 0 + ($productPrice ?? ($design->designable->base_price * $design->quantity));
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
            ->with(['designable', 'owner'])
            ->whereHas('users', function ($query) {
                $query->where('user_id', auth('sanctum')->id());
            })
            ->get();
    }

}
