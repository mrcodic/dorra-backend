<?php

namespace App\Services;


use App\Jobs\ProcessBase64Image;
use App\Jobs\RenderFabricJsonToPngJob;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Implementations\ProductSpecificationOptionRepository;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;


class DesignService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(
        DesignRepositoryInterface                   $repository,
        public TemplateRepositoryInterface          $templateRepository,
        public ProductSpecificationOptionRepository $optionRepository,
    )
    {
        parent::__construct($repository);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        if (!empty($validatedData['template_id'])) {
            $design = $this->handleTransaction(function () use ($validatedData) {
                $design = $this->repository->query()->firstOrCreate(['template_id' => $validatedData['template_id'],
                    'user_id' => $validatedData['user_id']], $validatedData);
                $this->templateRepository
                    ->find($validatedData['template_id'])
                    ->getFirstMedia('templates')
                    ->copy($design, 'designs');

                return $design;
            });

        } else {
            $design = $this->repository->query()->create($validatedData);
            RenderFabricJsonToPngJob::dispatch($validatedData['design_data'], $design, 'designs');
        }
        return $design->load($relationsToLoad);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);
        RenderFabricJsonToPngJob::dispatch($validatedData['design_data'], $model, 'designs');
        if (isset($validatedData['base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model);
        }
        return $model->load($relationsToLoad);
    }

    public function getDesigns()
    {
        $cookieId = request()->cookie('cookie_id');
        $userId = auth('sanctum')->id();
        if ($userId || $cookieId) {
            $designs = $this->repository->query()
                ->with('product')
                ->where(function ($q) use ($cookieId, $userId) {
                    if ($userId) {
                        $q->whereUserId($userId);
                    } elseif ($cookieId) {
                        $q->whereCookieId($cookieId);
                    }
                })->latest()->paginate();
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


            $productPrice = optional($design->productPrice)->price;
            $subTotal = $optionTotal + ($productPrice ?? ($design->product->base_price * $design->quantity));

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
            return $design->update($request->only(['product_price_id']));
        } else {
            return $design->update($request->only(['quantity']));
        }
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
        return $design->product->prices->pluck('quantity','id')->toArray();
    }
}
