<?php

namespace App\Services;


use App\Repositories\Interfaces\DiscountCodeRepositoryInterface;
use Yajra\DataTables\Facades\DataTables;

class DiscountCodeService extends BaseService
{

    public function __construct(DiscountCodeRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }
    public function getData()
    {
        $discountCodes = $this->repository
            ->query(['id', 'code', 'type', 'max_usage', 'used', 'expired_at', 'scope'])
            ->when(request()->filled('search_value'), function ($query) {
                $search = request('search_value');
                $query->where('code', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', request('created_at', 'desc'));

        return DataTables::of($discountCodes)
            ->editColumn('type', function ($discountCode) {
                return $discountCode->type->label();
            })->make();
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $discountCode = $this->repository->create($validatedData);
        if($validatedData['scope'] == 1){
            $discountCode->products()->attach($validatedData['product_ids']);
        }else{
            $discountCode->categories()->attach($validatedData['category_ids']);
        }

        return $discountCode->load($relationsToLoad);
    }



}
