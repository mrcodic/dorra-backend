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
        <div class="pt-0">
            <div class="row align-items-center px-1">
                {{-- Filters Row --}}
                <div class="px-1 d-flex flex-wrap align-items-center gap-1">
                    {{-- Search Input --}}
                    <form action="" method="get" class="position-relative col-12 col-md-4 col-lg-6">
                        <i data-feather="search"
                            class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                        <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                            id="search-category-form" placeholder="Search template..." style="height: 38px;">
                        <button type="button" id="clearRoleFilter"
                            class="position-absolute top-50 translate-middle-y text-muted"
                            style="margin-right: 5px; right: 0; background: transparent; border: none; font-weight: bold; color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;"
                            title="Clear search">
                            &times;
                        </button>
                    </form>

                    {{-- Product Filter --}}
                    <div class="col-12 col-md-2 col-lg-1">
                        <select name="product" class="form-select filter-product select2" data-placeholder="Product">
                            <option value="">Product</option>
                            @foreach($associatedData['products'] as $product)
                            <option value="{{ $product->id }}">{{ $product->getTranslation('name', app()->getLocale())
                                }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-12 col-md-2 col-lg-1">
                        <select name="status" class="form-select filter-status select2" data-placeholder="Status">
                            <option value="">Status</option>
                            @foreach(\App\Enums\Template\StatusEnum::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tags Filter --}}
                    <div class="col-12 col-md-2 col-lg-1">
                        <select name="tags" class="form-select filter-status select2" data-placeholder="Tags">
                            <option value="">Tags</option>--}}
                            @foreach($associatedData['tags'] as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->getTranslation('name', app()->getLocale())}}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- create template button --}}
                    <a class="btn btn-primary col-12 col-md-3 col-lg-2" href="{{ route('product-templates.create') }}">
                        <i data-feather="plus"></i>
                        Create Template
                    </a>
                </div>

                {{-- Divider --}}
                <div class="col-12 ">
                    <hr class="my-2">
                </div>

                {{-- Buttons Row --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-1">

                    <div class="col-4 col-md-1">
                        <select name="per_page" class="form-select filter-paginate-number">
                            <option value="" disabled {{ request('per_page')===null ? 'selected' : '' }}>Show
                            </option>
                            <option value="8" @selected(request('per_page')==8 )>8</option>
                            <option value="16" @selected(request('per_page')==16 ) selected>16</option>
                            <option value="32" @selected(request('per_page')==32 )>32</option>
                            <option value="80" @selected(request('per_page')==80 )>80</option>
                            <option value="all" @selected(request('per_page')=='all' )>All</option>
                        </select>
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
            <div class="delete-container d-flex flex-wrap align-items-center justify-content-center justify-content-md-between"
                style="z-index: 10;">
                <p id="selected-count-text">0 Templates are selected</p>

                <button type="button" id="delete-selected-btn"
                    class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns"
                    data-bs-toggle="modal" data-bs-target="#deleteTemplatesModal">
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