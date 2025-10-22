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
        // Global "Select All" (top-left)
        $('#selectAllGlobal').on('change', function () {
            const isChecked = $(this).is(':checked');

            // Only affect enabled permission checkboxes
            $('.permission-checkbox:not(:disabled)').prop('checked', isChecked);

            // Sync each row's group checkbox (ignore groups with no enabled items)
            $('.row-checkbox').each(function () {
                const group = $(this).data('group');
                const $enabled = $(`.${group}-checkbox:not(:disabled)`);
                if ($enabled.length) {
                    $(this).prop('checked', isChecked).prop('indeterminate', false);
                } else {
                    $(this).prop('checked', false).prop('indeterminate', false);
                }
            });
        });

        // Row-level "Select All" for a specific group
        $(document).on('change', '.row-checkbox', function () {
            const group     = $(this).data('group');
            const isChecked = $(this).is(':checked');

            // Only affect enabled boxes within that group
            $(`.${group}-checkbox:not(:disabled)`).prop('checked', isChecked);

            // Update global indeterminate/checked state
            updateGlobalToggle();
        });

        // Individual permission checkbox clicked -> keep row/global states accurate
        $(document).on('change', '.permission-checkbox', function () {
            const idParts = this.id.split('_'); // optional; or derive group from classes
            // If you used class "<group>-checkbox", get group from class:
            const group = (this.className.match(/(^|\s)([A-Za-z0-9\-]+)-checkbox(\s|$)/) || [])[2];
            if (group) updateGroupToggle(group);
            updateGlobalToggle();
        });

        // Helpers
        function updateGroupToggle(group) {
            const $all        = $(`.${group}-checkbox`);
            const $enabled    = $all.filter(':not(:disabled)');
            const $enabledOn  = $enabled.filter(':checked');

            const $rowToggle = $(`.row-checkbox[data-group="${group}"]`);
            if (!$enabled.length) {
                $rowToggle.prop({ checked: false, indeterminate: false });
                return;
            }
            if ($enabledOn.length === 0) {
                $rowToggle.prop({ checked: false, indeterminate: false });
            } else if ($enabledOn.length === $enabled.length) {
                $rowToggle.prop({ checked: true, indeterminate: false });
            } else {
                $rowToggle.prop({ checked: false, indeterminate: true });
            }
        }

        function updateGlobalToggle() {
            const $allEnabled = $('.permission-checkbox:not(:disabled)');
            const $onEnabled  = $allEnabled.filter(':checked');

            if ($allEnabled.length === 0) {
                $('#selectAllGlobal').prop({ checked: false, indeterminate: false });
                return;
            }
            if ($onEnabled.length === 0) {
                $('#selectAllGlobal').prop({ checked: false, indeterminate: false });
            } else if ($onEnabled.length === $allEnabled.length) {
                $('#selectAllGlobal').prop({ checked: true, indeterminate: false });
            } else {
                $('#selectAllGlobal').prop({ checked: false, indeterminate: true });
            }
        }

        // On page load, initialize correct states
        $(function () {
            // initialize each group’s row toggle
            $('.row-checkbox').each(function () {
                updateGroupToggle($(this).data('group'));
            });
            // initialize global toggle
            updateGlobalToggle();
        });

    </script>

@endsection
