<?php

namespace App\Services;



use App\Jobs\ProcessBase64Image;
use App\Models\Admin;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
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

    public function getAll(
        $relations = [],
        bool $paginate = false,
        $columns = ['*'],
        $perPage = 16
    )
    {
        request('with_design_data',true);

        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;


        $query = $this->repository
            ->query()
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
        if (request()->expectsJson()) {
            return $paginate ? $query
                ->whereNotNull('design_data')
                ->when(request('category_id'), fn($q) => $q->whereHas('products', fn($q) => $q->whereCategoryId(request('category_id'))))
                ->paginate($requested)
                : $query->get();
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
            $model = $this->repository->create($validatedData);
            $model->products()->sync($validatedData['product_ids']);
            $this->convertBase64ToImageLink($validatedData, $model);
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
            if (!empty($validatedData['product_ids']))
            {
                $model->products()->sync($validatedData['product_ids']);

            }
            return $model;
        });
       $this->convertBase64ToImageLink($validatedData, $model);
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
            ->with(['media','products'])
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
            ->when(!is_null($productId),function ($query) use ($productId) {
                $query->whereProductId($productId);
            })
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

    /**
     * @param $validatedData
     * @param $type
     * @param $model
     * @return string
     * @throws \Exception
     */
    public function convertBase64ToImageLink($validatedData, $model)
    {
        if (isset($validatedData['base64_preview_image'])) {
            if (preg_match('/^data:image\/(\w+);base64,/', $validatedData['base64_preview_image'], $type)) {
                $imageData = substr($validatedData['base64_preview_image'], strpos($validatedData['base64_preview_image'], ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    throw new \Exception('Invalid image type');
                }

                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    throw new \Exception('base64_decode failed');
                }
            } else {
                throw new \Exception('Invalid base64 format');
            }

            $tempDir = storage_path('app/tmp_uploads');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempFilePath = $tempDir . '/' . uniqid('preview_') . '.' . $type;

            if (file_put_contents($tempFilePath, $imageData) === false) {
                throw new \Exception('Failed to write temp file');
            }
            $model->clearMediaCollection('templates');
            $model->addMedia($tempFilePath)
                ->toMediaCollection('templates');

            @unlink($tempFilePath);
//                ProcessBase64Image::dispatch($validatedData['base64_preview_image'], $model);
        }

    }


}
