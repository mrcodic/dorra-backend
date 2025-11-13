<?php

namespace App\Services;


use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use App\Repositories\Interfaces\{CartItemRepositoryInterface,
    CartRepositoryInterface,
    CategoryRepositoryInterface,
    OfferRepositoryInterface,
    ProductRepositoryInterface
};
use Illuminate\Support\Arr;
use Yajra\DataTables\DataTables;


class OfferService extends BaseService
{
    public function __construct(
        OfferRepositoryInterface           $repository,
        public CategoryRepositoryInterface $categoryRepository,
        public ProductRepositoryInterface  $productRepository,
        public CartItemRepositoryInterface $cartItemRepository,
        public CartRepositoryInterface     $cartRepository,
    )
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $offers = $this->repository
            ->query()
            ->with('categories:id,name', 'products:id,name')
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
            })->addColumn('name_translate', function ($offer) {
                return $offer->getTranslations('name');
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
            })->addColumn('action', function () {
                return [
                    'can_show' => (bool)auth()->user()->can('offers_show'),
                    'can_edit' => (bool)auth()->user()->can('offers_update'),
                    'can_delete' => (bool)auth()->user()->can('offers_delete'),
                ];
            })->make();
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $offer = $this->repository->create($validatedData);

        if (!empty($validatedData['category_ids'])) {
            $this->handleTransaction(function () use ($offer, $validatedData) {
                $categories = $this->categoryRepository->query()->whereIn('id', $validatedData['category_ids'])->get();
                collect($categories)->each(function ($category) use ($offer) {
                    $category->offers()->attach($offer->id);
                });
                $cartIds = $this->cartItemRepository->query()
                    ->where('cartable_type', Category::class)
                    ->whereIn('cartable_id', $validatedData['category_ids'])
                    ->pluck('cart_id');
                dd($cartIds);
                $this->cartRepository->query()->whereIn('id', $cartIds)->update(['discount_amount' => 0, 'discount_code_id' => null]);
            });


        }

        if (!empty($validatedData['product_ids'])) {
            $this->handleTransaction(function () use ($offer, $validatedData) {
                $products = $this->productRepository->query()->whereIn('id', $validatedData['product_ids'])->get();
                collect($products)->each(function ($product) use ($offer) {
                    $product->offers()->attach($offer->id);
                });
                $cartIds = $this->cartItemRepository->query()
                    ->where('cartable_type', Product::class)
                    ->whereIn('cartable_id', $validatedData['product_ids'])
                    ->pluck('cart_id');
                $this->cartRepository->query()->whereIn('id', $cartIds)->update(['discount_amount' => 0, 'discount_code_id' => null]);
            });

        }
        return $offer;
    }


    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {

        $offer = $this->repository->update($validatedData, $id);

        $type = (int)Arr::get($validatedData, 'type', 0);
        $categoryIds = Arr::get($validatedData, 'category_ids', null);
        $productIds = Arr::get($validatedData, 'product_ids', null);

        $categoryIds = is_array($categoryIds) ? $categoryIds : ($categoryIds === null ? null : (array)$categoryIds);
        $productIds = is_array($productIds) ? $productIds : ($productIds === null ? null : (array)$productIds);

        if ($type === 1) {
            $offer->categories()->sync($categoryIds ?? []);
            $offer->products()->sync([]);
        } elseif ($type === 2) {
            $offer->products()->sync($productIds ?? []);
            $offer->categories()->sync([]);
        } else {
            $offer->categories()->sync([]);
            $offer->products()->sync([]);
        }


        return $offer;
    }


}
