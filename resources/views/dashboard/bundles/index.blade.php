@extends('layouts/contentLayoutMaster')

@section('title', 'Bundles')
@section('main-page', 'Bundles')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
@endsection

@section('content')
<section class="app-user-list">
    <div class="card">
        <div class="card-datatable table-responsive pt-0">

            <div class="row gx-2 gy-2 align-items-center px-1 py-1">

                <div class="col-12 col-md-7">
                    <form action="" method="get" class="position-relative search-form">
                        <i
                            data-feather="search"
                            class="position-absolute top-50 translate-middle-y ms-2 text-muted"
                        ></i>

                        <input
                            type="text"
                            class="form-control ps-5 border rounded-3"
                            id="search-bundle-form"
                            placeholder="Search bundle..."
                            style="height: 38px;"
                        >

                        <button
                            type="button"
                            id="clear-bundle-search"
                            class="position-absolute top-50 translate-middle-y text-muted"
                            style="margin-right: 5px; right: 0; background: transparent; border: none; font-weight: bold; color: #aaa; cursor: pointer; font-size: 18px;"
                        >
                            &times;
                        </button>
                    </form>
                </div>

                <div class="col-6 col-md-2">
                    <select class="form-select filter-bundle-status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                @can('bundles_create')
                    <div class="col-12 col-md-3 text-md-end">
                        <a
                            class="btn btn-outline-primary w-100 w-md-auto"
                            data-bs-toggle="modal"
                            data-bs-target="#addBundleModal"
                        >
                            <i data-feather="plus"></i>
                            Add New Bundle
                        </a>
                    </div>
                @endcan
            </div>

            <table class="bundle-list-table table">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input
                                type="checkbox"
                                id="select-all-bundles"
                                class="form-check-input"
                                @disabled(!auth()->user()->hasPermissionTo('bundles_delete'))
                            >
                        </th>
                        <th>Bundle</th>
                        <th>Trigger</th>
                        <th>Rewards</th>
                        <th>Usage</th>
                        <th>Popup</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>

            <div
                id="bundle-bulk-delete-container"
                class="my-2 bulk-delete-container"
                style="display: none;"
            >
                <div class="delete-container d-flex align-items-center justify-content-between">
                    <p id="bundle-selected-count-text" class="mb-0">0 bundles are selected</p>

                    <button
                        type="button"
                        id="delete-selected-bundles-btn"
                        class="btn btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteBundlesModal"
                    >
                        <i data-feather="trash-2"></i>
                        Delete Selected
                    </button>
                </div>
            </div>
        </div>

        @include('modals.bundles.show-bundle')
        @include('modals.bundles.add-bundle')
        @include('modals.bundles.edit-bundle')

        @include('modals.delete', [
            'id' => 'deleteBundleModal',
            'formId' => 'deleteBundleForm',
            'title' => 'Delete Bundle',
        ])

        @include('modals.delete', [
            'id' => 'deleteBundlesModal',
            'formId' => 'deleteBundlesForm',
            'buttonId' => 'confirm-delete-bundles',
            'title' => 'Delete Bundles',
            'confirmText' => 'Are you sure you want to delete these bundles?',
        ])
    </div>
</section>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
@endsection

@section('page-script')
<script>
    window.bundleDisplayOnVisitBundleId = @json($associatedData['display_bundle_on_visit_bundle_id'] ?? null);

    const bundlesDataUrl = @json(route('bundles.data'));
    const bundleStoreUrl = @json(route('bundles.store'));
    const bundleItemMetaUrl = @json(route('bundles.item-meta'));
    const bundleProductsByCategoriesUrl = @json(route('products.categories'));
    const bundleUpdateUrlTemplate = @json(route('bundles.update', ['bundle' => '__ID__']));
    const bundleDeleteUrlTemplate = @json(route('bundles.destroy', ['bundle' => '__ID__']));
    const bundleBulkDeleteUrl = @json(route('bundles.bulk-delete'));
    const bundleCsrfToken = @json(csrf_token());
</script>

<script src="{{ asset('js/scripts/pages/app-bundle-list.js') }}?v={{ time() }}"></script>
@endsection
