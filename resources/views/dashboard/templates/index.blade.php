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
                <div class="row gx-2  align-items-center px-1">
                    {{-- Filters Row --}}
                    <div class="row gx-2 ">
                        {{-- Search Input --}}
                        <div class="col-12 col-md-6">
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

                        {{-- Status Filter --}}
                        <div class="col-6 col-md-2">
                            <select name="status" class="form-select filter-status select2" data-placeholder="Status">
                                <option value="">Status</option>
                                @foreach(\App\Enums\Template\StatusEnum::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tags Filter --}}
                        {{--                    <div class="col-6 col-md-2">--}}
                        {{--                        <select name="tags" class="form-select filter-status select2" data-placeholder="Tags">--}}
                        {{--                            <option value="">Tags</option>--}}
                        {{--                            @foreach($associatedData['tags'] as $tag)--}}
                        {{--                            <option value="{{ $tag->id }}">{{ $tag->getTranslation('name', app()->getLocale()) }}</option>--}}
                        {{--                            @endforeach--}}
                        {{--                        </select>--}}
                        {{--                    </div>--}}

                        {{-- Product Filter --}}
                        <div class="col-6 col-md-2">
                            <select name="product" class="form-select filter-product select2"
                                    data-placeholder="Product">
                                <option value="">Product</option>
                                @foreach($associatedData['products'] as $product)
                                    <option
                                        value="{{ $product->id }}">{{ $product->getTranslation('name', app()->getLocale()) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="col-12 ">
                        <hr class="my-2">
                    </div>

                    {{-- Buttons Row --}}
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-1">

                        <div class="col-12 col-md-1">
                            <select name="per_page"
                                    class="form-select filter-paginate-number">
                                <option value="" disabled {{ request('per_page') === null ? 'selected' : '' }}>Show
                                </option>
                                <option value="8" @selected(request('per_page') == 8 )>8</option>
                                <option value="16" @selected(request('per_page') == 16 ) selected>16</option>
                                <option value="32" @selected(request('per_page') == 32 )>32</option>
                                <option value="80" @selected(request('per_page') == 80 )>80</option>
                                <option value="all" @selected(request('per_page') == 'all')>All</option>
                            </select>


                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-4 d-flex gap-1">
                            <a class="btn btn-outline-secondary w-100 w-md-auto"
                               href="{{ config('services.editor_url') }}" target="_blank">
                                <i data-feather="upload"></i>
                                Upload Template
                            </a>
                            <a class="btn btn-primary w-100 w-md-auto"  data-bs-target="#templateModal" data-bs-toggle="modal" href="">
                                <i data-feather="plus"></i>
                                Create Template
                            </a>
                        </div>
                    </div>
                </div>


                <div class="row gx-2 gy-2 align-items-center px-1 pt-2" id="templates-container">
                    @include("dashboard.partials.filtered-templates")
                </div>
                <div id="pagination-container">
                    @if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-2 px-1">
                            {{ $data->withQueryString()->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
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
        <div class="modal new-user-modal fade" id="templateModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="add-new-user modal-content pt-0 px-1">

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    <div class="modal-header mb-1 border-0 p-0">
                        <h5 class="modal-title fs-4">Select Product to add template</h5>

                    </div>
                    <form action="{{ route("check.product.type") }}" method="post">
                        @csrf
                        <div class="modal-body flex-grow-1 d-flex flex-column gap-2">
                            <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                                <input
                                    class="form-check-input me-2"
                                    type="radio"
                                    name="product_type"
                                    id="codeTshirt"
                                    value="T-shirt"
                                    required
                                />
                                <label class="form-check-label mb-0 flex-grow-1" for="codeTshirt">T-shirt</label>
                            </div>
                            <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                                <input
                                    class="form-check-input me-2"
                                    type="radio"
                                    name="product_type"
                                    id="codeOther"
                                    value="other"
                                    required
                                />
                                <label class="form-check-label mb-0 flex-grow-1" for="codeOther">Other</label>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit"
                                    class="btn btn-primary ">
                                Next
                            </button>
                    </form>





                </div>

            </div>
        </div>
        </div>
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
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: function () {
                    $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%'
            });
        });

        const templatesDataUrl = "{{ route('templates.data') }}";
        const showTemplateUrl = "{{ config("services.editor_url ") }}";

        const locale = "{{ app()->getLocale() }}";
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
