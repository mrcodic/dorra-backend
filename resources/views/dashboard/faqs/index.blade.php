@extends('layouts/contentLayoutMaster')

@section('title', 'FAQs')
@section('main-page', 'FAQs')

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
            <div class="px-1 d-flex flex-wrap justify-content-between align-items-center gap-1">
                <form action="" method="get" class="flex-grow-1 me-1 col-12 col-md-5 position-relative search-form">
                    <i data-feather="search" class="position-absolute top-50 translate-middle-y mx-1 text-muted"></i>
                    <input type="text" class="form-control ps-5 border rounded-3" name="search_value"
                        id="search-faq-form" placeholder="Search here" style="height: 38px;">
                    <button type="button" id="clear-search"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                   background: transparent; border: none; font-weight: bold;
                   color: #aaa; cursor: pointer; font-size: 18px; line-height: 1;"
                            title="Clear filter">
                        &times;
                    </button>
                </form>
                <div class="col-12 col-md-2">
                    <select name="created_at" class="form-select filter-date">
                        <option value="" selected disabled>Date</option>
                        <option value="asc">Oldest</option>
                        <option value="desc">Newest</option>
                    </select>
                </div>
                <button type="button" class="btn btn-outline-primary col-12 col-md-2" data-bs-toggle="modal"
                    data-bs-target="#addQuestionModal">
                    Add New Question
                </button>
            </div>

            <table class="faq-list-table table">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all-checkbox">
                        </th>
                        <th>Question</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
            <div id="bulk-delete-container" class="my-2 bulk-delete-container" style="display: none;">
                <div class="delete-container d-flex flex-wrap align-items-center justify-content-center justify-content-md-between"
                     style="z-index: 10;">
                    <p id="selected-count-text">0 Faqs are selected</p>
                    <!-- Trigger button -->
                    <button type="button"
                            class="btn btn-outline-danger d-flex justify-content-center align-items-center gap-1"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteFaqsModal">
                        <i data-feather="trash-2"></i> Delete Selected
                    </button>

                    <!-- Hidden bulk delete form -->
                    <form style="display:none;" id="bulk-delete-form" method="POST"
                          action="{{ route('faqs.bulk-delete') }}">
                        @csrf
                    </form>

                    <!-- Bulk delete modal -->



                </div>
            </div>



        </div>
        @include('modals/questions/add-question')
        @include('modals/questions/show-question')
        @include('modals/questions/edit-question')

        @include('modals.delete', [
                              'id' => 'deleteFaqsModal',
                              'formId' => 'bulk-delete-form',
                              'title' => 'Delete Faqs',
                              'confirmText' => 'Are you sure you want to delete these items?'
                           ])
        @include('modals.delete',[
              'id' => 'deleteFaqModal',
              'formId' => 'deleteFaqForm',
              'title' => 'Delete Faq',
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://unpkg.com/feather-icons"></script>
<script>
    const faqsDataUrl = "{{ route('faqs.data') }}";
        const locale = "{{ app()->getLocale() }}";

</script>
<script src="{{ asset('js/scripts/pages/app-question-list.js') }}?v={{ time() }}"></script>
<script !src="">
    handleAjaxFormSubmit("#deleteFaqForm",{
        successMessage: "Faq deleted Successfully",
        onSuccess:function () {
            $("#deleteFaqModal").modal("hide");
            location.reload()
        }
    })
</script>

{{-- Page js files --}}
@endsection
