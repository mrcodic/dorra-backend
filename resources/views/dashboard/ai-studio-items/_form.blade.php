@php
    $studioItem = $model ?? null;
    $isEdit = (bool) $studioItem;

    $selectedGenerationType = old(
        'generation_type',
        $studioItem?->generation_type?->value
            ?? $studioItem?->generation_type
    );

      $selectedResolution = old(
        'default_resolution',
        $studioItem?->default_resolution ?? '1024x1024'
    );

    $selectedAspectRatio = old(
        'aspect_ratio',
        $studioItem?->aspect_ratio
    );
@endphp

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css"
>

<form
    id="studio-item-form"
    action="{{ $isEdit
        ? route('ai-studio-items.update', $studioItem->id)
        : route('ai-studio-items.store') }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Name English *
            </label>

            <input
                type="text"
                name="name[en]"
                class="form-control"
                value="{{ old(
                    'name.en',
                    $isEdit
                        ? $studioItem->getTranslation('name', 'en')
                        : ''
                ) }}"
            >
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Name Arabic *
            </label>

            <input
                type="text"
                name="name[ar]"
                class="form-control"
                value="{{ old(
                    'name.ar',
                    $isEdit
                        ? $studioItem->getTranslation('name', 'ar')
                        : ''
                ) }}"
            >
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Description English
            </label>

            <textarea
                name="description[en]"
                class="form-control"
                rows="3"
            >{{ old(
                'description.en',
                $isEdit
                    ? $studioItem->getTranslation('description', 'en')
                    : ''
            ) }}</textarea>
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Description Arabic
            </label>

            <textarea
                name="description[ar]"
                class="form-control"
                rows="3"
            >{{ old(
                'description.ar',
                $isEdit
                    ? $studioItem->getTranslation('description', 'ar')
                    : ''
            ) }}</textarea>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Generation Type *
            </label>

            <select
                name="generation_type"
                class="form-select"
            >
                <option value="">
                    Select Type
                </option>

                @foreach(\App\Enums\Ai\AiGenerationTypeEnum::cases() as $type)
                    <option
                        value="{{ $type->value }}"
                        @selected(
                            $selectedGenerationType === $type->value
                        )
                    >
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Default Resolution
            </label>

            <select
                name="default_resolution"
                class="form-select"
            >
                <option value="">
                    Select Resolution
                </option>

                <option
                    value="512x512"
                    @selected($selectedResolution === '512x512')
                >
                    512x512
                </option>

                <option
                    value="768x768"
                    @selected($selectedResolution === '768x768')
                >
                    768x768
                </option>

                <option
                    value="1024x1024"
                    @selected($selectedResolution === '1024x1024')
                >
                    1024x1024
                </option>
            </select>
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
            <label class="form-label">
                Credits Cost *
            </label>

            <input
                type="number"
                name="credits_cost"
                min="0"
                class="form-control"
                value="{{ old(
                    'credits_cost',
                    $studioItem?->credits_cost ?? 1
                ) }}"
            >
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Sort Order
            </label>

            <input
                type="number"
                name="sort_order"
                min="0"
                class="form-control"
                value="{{ old(
                    'sort_order',
                    $studioItem?->sort_order ?? 0
                ) }}"
            >
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label">
                Image
            </label>

            <input
                type="file"
                name="image"
                class="form-control"
                accept="image/*"
            >

            @if(
                $isEdit
                && $studioItem->getFirstMediaUrl('image')
            )
                <div class="mt-1">
                    <img
                        src="{{ $studioItem->getFirstMediaUrl('image') }}"
                        width="120"
                        height="120"
                        class="rounded border"
                        style="object-fit:cover"
                    >
                </div>
            @endif
        </div>

        <div class="col-md-6 mb-1">
            <label class="form-label d-block">
                Status
            </label>

            <input
                type="hidden"
                name="is_active"
                value="0"
            >

            <div class="form-check form-switch">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    id="is-active"
                    class="form-check-input"
                    @checked(
                        old(
                            'is_active',
                            $studioItem?->is_active ?? true
                        )
                    )
                >

                <label
                    for="is-active"
                    class="form-check-label"
                >
                    Active
                </label>
            </div>
        </div>

        <div class="col-12">
            <hr>

            <h5 class="mb-1">
                Generation Settings
            </h5>
        </div>

        @php
            $settings = old(
                'settings',
                $studioItem?->settings ?? []
            );

            $orientation = data_get(
                $settings,
                'orientation'
            );

            $transparentBackground = (bool) data_get(
                $settings,
                'transparent_background',
                false
            );

            $printReady = (bool) data_get(
                $settings,
                'print_ready',
                false
            );
        @endphp

        <div class="col-md-4 mb-1">
            <label class="form-label">
                Orientation
            </label>

            <select
                name="settings[orientation]"
                class="form-select"
            >
                <option value="">
                    Default
                </option>

                <option
                    value="square"
                    @selected($orientation === 'square')
                >
                    Square
                </option>

                <option
                    value="portrait"
                    @selected($orientation === 'portrait')
                >
                    Portrait
                </option>

                <option
                    value="landscape"
                    @selected($orientation === 'landscape')
                >
                    Landscape
                </option>
            </select>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label d-block">
                Transparent Background
            </label>

            <input
                type="hidden"
                name="settings[transparent_background]"
                value="0"
            >

            <div class="form-check form-switch">
                <input
                    type="checkbox"
                    name="settings[transparent_background]"
                    value="1"
                    id="transparent-background"
                    class="form-check-input"
                    @checked($transparentBackground)
                >

                <label
                    for="transparent-background"
                    class="form-check-label"
                >
                    Enable
                </label>
            </div>
        </div>

        <div class="col-md-4 mb-1">
            <label class="form-label d-block">
                Print Ready
            </label>

            <input
                type="hidden"
                name="settings[print_ready]"
                value="0"
            >

            <div class="form-check form-switch">
                <input
                    type="checkbox"
                    name="settings[print_ready]"
                    value="1"
                    id="print-ready"
                    class="form-check-input"
                    @checked($printReady)
                >

                <label
                    for="print-ready"
                    class="form-check-label"
                >
                    Enable
                </label>
            </div>
        </div>

    </div>

    <div class="d-flex justify-content-end gap-1 mt-2">
        <a
            href="{{ route('ai-studio-items.index') }}"
            class="btn btn-outline-secondary"
        >
            Cancel
        </a>

        <button
            type="submit"
            id="save-studio-item"
            class="btn btn-primary"
        >
            <i data-feather="save"></i>
            {{ $isEdit ? 'Update' : 'Create' }}
        </button>
    </div>
