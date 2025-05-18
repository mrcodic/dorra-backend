    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var dt_user_table = $('.tag-list-table').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    ajax: {
        url: tagsDataUrl,
        type: 'GET',
        data: function (d) {
            d.search_value = $('#search-tag-form').val();
            d.created_at = $('.filter-date').val();

            return d;
        }
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
        <div class="d-flex gap-1">
                                <a href="#" class="view-details"
                                   data-bs-toggle="modal"
                                     data-bs-target="#showTagModal"
                                     data-id="${data}"
                                     data-name_ar="${row.name_ar}"
                                     data-name_en="${row.name_en}"
                                     data-products="${row.no_of_products}"
                                     data-showdate="${row.show_date}">
                                     <i data-feather="eye"></i>
                                </a>

                          <a href="#" class="edit-details"
                           data-bs-toggle="modal"
                           data-bs-target="#editTagModal"
                             data-id="${data}"
                             data-name_ar="${row.name_ar}"
                             data-name_en="${row.name_en}"
                             data-products="${row.no_of_products}"
                             data-showdate="${row.show_date}">
                            <i data-feather="edit-3"></i>
                       </a>

      <a href="#" class="text-danger open-delete-tag-modal"
   data-id="${data}"
   data-name="${row.name}"
   data-action="/categories/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteTagModal">
   <i data-feather="trash-2"></i>
</a>

          </div>
        `;
            }
        }
    ],
    order: [[1, 'asc']],
    dom:
    '<"d-flex align-items-center header-actions mx-2 row mb-2"' +
    '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
    ">" +
    ">t" +
    '<"d-flex  mx-2 row mb-1"' +
    '<"col-sm-12 col-md-6"i>' +
    '<"col-sm-12 col-md-6"p>' +
    ">",

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
    $('#search-tag-form').on('keyup', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            dt_user_table.draw();
        }, 300);
    });

    $('.filter-date').on('change', function () {
        dt_user_table.draw();
    });
$(document).ready(function () {
    const saveButton = $('.saveChangesButton');
    const saveLoader = $('.saveLoader');
    const saveButtonText = $('.saveChangesButton .btn-text');
    $('#addTagForm').on('submit', function (e) {
        e.preventDefault();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
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

                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
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
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
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

        $(document).on('click', '.edit-details', function (e) {
        const tagNameAR = $(this).data('name_ar');
        const tagNameEn = $(this).data('name_en');
        const tagId = $(this).data('id');

        // Populate modal
        $('#editTagModal #edit-tag-name-ar').val(tagNameAR);
        $('#editTagModal #edit-tag-name-en').val(tagNameEn);
        $('#editTagModal #edit-tag-id').val(tagId);

        // Show modal
        $('#editTagModal').modal('show');

    });

    $('#editButton').on('click', function () {
        var nameEN = $('#tag-name-en').val();
        var nameAR = $('#tag-name-ar').val();
        var id = $('#tag-id').val();


        $('#edit-tag-name-en').val(nameEN);
        $('#edit-tag-name-ar').val(nameAR);
        $('#edit-tag-id').val(id);
        $('#editTagModal').modal('show');

    });

    $('#editTagForm').on('submit', function (e) {
        e.preventDefault();
        var tagId = $(this).find('#edit-tag-id').val();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        $.ajax({
            url: `tags/${tagId}`,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {

                Toastify({
                    text: "Tag updated successfully!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true
                }).showToast();
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                // Close modal
                $('#editTagModal').modal('hide');
                $('#showTagModal').modal('hide');
                $('.tag-list-table').DataTable().ajax.reload(null,false);



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
                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
            }
        });
    });




    $(document).on("click", ".open-delete-tag-modal", function () {
        const tagId = $(this).data("id");
        $("#deleteTagForm").data("id", tagId);
    });

    $(document).on('submit', '#deleteTagForm', function (e) {
        e.preventDefault();
        const tagId = $(this).data("id");

        $.ajax({
            url: `/tags/${tagId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteTagModal").modal("hide");

                Toastify({
                    text: "Tag deleted successfully!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".tag-list-table").DataTable().ajax.reload(null, false);


            },
            error: function () {
                $("#deleteTagModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                $(".tag-list-table").DataTable().ajax.reload(null, false);

            },
        });
    });


    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "tags/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteTagsModal").modal("hide");
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
                $("#deleteTagsModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                // Reload DataTable

                $('#bulk-delete-container').hide();
                $('.tag-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".category-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });


});
