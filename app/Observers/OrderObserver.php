<?php

namespace App\Observers;

use App\Enums\Order\StatusEnum;
use App\Models\Admin;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
public function storeResource($validatedData = [], $relationsToStore = [], $relationsToLoad = [])
{
    $cacheKey = getOrderStepCacheKey();
    $orderStepData = Cache::get($cacheKey, []);
    
    if (empty($orderStepData)) {
        throw new \Exception('Order step data not found in cache');
    }

    // Prepare order data from cached step data
    $orderData = [
        'user_id' => $orderStepData['user_info']['id'] ?? null,
        'product_id' => $orderStepData['product_id'] ?? null,
        'design_id' => $orderStepData['design_info']['id'] ?? null,
        'price_id' => $orderStepData['price_id'] ?? null,
        'quantity' => $orderStepData['pricing_details']['quantity'] ?? 1,
        'sub_total' => $orderStepData['pricing_details']['sub_total'] ?? 0,
        'total' => $orderStepData['pricing_details']['total'] ?? $orderStepData['pricing_details']['sub_total'] ?? 0,
        'discount_amount' => $orderStepData['pricing_details']['discount_amount'] ?? 0,
        'discount_code_id' => $orderStepData['pricing_details']['discount_code_id'] ?? null,
        'shipping_address_id' => $orderStepData['shipping_info']['id'] ?? null,
        'status' => StatusEnum::PENDING, // Default status, will be updated by observer
        'notes' => $orderStepData['personal_info']['notes'] ?? null,
        'special_instructions' => $orderStepData['personal_info']['special_instructions'] ?? null,
    ];

    // Merge with any additional validated data
    $orderData = array_merge($orderData, $validatedData);

    // Create the order
    $order = $this->repository->create($orderData);

    // Handle specification options if they exist
    if (isset($orderStepData['specs']) && !empty($orderStepData['specs'])) {
        $this->attachSpecificationOptions($order, $orderStepData['specs']);
    }

    // Store any additional relations
    if (!empty($relationsToStore)) {
        foreach ($relationsToStore as $relation => $data) {
            $order->$relation()->create($data);
        }
    }

    // Load specified relations
    if (!empty($relationsToLoad)) {
        $order->load($relationsToLoad);
    }

    // Clear the cache after successful order creation
    Cache::forget($cacheKey);

    return $order;
}

/**
 * Attach specification options to the order
 */
private function attachSpecificationOptions($order, $specs)
{
    $specData = [];
    
    foreach ($specs as $spec) {
        foreach ($spec['options'] as $optionId) {
            $option = $this->specificationOptionRepository->find($optionId);
            $specData[$optionId] = [
                'specification_id' => $spec['id'],
                'price' => $option->price ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    if (!empty($specData)) {
        $order->specificationOptions()->attach($specData);
    }
}

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