</form>

<script src="https://unpkg.com/feather-icons"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    $(function () {
        feather.replace();

        const form = $('#studio-item-form');
        const submitButton = $('#save-studio-item');
        const originalHtml = submitButton.html();

        let isSubmitting = false;

        function showToast(message, isError = true) {
            Toastify({
                text: message,
                duration: 4000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: isError
                    ? '#EA5455'
                    : '#28C76F'
            }).showToast();
        }

        function resetSubmitButton() {
            isSubmitting = false;

            submitButton
                .prop('disabled', false)
                .html(originalHtml);

            feather.replace();
        }

        form
            .off('submit.aiStudioItem')
            .on('submit.aiStudioItem', function (e) {
                e.preventDefault();

                if (isSubmitting) {
                    return;
                }

                isSubmitting = true;

                submitButton
                    .prop('disabled', true)
                    .html(`
                    <span class="spinner-border spinner-border-sm me-50"></span>
                    Saving...
                `);

                const formData = new FormData(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,

                    success: function (response) {
                        showToast(
                            response.message ?? 'Saved successfully.',
                            false
                        );

                        setTimeout(() => {
                            window.location.href =
                                "{{ route('ai-studio-items.index') }}";
                        }, 500);
                    },

                    error: function (xhr) {
                        const response = xhr.responseJSON ?? {};

                        if (
                            xhr.status === 422
                            && response.errors
                        ) {
                            Object.values(response.errors)
                                .flat()
                                .forEach(message => {
                                    showToast(message);
                                });
                        } else {
                            showToast(
                                response.message
                                ?? 'Something went wrong.'
                            );
                        }

                        resetSubmitButton();
                    }
                });
            });
    });
</script>
