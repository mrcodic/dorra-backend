<?php

namespace App\Services;


use App\Enums\Template\UnitEnum;
use App\Jobs\ProcessBase64Image;
use App\Jobs\RenderFabricJsonToPngJob;
use App\Models\Admin;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TemplateService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(TemplateRepositoryInterface $repository
        , public ProductRepositoryInterface                 $productRepository
    )
    {
        parent::__construct($repository);

    }

    public function checkProductType($request): bool
    {
        session(['product_type' => $request->input('product_type')]);
        $productType = session('product_type');
        $product = $this->productRepository
            ->query()
            ->where('name->en', $productType)
            ->first();
        if ($productType == 'other') {
            return true;
        }
        if (!$product) {
            return false;
        }
        return true;
    }

    /**
     * @throws ValidationException
     */
    public function checkProductTypeInEditor($request)
    {
        $productType = $request->product_type;
        $product = $this->productRepository
            ->query()
            ->where('name->en', $productType)
            ->first();
        if (!$product && $productType == 'T-shirt') {
            throw ValidationException::withMessages([
                'product_type' => 'You must create product called T-shirt to upload template for it'
            ]);
        } else {
            return $productType;
        }
    }

    public function getAll(
        $relations = [],
        bool $paginate = false,
        $columns = ['*'],
        $perPage = 16
    )
    {

        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;


        $query = $this->repository
            ->query(['id', 'name', 'product_id', 'status', 'created_at', 'type', 'height', 'width'])
            ->with(['product:id,name', 'product.tags'])
            ->when(request()->filled('search_value'), function ($q) {
                $locale = app()->getLocale();
                $q->where("name->{$locale}", 'LIKE', '%' . request('search_value') . '%');
            })
            ->when(request()->filled('product_id'), fn($q) => $q->whereProductId(request('product_id')))
            ->when(request()->filled('status'), fn($q) => $q->whereStatus(request('status')))
            ->latest();

        if (request()->ajax()) {
            return $pageSize === null
                ? $query->get()
                : $query->paginate($pageSize)->withQueryString();
        }

        return $this->repository->all(
            $paginate,
            $columns,
            $relations,
            filters: $this->filters,
            perPage: $pageSize ?? $perPage
        );
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData, $relationsToStore, $relationsToLoad) {


            if (($validatedData['product_type'] ?? null) === 'T-shirt') {
                $tShirtProduct = $this->productRepository
                    ->query()
                    ->where('name->en', $validatedData['product_type'])
                    ->first();
                if (!$tShirtProduct) {
                    throw ValidationException::withMessages(['product_type' => 'You must create product called T-shirt to upload template for it.']);
                }

                $validatedData['product_id'] = $tShirtProduct->id;

            } elseif (($validatedData['product_type'] ?? null) === 'other') {
                foreach (['width', 'height', 'unit', 'product_id'] as $field) {
                    if (empty($validatedData[$field])) {
                        throw ValidationException::withMessages(['product_type' => "{$field} is required when product_type is 'other'."]);

                    }
                }
            }

            if (empty($validatedData['height'])) {
                $validatedData['height'] = 650;
            }
            if (empty($validatedData['width'])) {
                $validatedData['width'] = 650;
            }
            if (empty($validatedData['unit'])) {
                $validatedData['unit'] = \App\Enums\Template\UnitEnum::PIXEL->value;
            }

            $model = $this->repository->create($validatedData);

            if (isset($validatedData['specifications'])) {
                $model->specifications()->attach($validatedData['specifications']);
            }

            if (isset($validatedData['base64_preview_image'])) {
                RenderFabricJsonToPngJob::dispatch($validatedData['base64_preview_image'], $model, 'templates');
            }

            return $model->refresh();
        });

        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }

        return $model->load($relationsToLoad);
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData, $id) {
            $model = $this->repository->update($validatedData, $id);
            if (isset($validatedData['specifications'])) {
                $model->specifications()->sync($validatedData['specifications']);
            }
            return $model;
        });
        if (isset($validatedData['base64_preview_image'])) {
            ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model);
        }
        /*if (isset($validatedData['design_data']))
      {
          RenderFabricJsonToPngJob::dispatch($validatedData['design_data'], $model, 'templates');
      }*/
        if (request()->allFiles()) {
            handleMediaUploads(request()->allFiles(), $model);
        }
        return $model->load($relationsToLoad);
    }

    public function getProductTemplates($productId)
    {
        $search = trim(request()->input('search'));
        $type = request()->input('type');
        $tags = array_filter((array)request()->input('tags'));
        $recent = request()->boolean('recent');

        return $this->repository->query()
            ->with('media')
            ->when($search, function ($query) use ($search) {
                $locale = app()->getLocale();
                $query->where("name->{$locale}", 'LIKE', "%{$search}%");
            })
            ->when($type !== null && $type !== '', function ($query) use ($type) {
                $query->whereType($type);
            })
            ->when(!empty($tags), function ($query) use ($tags) {
                $query->whereHas('product.tags', function ($q) use ($tags) {
                    $q->whereIn('tags.id', $tags);
                });
            })
            ->when($recent == true, function ($query) use ($recent) {
                $query->whereNotNull('updated_at')
                    ->orderByDesc('updated_at')
                    ->take(10);
            }, function ($query) {
                $query->oldest();
            })
            ->whereProductId($productId)
            ->paginate(10);
    }

    public function templateAssets()
    {
        return Media::query()
//            ->whereMorphedTo('model',auth($this->activeGuard)->user())
            ->whereCollectionName("template_assets")
            ->latest()
            ->paginate();
    }

    public function storeTemplateAssets($request)
    {
        $validated = $request->validate(["file" => "required|file|mimes:svg"]);
        return handleMediaUploads($validated['file'], Admin::find(1), "template_assets");
//        return handleMediaUploads($validated['file'],auth(getActiveGuard())->user(),"template_assets");

    }


}
