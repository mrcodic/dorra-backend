@php
use Illuminate\Support\Facades\Cache;
$templates = $templates ?? collect();
$orderData = Cache::get(getOrderStepCacheKey()) ?? [];
@endphp

<div id="step-3" class="step">
    <div class="row gx-2 gy-2 align-items-center px-1 pt-2" id="templates-container">

        @forelse ($templates as $template)

        <div class="col-md-6 col-lg-4 col-xxl-4 custom-4-per-row" data-template-id="{{ $template->id }}">
            <div class="position-relative" style="box-shadow: 0px 4px 6px 0px #4247460F;">
                <div style="background-color: #F4F6F6;height:200px" class="d-flex justify-content-center">
                    <!-- Top Image --> <img src="{{  $template->getFirstMediaUrl('templates') ?: asset("
                        images/default-photo.png") }}" class="mx-auto d-block "
                        style="height:100%; width:auto;max-width: 100%; " alt="Template Image">
                </div> <!-- Template Info -->
                <div class="card-body text-start p-2">
                    <div>
                        <h6 class="fw-bold mb-1 text-black fs-3"
                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;height:22px">
                            {{ $template->getTranslation('name', app()->getLocale()) }}
                        </h6>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="text-16">Dimensions: <span class="text-black">{{ $template->height_mm }} × {{
                                    $template->width_mm }} mm</span>
                            </div>

                            <span class="badge text-light p-75 px-2 template-status-label"
                                style="background-color: #222245">
                                {{ $template->type?->label() }}
                            </span>
                        </div>
                        <p class="fs-4 mb-1"
                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;height:22px">
                            {{ $template->getTranslation('name', app()->getLocale()) }} </p>
                    </div> <!-- Tags -->
                    <div class="d-flex flex-wrap justify-content-start gap-1 mb-2" style="min-height: 44px;">
                        @foreach($template->tags ?? [] as $tag)
                        <span class="badge rounded-pill text-black d-flex justify-content-center align-items-center"
                            style="background-color: #FCF8FC;">{{ $tag->getTranslation('name',app()->getLocale())
                            }}</span>
                        @endforeach
                    </div> <!-- Palette and Status -->

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex" style="gap: 5px;">
                            <div class="rounded-circle" style="width: 15px; height: 15px; background-color: #FF5733;">
                            </div>
                            <div class="rounded-circle" style="width: 15px; height: 15px; background-color: #33B5FF;">
                            </div>
                            <div class="rounded-circle" style="width: 15px; height: 15px; background-color: #9B59B6;">
                            </div>
                        </div>
                        <span class="badge text-dark p-75 px-2 template-status-label"
                            data-template-id="{{ $template->id }}" style="background-color: #CED5D4">
                            {{ $template->status?->label() }}
                        </span>
                    </div>
                    <div class="mt-auto">
                        <!-- Pushes button to bottom -->
                        <a class="btn btn-primary w-100"
                            href="{{ config('editor_url').'templates/'.$template->id.'/users/'.$orderData["
                            user_info"]["id"] }}">
                            Customize Template
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @empty
        <div class="d-flex flex-column justify-content-center align-items-center text-center py-5 w-100"
            style="min-height:65vh;">
            <!-- Empty Image -->
            <img src="{{ asset('images/Empty.png') }}" alt="No Templates" style="max-width: 200px;" class="mb-2">

            <!-- Empty Message -->
            <p class="mb-2 text-secondary">this product doesn’t have any live templates.</p>

        </div>
        @endforelse
    </div>
    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-secondary me-1" id="prev-step-3" data-prev-step>Back</button>
    </div>
</div>

<script !src="">
    $(document).on('click', '#nextStep3', function () {
        $('#step-3').hide();
        $('#step-4').show();

    });
    $(document).on('click', '#prev-step-3', function () {
        $('#step-3').hide();
        $('#step-2').show();

    });
</script>