    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var dt_user_table = $('.tag-list-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: tagsDataUrl,
        type: 'GET'
    },
    columns: [
        { data: null, defaultContent: "", orderable: false, render: function (data, type, row, meta) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            } },
        {data: 'name'},
        {data: 'no_of_products'},
        {data: 'no_of_templates'},
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
                 data-bs-target="#showTagModal"
                 data-id="${data}"
                 data-name_ar="${row.name_ar}"
                 data-name_en="${row.name_en}"
                 data-products="${row.no_of_products}"
                 data-showdate="${row.show_date}">
                <i data-feather="file-text"></i> Details
              </a>

              <a href="#" class="dropdown-item text-danger delete-tag" data-id="${data}">
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
    '<"d-flex align-items-center header-actions mx-2 row mb-2"' +
    '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
    '<"d-flex align-items-center flex-grow-1 me-2"f>' + // Search input
    '<"d-flex align-items-center gap-1"B>' + // Buttons + Date Filter
    ">" +
    ">t" +
    '<"d-flex  mx-2 row mb-1"' +
    '<"col-sm-12 col-md-6"i>' +
    '<"col-sm-12 col-md-6"p>' +
    ">",
    buttons: [
        {
            text: '<input type="date" class="form-control" style="width: 120px;" />',
            className: "btn border-0",
            action: function (e, dt, node, config) {
                e.preventDefault();
            },
        },
        {
            text: 'Add New Tag',
            className: 'add-new btn btn-primary',
            attr: {
                'data-bs-toggle': 'modal',
                'data-bs-target': '#addTagModal'
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
        search: '',
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
                text: "Tag added successfully!",
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

    $(document).on('click', '.delete-tag', function (e) {
        e.preventDefault();
        var $table = $('.tag-list-table').DataTable();
        var $row = $(this).closest('tr');
        var rowData = $table.row($row).data();
        var tagId = $(this).data('id');
        var tagName = rowData.name;

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to delete tag "${tagName}". This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: `/tags/${tagId}`,
                    method: 'DELETE',
                    success: function (res) {
                        Swal.fire('Deleted!', 'Tag has been deleted.', 'success');
                        $table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire('Failed', 'Could not delete tag.', 'error');
                    }
                });
            }
        });
    });
    $(document).on('click', '.view-details', function (e) {
        const tagNameAR = $(this).data('name_ar');
        const tagNameEn = $(this).data('name_en');
        const products = $(this).data('products');
        const addedDate = $(this).data('showdate');
        const id = $(this).data('id');
        // Populate modal
        $('#showTagModal #tag-name-ar').val(tagNameAR);
        $('#showTagModal #tag-name-en').val(tagNameEn);
        $('#showTagModal #tag-products').val(products);
        $('#showTagModal #tag-date').val(addedDate);
        $('#showTagModal #tag-id').val(id);


        // Show modal
        $('#showTagModal').modal('show');

    });

    $('#editButton').on('click', function () {
        var nameEN = $('#tag-name-en').val();
        var nameAR = $('#tag-name-ar').val();
        var id = $('#tag-id').val();


        $('#edit-tag-name-en').val(nameEN);
        $('#edit-tag-name-ar').val(nameAR);
        $('#edit-tag-id').val(id);
        console.log(id)
        $('#editTagModal').modal('show');

    });

    $('#editTagForm').on('submit', function (e) {
        console.log("D")
        e.preventDefault();
        var tagId = $(this).find('#edit-tag-id').val();
        $.ajax({
            url: `tags/${tagId}`,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                console.log(response);

                Toastify({
                    text: "Tag updated successfully!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true
                }).showToast();

                // Close modal
                $('#editTagModal').modal('hide');
                $('#showTagModal').modal('hide');
                $('#tag-list-table').DataTable().ajax.reload();



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






    $('#addTagForm').on('submit', function (e) {
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
                    text: "Tag added successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true
                }).showToast();



                $('#addTagForm')[0].reset();
                $('#addTagModal').modal('hide');

                $('.tag-list-table').DataTable().ajax.reload(); // reload your table
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


    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        Swal.fire({
            title: `Are you sure?`,
            text: `You're about to delete ${selectedIds.length} tags.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete them!",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "tags/bulk-delete",
                    method: "POST",
                    data: {
                        ids: selectedIds,
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    success: function (response) {
                        Toastify({
                            text: "Selected tags deleted successfully!",
                            duration: 1500,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true,
                        }).showToast();

                        // Reload DataTable

                        $('#bulk-delete-container').hide();
                        $('.category-checkbox').prop('checked', false);
                        $('#select-all-checkbox').prop('checked', false);
                        $(".tag-list-table").DataTable().ajax.reload(null, false);

                    },
                    error: function () {
                        Swal.fire("Error", "Could not delete selected tags.", "error");
                    },
                });
            }
        });
    });

});
