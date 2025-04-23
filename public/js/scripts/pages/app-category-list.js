
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var dt_user_table = $('.category-list-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: categoriesDataUrl,
        type: 'GET'
    },
    columns: [
        { data: null, defaultContent: '', orderable: false },
        { data: 'name' },
        { data: 'sub_categories' },
        { data: 'sub_categories' },
        { data: 'no_of_products' },
        { data: 'added_date' },

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
              <a href="#"
                 class="dropdown-item view-details"
                 data-bs-toggle="modal"
                 data-bs-target="#modals-slide-in"
                 data-id="${data}"
                 data-name="${row.name}"
                 data-description="${row.description}"
                 data-subcategories="${row.sub_categories}"
                 data-products="${row.no_of_products}"
                 data-added="${row.added_date}">
                <i data-feather="file-text"></i> Details
              </a>
               <a href="/products/${data}/edit" class="dropdown-item">
                <i data-feather="edit"></i> Edit
              </a>


              <a href="#" class="dropdown-item text-danger delete-category" data-id="${data}">
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
            text: 'Add New Category',
            className: 'add-new btn btn-primary',
            action: function (e, dt, node, config) {
                window.location.href = categoriesCreateUrl;
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

$(document).ready(function ()   {

    $(document).ready(function () {
        // Check if the product was added successfully
        if (sessionStorage.getItem('Category_added') == 'true') {
            // Show the success Toastify message
            Toastify({
                text: "Category added successfully!",
                duration: 4000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28a745",  // Green for success
                close: true
            }).showToast();

            // Remove the flag after showing the Toastify message
            sessionStorage.removeItem('Category_added');
        }
    });

    $(document).on('click', '.delete-category', function (e) {
        e.preventDefault();

        var $table = $('.category-list-table').DataTable();
        var $row = $(this).closest('tr');
        var rowData = $table.row($row).data();

        var categoryId = $(this).data('id');
        var categoryName = rowData.name;

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to delete user "${categoryName}". This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: `/categories/${categoryId}`,
                    method: 'DELETE',
                    success: function (res) {
                        Swal.fire('Deleted!', 'Category has been deleted.', 'success');
                        $table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire('Failed', 'Could not delete category.', 'error');
                    }
                });
            }
        });
    });
    $(document).on('click', '.view-details', function (e) {
        e.preventDefault();

        // Get the data from attributes
        const categoryName = $(this).data('name');
        const subCategories = $(this).data('subcategories');
        const products = $(this).data('products');
        const addedDate = $(this).data('added');
        const description = $(this).data('description');
        console.log(description)

        console.log(subCategories)
        // Populate modal
        $('#showCategoryModal #category-name').val(categoryName);
        $('#showCategoryModal #category-subcategories').val(subCategories);
        $('#showCategoryModal #category-products').val(products);
        $('#showCategoryModal #category-date').val(addedDate);
        $('#showCategoryModal #category-description').val(description);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('showCategoryModal'));
        modal.show();
    });





});
