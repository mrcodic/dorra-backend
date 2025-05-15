@extends('layouts/contentLayoutMaster')

@section('title', 'Add Role')
@section('main-page', 'Roles')
@section('sub-page', 'Add New Role')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<div class="container bg-white p-3">
    <!-- Add role form -->
    <form id="addRoleForm" class="row" method="post" action="{{ route('roles.store') }}">
        @csrf
        <div class="col-12">
            <label class="form-label" for="modalRoleName">Role Name</label>
            <input
                type="text"
                id="modalRoleName"
                name="name"
                class="form-control"
                placeholder="Enter role name"
                tabindex="-1"
                data-msg="Please enter role name" />
        </div>

        <!-- New Role Description field -->
        <div class="col-12 mt-1">
            <label class="form-label" for="modalRoleDescription">Role Description</label>
            <textarea
                id="modalRoleDescription"
                name="description"
                class="form-control"
                rows="3"
                placeholder="Enter role description"
                data-msg="Please enter a description for the role"></textarea>
        </div>

        <div class="col-12">
            <h4 class="mt-2 pt-50">Role Permissions</h4>
            <!-- Permission table -->
            <div class="table-responsive">
                <table class="table ">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAllGlobal" />
                                    <label class="form-check-label" for="selectAllGlobal">Permission</label>
                                </div>
                            </th>

                            <th>Create</th>
                            <th>Read</th>
                            <th>Update</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>

                    @foreach($associatedData['permissions'] as $group => $groupPermissions)
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input row-checkbox" />
                                    <span>{{ $group }}</span>
                                </div>
                            </td>

                            @foreach(['Create', 'Read', 'Update', 'Delete'] as $action)
                                @php
                                    $perm = $groupPermissions->firstWhere('name', $group . $action);
                                @endphp
                                <td>
                                    <div class="form-check">
                                        @if($perm)
                                            <input
                                                type="checkbox"
                                                class="form-check-input permission-checkbox {{ $group }}-checkbox"
                                                name="permissions[]"
                                                value="{{ $perm->name }}"
                                                id="{{ $perm->name }}"
                                            />
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>

            <!-- Permission table -->
        </div>
        <div class="d-flex justify-content-end mt-2">
            <button type="submit" class="btn btn-primary ms-1">Add New Role</button>
        </div>
    </form>
    <!--/ Add role form -->
</div>
@endsection

@section('vendor-script')
<script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>


<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<!-- Page js files -->
<script src="{{ asset(mix('js/scripts/pages/modal-add-role.js')) }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-access-roles.js')) }}"></script>
<script>
    $(document).ready(function () {
        $('#addRoleForm').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(this);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    Toastify({
                        text: "Role created successfully!",
                        duration: 3000,
                        gravity: "top",
                        backgroundColor: "#28a745",
                    }).showToast();

                    form.trigger('reset'); // Clear form
                },
                error: function (xhr) {
                    let errorMsg = 'Something went wrong.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }

                    Toastify({
                        text: errorMsg,
                        duration: 4000,
                        gravity: "top",
                        backgroundColor: "#dc3545",
                    }).showToast();
                }
            });
        });
    });
</script>

@endsection
