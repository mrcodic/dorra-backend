$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$(document).ready(function () {
    fetchTemplates();

    $('.filter-status, .filter-product, #search-category-form, .filter-paginate-number').on('change keyup', function () {
        fetchTemplates();
    });


    $(document).on("click", ".open-delete-mockup-modal", function () {
        const mockupId = $(this).data("id");
        $('#deleteMockupForm input[name="id"]').val(mockupId);
        $("#deleteMockupForm").attr('action', `mockups/${mockupId}`);
    });

    handleAjaxFormSubmit('#deleteMockupForm', {
        successMessage: "✅ Mockup deleted successfully!",
        closeModal: '#deleteMockupModal',
        onSuccess: function (response, $form) {
            const deletedId = response.data.id;
            const card = $(`button[data-id="${deletedId}"]`).closest('.col-md-6.col-lg-4.col-xxl-4');
            card.remove();
            location.reload();
        }
    });

    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();
        console.log(selectedIds)

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "mockups/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteMockupsModal").modal("hide");

                Toastify({
                    text: "Selected mockups deleted successfully!",
                    duration: 1000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                removeTemplateCards(response.data);

                $('#bulk-delete-container').hide();
                $('.category-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                location.reload();
            },


            error: function () {
                $("#deleteMockupsModal").modal("hide");
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
                $('.product-checkbox').prop('checked', false);
                $('#select-all-checkbox').prop('checked', false);
                $(".template-list-table").DataTable().ajax.reload(null, false);

            },
        });

    });


});


function fetchTemplates(page = 1) {
    $.ajax({
        url: '/mockups',
        type: 'GET',
        data: {
            page,
            search_value: $('#search-category-form').val(),
            product_id: $('.filter-product').val(),
            status: $('.filter-status').val(),
            per_page: $('.filter-paginate-number').val()
        },
        success: res => {
            $('#templates-container').html(res.data.cards);
            $('#pagination-container').html(res.data.pagination);
        },
        error: xhr => console.error('Failed to fetch templates:', xhr)
    });
}

/* 3-A  — page-size selector */
$('.filter-paginate-number').on('change', () => fetchTemplates(1));

/* 3-B  — delegate clicks on the (new) paginator links */
$(document).on('click', '#pagination-container a.page-link', function (e) {
    e.preventDefault();
    const url = new URL($(this).attr('href'), location.origin);
    const page = url.searchParams.get('page') || 1;
    fetchTemplates(page);
});


// Checkbox select all
$('#select-all-checkbox').on('change', function () {
    $('.category-checkbox').prop('checked', this.checked);
    updateBulkDeleteVisibility();
});

// Single checkbox toggle
$(document).on('change', '.category-checkbox', function () {
    if (!this.checked) {
        $('#select-all-checkbox').prop('checked', false);
    } else if ($('.category-checkbox:checked').length === $('.category-checkbox').length) {
        $('#select-all-checkbox').prop('checked', true);
    }
    updateBulkDeleteVisibility();
});


// Update bulk delete container
function updateBulkDeleteVisibility() {
    const selected = $('.category-checkbox:checked').length;
    if (selected > 0) {
        $('#selected-count-text').text(`${selected} Mockup${selected > 1 ? 's' : ''} are selected`);
        $('#bulk-delete-container').show();
    } else {
        $('#bulk-delete-container').hide();
    }
}


// Listen to checkbox change
$(document).on("change", ".category-checkbox", function () {
    let checkedCount = $(".category-checkbox:checked").length;
    $("#bulk-delete-container").toggle(checkedCount > 0);
});
// Select All functionality
$(document).on('change', '#select-all-checkbox', function () {
    const isChecked = $(this).is(':checked');
    $('.category-checkbox').prop('checked', isChecked).trigger('change');
});
// Update "Select All" checkbox based on individual selections
$(document).on('change', '.category-checkbox', function () {
    const all = $('.category-checkbox').length;
    const checked = $('.category-checkbox:checked').length;

    $('#select-all-checkbox').prop('checked', all === checked);
    $('#bulk-delete-container').toggle(checked > 0);
});


// Optional: Hide button when table is redrawn
dt_user_table.on("draw", function () {
    $("#bulk-delete-container").hide();
});

function removeTemplateCards(ids) {
    if (Array.isArray(ids)) {
        ids.forEach(function (id) {
            $('[data-template-id="' + id + '"]').fadeOut(300, function () {
                $(this).remove();
            });
        });
    }
}





