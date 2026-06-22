function escapeAttr(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');
}

function encodeDataJson(value) {
    return encodeURIComponent(JSON.stringify(value || null));
}

function decodeDataJson(value, fallback = null) {
    try {
        return JSON.parse(decodeURIComponent(value || ''));
    } catch (e) {
        return fallback;
    }
}

function setEditPlanIcon(media) {
    $('#editPlanIconId').val(media?.id || '');
    $('#editRemoveIcon').val('0');

    const dzElement = document.querySelector('#edit-plan-icon-dropzone');

    if (!dzElement || !dzElement.dropzone) {
        return;
    }

    const dz = dzElement.dropzone;

    // clear previous preview silently
    window.__settingEditPlanIcon = true;
    dz.removeAllFiles(true);
    window.__settingEditPlanIcon = false;

    if (!media || !media.id || !media.url) {
        return;
    }

    const mockFile = {
        name: media.file_name || 'plan-icon',
        size: media.size || 12345,
        _hiddenInputId: media.id,
        _isMock: true
    };

    dz.emit('addedfile', mockFile);
    dz.emit('thumbnail', mockFile, media.url);
    dz.emit('complete', mockFile);
    dz.files.push(mockFile);
}
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});
const dt_user_table = $(".plan-list-table").DataTable({
    processing: true,
    serverSide: true,
    searching: false, // using custom search
    ajax: {
        url: plansDataUrl,
        type: "GET",
        data: function (d) {
            d.search_value = $("#search-category-form").val(); // get from input
            d.role_id = $(".filter-role").val();
            d.status = $(".filter-status").val();
            return d;
        },
    },
    columns: [
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return row?.action?.can_delete
                    ? `<input type="checkbox" name="ids[]" class="category-checkbox" value="${row.id}">`
                    : "";
            },
        },
        { data: "id" },
        { data: "name" },
        { data: "price" },
        { data: "credits" },
        {
            data: "is_active",
            render: function (data) {
                if (data == 1) {
                    return '<i class="fas fa-check-circle text-success" title="Active"></i>';
                }
                return '<i class="fas fa-times-circle text-danger" title="Inactive"></i>';
            }
        },
        {
            data: "id",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                const canEdit = row?.action?.can_edit ?? false;
                const canDelete = row?.action?.can_delete ?? false;

                const recommendedFor = row.recommended_for || "";
                const featuresEncoded = encodeURIComponent(JSON.stringify(row.features || []));

                const icon = row.icon || {
                    id: row.icon_id || '',
                    url: row.icon_url || '',
                    file_name: row.icon_file_name || 'plan-icon',
                    size: row.icon_size || 12345
                };

                const iconEncoded = encodeDataJson(icon);

                const editBtn = canEdit ? `
            <a href="#" class="edit-details"
              data-bs-toggle="modal"
              data-bs-target="#editPlanModal"
              data-id="${escapeAttr(data)}"
              data-name="${escapeAttr(row.name || '')}"
              data-description="${escapeAttr(row.description || '')}"
              data-price="${escapeAttr(row.price || '')}"
              data-credits="${escapeAttr(row.credits || '')}"
              data-status="${escapeAttr(row.is_active)}"
              data-popular="${escapeAttr(row.is_popular)}"
              data-recommended_for="${escapeAttr(recommendedFor)}"
              data-features="${featuresEncoded}"
              data-icon="${iconEncoded}">
              <i data-feather="edit-3"></i>
            </a>` : "";

                const delBtn = canDelete ? `
            <a href="#" class="text-danger open-delete-admin-modal" data-id="${escapeAttr(data)}"
              data-bs-toggle="modal" data-bs-target="#deletePlanModal">
              <i data-feather="trash-2"></i>
            </a>` : "";

                return `${editBtn} ${delBtn}`;
            },
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
            next: "&nbsp;",
        },
    },
});
$("#clear-search").on("click", function () {
    $("#search-category-form").val(""); // clear input
    dt_user_table.search("").draw(); // reset DataTable search
});
// Custom search with debounce
let searchTimeout;
$("#search-category-form").on("keyup", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

// Custom search with debounce

$(".filter-role, .filter-status").on("change", function () {
    dt_user_table.ajax.reload();
});

// Checkbox select all
$("#select-all-checkbox").on("change", function () {
    $(".category-checkbox").prop("checked", this.checked);
    updateBulkDeleteVisibility();
});

// Single checkbox toggle
$(document).on("change", ".category-checkbox", function () {
    if (!this.checked) {
        $("#select-all-checkbox").prop("checked", false);
    } else if (
        $(".category-checkbox:checked").length ===
        $(".category-checkbox").length
    ) {
        $("#select-all-checkbox").prop("checked", true);
    }
    updateBulkDeleteVisibility();
});

// Redraw table resets checkboxes
dt_user_table.on("draw", function () {
    $("#select-all-checkbox").prop("checked", false);
    $("#bulk-delete-container").hide();
});

// Update bulk delete container
function updateBulkDeleteVisibility() {
    const selected = $(".category-checkbox:checked").length;
    if (selected > 0) {
        $("#selected-count-text").text(
            `${selected} Category${selected > 1 ? "ies" : "y"} are selected`
        );
        $("#bulk-delete-container").show();
    } else {
        $("#bulk-delete-container").hide();
    }
}

// Listen to checkbox change
$(document).on("change", ".category-checkbox", function () {
    let checkedCount = $(".category-checkbox:checked").length;
    $("#bulk-delete-container").toggle(checkedCount > 0);
});
// Select All functionality
$(document).on("change", "#select-all-checkbox", function () {
    const isChecked = $(this).is(":checked");
    $(".category-checkbox").prop("checked", isChecked).trigger("change");
});
// Update "Select All" checkbox based on individual selections
$(document).on("change", ".category-checkbox", function () {
    const all = $(".category-checkbox").length;
    const checked = $(".category-checkbox:checked").length;

    $("#select-all-checkbox").prop("checked", all === checked);
    $("#bulk-delete-container").toggle(checked > 0);
});

// Optional: Hide button when table is redrawn
dt_user_table.on("draw", function () {
    $("#bulk-delete-container").hide();
});

$(document).ready(function () {
    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderFeaturesRows(features) {
        const $list = $('#editFeaturesList');

        let arr = features;

        if (!Array.isArray(arr) && arr && typeof arr === 'object') {
            arr = Object.values(arr);
        }

        if (!Array.isArray(arr)) {
            arr = [];
        }

        if (!arr.length) {
            arr = [{ id: '', description: '' }];
        }

        const rows = arr.map((f, i) => {
            const fid = f?.id ?? '';
            const desc = f?.description ?? '';

            return `
            <div data-repeater-item class="row g-1 align-items-end mb-1">
                <div class="col-12 col-md-11">
                    <label class="form-label">Description</label>

                    <input
                        type="hidden"
                        name="features[${i}][id]"
                        value="${fid}">

                    <input
                        type="text"
                        name="features[${i}][description]"
                        class="form-control"
                        value="${desc}"
                        required>
                </div>

                <div class="col-12 col-md-1 d-flex justify-content-end">
                  <button type="button"
                                                data-repeater-delete
                                                class="btn btn-outline-danger btn-sm feature-remove">
                                            <i data-feather="x"></i>
                                        </button>
                </div>
            </div>
        `;
        }).join('');

        $list.html(rows);

        if (window.feather) {
            feather.replace();
        }
    }
    $(document).on('click', '.edit-details', function (e) {
        e.preventDefault();

        const modal = $('#editPlanModal');

        const id = $(this).data('id');
        const name = $(this).data('name');
        const description = $(this).data('description');
        const price = $(this).data('price');
        const credits = $(this).data('credits');
        const status = $(this).data('status');
        const popular = $(this).data('popular');
        const recommendedFor = $(this).data('recommended_for');

        const icon = decodeDataJson($(this).attr('data-icon'), null);

        let features = [];

        try {
            const encoded = $(this).attr('data-features') || '';
            const json = decodeURIComponent(encoded);
            features = JSON.parse(json || '[]');
        } catch (err) {
            console.warn('Invalid features JSON', err);
            features = [];
        }

        modal.find('form').attr('action', `/plans/${id}`);

        modal.find('input[name="name"]').val(name);
        modal.find('textarea[name="description"]').val(description);
        modal.find('textarea[name="recommended_for"]').val(recommendedFor || '');
        modal.find('input[name="price"]').val(price);
        modal.find('input[name="credits"]').val(credits);

        const toggle = modal.find('#editStatusToggle');
        const hiddenStatus = modal.find('#edit-status');
        const label = toggle.next('label');

        const active = String(status) === '1';

        toggle.prop('checked', active);
        hiddenStatus.val(active ? 1 : 0);
        label.text(active ? 'Active' : 'Inactive');

        const togglePopular = modal.find('#editIsPopularToggle');
        const hiddenPopular = modal.find('#edit-popular');
        const labelPopular = togglePopular.next('label');

        const isPopular = String(popular) === '1';

        togglePopular.prop('checked', isPopular);
        hiddenPopular.val(isPopular ? 1 : 0);
        labelPopular.text(isPopular ? 'Popular' : 'UnPopular');

        window.initEditRepeater();
        renderFeaturesRows(features);

        setEditPlanIcon(icon);
    });


    $(document).on("click", ".open-delete-admin-modal", function () {
        const adminId = $(this).data("id");

        $("#deletePlanForm").attr("action", `plans/${adminId}`);
    });

    handleAjaxFormSubmit("#deletePlanForm", {
        successMessage: "✅ Plan deleted successfully!",
        closeModal: "#deletePlanModal",
        onSuccess: function (response, $form) {
            $(".plan-list-table").DataTable().ajax.reload(null, false); // false = stay on current page
        },
    });

    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked")
            .map(function () {
                return $(this).val();
            })
            .get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "plans/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deletePlansModal").modal("hide");
                Toastify({
                    text: "Selected plans deleted successfully!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                // Reload DataTable

                $("#bulk-delete-container").hide();
                $(".category-checkbox").prop("checked", false);
                $("#select-all-checkbox").prop("checked", false);
                $(".plan-list-table").DataTable().ajax.reload(null, false);
            },
            error: function () {
                $("#deleteCategoriesModal").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 1500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28a745",
                    close: true,
                }).showToast();

                // Reload DataTable

                $("#bulk-delete-container").hide();
                $(".product-checkbox").prop("checked", false);
                $("#select-all-checkbox").prop("checked", false);
                $(".plan-list-table").DataTable().ajax.reload(null, false);
            },
        });
    });
});
