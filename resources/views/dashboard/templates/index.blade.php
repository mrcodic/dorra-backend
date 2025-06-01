@extends('layouts/contentLayoutMaster')

@section('title', 'Templates')
@section('main-page', 'Templates')

@section('vendor-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
    <!-- users list start -->
    <section class="">

        <!-- list and filter start -->
        <div class="card">
            <div class="card-body ">

                <div class="row">
                    <div class="col-md-4 user_role"></div>
                    <div class="col-md-4 user_plan"></div>
                    <div class="col-md-4 user_status"></div>
                </div>
            </div>
            <div class=" pt-0">
                <div class="row gx-2 gy-2 align-items-center px-1">

                    {{-- Search Input - 70% on md+, full width on xs --}}
                    <div class="col-12 col-md-4">
                        <form action="" method="get" class="position-relative">
                            <i data-feather="search"
                               class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                            <input
                                type="text"
                                class="form-control ps-5 border rounded-3"
                                name="search_value"
                                id="search-category-form"
                                placeholder="Search template..."
                                style="height: 38px;">
                        </form>
                    </div>
                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col">
                        <select name="created_at" class="form-select filter-status select2" data-placeholder="Status">
                            <option value="">Status</option>
                            @foreach(\App\Enums\Template\StatusEnum::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>

                            @endforeach
                        </select>
                    </div>
                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col">
                        <select name="created_at" class="form-select filter-status select2" data-placeholder="Tags">
                            <option value="">Tags</option>
                            @foreach($associatedData['tags'] as $tag)
                                <option
                                    value="{{ $tag->id }}">{{ $tag->getTranslation('name', app()->getLocale()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Filter Select - 10% on md+, half width on sm --}}
                    <div class="col">
                        <select name="created_at" class="form-select filter-product select2" data-placeholder="Product">
                            <option value="">Product</option>
                            @foreach($associatedData['products'] as $product)
                                <option
                                    value="{{ $product->id }}">{{ $product->getTranslation('name', app()->getLocale()) }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Add Button - 20% on md+, full width on xs --}}
                    <div class="col-6 col-md-2 text-md-end">
                        <a class="btn btn-outline-secondary w-100 w-md-auto" href="{{ config('services.editor_url') }} "
                           target="_blank">
                            <i data-feather="upload"></i>
                            Upload Template
                        </a>
                    </div>
                    {{-- Add Button - 20% on md+, full width on xs --}}
                    <div class="col-6 col-md-2 text-md-end">
                        <a class="btn btn-primary w-100 w-md-auto" href="{{ route("product-templates.create") }}">
                            <i data-feather="plus"></i>
                            Create Template
                        </a>
                    </div>

                </div>

                <div class="row gx-2 gy-2 align-items-center px-1 pt-2" id="templates-container">
                    @foreach ($data as $template)

                        <div class="col-md-6 col-lg-4 col-xxl-4 custom-4-per-row">
                            <div class="position-relative" style="box-shadow: 0px 4px 6px 0px #4247460F;">
                                <!-- Checkbox -->
                                <input type="checkbox"
                                       class="form-check-input position-absolute top-0 start-0 m-1 category-checkbox"
                                       value="{{ $template->id }}" name="selected_templates[]">
                                <div style="background-color: #F4F6F6;height:200px">

                                    <!-- Top Image -->
                                    <img
                                        src="{{ $template->getFirstMediaUrl('templates') ? $template->getFirstMediaUrl('templates'): asset("images/default-photo.png") }}"
                                        class="mx-auto d-block " style="height:100%; width: auto;" alt="Template Image">
                                </div>

                                <!-- Template Info -->
                                <div class="card-body text-start p-2">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-black fs-3"
                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;height:22px">
                                            {{ $template->getTranslation('name', app()->getLocale()) }}
                                        </h6>
                                        <p class="small mb-1"
                                           style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;height:22px">
                                            {{ $template->product->getTranslation('name', app()->getLocale()) }}
                                        </p>

                                    </div>


                                    <!-- Tags -->
                                    <div class="d-flex flex-wrap justify-content-start gap-1 mb-2"  style="min-height: 44px;">
                                        @foreach($template->product->tags as $tag)
                                            <span class="badge rounded-pill text-black d-flex justify-content-center align-items-center"
                                                  style="background-color: #FCF8FC;">{{ $tag->getTranslation('name',app()->getLocale()) }}</span>
                                        @endforeach
                                    </div>

                                    <!-- Palette and Status -->
                                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                                        <div class="d-flex" style="gap: 5px;">
                                            <div class="rounded-circle"
                                                 style="width: 15px; height: 15px; background-color: #FF5733;"></div>
                                            <div class="rounded-circle"
                                                 style="width: 15px; height: 15px; background-color: #33B5FF;"></div>
                                            <div class="rounded-circle"
                                                 style="width: 15px; height: 15px; background-color: #9B59B6;"></div>
                                        </div>
                                        <span class="badge text-dark  p-75"
                                              style="background-color: #CED5D4">{{ $template->status->label() }}</span>
                                    </div>

                                    <hr class="my-2">

                                    <!-- Footer Buttons -->

                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        <a class="btn btn-outline-secondary text-black publish-btn {{ $template->design_data  != 3 ? '' : 'disabled disabled-link' }}"
                                           href="{{ $template->design_data ? route('product-templates.publish', ['id' => $template->id]) : '#' }}">
                                            Publish
                                        </a>


                                        <a class="btn btn-outline-secondary text-black"
                                           href="{{ config('services.editor_url') . 'templates/' . $template->id }}"
                                           target="_blank">
                                            Show
                                        </a>

                                        <a class="btn btn-outline-secondary text-black" href="{{ route("product-templates.edit",$template->id) }}">Edit</a>
                                        <button class="btn btn-outline-danger open-delete-template-modal"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteTemplateModal"
                                                data-id="{{ $template->id }}">
                                            Delete
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach



                </div>

                <div class="mt-2 px-1">
                    {{ $data->links('pagination::bootstrap-5') }}

                </div>

            </div>

            <!-- Bulk Delete Bar -->
            <div id="bulk-delete-container" class="my-2 bulk-delete-container " style="display: none;">
                <div class="delete-container mx-auto">
                    <p id="selected-count-text">0 Templates are selected</p>

                    <button type="button" id="delete-selected-btn"
                            class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteTemplatesModal">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>
                </div>
            </div>


        </div>


        @include('modals.templates.add-template')
        @include('modals.templates.edit-template')
        @include('modals.delete',[
    'id' => 'deleteTemplateModal',
    'formId' => 'deleteTemplateForm',
    'title' => 'Delete Template',
    ])
        @include('modals.delete',[
        'id' => 'deleteTemplatesModal',
        'formId' => 'bulk-delete-form',
        'title' => 'Delete Templates',
        'confirmText' => 'Are you sure you want to delete this items?',
        ])

        <!-- list and filter end -->
    </section>
    <!-- users list ends -->
@endsection

@section('vendor-script')
    {{-- Vendor js files --}}
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jszip.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/pdfmake.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/vfs_fonts.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.print.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: function(){
                    $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%'
            });
        });

        const templatesDataUrl = "{{ route('templates.data') }}";
        const showTemplateUrl = "{{ config("services.editor_url") }}";

        const locale = "{{ app()->getLocale() }}";
        $(".publish-btn").on('click', function (e) {
            e.preventDefault();

            if ($(this).hasClass('disabled') || $(this).hasClass('disabled-link')) return;

            const url = $(this).attr('href');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    '_method': "PUT"
                },
                headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
                success: function (response) {
                    Toastify({
                        text: "Template published successfully",
                        duration: 3000,
                        gravity: "top",
                        backgroundColor: "#28a745",
                    }).showToast();
                },
                error: function (xhr) {
                    Toastify({
                        text: "Failed to publish template",
                        duration: 4000,
                        gravity: "top",
                        backgroundColor: "#EA5455",
                        close: true,
                    }).showToast();
                }
            });
        });

    </script>


    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/app-template-list.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const bulkContainer = document.getElementById("bulk-delete-container");

            function updateSelectionStatus() {
                const selectedCheckboxes = document.querySelectorAll("input[name='selected_templates[]']:checked");
                const count = selectedCheckboxes.length;

                document.getElementById("selected-count-text").textContent = `${count} Templates are selected`;
                bulkContainer.style.display = count > 0 ? "flex" : "none";
            }

            // Attach listener
            document.querySelectorAll("input[name='selected_templates[]']").forEach(cb => {
                cb.addEventListener("change", updateSelectionStatus);
            });
        });
    </script>

@endsection
