@php
    $aiCategory = $model ?? null;

    $selectedGenerationType = old(
        'generation_type',
        $aiCategory?->generation_type?->value ?? \App\Enums\Ai\AiGenerationTypeEnum::IMAGE->value
    );

    $settings = old('settings', $aiCategory?->settings ?? []);
@endphp

<div class="row">
    <div class="col-md-6 mb-1">
        <label class="form-label">Product</label>

        <select name="category_id" class="form-select">
            <option value="">Select Product</option>

            @foreach($associatedData['categories'] as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', $aiCategory?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-1">
        <label class="form-label">Default Resolution</label>

        <input type="text"
               name="default_resolution"
               value="{{ old('default_resolution', $aiCategory?->default_resolution) }}"
               class="form-control"
               placeholder="1024x1024">
    </div>

    <div class="col-md-4 mb-1">
        <label class="form-label">
            Aspect Ratio
        </label>

        <select
            name="aspect_ratio"
            class="form-select"
        >
            <option value="">
                Default
            </option>

            <option
                value="1:1"
                @selected($selectedAspectRatio === '1:1')
            >
                1:1
            </option>

            <option
                value="4:5"
                @selected($selectedAspectRatio === '4:5')
            >
                4:5
            </option>

            <option
                value="3:4"
                @selected($selectedAspectRatio === '3:4')
            >
                3:4
            </option>

            <option
                value="16:9"
                @selected($selectedAspectRatio === '16:9')
            >
                16:9
            </option>

            <option
                value="9:16"
                @selected($selectedAspectRatio === '9:16')
            >
                9:16
            </option>
        </select>
    </div>


    <div class="col-md-4 mb-1">
        <label class="form-label">Credits Cost</label>

        <input type="number"
               name="credits_cost"
               min="0"
               value="{{ old('credits_cost', $aiCategory?->credits_cost ?? 1) }}"
               class="form-control">
    </div>
    <div class="col-md-4 mb-1">
        <label class="form-label">Orientation</label>

        <select name="settings[orientation]" class="form-select">
            <option value="">Default</option>

            @foreach(['square', 'portrait', 'landscape'] as $orientation)
                <option value="{{ $orientation }}"
                    @selected(($settings['orientation'] ?? null) === $orientation)>
                    {{ ucfirst($orientation) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-1">
        <label class="form-label">Sort Order</label>

        <input type="number"
               name="sort_order"
               min="0"
               value="{{ old('sort_order', $aiCategory?->sort_order ?? 0) }}"
               class="form-control">
    </div>
</div>

<div class="d-flex flex-wrap gap-3 mt-1">
    <div class="form-check form-switch">
        <input type="hidden" name="enabled" value="0">

        <input type="checkbox"
               name="enabled"
               id="enabled"
               value="1"
               class="form-check-input"
            @checked(old('enabled', $aiCategory?->enabled ?? true))>

        <label for="enabled" class="form-check-label">
            AI Enabled
        </label>
    </div>

    <div class="form-check form-switch">
        <input type="hidden"
               name="settings[transparent_background]"
               value="0">

        <input type="checkbox"
               name="settings[transparent_background]"
               id="transparent-background"
               value="1"
               class="form-check-input"
            @checked($settings['transparent_background'] ?? false)>

        <label for="transparent-background" class="form-check-label">
            Transparent Background
        </label>
    </div>

    <div class="form-check form-switch">
        <input type="hidden"
               name="settings[print_ready]"
               value="0">

        <input type="checkbox"
               name="settings[print_ready]"
               id="print-ready"
               value="1"
               class="form-check-input"
            @checked($settings['print_ready'] ?? true)>

        <label for="print-ready" class="form-check-label">
            Print Ready
        </label>
    </div>
</div>
