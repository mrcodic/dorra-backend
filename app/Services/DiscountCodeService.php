<?php

namespace App\Services;


use App\Exports\DiscountCodesExport;
use App\Repositories\Interfaces\DiscountCodeRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
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
            ->query(['id', 'code', 'type', 'max_usage', 'used', 'expired_at', 'scope','value'])
            ->with(['categories:id,name','products:id,name'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                $query->where('code', 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('created_at', request('created_at', 'desc'));

        return DataTables::of($discountCodes)
            ->editColumn('type', function ($discountCode) {
                return $discountCode->type->label();
            })
            ->editColumn('scope', function ($discountCode) {
                return $discountCode->scope->label();
            })
            ->addColumn('prefix', function ($discountCode) {
                return Str::substr($discountCode->code, 0, 4);
            })
            ->addColumn('expired_date', function ($discountCode) {
                return Carbon::parse($discountCode->expired_at)->format('m/d/Y');
            })->addColumn('action', function () {
                return [
                    'can_show' => (bool) auth()->user()->hasPermissionTo('discount-codes_show'),
                    'can_edit' => (bool) auth()->user()->hasPermissionTo('discount-codes_update'),
                    'can_delete' => (bool) auth()->user()->hasPermissionTo('discount-codes_delete'),
                ];
            })
            ->make();
    }

    public function generateAndExport($validatedData, $relationsToLoad = [])
    {
        $discountCodes = $this->storeResource($validatedData, relationsToLoad: $relationsToLoad);
        return Excel::download(new DiscountCodesExport($discountCodes), 'Discount Codes - Dorra Dashboard .xlsx');
    }


    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        $count = $validatedData['number_of_discount_codes'] ?? 1;
        return collect()->times($count, function () use ($validatedData, $relationsToStore, $relationsToLoad) {
            $discountCode = $this->repository->create($validatedData);
            if ($validatedData['scope'] == 2) {
                $discountCode->products()->attach($validatedData['product_ids']);
            } elseif($validatedData['scope'] == 1) {
                $discountCode->categories()->attach($validatedData['category_ids']);
            }
            return $discountCode->load($relationsToLoad)->refresh();
        });
    }


}
