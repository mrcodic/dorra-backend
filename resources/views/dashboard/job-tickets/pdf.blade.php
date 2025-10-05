<!doctype html>
<html lang="{{ app()->getLocale() }}" @if(app()->getLocale()==='ar') dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#121212; }
        .row { display: flex; gap: 10px; }
        .col { display: flex; flex-direction: column; gap: 8px; }
        .badge { display:inline-block; padding:4px 8px; border-radius:6px; background:#222; color:#fff; }
        .img { max-width: 100%; height: auto; }
        .box { border:1px solid #ddd; border-radius:6px; padding:8px; }
        .table { width:100%; border-collapse: collapse; }
        .table th, .table td { border:1px solid #ccc; padding:6px; text-align:left; }
        .title { font-size: 18px; font-weight: 700; }
        .sub { color:#555; font-size: 10px; }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>
<div class="row">
    {{-- Left --}}
    <div class="col" style="flex:1">
        <span class="badge">{{ $model->code }}</span>

        @if($model->orderItem?->orderable)
            <img class="img" src="{{ $model->orderItem->orderable->getMainImageUrl() ?: asset('/images/item-photo.png') }}" alt="item">
        @endif

        @if($model->jobEvents->last()?->admin)
            <div class="box no-break">
                <strong>Last Operator</strong>
                <div class="row" style="align-items:flex-start;">
                    <img class="img" style="max-width:60px"
                         src="{{ $model->jobEvents->last()?->admin?->image?->getUrl() ?: asset('/images/admin-avatar.png') }}">
                    <div class="col" style="gap:2px">
                        <div>{{ $model->jobEvents->last()?->admin?->name }}</div>
                        <div class="sub">{{ $model->jobEvents->last()?->admin?->roles->first()?->name }}</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="box no-break">
            <strong>Order</strong>
            <div>#: {{ $model->orderItem->order->order_number }}</div>
        </div>

        @if($model->orderItem->itemable?->types)
            <div class="box no-break">
                <strong>Designs</strong>
                <div class="row" style="flex-wrap:wrap;">
                    @foreach($model->orderItem->itemable->types as $type)

                        <div class="col" style="flex:0 0 48%;">
                            <div class="sub">{{ $type->value->label() }} Design</div>


                                <img class="img" style="max-height:160px" src="{{ $model->orderItem->itemable->getImageUrlForType($type->value->label()) }}" alt="design">

                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Right --}}
    <div class="col" style="flex:1.4">
        <div class="title">{{ $model->orderItem->orderable?->name }}</div>
        <div class="sub">{{ $model->orderItem->orderable?->description }}</div>

        <div class="grid2">
            <div class="box"><b>Station:</b> {{ $model->station?->name }}</div>
            <div class="box"><b>Status:</b>  {{ $model->currentStatus?->name }}</div>
        </div>

        <div class="grid2">
            <div class="box"><b>Priority:</b> {{ $model->priority?->label() }}</div>
            <div class="box"><b>Due:</b> {{ $model->due_at?->format('d/m/Y') }}</div>
        </div>

        <div class="box no-break">
            <strong>Code</strong>
            <img class="img" src="{{ $model->barcode_png_url }}" alt="Code128">
        </div>

        @php
            $specsRaw = $model->specs ?? [];
            $specs = is_array($specsRaw) ? $specsRaw : (json_decode($specsRaw ?? '[]', true) ?? []);
        @endphp

        <div class="box no-break">
            <strong>Specifications</strong>
            @if(!empty($specs))
                <table class="table">
                    <thead><tr><th>Specification</th><th>Option</th></tr></thead>
                    <tbody>
                    @foreach($specs as $row)
                        <tr>
                            <th>{{ \Illuminate\Support\Str::headline((string)($row['spec_name'] ?? '')) }}</th>
                            <td>{{ $row['option_name'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="sub">No specifications.</div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
