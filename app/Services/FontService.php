<?php

namespace App\Services;

use App\Repositories\Interfaces\FontRepositoryInterface;
use Illuminate\Support\Arr;

class FontService extends BaseService
{

    public function __construct(FontRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }
    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10, $counts = [])
    {
        $styleName = request('style_name');
        $query = $this->repository->query()
            ->select($columns)
            ->with($relations)
            ->when(request()->filled('style_name'),function ($query) use ($styleName){
                $query ->whereHas('fontStyles', function ($query) use ($styleName) {
                    $query->where('name', 'like', '%' . $styleName . '%');
                });
            })
            ->withCount($counts);
        return $paginate ? $query->paginate($perPage, $columns) : $query->get($columns);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->create($validatedData);
        collect($validatedData['font_styles'])->each(function ($style) use ($model, $validatedData) {
            $fontStyle = $model->fontStyles()->create([
                'name' => $style['name'],
                'style_value' => $style['style_value'],
            ]);
            handleMediaUploads($style['file'], $fontStyle);
        });
        return $model->load('fontStyles');
    }

    public function update($validatedData, $font)
    {
        $font->update([
            'name' => $validatedData['name'],
        ]);

        collect($validatedData['font_styles'])->each(function ($style) use ($font) {

            $fontStyle =    $font->fontStyles()->updateOrCreate([
                'id' => Arr::get($style, 'id'),
            ], [
                'name' => $style['name'],
                'style_value' => $style['style_value'],
            ]);

            if (!empty($style['file'])) {
                handleMediaUploads($style['file'], $fontStyle, clearExisting: true);
            }

        });
        return $font->load('fontStyles');
    }


}
