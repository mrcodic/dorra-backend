<?php

namespace App\Services;


use App\Enums\Mockup\TypeEnum;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Repositories\Interfaces\MockupRepositoryInterface;
use App\Services\Mockup\MockupRenderer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MockupService extends BaseService
{
    public BaseRepositoryInterface $repository;

    public function __construct(MockupRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }


    public function getAll(
        $relations = [], bool $paginate = false, $columns = ['*'], $perPage = 16, $counts = [])
    {

        $requested = request('per_page', $perPage);
        $pageSize = $requested === 'all' ? null : (int)$requested;

        $query = $this->repository
            ->query()
            ->with(['category:id,name'])
            ->when(request()->filled('search_value'), function ($q) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $q->where('name', 'LIKE', '%' . request('search_value') . '%');
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->when(request()->filled('color'),function ($query){

            })
            ->when(request()->filled('product_id'), fn($q) => $q->whereCategoryId(request('product_id')))
            ->when(request()->filled('template_id'), fn($q) => $q->whereHas('templates',function ($query){
                $query->where('templates.id',request('template_id'));
            }))
            ->when(request()->filled('product_ids'), fn($q) => $q->whereIn('category_id', request()->array('product_ids')))
            ->when(request()->filled('type'), fn($q) => $q->whereHas('types', fn($q) => $q->where('types.id',request('type'))))
            ->when(request()->filled('search'), function ($q) {
                $q->where('name', 'like', '%' . request('search') . '%');
            })
            ->latest();

        if (request()->ajax()) {
            return $pageSize === null
                ? $query->get()
                : $query->paginate($pageSize)->withQueryString();
        }
        if (request()->expectsJson()) {
            return $query->paginate($pageSize);
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
//        $model = $this->handleTransaction(function () use ($validatedData) {


            $model = $this->repository->create($validatedData);
            $model->types()->attach(Arr::get($validatedData, 'types') ?? []);
            $templatesInput = collect(Arr::get($validatedData, 'templates', []));

            if ($templatesInput->isNotEmpty()) {
                $templateIds = $templatesInput->pluck('template_id')->all();
                $model->templates()->attach($templateIds);
                $model->load('templates');
                $templatesById = $templatesInput->keyBy('template_id');
                $rows = [];
                $typeMap = [
                    'front' => 1,
                    'back'  => 2,
                    'none'  => 3,
                ];

                foreach ($model->templates as $template) {
                    collect($model->types)
                        ->mapWithKeys(function ($type) use ($model, $template) {

                            $sideName = strtolower($type->value->name);

                            $baseMedia = $model->getMedia('mockups')
                                ->first(fn ($m) => $m->getCustomProperty('side') === $sideName &&
                                    $m->getCustomProperty('role') === 'base');

                            $maskMedia = $model->getMedia('mockups')
                                ->first(fn ($m) => $m->getCustomProperty('side') === $sideName &&
                                    $m->getCustomProperty('role') === 'mask');

                            if (!$baseMedia || !$maskMedia) {
                                return [$sideName => null];
                            }
                            $designMedia = $type == TypeEnum::BACK
                                ? $template->getFirstMedia('back_templates')
                                : $template->getFirstMedia('templates');

                            if (! $designMedia || ! $designMedia->getPath()) {
                                throw new \Exception("Missing design media for {$sideName}");
                            }
                            $binary = (new MockupRenderer())->render([
                                'base_path'   => $baseMedia->getPath(),
                                'shirt_path'  => $maskMedia->getPath(),
                                'design_path' =>$designMedia->getPath(),
                            ]);

                            $model
                                ->addMediaFromString($binary)
                                ->usingFileName("mockup_{$sideName}.png")
                                ->withCustomProperties([
                                    'side' => $sideName,
                                    'template_id' => $template->id,
                                ])
                                ->toMediaCollection('generated_mockups');


                        });

                    $input = $templatesById->get($template->id);
                    if (!$input) {
                        continue;
                    }

                    $pivotId = $template->pivot->id; // from withPivot('id')

                    foreach ($typeMap as $field => $typeValue) {
                        // e.g. if "front" exists in the request for this template
                        if (!empty($input[$field])) {
                            $rows[] = [
                                'mockup_template_id' => $pivotId,
                                'position_id'        => $input[$field],  // e.g. "1"
                                'template_type'      => $typeValue,      // e.g. 1 = front
                                'created_at'         => now(),
                                'updated_at'         => now(),
                            ];
                        }
                    }
                }

                if (!empty($rows)) {
                    DB::table('mockup_position_template')->insert($rows);
                }
            }

            return $model;
//        });

        return $this->handleFiles($model);
    }


    /**
     * @param mixed $model
     * @return mixed
     */
    public function handleFiles(mixed $model, $clearExisting = false): mixed
    {
        if (request()->allFiles()) {
            $types = collect(request()->input('types', []));
            $mediaTypes = collect(['base_image', 'mask_image']);
            $types->each(function ($type) use ($mediaTypes, $model, $clearExisting) {
                $sideName = strtolower(TypeEnum::from($type)->name);
                $mediaTypes->each(function ($mediaType) use ($sideName, $type, $model, $clearExisting) {
                    $inputName = "{$sideName}_{$mediaType}";

                    if (request()->hasFile($inputName)) {
                        $customProperties = [
                            'side' => $sideName,
                            'role' => str_contains($mediaType, 'base') ? 'base' : 'mask',
                        ];

                        handleMediaUploads(
                            request()->file($inputName),
                            $model,
                            customProperties: $customProperties,
                            clearExisting: $clearExisting);
                    }
                });
            });
        }
        return $model;
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->handleTransaction(function () use ($validatedData, $id, $relationsToLoad) {
            $model = $this->repository->update($validatedData, $id);
            $model->types()->sync(Arr::get($validatedData, 'types') ?? []);
            return $model;
        });

        return $this->handleFiles($model, true);
    }

    public function deleteResource($id)
    {
        $model = $this->repository->find($id);
        if ($model->hasMedia()) {
            clearMediaCollections($model);
        }
        $model->types()->newPivotStatement()
            ->where('typeable_id', (string)$model->id)
            ->where('typeable_type', get_class($model))
            ->delete();

        return $model->delete();
    }

    public function bulkDeleteResources($ids)
    {
        return $this->handleTransaction(function () use ($ids) {
            $models = $this->repository->query()->whereIn('id', $ids)->get();

            $models->each(function ($model) {

                $model->types()->detach();

                if ($model->hasMedia()) {
                    clearMediaCollections($model);
                }
            });
            return $this->repository->query()->whereIn('id', $ids)->delete();
        });


    }


    public function showAndUpdateRecent($id)
    {
        $mockup = $this->repository->find($id);
        return $mockup->load('types');
//        return auth('web')->user()->recentMockups()->syncWithoutDetaching([$mockup->id]);
    }

    public function destroyRecentMockup($id)
    {
        return auth('web')->user()->recentMockups()->detach($id);
    }

    public function recentMockups()
    {
        return auth('web')->user()->recentMockups()->take(5)->get();
    }
}
