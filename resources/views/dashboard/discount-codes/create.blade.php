@extends('layouts/contentLayoutMaster')

@section('title', 'Create New Invoice')
@section('main-page', 'Invoices')
@section('sub-page', 'Create New Invoice')

@section('vendor-style')
<!-- Vendor CSS Files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
<div class="container bg-white rounded-3 p-3 d-flex justify-content-center">

    <form action="{{ route('invoices.store') }}" method="POST" class="w-75">
        @csrf

        {{-- Invoice Number --}}
        <div class="mb-3">
            <label for="invoice_number" class="form-label fw-bold">Invoice Number</label>
            <input type="text" class="form-control" id="invoice_number" name="invoice_number" required>
        </div>

        {{-- Issued Date --}}
        <div class="mb-3">
            <label for="issued_date" class="form-label fw-bold">Issued Date</label>
            <input type="date" class="form-control" id="issued_date" name="issued_date" required>
        </div>

        {{-- Invoice Status --}}
        <div class="mb-3">
            <label class="form-label fw-bold d-block">Invoice Status</label>
            <div class="d-flex gap-3 mt-2">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_paid" value="Paid" required>
                    <label class="form-check-label text-black" for="status_paid">Paid</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_pending" value="Pending">
                    <label class="form-check-label text-black" for="status_pending">Pending</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_failed" value="Failed">
                    <label class="form-check-label text-black" for="status_failed">Failed</label>
                </div>
            </div>
        </div>

        {{-- Select Client --}}
        <div class="mb-3 row d-flex align-items-center">
            <div class="col-md-6">
                <label for="client_id" class="form-label fw-bold">Client</label>
                <select class="form-select select2" name="client_id" id="client_id" required>
                    <option value="">Select a client</option>

                    <option value="">name</option>

                </select>
            </div>
            <div class="col-md-6">
                <p class="fs-16 text-black"><span class="text-dark">Name:</span>John Doe</p>
                <p class="fs-16 text-black"><span class="text-dark">Email Address:</span>John Doe</p>
                <p class="fs-16 text-black"><span class="text-dark">Phone Number:</span>John Doe</p>
            </div>
        </div>

        {{-- Invoice Items Section --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Items</label>

            <div class="table-responsive  rounded">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Item</th>
                            <th style="width: 20%">Quantity</th>
                            <th style="width: 20%">Price</th>
                            <th style="width: 10%"></th> {{-- Delete column --}}
                        </tr>
                    </thead>
                    <tbody id="invoice-items-container">
                        {{-- Item Row Template --}}
                        <tr>
                            {{-- Item (Image + Name) --}}
                            <td class="d-flex align-items-center gap-3">
                                <img src="https://via.placeholder.com/50" alt="Item Image" class="rounded" width="50" height="50">
                                <span class="fw-bold">Item Name</span>
                            </td>

                            {{-- Quantity --}}
                            <td>
                                <select class="form-select form-select-sm" name="items[0][quantity]">
                                    @for ($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                </select>
                            </td>

                            {{-- Price --}}
                            <td>
                                EGP 200.00
                            </td>

                            {{-- Delete Button --}}
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger">
                                    <i data-feather="trash-2"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Add Items Button --}}
            <button type="button" id="add-item-btn" class="btn btn-outline-primary mt-3">
                <i data-feather="plus"></i> Add Items
            </button>
            <hr />
        </div>

        {{-- Add Discount Code --}}
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="" id="add-discount-checkbox">
            <label class="form-check-label fw-bold" for="add-discount-checkbox">
                Add Discount Code
            </label>
        </div>

        <div class="mb-3 " id="discount-code-row" style="display: none;">
            <div class="input-group">
                <input type="text" class="form-control" name="discount_code" placeholder="Enter discount code">
                <button class="btn btn-secondary" type="button">Apply</button>
            </div>
        </div>



        <!-- Pricing Summary -->
        <h5 class="mt-3 mb-1 text-black fs-16">Pricing Details</h5>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-dark fs-16 fw-bold">Subtotal</span>
            <span class="fs-4 text-black fw-bold">$65.00</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-dark fs-16 fw-bold">Discount</span>
            <span class="fs-16 text-black">-$5.00</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-dark fs-16 fw-bold">
                Delivery
                <i data-feather="info" data-bs-toggle="tooltip" title="Delivery charges may vary based on location."></i>
            </span>
            <span class="fs-16 text-black">$5.00</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span class="text-dark fs-16 fw-bold">
                Tax
                <i data-feather="info" data-bs-toggle="tooltip" title="Tax is calculated as per applicable laws."></i>
            </span>
            <span class="fs-16 text-black">$3.00</span>
        </div>

        <hr class="border-dashed my-1">

        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
            <span class="fs-4 text-black ">Total</span>
            <span class="fs-4 text-black fw-bold">$68.00</span>
        </div>

        {{-- Note Field --}}
        <div class="mb-3">
            <label for="note" class="form-label fw-bold">Note</label>
            <textarea class="form-control" id="note" name="note" rows="3"></textarea>
        </div>

        {{-- Buttons Row --}}
        <div class="d-flex justify-content-between">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancel</a>

            <div class="d-flex gap-2">
                <button type="submit" name="action" value="save" class="btn btn-outline-secondary">Add</button>
                <button type="submit" name="action" value="save_download" class="btn btn-primary">Add & Download</button>
            </div>
        </div>

    </form>

</div>

@endsection


@section('vendor-script')
<script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="https://unpkg.com/feather-icons"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        feather.replace();

        const discountCheckbox = document.getElementById('add-discount-checkbox');
        const discountRow = document.getElementById('discount-code-row');

        discountCheckbox.addEventListener('change', function() {
            discountRow.style.display = this.checked ? 'block' : 'none';
        });
    });
</script>




@endsection