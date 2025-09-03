@extends('layouts/contentLayoutMaster')

@section('title', 'Roles')
@section('main-page', 'Roles')

@section('vendor-style')
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
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
<div class="bg-white p-2" style="min-height: 770px;">
  <!-- Header actions -->
  <div class="d-flex gap-1 align-items-center flex-wrap mb-2">
    <div class="col-12 col-md-8">
      <input type="text" class="form-control" placeholder="Search here">
    </div>
    <a class="btn btn-outline-primary col-12 col-md-3" href="/roles/create">
      <i data-feather="plus"></i> Add New Role
    </a>
  </div>

  <!-- Role cards -->
  <div class="row">
    @foreach($associatedData['roles'] as $role)

    <div class="col-xl-4 col-lg-6 col-md-6">
      <div class="card">

        <div class="card-body">

          <div class="d-flex justify-content-between">
            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
              @foreach($role->users as $user)
              <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Jimmy Ressula"
                class="avatar avatar-sm pull-up">
                <img class="rounded-circle" src="{{$user->getFirstMediaUrl('admins') ?? asset('images/default-user.png')}}"
                  alt="Avatar" />
              </li>
              @endforeach
            </ul>
            <span>{{ $role->users_count }} Users</span>
          </div>
          <div class="d-flex justify-content-between align-items-end mt-1 pt-25">
            <div class="role-heading">
              <h4 class="">{{ $role->name }}</h4>
              <a href="{{ route('roles.edit',$role->id) }}" class="role-edit-modal">
                Edit Role
              </a>
            </div>

          </div>

        </div>

      </div>
    </div>
    @endforeach

    {{-- Repeat for more role cards --}}
  </div>
</div>
@endsection

@section('vendor-script')
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
<script src="{{ asset(mix('js/scripts/pages/modal-add-role.js')) }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-access-roles.js')) }}"></script>
@endsection
