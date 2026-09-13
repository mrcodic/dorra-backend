@php
use Illuminate\Support\Str;

$insideBundle = $insideBundle ?? false;

$product = $orderItem->orderable ?: $orderItem->itemable;

$isDesign = $orderItem->itemable && get_class($orderItem->itemable) === \App\Models\Design::class;
$isTemplate = $orderItem->itemable && get_class($orderItem->itemable) === \App\Models\Template::class;
$isDownload = $orderItem->type === \App\Enums\Item\TypeEnum::DOWNLOAD;

$orderItemPreview =
$orderItem->getFirstMediaUrl('order_item_mockups')
?: $orderItem->getFirstMediaUrl('order_item_previews');

$previewImage = match (true) {
filled($orderItemPreview) && ! $isDesign =>
$orderItemPreview,

$isDesign && ($orderItem->itemable->linked_to_mockup ?? false) =>
$orderItem->itemable->getFirstMediaUrl('front-mockup-designs')
?: $orderItem->itemable->getFirstMediaUrl('none-mockup-designs')
?: $orderItem->itemable->getFirstMediaUrl('back-mockup-designs'),

$isTemplate =>
$orderItem->itemable?->getFirstMediaUrl('templates-preview')
?: $orderItem->itemable?->getFirstMediaUrl('templates'),

$orderItem->itemable =>
$orderItem->itemable?->getFirstMediaUrl(
Str::plural(Str::lower(class_basename($orderItem->itemable)))
),

default => asset('images/default-product.png'),
};

$finalPrice = max(0, (float) $orderItem->sub_total - (float) $orderItem->discount_amount);
@endphp

<div class="{{ $insideBundle ? 'border rounded p-1 bg-white' : 'mb-1 border rounded p-1' }}">
    <div class="d-flex align-items-start justify-content-between">
        <div class="d-flex">
            <img
                src="{{ $previewImage ?: asset('images/default-product.png') }}"
                class="me-3 rounded"
                alt="Product"
                style="width: 60px; height: 60px; object-fit: cover;"
            >

            <div>
                <div class="fw-bold text-black fs-16">
                    {{ $product->name ?? 'No Product Found' }}

                    <span class="badge ms-1 {{ $isDownload ? 'bg-info' : 'bg-success' }}">
                        {{ $isDownload ? 'Download' : 'Print' }}
                    </span>

                    @if($orderItem->bundle_role)
                    <span class="badge ms-1 {{ $orderItem->bundle_role === 'reward' ? 'bg-light-success text-success' : 'bg-light-primary text-primary' }}">
                            {{ ucfirst($orderItem->bundle_role) }}
                        </span>
                    @endif
                </div>

                <div class="text-dark fs-5">
                    Qty: {{ $orderItem->quantity }}
                </div>

                @if($orderItem->color)
                <div class="text-dark small d-flex align-items-center mt-25">
                    <span class="me-1">Color:</span>

                    <span
                        class="rounded-circle border me-1"
                        style="
                                width: 16px;
                                height: 16px;
                                display: inline-block;
                                background-color: {{ $orderItem->color }};
                            "
                    ></span>

                    <span class="text-muted">{{ $orderItem->color }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="text-end">
            @if($orderItem->discount_amount > 0)
            <div class="text-muted text-decoration-line-through small">
                {{ number_format($orderItem->sub_total ?? 0, 2) }}
            </div>

            <div class="fw-bold text-success">
                {{ number_format($finalPrice, 2) }}
            </div>

            <div class="text-danger small">
                -{{ number_format($orderItem->discount_amount, 2) }}
            </div>
            @else
            <div class="fw-bold text-black">
                {{ number_format($orderItem->sub_total ?? 0, 2) }}
            </div>
            @endif
        </div>
    </div>

    {{-- Specs --}}
    @if($orderItem->relationLoaded('specs') && $orderItem->specs->isNotEmpty())
    <div class="mt-1">
        <div class="text-muted small mb-25">Specifications:</div>

        <div class="d-flex flex-wrap gap-50">
            @foreach($orderItem->specs as $spec)
            <span class="badge bg-light-secondary text-dark">
                        {{ $spec->spec_name }}: {{ $spec->option_name }}
                    </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Types / Download Design Area --}}
    @if($orderItem->itemable?->types)
    <div class="d-flex flex-column mt-1">
        <p style="color: #424746; margin: 0; font-size: 16px">Designs:</p>

        <div class="d-flex flex-wrap align-items-center gap-1 justify-content-between mt-50">
            @foreach($orderItem->itemable->types as $type)
            @php
            $itemable = $orderItem->itemable;
            $label = $type->value->label();

            $useTemplate = $isDesign && $itemable->template?->approach === 'without_editor';

            $downloadUrl = ($useTemplate ? $itemable->template : $itemable)
            ->getImageUrlForType($label);

            $coloredPreview = null;

            if ($orderItem->color) {
            $coloredPreview = $orderItem->getMedia('order_item_previews')
            ->first(fn ($m) => $m->getCustomProperty('type') == $label)
            ?->getUrl();
            }
            @endphp

            <div class="d-flex flex-column">
                <p style="margin: 0; color: #121212">{{ $label }} Design</p>

                <img
                    class="img-fluid rounded"
                    style="max-height: 200px"
                    src="{{ $downloadUrl }}"
                    alt="{{ $label }} item photo"
                >

                <a
                    href="{{ $downloadUrl }}"
                    download
                    target="_blank"
                    class="btn btn-sm btn-primary mt-2"
                >
                    <i data-feather="download" class="me-25"></i>
                    Download Design
                </a>

                @if($coloredPreview)
                <a
                    href="{{ $coloredPreview }}"
                    download
                    target="_blank"
                    class="btn btn-sm btn-outline-secondary mt-50 mb-2 d-flex align-items-center justify-content-center gap-50"
                >
                                <span
                                    class="rounded-circle border"
                                    style="width: 12px; height: 12px; display:inline-block; background-color: {{ $orderItem->color }}; flex-shrink:0;"
                                ></span>

                    <i data-feather="download" class="me-25"></i>
                    Download with Color
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    @if(
    $model->status == \App\Enums\Order\StatusEnum::CONFIRMED &&
    ($orderItem->orderable?->download_production_file || $orderItem->orderable?->category?->download_production_file)
    )
    <div class="d-flex gap-1 mt-2">
        <a
            href="{{ route('orders.order-items.production-file.download', ['orderItem' => $orderItem->id, 'variant' => 'original']) }}"
            class="btn btn-sm btn-outline-dark"
        >
            <i data-feather="printer" class="me-25"></i>
            Production File (Original)
        </a>

        @if($orderItem->color)
        <a
            href="{{ route('orders.order-items.production-file.download', ['orderItem' => $orderItem->id, 'variant' => 'colored']) }}"
            class="btn btn-sm btn-outline-dark"
        >
                        <span
                            class="rounded-circle border me-1"
                            style="width: 12px; height: 12px; display:inline-block; background-color: {{ $orderItem->color }};"
                        ></span>

            Production File (Colored)
        </a>
        @endif
    </div>
    @endif
    @endif
</div>
