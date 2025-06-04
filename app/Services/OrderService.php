<?php

namespace App\Services;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class OrderService extends BaseService
{
    public function __construct(OrderRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function storeStepData(array $stepData): void
    {
        $cacheKey = getOrderStepCacheKey();

        $existing = Cache::get($cacheKey, []);
        $merged = array_merge($existing, $stepData);

        Cache::put($cacheKey, $merged, now()->addHours(1));
    }

    public function storeStep1($request)
    {
        $request->validate(["user_id" => ["required","exists:users,id"]]);
        $this->storeStepData($request->only("user_id"));

    }
    public function storeStep2($request)
    {
        $request->validate(["product_id" => ["required","exists:products,id"]]);
        $this->storeStepData($request->only("product_id"));

    }




}
