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
        $model = $this->repository->create($validatedData);
        collect($validatedData['font_styles'])->each(function ($style) use ($model, $validatedData) {
            $fontStyle = $model->fontStyles()->create([
                'name' => $style['name'],
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
            $fontStyle = $font->fontStyles()->find($style['id']);

            if ($fontStyle) {
                $fontStyle->update([
                    'name' => $style['name'],
                ]);

                if (!empty($style['file'])) {
                    handleMediaUploads($style['file'], $fontStyle, clearExisting: true);
                }
            }
        });
        return $font->load('fontStyles');
    }



}
