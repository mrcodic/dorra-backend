<?php

namespace App\Services;


use App\Jobs\ProcessBase64Image;
use App\Jobs\RenderFabricJsonToPngJob;
use App\Models\CartItem;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Implementations\ProductSpecificationOptionRepository;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\GuestRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;


class DesignService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(
        DesignRepositoryInterface                   $repository,
        public TemplateRepositoryInterface          $templateRepository,
        public ProductSpecificationOptionRepository $optionRepository,
        public UserRepositoryInterface              $userRepository,
        public GuestRepositoryInterface             $guestRepository,
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
                    ->getFirstMedia('templates')
                    ->copy($design, 'designs');

                return $design->load([
                    'product.prices',
                    'media',
                    'directProduct.prices',
                    'template:id',
                    'template.specifications.options',
                ]);
            });

        } else {
            $design = $this->repository->query()->create($validatedData);
//            RenderFabricJsonToPngJob::dispatch($validatedData['design_data'], $design, 'designs');
        }
        if ($validatedData['user_id']) {
            $design->users()->attach(
                $this->userRepository->find($validatedData['user_id'])
            );
        }


        return $design->load([
            'media',
            'directProduct.prices',
            'product.prices',
            'template:id',
            'template.specifications.options',
        ]);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
//        RenderFabricJsonToPngJob::dispatch($validatedData['design_data'], $model, 'designs');
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
            $guest = $this->guestRepository->query()
                ->where('cookie_value', $cookieValue)
                ->first();
            $guestId = $guest?->id;
        }

        if ($userId || $guestId) {
            $designs = $this->repository->query()
                ->with([
                    'product.category' => fn($q) => $q->select('id', 'name'),
                    'product.saves' => fn($q) => $q->select('id'),
                    'saves' => function ($query) {
                        $query->where('user_id', auth('sanctum')->id());
                    },
                    'owner' => fn($q) => $q->select('id', 'first_name', 'last_name'),
                    'template' => fn($q) => $q->select('id', 'name', 'description'),
                ])
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->when(!$userId && $guestId, fn($q) => $q->where('guest_id', $guestId))
                ->when(request()->filled('owner_id'), fn($q) => $q->where('user_id', request('owner_id'))
                )
                ->when(request()->filled('category_id'), fn($q) => $q->whereHas('product.category', fn($query) => $query->where('id', request('category_id'))
                )
                )
                ->orderBy('created_at', request('date', 'desc'))
                ->paginate();
        }

        return $designs ?? new LengthAwarePaginator(
            collect([]),
            0,
            10,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
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


    public function addQuantity($request, $id)
    {
        $design = $this->repository->find($id);

        if ($design->product->has_custom_prices) {
            $updated = $design->update($request->only(['product_price_id']));
        } else {
            $updated = $design->update($request->only(['quantity']));
        }

        if ($design->cartItems->isNotEmpty()) {
            collect($design->cartItems)->each(function ($cart) use ($design) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('design_id', $design->id)
                    ->first();

                if ($cartItem) {
                    $cartItem->update([
                        'sub_total' => $design->total_price
                    ]);
                }
            });
        }

        return $updated;
    }

    public function priceDetails($designId): array
    {
        $design = $this->repository->find($designId);
        return [
            'design' => $design,
            'specs' => $design->specifications->load(['options'])
        ];
    }

    public function getQuantities($designId)
    {
        $design = $this->repository->find($designId);
        return $design->product->prices->pluck('quantity', 'id')->toArray();
    }

    public function owners()
    {

        return auth('sanctum')->user()
            ->designs()
            ->with('owner:id,first_name,last_name')
            ->get()
            ->pluck('owner')
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
