<?php

namespace App\Services;


use Illuminate\Http\JsonResponse;
use App\Repositories\Interfaces\{CategoryRepositoryInterface, OfferRepositoryInterface, ProductRepositoryInterface};
use Yajra\DataTables\DataTables;


class OfferService extends BaseService
{
    public function __construct(
        OfferRepositoryInterface           $repository,
        public CategoryRepositoryInterface $categoryRepository,
        public ProductRepositoryInterface  $productRepository
    )
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $offers = $this->repository
            ->query()
            ->with('categories:id,name' , 'products:id,name')
            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                        '%' . strtolower(request('search_value')) . '%'
                    ]);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('type'), function ($query) {
                $query->whereType(request('type'));
            })
            ->latest();

        return DataTables::of($offers)
            ->addColumn('name', function ($offer) {
                return $offer->getTranslation('name', app()->getLocale());
            })
            ->editColumn('type', function ($offer) {
                return [
                    'value' => $offer->type->value,
                    'label' => $offer->type->label()
                ];
            })
            ->editColumn('start_at', function ($offer) {
                return $offer->start_at->format('d/m/Y');
            })->editColumn('end_at', function ($offer) {
                return $offer->end_at->format('d/m/Y');
            })->make();
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $offer = $this->repository->create($validatedData);

        if (!empty($validatedData['category_ids'])) {
            $categories = $this->categoryRepository->query()->whereIn('id', $validatedData['category_ids'])->get();
            collect($categories)->each(function ($category) use ($offer) {
                $category->offers()->attach($offer->id);
            });

        }

        if (!empty($validatedData['product_ids'])) {
            $products = $this->productRepository->query()->whereIn('id', $validatedData['product_ids'])->get();
            collect($products)->each(function ($product) use ($offer) {
                $product->offers()->attach($offer->id);
            });
        }
        return $offer;
    }

}
