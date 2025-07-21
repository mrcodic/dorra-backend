@forelse ($data as $template)
    <div class="col-md-6 col-lg-4 col-xxl-4 custom-4-per-row" data-template-id="{{ $template->id }}">
        <div class="position-relative"
             style="box-shadow: 0px 4px 6px 0px #4247460F;border:1px solid #CED5D4;border-radius:12px">
            <!-- Checkbox -->
            <input type="checkbox" class="form-check-input position-absolute top-0 start-0 m-1 category-checkbox"
                   value="{{ $template->id }}" name="selected_templates[]">
            <!-- Action Icon with Dropdown (Top Right) -->
            <div class="dropdown position-absolute top-0 end-0 m-1">
                <button class="btn btn-sm  border-0" type="button" id="actionDropdown{{ $template->id }}"
                        data-bs-toggle="dropdown" aria-expanded="false" style="background-color:#F9FDFC">
                    <i data-feather="more-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdown{{ $template->id }}">
                    <li><a class="dropdown-item" href="{{ route("product-templates.edit",$template->id) }}"><i
                                data-feather="edit-3" class="me-1"></i>Edit</a></li>
                    <li>
                        <a class="dropdown-item"
                           href="{{ config('services.editor_url') . 'templates/' . $template->id ."?has_mockup=" .
                          ($template->products->contains('has_mockup', true) ? 'true' : 'false')}}"
                           target="_blank">
                            <i data-feather="eye" class="me-1"></i>Show
                        </a>
                    </li>
                    <form class="change-status-form"
                          action="{{ route("product-templates.change-status",$template->id) }}" method="post">
                        @csrf
                        @method("PUT")
                        <input type="hidden" name="status" value="{{ \App\Enums\Template\StatusEnum::PUBLISHED }}">

                        <button class="dropdown-item change-status-btn w-100
                                {{ (!empty($template->design_data) && $template->status !== \App\Enums\Template\StatusEnum::PUBLISHED) ? '' : 'disabled' }}   ">
                            <i data-feather="send" class="me-1">

                            </i>Publish
                        </button>
                    </form>
                    </li>


                    <li>
                        <form class="change-status-form"
                              action="{{ route("product-templates.change-status",$template->id) }}" method="post">
                            @csrf
                            @method("PUT")
                            <input type="hidden" name="status" value="{{ \App\Enums\Template\StatusEnum::DRAFTED }}">
                            <button
                                class="dropdown-item change-status-btn w-100 {{ $template->design_data && $template->status !== \App\Enums\Template\StatusEnum::DRAFTED ? '' : 'disabled' }}">
                                <i data-feather="file" class="me-1">
                                </i>Draft
                            </button>
                        </form>
                    </li>
                    <li>
                        <form class="change-status-form"
                              action="{{ route("product-templates.change-status",$template->id) }}" method="post">
                            @csrf
                            @method("PUT")
                            <input type="hidden" name="status" value="{{ \App\Enums\Template\StatusEnum::LIVE }}">
                            <button
                                class="dropdown-item change-status-btn w-100 {{ $template->design_data && $template->status !== \App\Enums\Template\StatusEnum::DRAFTED ? '' : 'disabled' }}">
                                <i data-feather="radio" class="me-1">

                                </i>Live
                            </button>
                        </form>
                    </li>
                    <li>
                        <button class="dropdown-item text-danger open-delete-template-modal w-100"
                                data-bs-toggle="modal" data-bs-target="#deleteTemplateModal"
                                data-id="{{ $template->id }}"><i data-feather="trash-2" class="me-1 text-danger"></i>Delete
                        </button>
                    </li>
                </ul>
            </div>
            <div style="background-color: #F4F6F6;height:200px;border-radius:12px"> <!-- Top Image --> <img
                    src="{{  $template->getFirstMediaUrl('templates') ?: asset("images/default-photo.png") }}"
                    class="mx-auto d-block "
                    style="height:100%; width:auto;max-width: 100%;border-top-left-radius:12px ;border-top-right-radius:12px"
                    alt="Template Image">
            </div> <!-- Template Info -->
            <div class="card-body text-start p-2">
                <div>
                    <h6 class="fw-bold mb-1 text-black fs-3"
                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 300px; height: 54px;">
                        {{ $template->getTranslation('name', app()->getLocale()) }}
                    </h6>

                    <!-- Info Row -->
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <p style="display:inline; margin-right: 10px">Type:</p>
                            @foreach( $template->types as $type)
                                <span class="badge text-light p-75 px-2 template-status-label "
                                      style="background-color: #222245">
                             {{ $type->value->label()}}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div> <!-- Tags -->
                <div class="mb-1">
                    <p style="display: inline">Created at: {{ $template->created_at->format('d/m/Y') }}</p>
                    <p style="display: inline">Last update: {{ $template->updated_at->format('d/m/Y') }}</p>
                </div>
                <div class="d-flex flex-wrap justify-content-start gap-1 mb-1"
                     style="min-height: 44px;"> @foreach($template->tags as $tag)
                        <span class="text-black"
                              style="background-color: #FCF8FC; padding: 8px; border-radius: 8px; display: inline-block;">
                         {{ $tag->getTranslation('name', app()->getLocale()) }}
                       </span>

                    @endforeach
                </div> <!-- Palette and Status -->

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="d-flex" style="gap: 5px;">
                        <div class="rounded-circle" style="width: 24px; height: 24px; background-color: #FF5733;"></div>
                        <div class="rounded-circle" style="width: 24px; height: 24px; background-color: #33B5FF;"></div>
                        <div class="rounded-circle" style="width: 24px; height: 24px; background-color: #9B59B6;"></div>
                    </div>
                    <span class="badge text-dark p-75 px-2 template-status-label"
                          data-template-id="{{ $template->id }}"
                          style="background-color: #CED5D4">
                    {{ $template->status->label() }}
                </span>

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
        <p class="mb-2 text-secondary">Nothing to show yet.</p>

        <!-- Create Button -->
        <a class="btn btn-primary" data-bs-target="#templateModal" data-bs-toggle="modal">
            <i data-feather="plus"></i>
            Create Template
        </a>
    </div>
