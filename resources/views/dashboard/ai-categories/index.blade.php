@extends('layouts/contentLayoutMaster')

@section('title', 'AI Products')
@section('main-page', 'AI Products')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endsection

@section('content')
    <div class="card p-1">
        <section class="app-user-list">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-1">
                        <div>
                            <h4 class="mb-25">AI Products</h4>
                            <p class="text-muted mb-0">
                                Manage product AI configuration, credits and generation settings.
                            </p>
                        </div>

                        @can('ai-categories_create')
                            <a href="{{ route('ai-categories.create') }}" class="btn btn-primary">
                                <i data-feather="plus"></i>
                                Add AI Product
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-datatable table-responsive pt-0">
                    <div class="px-1 mb-2 d-flex flex-wrap align-items-center gap-1">
                        <div class="position-relative flex-grow-1 col-12 col-md-4">
                            <i data-feather="search"
                               class="position-absolute top-50 translate-middle-y ms-2 text-muted"></i>

                            <input type="text"
                                   id="search-ai-product"
                                   class="form-control ps-5 pe-3"
                                   placeholder="Search product...">

                            <button type="button"
                                    id="clear-search"
                                    class="border-0 bg-transparent position-absolute"
                                    style="right:10px;top:50%;transform:translateY(-50%);font-size:18px;color:#aaa">
                                &times;
                            </button>
                        </div>

                        <div class="col-12 col-md-2">
                            <select class="form-select filter-generation-type">
                                <option value="">All Types</option>

                                @foreach(\App\Enums\Ai\AiGenerationTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}">
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <select class="form-select filter-enabled">
                                <option value="">All Status</option>
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div>

                    <table class="ai-product-list-table table">
                        <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Generation Type</th>
                            <th>Prompt Template</th>
                            <th>Resolution</th>
                            <th>Aspect Ratio</th>
                            <th>Credits</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
@endsection

@section('page-script')
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        const aiProductsDataUrl = "{{ route('ai-categories.data') }}";
        const aiProductsBaseUrl = "{{ url('ai-categories') }}";
        const csrfToken = "{{ csrf_token() }}";

        const table = $('.ai-product-list-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: false,

            ajax: {
                url: aiProductsDataUrl,
                type: 'GET',
                data: function (d) {
                    d.search_value = $('#search-ai-product').val();
                    d.generation_type = $('.filter-generation-type').val();
                    d.enabled = $('.filter-enabled').val();
                    return d;
                }
            },

            columns: [
                {
                    data: 'category_name',
                    render: function (data) {
                        return `<div class="fw-bolder">${data ?? '-'}</div>`;
                    }
                },
                {
                    data: 'generation_type_label',
                    render: function (data) {
                        return `
                            <span class="badge bg-light-primary text-primary">
                                ${data}
                            </span>
                        `;
                    }
                },
                {
                    data: 'prompt_template_name'
                },
                {
                    data: 'default_resolution',
                    render: data => data || '-'
                },
                {
                    data: 'aspect_ratio',
                    render: data => data || '-'
                },
                {
                    data: 'credits_cost',
                    render: data => `${data} Credits`
                },
                {
                    data: 'enabled',
                    render: function (data) {
                        return data
                            ? `<span class="badge bg-light-success text-success">Enabled</span>`
                            : `<span class="badge bg-light-danger text-danger">Disabled</span>`;
                    }
                },
                {
                    data: 'id',
                    render: function (id, type, row) {
                        const buttons = [];

                        if (row?.action?.can_edit) {
                            buttons.push(`
                                <a href="${aiProductsBaseUrl}/${id}/edit"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Edit">
                                    <i data-feather="edit-2"></i>
                                </a>
                            `);

                            buttons.push(`
                                <a href="${aiProductsBaseUrl}/${id}/questions"
                                   class="btn btn-sm btn-outline-info"
                                   title="Configure Questions">
                                    <i data-feather="list"></i>
                                </a>
                            `);
                        }

                        if (row?.action?.can_delete) {
                            buttons.push(`
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger delete-ai-product"
                                        data-id="${id}"
                                        title="Delete">
                                    <i data-feather="trash-2"></i>
                                </button>
                            `);
                        }

                        return `
                            <div class="d-flex gap-1 align-items-center">
                                ${buttons.join('')}
                            </div>
                        `;
                    }
                }
            ],

            drawCallback: function () {
                feather.replace();
            },

            language: {
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;'
                }
            }
        });

        let searchTimeout;

        $('#search-ai-product').on('keyup', function () {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                table.draw();
            }, 300);
        });

        $('#clear-search').on('click', function () {
            $('#search-ai-product').val('');
            table.draw();
        });

        $('.filter-generation-type, .filter-enabled').on('change', function () {
            table.draw();
        });

        $(document).on('click', '.delete-ai-product', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Delete AI Product?',
                text: 'This will remove the AI configuration for this product.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#ea5455'
            }).then(result => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `${aiProductsBaseUrl}/${id}`,
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        _method: 'DELETE'
                    },

                    success: function () {
                        Toastify({
                            text: 'AI Product deleted successfully!',
                            duration: 2500,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#28C76F',
                            close: true
                        }).showToast();

                        table.ajax.reload(null, false);
                    },

                    error: function (xhr) {
                        Toastify({
                            text: xhr.responseJSON?.message ?? 'Something went wrong!',
                            duration: 4000,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#EA5455',
                            close: true
                        }).showToast();
                    }
                });
            });
        });

        feather.replace();
    </script>
@endsection
