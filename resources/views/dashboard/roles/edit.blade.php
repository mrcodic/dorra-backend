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
            <div class="col-md-6 mb-1">
                <label class="form-label" for="modalRoleName">Role Name (EN)</label>
                <input type="text" id="modalRoleName" name="name[en]" class="form-control"
                       value="{{ $model->getTranslation('name','en') }}"
                       placeholder="Enter role name in english" tabindex="-1" data-msg="Please enter role name"/>
            </div>
            <div class="col-md-6 mb-1">
                <label class="form-label" for="modalRoleName">Role Name (Ar)</label>
                <input type="text" id="modalRoleName" name="name[ar]" class="form-control"
                       value="{{ $model->getTranslation('name','ar') }}"
                       placeholder="Enter role name in arabic" tabindex="-1" data-msg="Please enter role name"/>
            </div>


            <!-- New Role Description field -->
            <div class="col-md-6 mt-1">
                <label class="form-label" for="modalRoleDescription">Role Description (EN)</label>
                <textarea id="modalRoleDescription" name="description[en]" class="form-control" rows="3"
                          placeholder="Enter role description in english"
                          data-msg="Please enter a description for the role">{{ $model->getTranslation('description','en') }}</textarea>
            </div>
            <div class="col-md-6 mt-1">
                <label class="form-label" for="modalRoleDescription">Role Description (AR)</label>
                <textarea id="modalRoleDescription" name="description[ar]" class="form-control" rows="3"
                          placeholder="Enter role description in arabic"
                          data-msg="Please enter a description for the role ">{{ $model->getTranslation('description','ar') }}</textarea>
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

                            $rolePermissionNames = $model->permissions
                                ->pluck('name')
                                ->map(fn ($n) => Str::lower($n))
                                ->values();
                        @endphp

                        @foreach($associatedData['permissions'] as $group => $groupPermissions)
                            @php

                                $groupKey  = Str::kebab($group);

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
                                        $actionKey = Str::snake(Str::lower($action));
                                        $permName = "{$groupKey}_{$actionKey}";
                                 $supportedActions = $groupPermissions->pluck('name')->map(fn ($n) => Str::afterLast($n, '_'))
                                          ->unique()
                                          ->values()
                                          ->all();
                                        $isAvailable = collect($supportedActions)->contains(strtolower($action));
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
                <button type="button" data-bs-toggle="modal" data-bs-target="#deleteRoleModal"
                        class="btn btn-outline-danger">Delete
                </button>
                <div class="d-flex gap-1">
                    <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
        <!--/ Add role form -->
    </div>
    @include('modals.delete',[
     'id' => 'deleteRoleModal',
    'formId' => 'deleteRoleForm',
    'title' => 'Delete Role',
    'action' => route('roles.destroy', $model),
])
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
        handleAjaxFormSubmit("#editRoleForm", {
            successMessage: "Role updated successfully",
            onSuccess: function () {
                location.replace('/roles');

            }
        })
        handleAjaxFormSubmit("#deleteRoleForm", {
            successMessage: "Role deleted successfully",
            onSuccess: function () {
                location.replace('/roles');

            }
        })
    </script>
    <script>

        // ✅ Keep the rest of your code; just ensure every bulk op ignores disabled
        // Global "Select All"
        $(document).on('change', '#selectAllGlobal', function () {
            const isChecked = $(this).is(':checked');

            // only toggle enabled permissions
            $('.permission-checkbox:not(:disabled)').prop('checked', isChecked);

            // sync each row's group checkbox
            $('.row-checkbox').each(function () {
                const group    = $(this).data('group');
                const $enabled = $(`.${group}-checkbox:not(:disabled)`);
                if ($enabled.length) {
                    $(this).prop('checked', isChecked).prop('indeterminate', false);
                } else {
                    $(this).prop({ checked: false, indeterminate: false });
                }
            });
            // Row-level "Select All"
            $(document).on('change', '.row-checkbox', function () {
                const group     = $(this).data('group');
                const isChecked = $(this).is(':checked');

                // ✅ Only toggle enabled inputs
                $(`.${group}-checkbox:not(:disabled)`).prop('checked', isChecked);

                updateGlobalToggle();
            });

            // Individual checkbox click
            $(document).on('change', '.permission-checkbox', function () {
                const group = (this.className.match(/(^|\s)([A-Za-z0-9\-]+)-checkbox(\s|$)/) || [])[2];
                if (group) updateGroupToggle(group);
                updateGlobalToggle();
            });

            function updateGroupToggle(group) {
                const $all        = $(`.${group}-checkbox`);
                const $enabled    = $all.filter(':not(:disabled)');
                const $enabledOn  = $enabled.filter(':checked');
                const $rowToggle  = $(`.row-checkbox[data-group="${group}"]`);

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

            // Initialize correct states on load
            $(function () {
                $('.row-checkbox').each(function () { updateGroupToggle($(this).data('group')); });
                updateGlobalToggle();
            });
        });
    </script>

@endsection
