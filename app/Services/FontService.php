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

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $model = $this->repository->query()
            ->firstOrCreate(['id' => Arr::get($validatedData,'font_id')],$validatedData);
        $fontStyle = $model->fontStyles()->create([
            'name' => $validatedData['font_style_name'],
        ]);
        handleMediaUploads($validatedData['font_style_file'], $fontStyle);
        return $model->load('fontStyles');

    }


}
