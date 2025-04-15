var dt_user_table = $('.user-list-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: usersDataUrl,
        type: 'GET'
    },
    columns: [
        { data: null, defaultContent: '', orderable: false },
        { data: 'name' },
        { data: 'email' },
        { data: 'status' },
        { data: 'joined_date' },
        { data: 'orders_count' },
        {
            data: 'id',
            orderable: false,
            render: function (data, type, row, meta) {
                return `
          <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i data-feather="more-vertical"></i>
            </button>
            <div class="dropdown-menu">
              <a href="/users/${data}/edit" class="dropdown-item">
                <i data-feather="edit"></i> Edit
              </a>
              <a href="#" class="dropdown-item text-danger delete-user" data-id="${data}">
                <i data-feather="trash-2"></i> Delete
              </a>
            </div>
          </div>
        `;
            }
        }
    ],
    order: [[1, 'asc']],
    dom:
        '<"d-flex justify-content-between align-items-center header-actions mx-2 row mt-75"' +
        '<"col-sm-12 col-lg-4 d-flex justify-content-center justify-content-lg-start" l>' +
        '<"col-sm-12 col-lg-8 ps-xl-75 ps-0"<"dt-action-buttons d-flex align-items-center justify-content-center justify-content-lg-end flex-lg-nowrap flex-wrap"<"me-1"f>B>>' +
        '>t' +
        '<"d-flex justify-content-between mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
    buttons: [
        {
            extend: 'collection',
            className: 'btn btn-outline-secondary dropdown-toggle me-2',
            text: feather.icons['external-link'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
            buttons: [
                {
                    extend: 'print',
                    text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'csv',
                    text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'excel',
                    text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'pdf',
                    text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'copy',
                    text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                }
            ],
            init: function (api, node, config) {
                $(node).removeClass('btn-secondary');
                $(node).parent().removeClass('btn-group');
                setTimeout(function () {
                    $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex mt-50');
                }, 50);
            }
        },
        {
            text: 'Add New User',
            className: 'add-new btn btn-primary',
            attr: {
                'data-bs-toggle': 'modal',
                'data-bs-target': '#modals-slide-in'
            },
            init: function (api, node, config) {
                $(node).removeClass('btn-secondary');
            }
        }
    ],
    drawCallback: function () {
        feather.replace();
    },
    language: {
        sLengthMenu: 'Show _MENU_',
        search: 'Search',
        searchPlaceholder: 'Search..',
        paginate: {
            previous: '&nbsp;',
            next: '&nbsp;'
        }
    }
});
