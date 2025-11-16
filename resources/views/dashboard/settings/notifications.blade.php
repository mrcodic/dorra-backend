@extends('layouts/contentLayoutMaster')

@section('title', 'Settings-Notifications')
@section('main-page', 'Notifications')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')


<div class="bg-white p-2">
    {{-- Customers Notifications Table --}}
    <form action="" method="POST">
        @csrf

        @foreach($groups as $scope => $rows)
            <div class="table-responsive mb-2">
                <table class="table">
                    <thead>
                    <tr>
                        <th>
                            <div class="form-check">
                                @php $scopeId = 'selectAll-'.$scope; @endphp
                                <input type="checkbox" class="form-check-input select-all" id="{{ $scopeId }}" data-scope="{{ $scope }}">
                                <label class="form-check-label" for="{{ $scopeId }}">{{ Str::headline($scope) }}</label>
                            </div>
                        </th>
                        <th>Email</th>
                        <th>Notification</th>
                    </tr>
                    </thead>
                    <tbody data-scope="{{ $scope }}">
                    @foreach($rows as $i => $row)
                        @php $rowId = $scope.'-'.$i; @endphp
                        <tr>
                            <td>
                                <div class="form-check d-flex align-items-center gap-2">
                                    <input type="checkbox" class="form-check-input row-checkbox" id="{{ $rowId }}"
                                        {{ ($row['email'] || $row['notification']) ? 'checked' : '' }}>
                                    <label for="{{ $rowId }}">{{ $row['label'] }}</label>
                                </div>
                            </td>

                            <td>
                                <input type="hidden" name="settings[{{ $row['dot'] }}.email]" value="0">
                                <input type="checkbox" class="form-check-input permission-checkbox"
                                       name="settings[{{ $row['dot'] }}.email]" value="1"
                                    {{ $row['email'] ? 'checked' : '' }}>
                            </td>

                            <td>
                                <input type="hidden" name="settings[{{ $row['dot'] }}.notification]" value="0">
                                <input type="checkbox" class="form-check-input permission-checkbox"
                                       name="settings[{{ $row['dot'] }}.notification]" value="1"
                                    {{ $row['notification'] ? 'checked' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <div class="d-flex justify-content-end mt-2">
            <button type="reset" class="btn btn-outline-secondary me-1">Discard Changes</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>


    <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-secondary me-1">Discard Changes</button>
        <button class="btn btn-primary">Save</button>
    </div>
</div>
@endsection

@section('vendor-script')
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/tables/datatable/datatables.checkboxes.min.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script src="{{ asset(mix('js/scripts/pages/modal-add-role.js')) }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-access-roles.js')) }}"></script>
@endsection
