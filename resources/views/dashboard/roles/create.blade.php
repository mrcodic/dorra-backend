@php use Illuminate\Support\Str; @endphp
@extends('layouts/contentLayoutMaster')
@section('title', 'Add Role')
@section('main-page', 'Roles')
@section('sub-page', 'Add New Role')
@section('main-page-url', route("roles.index"))
@section('sub-page-url', route("roles.create"))
@section('vendor-style')
    <!-- Vendor CSS Files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
    <div class="bg-white p-2">
        <!-- Add role form -->
        <form id="addRoleForm" class="row" method="post" action="{{ route('roles.store') }}">
            @csrf
            <div class="col-md-6 mb-1">
                <label class="form-label" for="modalRoleName">Role Name (EN)</label>
                <input type="text" id="modalRoleName" name="name[en]" class="form-control"
                       placeholder="Enter role name in english" tabindex="-1" data-msg="Please enter role name"/>
            </div>
            <div class="col-md-6 mb-1">
                <label class="form-label" for="modalRoleName">Role Name (Ar)</label>
                <input type="text" id="modalRoleName" name="name[ar]" class="form-control"
                       placeholder="Enter role name in arabic" tabindex="-1" data-msg="Please enter role name"/>
            </div>

            <!-- New Role Description field -->
            <div class="col-md-6 mt-1">
                <label class="form-label" for="modalRoleDescription">Role Description (EN)</label>
                <textarea id="modalRoleDescription" name="description[en]" class="form-control" rows="3"
                          placeholder="Enter role description in english"
                          data-msg="Please enter a description for the role"></textarea>
            </div>
            <div class="col-md-6 mt-1">
                <label class="form-label" for="modalRoleDescription">Role Description (AR)</label>
                <textarea id="modalRoleDescription" name="description[ar]" class="form-control" rows="3"
                          placeholder="Enter role description in arabic"
                          data-msg="Please enter a description for the role "></textarea>
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
                                    <input type="checkbox" class="form-check-input" id="selectAllGlobal"/>
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
                                        <input type="checkbox" class="form-check-input row-checkbox"
                                               data-group="{{ $group }}"/>

                                        <span>{{ $group }}</span>
                                    </div>
                                </td>

                                @foreach(\App\Enums\Permission\PermissionAction::values() as $action)
                                    @php
                                       $supportedActions = $groupPermissions->pluck('name')->map(fn ($n) => Str::afterLast($n, '_'))
                                          ->unique()
                                          ->values()
                                          ->all();
//                                        $permissionKey = strtolower($group) . '_' . strtolower($action);
                                        $isAvailable = collect($supportedActions)->contains(strtolower($action));
                                    @endphp

                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox"
                                                   class="form-check-input permission-checkbox {{ $group }}-checkbox"
                                                   name="permissions[]"
                                                   value="{{strtolower($group).'_'.strtolower($action) }}"
                                                   id="{{ $group.$action }}" @disabled(!$isAvailable) />

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
            $('.row-checkbox').on('change', function () {
                const group = $(this).data('group');
                const isChecked = $(this).is(':checked');
                $(`.${group}-checkbox`).prop('checked', isChecked);
            });

        });
        handleAjaxFormSubmit("#addRoleForm",{
            successMessage: "Role added successfully",
            onSuccess: function () {
                location.replace('/roles');
            }
        })
    </script>

@endsection
