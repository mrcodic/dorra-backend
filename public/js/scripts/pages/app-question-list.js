$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".faq-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: faqsDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-faq-form').val(); // get from input
            d.created_at = $('.filter-date').val();
            return d;
        }
    },
    columns: [
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            }
        },

        {data: "question"},
        {data: "created_at"},
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {

                return `

         <a href="#" class="edit-details"
           data-bs-toggle="modal"
           data-bs-target="#editQuestionModal"
              data-question_ar="${row.question_ar}"
               data-question_en="${row.question_en}"
               data-answer_ar="${row.answer_ar}"
               data-answer_en="${row.answer_en}"
           data-id="${data}">

                <i data-feather="edit-3"></i>
              </a>

        <a href="#" class=" text-danger open-delete-faq-modal" data-id="${data}"
                data-bs-toggle="modal"
                data-bs-target="#deleteFaqModal" >
                <i data-feather="trash-2"></i>
              </a>

          </div>
        `;
            }
        }
    ],
    order: [[1, "asc"]],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
        '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
        ">" +
        ">t" +
        '<"d-flex mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        ">",
    drawCallback: function () {
        feather.replace();
    },

    language: {
        sLengthMenu: "Show _MENU_",
        search: "",
        searchPlaceholder: "Search..",
        paginate: {
            previous: "&nbsp;",
            next: "&nbsp;"
        }
    }
});

$('#clear-search').on('click', function () {
    $('#search-faq-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
// Custom search with debounce
let searchTimeout;
$('#search-faq-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$('.filter-date').on('change', function () {
    dt_user_table.ajax.reload();
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

// Redraw table resets checkboxes
dt_user_table.on('draw', function () {
    $('#select-all-checkbox').prop('checked', false);
    $('#bulk-delete-container').hide();
});

// Update bulk delete container
function updateBulkDeleteVisibility() {
    const selected = $('.category-checkbox:checked').length;
    if (selected > 0) {
        $('#selected-count-text').text(`${selected} Faq${selected > 1 ? 's' : ''} are selected`);
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

$(document).ready(function () {
    $(document).on("click", ".edit-details", function (e) {
        console.log("Dsfs")
        const $button = $(this);

        const faqId = $button.data('id') || '';
        const questionAr = $button.data('question_ar') || '';
        const questionEn = $button.data('question_en') || '';

        const answerAr = $button.data('answer_ar') || '';
        const answerEn = $button.data('answer_en') || '';
        // Populate modal
        $("#editQuestionModal #question-en").val(questionEn);
        $("#editQuestionModal #question-ar").val(questionAr);
        $("#editQuestionModal #answer-ar").val(answerAr);
        $("#editQuestionModal #answer-en").val(answerEn);

        $('#editFaqForm').attr('action', `faqs/${faqId}`);
    });



    $(document).on("click", ".open-delete-faq-modal", function () {
        const adminId = $(this).data("id");

        $("#deleteFaqForm").attr('action',`faqs/${adminId}`);
    });


    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url:"/faqs/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function () {
                $("#deleteFaqsModal").modal("hide");
                Toastify({
                    text: "Selected FAQs deleted successfully!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                $('#bulk-delete-container').hide();
                $('#select-all-checkbox').prop('checked', false);
                $(".faq-list-table").DataTable().ajax.reload(null, false);
            }
        });
    });



});
