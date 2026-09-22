$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

const aiQuestionTable = $('.ai-question-list-table').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    ordering: false,

    ajax: {
        url: aiGuideQuestionsDataUrl,
        type: 'GET',
        data: function (d) {
            d.search_value = $('#search-ai-question-form').val();
            d.type = $('.filter-type').val();
            d.is_active = $('.filter-status').val();

            return d;
        }
    },

    columns: [
        {
            data: 'title',
            orderable: false,
            render: function (data, type, row) {
                return `
                    <div class="fw-bolder">${data}</div>
                    <small class="text-muted">${row.key}</small>
                `;
            }
        },
        {
            data: 'type_label',
            orderable: false,
            render: function (data) {
                return `
                    <span class="badge bg-light-primary text-primary">
                        ${data}
                    </span>
                `;
            }
        },
        {
            data: 'prompt_label',
            orderable: false
        },
        {
            data: 'options_count',
            orderable: false,
            render: function (data, type, row) {
                return row.type === 'single_select' ? data : '-';
            }
        },
        {
            data: 'required',
            orderable: false,
            render: function (data) {
                return data
                    ? `<span class="badge bg-light-danger text-danger">Required</span>`
                    : `<span class="badge bg-light-secondary text-secondary">Optional</span>`;
            }
        },
        {
            data: 'sort_order',
            orderable: false
        },
        {
            data: 'is_active',
            orderable: false,
            render: function (data) {
                return data
                    ? `<span class="badge bg-light-success text-success">Active</span>`
                    : `<span class="badge bg-light-danger text-danger">Inactive</span>`;
            }
        },
        {
            data: 'id',
            orderable: false,
            render: function (data, type, row) {
                const buttons = [];

                if (row?.action?.can_edit) {
                    buttons.push(`
                        <a href="${aiGuideQuestionsBaseUrl}/${data}/edit"
                           class="btn btn-sm btn-outline-primary">
                            <i data-feather="edit-2"></i>
                        </a>
                    `);
                }

                if (row?.action?.can_delete) {
                    buttons.push(`
                        <button type="button"
                                class="btn btn-sm btn-outline-danger delete-question"
                                data-id="${data}">
                            <i data-feather="trash-2"></i>
                        </button>
                    `);
                }

                if (!buttons.length) {
                    return '';
                }

                return `
                    <div class="d-flex gap-1 align-items-center">
                        ${buttons.join('')}
                    </div>
                `;
            }
        }
    ],

    order: [[5, 'asc']],

    dom:
        '<"d-flex align-items-center header-actions mx-2 row mb-2"' +
        '<"col-12 d-flex flex-wrap align-items-center justify-content-between">' +
        '>t' +
        '<"d-flex mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',

    drawCallback: function () {
        feather.replace();
    },

    language: {
        sLengthMenu: 'Show _MENU_',
        search: '',
        searchPlaceholder: 'Search..',
        paginate: {
            previous: '&nbsp;',
            next: '&nbsp;'
        }
    }
});

let searchTimeout;

$('#search-ai-question-form').on('keyup', function () {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        aiQuestionTable.draw();
    }, 300);
});

$('#clear-search').on('click', function () {
    $('#search-ai-question-form').val('');
    aiQuestionTable.draw();
});

$('.filter-type, .filter-status').on('change', function () {
    aiQuestionTable.draw();
});

$(document).on('click', '.delete-question', function () {
    const id = $(this).data('id');

    Swal.fire({
        title: 'Delete Question?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#ea5455'
    }).then(result => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: `${aiGuideQuestionsBaseUrl}/${id}`,
            type: 'POST',
            data: {
                _token: csrfToken,
                _method: 'DELETE'
            },
            success: function () {
                Toastify({
                    text: 'Question deleted successfully!',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#28C76F',
                    close: true
                }).showToast();

                aiQuestionTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                Toastify({
                    text: xhr.responseJSON?.message || 'Something went wrong!',
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
