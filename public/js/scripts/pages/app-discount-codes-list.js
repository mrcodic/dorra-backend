$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

var dt_user_table = $(".code-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    ajax: {
        url: discountCodeDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-code-form').val(); // get from input
            d.created_at = $('.filter-date').val();
            return d;
        }
    },
    columns: [
        { data: null, defaultContent: "", orderable: false },
        { data: "code" },
        { data: "type" },
        { data: "max_usage" },
        { data: "used" },
        { data: "expired_at" },
        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                return `
         <div class="d-flex gap-1">
             <a href="" class="">
                <i data-feather="eye"></i>
              </a>
              <a href="" class="">
                <i data-feather="edit-3"></i>
              </a>

             <a href="#" class="text-danger  open-delete-code-modal"
   data-id="${data}"
   data-name="${row.name}"
   data-action="/discount-codes/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteCodeModal">
   <i data-feather="trash-2"></i>
</a>


          </div>
        `;
            },
        },
    ],
    order: [[1, "asc"]],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
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

    ],
    drawCallback: function () {
        feather.replace();
    },
    language: {
        sLengthMenu: "Show _MENU_",
        search: "",
        searchPlaceholder: "Search..",
        paginate: {
            previous: "&nbsp;",
            next: "&nbsp;",
        },
    },
});
// Custom search with debounce
let searchTimeout;
$('#search-code-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$('.filter-date').on('change', function () {
    dt_user_table.draw();
});

$(document).on("click", ".open-delete-code-modal", function () {
    const codeId = $(this).data("id");
    $("#deleteCodeForm").data("id", codeId);
});

$(document).on("submit", "#deleteCodeForm", function (e) {
    e.preventDefault();
    const codeId = $(this).data("id");

    $.ajax({
        url: `/discount-codes/${codeId}`,
        method: "DELETE",
        success: function (res) {

            $("#deleteCodeModal").modal("hide");

            Toastify({
                text: "Code deleted successfully!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#28C76F",
                close: true,
            }).showToast();
            $(".code-list-table").DataTable().ajax.reload(null, false);


        },
        error: function () {

            $("#deleteCodeModal").modal("hide");
            Toastify({
                text: "Something Went Wrong!",
                duration: 2000,
                gravity: "top",
                position: "right",
                backgroundColor: "#EA5455", // red
                close: true,
            }).showToast();
            $(".code-list-table").DataTable().ajax.reload(null, false);

        },
    });
});

