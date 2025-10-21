@php use Illuminate\Support\Str; @endphp
@extends('layouts/contentLayoutMaster')
@section('title', 'Edit Role')
@section('main-page', 'Roles')
@section('sub-page', 'Edit Role')
@section('main-page-url', route("roles.index"))
@section('sub-page-url', route("roles.edit", $model))
@section('vendor-style')
    <!-- Vendor CSS Files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
    <div class="bg-white p-2">
        <!-- Add role form -->
        <form id="editRoleForm" class="row" method="post" action="{{ route('roles.update', $model->id) }}">
            @csrf
            @method("PUT")
            <div class="col-12">
                <label class="form-label" for="modalRoleName">Role Name</label>
                <input type="text" id="modalRoleName" name="role" value="{{ $model->name }}" class="form-control"
                       placeholder="Enter role name" tabindex="-1" data-msg="Please enter role name"/>
            </div>

            <!-- New Role Description field -->
            <div class="col-12 mt-1">
                <label class="form-label" for="modalRoleDescription">Role Description</label>
                <textarea id="modalRoleDescription" name="modalRoleDescription" class="form-control" rows="3"
                          placeholder="Enter role description"
                          data-msg="Please enter a description for the role">{{ $model->description }}</textarea>
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

                        @php
                            use Illuminate\Support\Str;

                            // Lowercased list of the role's permission names (e.g. posts_update, users_create)
                            $rolePermissionNames = $model->permissions
                                ->pluck('name')
                                ->map(fn ($n) => Str::lower($n))
                                ->values();
                        @endphp

                        @foreach($associatedData['permissions'] as $group => $groupPermissions)
                            @php
                                // Normalize the group key once for ids/classes/data-attrs
                                $groupKey = Str::snake(Str::lower($group));

                                // Lowercased list of this group's available permission names
                                $groupPermissionNames = $groupPermissions
                                    ->pluck('name')
                                    ->map(fn ($n) => Str::lower($n))
                                    ->values();
                            @endphp

                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            class="form-check-input row-checkbox"
                                            data-group="{{ $groupKey }}"
                                        />
                                        <span>{{ $group }}</span>
                                    </div>
                                </td>

                                @foreach(\App\Enums\Permission\PermissionAction::values() as $action)
                                    @php
                                        // Normalize action to snake/lower (e.g. "Update Profile" => "update_profile")
                                        $actionKey = Str::snake(Str::lower($action));

                                        // Full permission name for this cell
                                        $permName = "{$groupKey}_{$actionKey}";

                                        // Available in this group?
                                        $isAvailable = $groupPermissionNames->contains($permName);

                                        // Already assigned to this role?
                                        $isChecked = $rolePermissionNames->contains($permName);
                                    @endphp

                                    <td>
                                        <div class="form-check">
                                            <input
                                                type="checkbox"
                                                class="form-check-input permission-checkbox {{ $groupKey }}-checkbox"
                                                name="permissions[]"
                                                value="{{ $permName }}"
                                                id="perm_{{ $groupKey }}_{{ $actionKey }}"
                                                @checked($isChecked)
                                                @disabled(!$isAvailable)
                                            />
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
            <div class="d-flex justify-content-between mt-2">
                <button type="submit" class="btn btn-outline-danger">Delete</button>
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-outline-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
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
    <script !src="">
        $(document).ready(function () {
            $('.row-checkbox').on('change', function () {
                const group = $(this).data('group');
                const isChecked = $(this).is(':checked');
                $(`.${group}-checkbox`).prop('checked', isChecked);
            });
        });
    </script>
@endsection
