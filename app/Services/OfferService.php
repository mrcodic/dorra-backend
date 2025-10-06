<?php

namespace App\Services;


use Illuminate\Http\JsonResponse;
use App\Repositories\Interfaces\{
    OfferRepositoryInterface,
};
use Yajra\DataTables\DataTables;


class OfferService extends BaseService
{
    public function __construct(
        OfferRepositoryInterface $repository,
    )
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();
        $offers = $this->repository
            ->query()
            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.\"{$locale}\"'))) LIKE ?", [
                        '%' . strtolower(request('search_value')) . '%'
                    ]);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })

            ->latest();

        return DataTables::of($offers)
            ->editColumn('start_at', function ($offer) {
                $offer->format('d/m/Y');
            })->editColumn('end_at', function ($offer) {
                $offer->format('d/m/Y');
            })->make();
    }


}
