@extends('layouts/contentLayoutMaster')

@section('title', 'Settings - Notifications')
@section('main-page', 'Notifications')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
@php
    $customerNotifications = [
        'New Customer Signed up',
    ];

    $orderNotifications = [
        'Order Purchased',
        'Order Cancelled',
        'Order Confirmed',
        'Order Refund Request',
        'Order Payment Error',
    ];
    $shipping = [
        'Picked Up',
        'Delivered',
    ];
@endphp

<div class="container bg-white p-3">
    {{-- Customers Notifications Table --}}
    <div class="table-responsive mb-4">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAllCustomers" />
                            <label class="form-check-label" for="selectAllCustomers">Customers</label>
                        </div>
                    </th>
                    <th>Email</th>
                    <th>Notification</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customerNotifications as $index => $notification)
                    <tr>
                        <td>
                            <div class="form-check d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input row-checkbox" id="cust-{{ $index }}" />
                                <label for="cust-{{ $index }}">{{ $notification }}</label>
                            </div>
                        </td>
                        <td>
                            <input type="checkbox" class="form-check-input permission-checkbox" name="email_permissions[]" />
                        </td>
                        <td>
                            <input type="checkbox" class="form-check-input permission-checkbox" name="notification_permissions[]" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Orders Notifications Table --}}
    <div class="table-responsive mb-4">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAllOrders" />
                            <label class="form-check-label" for="selectAllOrders">Orders</label>
                        </div>
                    </th>
                    <th>Email</th>
                    <th>Notification</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderNotifications as $index => $notification)
                    <tr>
                        <td>
                            <div class="form-check d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input row-checkbox" id="order-{{ $index }}" />
                                <label for="order-{{ $index }}">{{ $notification }}</label>
                            </div>
                        </td>
                        <td>
                            <input type="checkbox" class="form-check-input permission-checkbox" name="email_permissions[]" />
                        </td>
                        <td>
                            <input type="checkbox" class="form-check-input permission-checkbox" name="notification_permissions[]" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

        {{-- Shipping Notifications Table --}}
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAllOrders" />
                            <label class="form-check-label" for="selectAllOrders">Shipping</label>
                        </div>
                    </th>
                    <th>Email</th>
                    <th>Notification</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shipping as $index => $shipping)
                    <tr>
                        <td>
                            <div class="form-check d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input row-checkbox" id="shipping-{{ $index }}" />
                                <label for="shipping-{{ $index }}">{{ $shipping }}</label>
                            </div>
                        </td>
                        <td>
                            <input type="checkbox" class="form-check-input permission-checkbox" name="email_permissions[]" />
                        </td>
                        <td>
                            <input type="checkbox" class="form-check-input permission-checkbox" name="notification_permissions[]" />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

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
