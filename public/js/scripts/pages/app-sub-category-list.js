$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var dt_user_table = $('.sub-category-list-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: subCategoriesDataUrl,
        type: 'GET'
    },
    columns: [
        {data: null, defaultContent: '', orderable: false},
        {data: 'name'},
        {data: 'no_of_products'},
        {data: 'added_date'},

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
              <a href=""
                 class="dropdown-item view-details"
                 data-bs-toggle="modal"
                 data-bs-target="#showSubCategoryModal"
                 data-id="${data}"
                 data-name_ar="${row.name_ar}"
                 data-name_en="${row.name_en}"
                 data-products="${row.no_of_products}"
                 data-showdate="${row.show_date}"
                 data-parent="${row.parent_name}">
                <i data-feather="file-text"></i> Details
              </a>

              <a href="#" class="dropdown-item text-danger delete-sub-category" data-id="${data}">
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
            text: feather.icons['external-link'].toSvg({class: 'font-small-4 me-50'}) + 'Export',
            buttons: [
                {
                    extend: 'print',
                    text: feather.icons['printer'].toSvg({class: 'font-small-4 me-50'}) + 'Print',
                    className: 'dropdown-item',
                    exportOptions: {columns: [1, 2, 3, 4, 5]}
                },
                {
                    extend: 'csv',
                    text: feather.icons['file-text'].toSvg({class: 'font-small-4 me-50'}) + 'Csv',
                    className: 'dropdown-item',
                    exportOptions: {columns: [1, 2, 3, 4, 5]}
                },
                {
                    extend: 'excel',
                    text: feather.icons['file'].toSvg({class: 'font-small-4 me-50'}) + 'Excel',
                    className: 'dropdown-item',
                    exportOptions: {columns: [1, 2, 3, 4, 5]}
                },
                {
                    extend: 'pdf',
                    text: feather.icons['clipboard'].toSvg({class: 'font-small-4 me-50'}) + 'Pdf',
                    className: 'dropdown-item',
                    exportOptions: {columns: [1, 2, 3, 4, 5]}
                },
                {
                    extend: 'copy',
                    text: feather.icons['copy'].toSvg({class: 'font-small-4 me-50'}) + 'Copy',
                    classNamef: 'dropdown-item',
                    exportOptions: {columns: [1, 2, 3, 4, 5]}
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
            text: 'Add New SubCategory',
            className: 'add-new btn btn-primary',
            attr: {
                'data-bs-toggle': 'modal',
                'data-bs-target': '#addSubCategoryModal'
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

$(document).ready(function () {

    $(document).ready(function () {
        // Check if the product was added successfully
        if (sessionStorage.getItem('Category_added') == 'true') {
            // Show the success Toastify message
            Toastify({
                text: "Subcategory added successfully!",
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

    $(document).on('click', '.delete-sub-category', function (e) {
        e.preventDefault();

        var $table = $('.sub-category-list-table').DataTable();
        var $row = $(this).closest('tr');
        var rowData = $table.row($row).data();
        var categoryId = $(this).data('id');
        var categoryName = rowData.name;

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to delete category "${categoryName}". This action cannot be undone.`,
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
        const categoryNameAR = $(this).data('name_ar');
        const categoryNameEn = $(this).data('name_en');
        const products = $(this).data('products');
        const addedDate = $(this).data('showdate');
        const id = $(this).data('id');
        const parentName = $(this).data('parent');
        // Populate modal
        $('#showSubCategoryModal #sub-category-name-ar').val(categoryNameAR);
        $('#showSubCategoryModal #sub-category-name-en').val(categoryNameEn);
        $('#showSubCategoryModal #sub-category-products').val(products);
        $('#showSubCategoryModal #sub-category-date').val(addedDate);
        $('#showSubCategoryModal #sub-category-id').val(id);
        $('#showSubCategoryModal #parent-name').val(parentName);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('showSubCategoryModal'));
        modal.show();
    });

    $('#editButton').on('click', function () {

        var nameEN = $('#sub-category-name-en').val();
        var nameAR = $('#sub-category-name-ar').val();
        var id = $('#sub-category-id').val();
        console.log(id)

        $('#edit-sub-category-name-en').val(nameEN);
        $('#edit-sub-category-name-ar').val(nameAR);
        $('#edit-sub-category-id').val(id);

        $('#editSubCategoryModal').modal('show');

    });

    $('#editSubCategoryForm').on('submit', function (e) {
        console.log("D")
        e.preventDefault(); // prevent default form submission
        var categoryId = $(this).find('#edit-sub-category-id').val();
        console.log(categoryId)
        $.ajax({
            url: `sub-categories/${categoryId}`,
            type: 'POST', // IMPORTANT: Laravel expects POST + method spoofing (@method('PUT'))
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                console.log(response);

                Toastify({
                    text: "Subcategory updated successfully!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F", // green for success
                    close: true
                }).showToast();

                // Close modal
                $('#editSubCategoryModal').modal('hide');
                $('#showSubCategoryModal').modal('hide');
                $('#sub-category-list-table').DataTable().ajax.reload();



            },
            error: function (xhr) {
                var errors = xhr.responseJSON.errors;
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        Toastify({
                            text: errors[key][0],
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455", // red
                            close: true
                        }).showToast();
                    }
                }
            }
        });
    });






    $('#addSubCategoryForm').on('submit', function (e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'), // dynamic action URL
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Toastify({
                    text: "Category added successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true
                }).showToast();



                $('#addSubCategoryForm')[0].reset();
                $('#addSubCategoryModal').modal('hide');

                $('.sub-category-list-table').DataTable().ajax.reload(); // reload your table
            },
            error: function (xhr) {
                var errors = xhr.responseJSON.errors;
                for (var key in errors) {
                    if (errors.hasOwnProperty(key)) {
                        Toastify({
                            text: errors[key][0],
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true
                        }).showToast();
                    }
                }
            }
        });
    });


});
