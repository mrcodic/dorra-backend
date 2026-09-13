@php
    $orderItems = $model->orderItems ?? collect();

    $bundleGroups = $orderItems
        ->filter(fn ($item) => filled($item->bundle_group_key))
        ->groupBy('bundle_group_key');

    $normalItems = $orderItems
        ->filter(fn ($item) => blank($item->bundle_group_key))
        ->values();
@endphp

{{-- Bundle Groups --}}
@foreach($bundleGroups as $bundleGroupKey => $items)
    @php
        $firstItem = $items->first();
        $bundle = $firstItem?->bundle;

        $bundleSubTotal = (float) $items->sum('sub_total');
        $bundleDiscount = (float) $items->sum('discount_amount');
        $bundleTotal = max(0, $bundleSubTotal - $bundleDiscount);
    @endphp

    <div class="mb-2 border rounded p-1" style="border-color: #7367f0 !important; background-color: #fbfbff;">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1 mb-1">
            <div>
                <div class="fw-bold text-black fs-16">
                    <span class="badge bg-light-primary text-primary me-50">Bundle</span>
                    {{ $bundle?->name ?? 'Bundle #' . $firstItem?->bundle_id }}
                </div>

                <div class="text-muted small mt-25">
                    Group: {{ $bundleGroupKey }}
                </div>
            </div>

            <div class="text-end">
                @if($bundleDiscount > 0)
                    <div class="text-muted text-decoration-line-through small">
                        {{ number_format($bundleSubTotal, 2) }}
                    </div>

                    <div class="fw-bold text-success">
                        {{ number_format($bundleTotal, 2) }}
                    </div>

                    <div class="text-danger small">
                        -{{ number_format($bundleDiscount, 2) }}
                    </div>
                @else
                    <div class="fw-bold text-black">
                        {{ number_format($bundleSubTotal, 2) }}
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column gap-1">
            @foreach($items->values() as $orderItem)
                @include('dashboard.orders._order-item-card', [
                    'model' => $model,
                    'orderItem' => $orderItem,
                    'insideBundle' => true,
                ])
            @endforeach
        </div>
    </div>
@endforeach

{{-- Normal Items --}}
@foreach($normalItems as $orderItem)
    @include('dashboard.orders._order-item-card', [
        'model' => $model,
        'orderItem' => $orderItem,
        'insideBundle' => false,
    ])
@endforeach
