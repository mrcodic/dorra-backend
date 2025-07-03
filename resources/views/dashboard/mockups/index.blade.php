@extends('layouts/contentLayoutMaster')

@section('title', 'Mockups')
@section('main-page', 'Mockups')

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
                                    placeholder="Search mockup..."
                                    style="height: 38px;">
                            </form>
                        </div>



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

                        {{-- Type Filter --}}
                        <div class="col-6 col-md-2">
                            <select name="type" class="form-select filter-type select2" data-placeholder="Type">
                                <option value="">Type</option>
                                @foreach(\App\Enums\Mockup\TypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6 col-md-2">
                            <a class="btn btn-outline-secondary"  data-bs-toggle="modal"
                               data-bs-target="#addMockupModal" href="">
                                <i data-feather="plus"></i>
                                Create Mockup
                            </a>
                        </div>
                    </div>


                    </div>


                <div class="row gx-2 gy-2 align-items-center px-1 pt-2" id="templates-container">
                    @include("dashboard.partials.filtered-mockups")
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
                    <p id="selected-count-text">0 Mockups are selected</p>

                    <button type="button" id="delete-selected-btn"
                            class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1 delete-selected-btns"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteMockupsModal">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>
                </div>
            </div>


        </div>


        @include('modals.mockups.add-mockup')
        @include('modals.mockups.show-mockup')
        @include('modals.delete',[
        'id' => 'deleteMockupModal',
        'formId' => 'deleteMockupForm',
        'title' => 'Delete Mockup',
        ])
        @include('modals.delete',[
        'id' => 'deleteMockupsModal',
        'formId' => 'bulk-delete-form',
        'title' => 'Delete Mockups',
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
    <script src="{{ asset('js/scripts/pages/app-mockup-list.js') }}?v={{ time() }}"></script>

    <script>
        handleAjaxFormSubmit("#editMockupForm", {
            successMessage: "Mockup Updated Successfully",
            onSuccess: function() {
                $('#editMockupModal').modal('hide');
                location.reload();
            }
        })

        $(document).ready(function () {
            $('.select2').select2({
                placeholder: function () {
                    $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%'
            });
        });

        const mockupsDataUrl = "{{ route('mockups.data') }}";


        const locale = "{{ app()->getLocale() }}";
    </script>


    {{-- Page js files --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const bulkContainer = document.getElementById("bulk-delete-container");

            function updateSelectionStatus() {
                const selectedCheckboxes = document.querySelectorAll("input[name='selected_mockups[]']:checked");
                const count = selectedCheckboxes.length;

                document.getElementById("selected-count-text").textContent = `${count} Mockups are selected`;
                bulkContainer.style.display = count > 0 ? "flex" : "none";
            }

            // Attach listener
            document.querySelectorAll("input[name='selected_mockups[]']").forEach(cb => {
                cb.addEventListener("change", updateSelectionStatus);
            });
        });
    </script>

@endsection
