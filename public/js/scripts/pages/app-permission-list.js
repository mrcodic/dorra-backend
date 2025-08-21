$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".permissions-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: permissionsDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $('#search-permission-form').val(); // get from input
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
        { data: "group" ,render: function (data){
                return data[locale];
            } },
        {
            data: "roles",
            render: function (data) {
                // Check if data is a non-empty array
                if (!Array.isArray(data) || data.length === 0) {
                    return '-';
                }

                return `
            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                ${data.map(role => `
                    <span style="background-color: #FCF8FC; color: #000; padding: 6px 12px; border-radius: 12px; font-size: 14px;">
                        ${role.name[locale]}
                    </span>`).join("")}
            </div>
        `;
            }
        },

        { data: "created_at" },
        {
            data: "id",
            orderable: false,
            render: function (data, type, row, meta) {
                return `
        <div class="d-flex gap-1">
              <a href="" class="" data-bs-target="#editPermissionModal" data-bs-toggle="modal">
                <i data-feather="edit-3"></i>
              </a>

              <a href="#" class=" text-danger open-delete-product-modal" data-id="${data}"
                data-bs-toggle="modal"
                data-bs-target="#deletePermissionModal" >
                <i data-feather="trash-2"></i>
              </a>

          </div>
        `;
            }
        }

    ],
    drawCallback: function () {
        feather.replace(); // Re-initialize feather icons
    },
    order: [[1, "asc"]],
    dom:
        '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
        '<"col-12 d-flex flex-wrap align-items-center justify-content-between"' +
        '<"d-flex align-items-center flex-grow-1 me-2"f>' +
        '<"d-flex align-items-center gap-1"B>' +
        ">" +
        ">t" +
        '<"d-flex mx-2 row mb-1"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        ">",
    buttons: [

    ],
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
