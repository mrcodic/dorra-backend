@extends('layouts/contentLayoutMaster')

@section('title', 'AI Studio Items')
@section('main-page', 'AI Studio Items')

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
                            <h4 class="mb-25">AI Studio Items</h4>
                            <p class="text-muted mb-0">
                                Manage Image, Illustration, Pattern, Logo and other AI Studio generation modes.
                            </p>
                        </div>

                        @can('ai-studio-items_create')
                            <a href="{{ route('ai-studio-items.create') }}" class="btn btn-primary">
                                <i data-feather="plus"></i>
                                Add Studio Item
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
                                   id="search-studio-item"
                                   class="form-control ps-5 pe-3"
                                   placeholder="Search studio item...">

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
                            <select class="form-select filter-status">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <table class="ai-studio-items-table table">
                        <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Generation Type</th>
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
@endsection

@section('page-script')
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        $(function () {
            const dataUrl = "{{ route('ai-studio-items.data') }}";
            const baseUrl = "{{ url('ai-studio-items') }}";
            const csrfToken = "{{ csrf_token() }}";

            const table = $('.ai-studio-items-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,

                ajax: {
                    url: dataUrl,
                    data: function (d) {
                        d.search_value = $('#search-studio-item').val();
                        d.generation_type = $('.filter-generation-type').val();
                        d.is_active = $('.filter-status').val();
                    }
                },

                columns: [
                    {
                        data: 'name',
                        render: function (data, type, row) {
                            const image = row.image
                                ? `<img src="${row.image}" width="42" height="42"
                                class="rounded me-1" style="object-fit:cover">`
                                : '';

                            return `
                        <div class="d-flex align-items-center">
                            ${image}
                            <span class="fw-bolder">${data ?? '-'}</span>
                        </div>
                    `;
                        }
                    },
                    {
                        data: 'generation_type_label',
                        render: data => `
                    <span class="badge bg-light-primary text-primary">
                        ${data ?? '-'}
                    </span>
                `
                    },
                    {
                        data: 'credits_cost',
                        render: data => `${data ?? 0} Credits`
                    },
                    {
                        data: 'is_active',
                        render: data => data
                            ? `<span class="badge bg-light-success text-success">Active</span>`
                            : `<span class="badge bg-light-danger text-danger">Inactive</span>`
                    },
                    {
                        data: 'id',
                        render: function (id, type, row) {
                            const buttons = [];

                            if (row?.action?.can_edit) {
                                buttons.push(`
                            <a href="${baseUrl}/${id}/edit"
                               class="btn btn-sm btn-outline-primary"
                               title="Edit">
                                <i data-feather="edit-2"></i>
                            </a>

                            <a href="${baseUrl}/${id}/questions"
                               class="btn btn-sm btn-outline-info"
                               title="Configure Questions">
                                <i data-feather="list"></i>
                            </a>
                        `);
                            }

                            if (row?.action?.can_delete) {
                                buttons.push(`
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger delete-item"
                                    data-id="${id}">
                                <i data-feather="trash-2"></i>
                            </button>
                        `);
                            }

                            return `<div class="d-flex gap-1">${buttons.join('')}</div>`;
                        }
                    }
                ],

                drawCallback: function () {
                    feather.replace();
                }
            });

            let timer;

            $('#search-studio-item').on('keyup', function () {
                clearTimeout(timer);
                timer = setTimeout(() => table.draw(), 300);
            });

            $('#clear-search').on('click', function () {
                $('#search-studio-item').val('');
                table.draw();
            });

            $('.filter-generation-type, .filter-status').on('change', function () {
                table.draw();
            });

            $(document).on('click', '.delete-item', function () {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Delete Studio Item?',
                    text: 'This will remove this AI Studio configuration.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    confirmButtonColor: '#ea5455'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: `${baseUrl}/${id}`,
                        type: 'POST',
                        data: {
                            _token: csrfToken,
                            _method: 'DELETE'
                        },
                        success: function (response) {
                            Toastify({
                                text: response.message ?? 'Deleted successfully.',
                                duration: 2500,
                                gravity: 'top',
                                position: 'right',
                                backgroundColor: '#28C76F'
                            }).showToast();

                            table.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            Toastify({
                                text: xhr.responseJSON?.message ?? 'Something went wrong.',
                                duration: 4000,
                                gravity: 'top',
                                position: 'right',
                                backgroundColor: '#EA5455'
                            }).showToast();
                        }
                    });
                });
            });

            feather.replace();
        });
    </script>
@endsection

