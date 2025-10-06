@extends('layouts/contentLayoutMaster')

@section('title', 'Offers')
@section('main-page', 'Offers')

@section('vendor-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
<!-- users list start -->
<section class="app-user-list">

    <!-- list and filter start -->
    <div class="card">
        <div class="card-body ">

            <div class="row">
                <div class="col-md-4 user_role"></div>
                <div class="col-md-4 user_plan"></div>
                <div class="col-md-4 user_status"></div>
            </div>
        </div>
        <div class="card-datatable table-responsive pt-0">
            <div class="row gx-2 gy-2 align-items-center px-1">

                {{-- Search Input - 70% on md+, full width on xs --}}
                <div class="col-12 col-md-7">
                    <form action="" method="get" class="position-relative search-form">
                        <i data-feather="search"
                            class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>
                        <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                            id="search-offer-form" placeholder="Search offer..." style="height: 38px;">
                        <button type="button"  id="clear-search"
                            class="position-absolute top-50 translate-middle-y text-muted"
                            style="margin-right: 5px; right: 0; background: transparent; border: none; font-weight: bold; color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;"
                            title="Clear search">
                            &times;
                        </button>
                    </form>
                </div>

                {{-- Filter Select - 10% on md+, half width on sm --}}
                <div class="col-6 col-md-2">
                    <select name="created_at" class="form-select filter-type" name="type">
                        <option value="" selected disabled>Type</option>
                        <option value="">All</option>
                        @foreach(\App\Enums\Offer\TypeEnum::cases() as $type)
                            <option value="{{ $type }}">{{ $type->label() }}</option>
                        @endforeach

                    </select>
                </div>

                {{-- Add Button - 20% on md+, full width on xs --}}
                <div class="col-6 col-md-3 text-md-end">
                    <a class="btn btn-outline-primary w-100 w-md-auto" data-bs-toggle="modal"
                        data-bs-target="#addOfferModal">
                        <i data-feather="plus"></i>
                        Add New Offer
                    </a>
                </div>

            </div>


            <table class="offer-list-table table">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox" class="form-check-input">
                        </th>
                        <th>Offer Name</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
            <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display:none;">
                <div class="delete-container d-flex align-items-center gap-2">
                    <p id="selected-count-text" class="m-0">0 Offers are selected</p>

                    <!-- Open modal -->
                    <button type="button"
                            id="open-bulk-delete-modal"
                            class="btn btn-outline-danger d-flex align-items-center gap-1">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>

                    <!-- Hidden form actually posted on confirm -->
                    <form id="bulk-delete-form" method="POST" action="{{ route('offers.bulk-delete') }}" style="display:none;">
                        @csrf
                        <!-- We’ll submit via AJAX; no visible button here -->
                    </form>
                </div>
            </div>


        </div>

        @include('modals.offers.show-offer')
        @include('modals.offers.add-offer')
        @include('modals.offers.edit-offer')




        @include('modals.delete',[
        'id' => 'deleteOfferModal',
        'formId' => 'deleteOfferForm',
        'title' => 'Delete Offer',
        ])
        @include('modals.delete', [
          'id' => 'deleteOffersModal',
          'formId' => 'bulk-delete-form',
          'buttonId' => 'confirm-bulk-delete',
          'title' => 'Delete Offers',
          'confirmText' => 'Are you sure you want to delete these items?',
        ])



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
    const offersDataUrl = "{{ route('offers.data') }}";
        const offersCreateUrl = "{{ route('offers.create') }}";
        const locale = "{{ app()->getLocale() }}";
</script>

<script>
    $(document).ready(function () {
            // Select all toggle
            $('#select-all-checkbox').on('change', function () {
                $('.category-checkbox').prop('checked', this.checked);
                updateBulkDeleteVisibility();
            });

            // When individual checkbox changes
            $(document).on('change', '.category-checkbox', function () {
                // If any is unchecked, uncheck "Select All"
                if (!this.checked) {
                    $('#select-all-checkbox').prop('checked', false);
                } else if ($('.category-checkbox:checked').length === $('.category-checkbox').length) {
                    $('#select-all-checkbox').prop('checked', true);
                }
                updateBulkDeleteVisibility();
            });


            // On table redraw (e.g. pagination, search)
            $(document).on('draw.dt', function () {
                $('#bulk-delete-container').hide();
                $('#select-all-checkbox').prop('checked', false);
            });

            // Close bulk delete container
            $(document).on('click', '#close-bulk-delete', function () {
                $('#bulk-delete-container').hide();
                $('.category-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
            });

            // Update the bulk delete container visibility
            function updateBulkDeleteVisibility() {
                const selectedCheckboxes = $('.category-checkbox:checked');
                const count = selectedCheckboxes.length;

                if (count > 0) {
                    $('#selected-count-text').text(`${count} Offer${count > 1  ? 's' : ''} are selected`);
                    $('#bulk-delete-container').show();
                } else {
                    $('#bulk-delete-container').hide();
                }
            }


        });
</script>

{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-offer-list.js') }}?v={{ time() }}"></script>
@endsection