@endforelse
<script !src="">
    document.addEventListener("DOMContentLoaded", function () {
        if (window.feather) {
            feather.replace();
        }
    });

    // Also run after AJAX updates
    $(document).ajaxComplete(function () {
        if (window.feather) {
            feather.replace();
        }
    });
    $(document).ready(function () {
        handleAjaxFormSubmit('.change-status-form', {
            successMessage: '✅ Status updated successfully!',
            onSuccess: function (response, $form) {
                console.log('Success:', response);

                const templateId = response.data.id;
                const status = response.data.status.value; // assuming this contains status enum value
                const statusLabel = response.data.status.label;
                const designData = response.data.design_data; // assuming design_data returned from server

                // Update status label text
                const $statusLabel = $('.template-status-label[data-template-id="' + templateId + '"]');
                if ($statusLabel.length) {
                    $statusLabel.text(statusLabel);
                }

                // Find the template card div by data-template-id
                const $templateCard = $('[data-template-id="' + templateId + '"]');

                if ($templateCard.length) {
                    // Find publish button
                    const $publishBtn = $templateCard.find('form.change-status-form input[name="status"][value="{{ \App\Enums\Template\StatusEnum::PUBLISHED }}"]').siblings('button');
                    // Find draft button
                    const $draftBtn = $templateCard.find('form.change-status-form input[name="status"][value="{{ \App\Enums\Template\StatusEnum::DRAFTED }}"]').siblings('button');
                    // Find live button
                    const $liveBtn = $templateCard.find('form.change-status-form input[name="status"][value="{{ \App\Enums\Template\StatusEnum::LIVE }}"]').siblings('button');

                    // Logic to enable/disable buttons, example (adjust according to your rules):

                    // Enable Publish if design_data exists and status not published
                    if (designData && status !== {{ \App\Enums\Template\StatusEnum::PUBLISHED->value }}) {
                        $publishBtn.removeClass('disabled').prop('disabled', false);
                    } else {
                        $publishBtn.addClass('disabled').prop('disabled', true);
                    }

                    // Enable Draft if design_data exists and status not drafted
                    if (designData && status !== {{ \App\Enums\Template\StatusEnum::DRAFTED->value }}) {
                        $draftBtn.removeClass('disabled').prop('disabled', false);
                    } else {
                        $draftBtn.addClass('disabled').prop('disabled', true);
                    }

                    // Enable Live if design_data exists and status not live (adjust this condition if needed)
                    if (designData && status !== {{ \App\Enums\Template\StatusEnum::LIVE->value }}) {
                        $liveBtn.removeClass('disabled').prop('disabled', false);
                    } else {
                        $liveBtn.addClass('disabled').prop('disabled', true);
                    }
                }
            },

            onError: function (xhr, $form) {
                console.error('Error:', xhr);
            },
            resetForm: false,
        });
    });
</script>
