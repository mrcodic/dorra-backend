@extends('layouts/contentLayoutMaster')

@section('title', 'Roles')
@section('main-page', 'Roles')

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
<!-- Role cards -->
<div class="row">
  <div class="col-xl-4 col-lg-6 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between">
        
          <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
            <li
              data-bs-toggle="tooltip"
              data-popup="tooltip-custom"
              data-bs-placement="top"
              title="Jimmy Ressula"
              class="avatar avatar-sm pull-up"
            >
              <img class="rounded-circle" src="{{asset('images/avatars/4.png')}}" alt="Avatar" />
            </li>
            <li
              data-bs-toggle="tooltip"
              data-popup="tooltip-custom"
              data-bs-placement="top"
              title="John Doe"
              class="avatar avatar-sm pull-up"
            >
              <img class="rounded-circle" src="{{asset('images/avatars/1.png')}}" alt="Avatar" />
            </li>
            <li
              data-bs-toggle="tooltip"
              data-popup="tooltip-custom"
              data-bs-placement="top"
              title="Kristi Lawker"
              class="avatar avatar-sm pull-up"
            >
              <img class="rounded-circle" src="{{asset('images/avatars/2.png')}}" alt="Avatar" />
            </li>
            <li
              data-bs-toggle="tooltip"
              data-popup="tooltip-custom"
              data-bs-placement="top"
              title="Kaith D'souza"
              class="avatar avatar-sm pull-up"
            >
              <img class="rounded-circle" src="{{asset('images/avatars/5.png')}}" alt="Avatar" />
            </li>
            <li
              data-bs-toggle="tooltip"
              data-popup="tooltip-custom"
              data-bs-placement="top"
              title="Danny Paul"
              class="avatar avatar-sm pull-up"
            >
              <img class="rounded-circle" src="{{asset('images/avatars/7.png')}}" alt="Avatar" />
            </li>
          </ul>
          <span>7 Users</span>
        </div>
        <div class="d-flex justify-content-between align-items-end mt-1 pt-25">
          <div class="role-heading">
            <h4 class="">Manager</h4>
            <a href="javascript:;" class="role-edit-modal" data-bs-toggle="modal" data-bs-target="#addRoleModal">
              <small class="">Edit Role</small>
            </a>
          </div>
          <a href="javascript:void(0);" class="text-body"><i data-feather="copy" class="font-medium-5"></i></a>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Role cards -->

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
   <script>
       const productsDataUrl = "{{ route('products.data') }}";
       const productsCreateUrl = "{{ route('products.create') }}";
   </script>

    {{-- Page js files --}}
   <!-- Page js files -->
   <script src="{{ asset(mix('js/scripts/pages/modal-add-role.js')) }}"></script>
  <script src="{{ asset(mix('js/scripts/pages/app-access-roles.js')) }}"></script>
@endsection
