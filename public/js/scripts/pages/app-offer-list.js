$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var dt_user_table = $('.offer-list-table').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    orderable: false,
    ajax: {
        url: offersDataUrl,
        type: 'GET',
        data: function (d) {
            d.search_value = $('#search-offer-form').val();
            d.type = $('.filter-type').val();

            return d;
        }
    },
    columns: [
        {
            data: null, defaultContent: "", orderable: false, render: function (data, type, row, meta) {
                return `<input type="checkbox" name="ids[]" class="category-checkbox" value="${data.id}">`;
            }
        },
        {data: 'name', orderable: false},
        {
            data: 'type',
            orderable: false,
            render: function (data, type, row) {
                return data?.label ?? '-';
            }
        },
        {data: 'value', orderable: false},
        {data: 'start_at', orderable: false},
        {data: 'end_at', orderable: false},

        {
            data: 'id',
            orderable: false,
            render: function (data, type, row, meta) {
                return `
        <div class="d-flex gap-1">
                                <a href="#" class="view-details"
                                   data-bs-toggle="modal"
                                     data-bs-target="#showOfferModal"
                                     data-id="${data}"
                                     data-name="${row.name}"
                                     data-value="${row.value}"
                                     data-type="${row.type.value}"
                                     data-start_at="${row.start_at}"
                                     data-end_at="${row.end_at}"
                                          data-products='${JSON.stringify(row.products || [])}'
       data-categories='${JSON.stringify(row.categories || [])}'>

                                     <i data-feather="eye"></i>
                                </a>

                          <a href="#" class="edit-details"
                           data-bs-toggle="modal"
                           data-bs-target="#editOfferModal"
                                     data-id="${data}"
                                     data-name_en="${row.name_translate.en || ''}"
                                     data-name_ar="${row.name_translate.ar || ''}"
                                     data-value="${row.value}"
                                     data-type="${row.type.value}"
                                     data-start_at="${row.start_at}"
                                     data-end_at="${row.end_at}"
                                     data-products='${JSON.stringify(row.products || [])}'
                                     data-categories='${JSON.stringify(row.categories || [])}'
                                          >
                            <i data-feather="edit-3"></i>
                       </a>

      <a href="#" class="text-danger open-delete-offer-modal"
   data-id="${data}"
   data-action="/offers/${data}"
   data-bs-toggle="modal"
   data-bs-target="#deleteOfferModal">
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
$('#clear-search').on('click', function () {
    $('#search-offer-form').val('');  // clear input
    dt_user_table.search('').draw();  // reset DataTable search
});
let searchTimeout;
$('#search-offer-form').on('keyup', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        dt_user_table.draw();
    }, 300);
});

$('.filter-type').on('change', function () {
    dt_user_table.draw();
});
$(document).ready(function () {
    const saveButton = $('.saveChangesButton');
    const saveLoader = $('.saveLoader');
    const saveButtonText = $('.saveChangesButton .btn-text');
    $('#addFlagForm').on('submit', function (e) {
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
                    text: "Flag added successfully!",
                    duration: 2000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true
                }).showToast();

                saveButton.prop('disabled', false);
                saveLoader.addClass('d-none');
                saveButtonText.removeClass('d-none');
                $('#addFlagForm')[0].reset();
                $('#addFlagModal').modal('hide');
                location.reload();

                $('.offer-list-table').DataTable().ajax.reload(); // reload your table
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

// helper: normalize "2025-10-06 12:34:00" or ISO to "2025-10-06"
    function toDateForInput(s) {
        if (!s) return '';
        s = String(s).trim();

        // already yyyy-mm-dd
        if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;

        // dd/mm/yyyy → yyyy-mm-dd
        const m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (m) return `${m[3]}-${m[2]}-${m[1]}`;

        // yyyy-mm-dd HH:MM:SS or ISO → take date part
        if (s.includes(' ') || s.includes('T')) return s.split('T')[0].split(' ')[0];

        return '';
    }

// helpers to normalize payloads & render chips
    function toItemsArray(raw) {
        // Accept JSON string, array of IDs, or array of objects with {id, name/title/label}
        if (raw == null) return [];
        if (typeof raw === 'string') {
            try { raw = JSON.parse(raw); } catch (_) { /* keep as-is */ }
        }
        if (!Array.isArray(raw)) return [];

        return raw.map((it) => {
            // normalize id
            const id = String(
                (it && (it.id ?? it.value ?? it)) // object or primitive
            );

            // pick a "name-like" field
            let name = (it && (it.name ?? it.title ?? it.label ?? null));

            // if name is another object (e.g. {en, ar}), pick a sensible locale/first value
            if (name && typeof name === 'object') {
                name = name.en || name.ar || Object.values(name)[0] || `#${id}`;
            }

            if (!name) name = `#${id}`; // fallback

            return { id, name: String(name) };
        });
    }

    function renderChips($container, items) {
        console.log(items)
        if (!items.length) {
            $container.html('<span class="text-muted">— none —</span>');
            return;
        }
        const html = items.map(it =>
            `<span class="badge rounded-pill bg-light border text-dark me-1 mb-1">
       ${_.escape(it.name.en)} <small class="text-muted">#${_.escape(it.id)}</small>
     </span>`
        ).join('');
        $container.html(html);
    }

    $(document).on('click', '.view-details', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const $m = $('#showOfferModal');

        // existing fields...
        const name   = $btn.data('name') ?? '';
        const value  = $btn.data('value') ?? '';
        const type   = String($btn.data('type') ?? '').toLowerCase(); // '1'/'2'/'products'/'categories'
        const start  = toDateForInput($btn.data('start_at'));
        const end    = toDateForInput($btn.data('end_at'));

        // normalize products/categories array from data-* (use attr to avoid jQuery caching)
            const products   = toItemsArray($btn.attr('data-products'));
        console.log($btn.attr('data-products'),products)
        const categories = toItemsArray($btn.attr('data-categories'));

        // fill
        $m.find('#showOfferName').val(name);
        $m.find('#showOfferValue').val(value);
        $m.find('#showStartDate').val(start);
        $m.find('#showEndDate').val(end);

        const isProducts   = (type === '2' || type === 'products' || type === 'product');
        const isCategories = (type === '1' || type === 'categories' || type === 'category');

        // set radios
        $m.find('#showApplyToProducts').prop('checked', isProducts);
        $m.find('#showApplyToCategories').prop('checked', isCategories);

        // show/hide sections + render chips
        $m.find('#showProductsWrap').toggleClass('d-none', !isProducts);
        $m.find('#showCategoriesWrap').toggleClass('d-none', !isCategories);

        renderChips($m.find('#showProducts'), products);
        renderChips($m.find('#showCategories'), categories);

        $m.modal('show');
    });

    function cleanPercent(val) {
        if (val == null) return '';
        let s = String(val).trim();

        // convert Arabic-Indic digits → Latin
        const arabicDigits = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
        s = s.replace(/[٠-٩]/g, d => arabicDigits[d] || d);

        // remove percent signs and spaces
        s = s.replace(/[%٪]/g, '').trim();

        // keep only digits, one dot/comma, and optional leading minus
        s = s.replace(/(?!^-)[^0-9.,]/g, '');

        // normalize comma to dot for decimals
        if (s.indexOf(',') !== -1 && s.indexOf('.') === -1) s = s.replace(',', '.');

        return s;
    }
// --- helpers ---
    function toIdArray(raw) {
        // Accept JSON string, array of IDs, or array of objects [{id:..}, {value:..}]
        if (raw == null) return [];
        if (typeof raw === 'string') {
            try { raw = JSON.parse(raw); } catch(e){ /* keep as-is */ }
        }
        if (!Array.isArray(raw)) return [];
        return raw.map(it => {
            if (typeof it === 'object' && it !== null) {
                return String(it.id ?? it.value ?? it);
            }
            return String(it);
        });
    }

    function selectValues($select, ids) {
        // Ensure every id has an <option>, otherwise Select2 won't show it as selected
        ids.forEach(id => {
            if ($select.find(`option[value="${id}"]`).length === 0) {
                $select.append(new Option(`#${id}`, id, false, false)); // temp label if missing
            }
        });
        $select.val(ids).trigger('change'); // Select2 refresh
    }

    $(document).on('click', '.edit-details', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const id       = $btn.data('id');
        const nameEn   = $btn.data('name_en') ?? '';
        const nameAr   = $btn.data('name_ar') ?? '';
        const value    = cleanPercent($btn.data('value') ?? '');
        const typeRaw  = String($btn.data('type') ?? '').toLowerCase(); // "1"/"2"/"products"/"categories"
        const startAt  = toDateForInput($btn.data('start_at'));
        const endAt    = toDateForInput($btn.data('end_at'));

        // read from attributes to avoid jQuery’s data cache + normalize to ID array (strings)
        const productIds   = toIdArray($btn.attr('data-products'));
        const categoryIds  = toIdArray($btn.attr('data-categories'));

        const $m = $('#editOfferModal');
        $('#editOfferForm').attr('action', '/offers/' + id);

        $m.find('#editOfferNameEn').val(nameEn);
        $m.find('#editOfferNameAr').val(nameAr);
        $m.find('#editOfferValue').val(value);
        $m.find('#editStartDate').val(startAt);
        $m.find('#editEndDate').val(endAt);

        const isProducts   = (typeRaw === '2' || typeRaw === 'products' || typeRaw === 'product');
        const isCategories = (typeRaw === '1' || typeRaw === 'categories' || typeRaw === 'category');

        $('#editApplyToProducts').prop('checked', isProducts);
        $('#editApplyToCategories').prop('checked', isCategories);

        if (isProducts) {
            $('.productsField').removeClass('d-none');
            $('.categoriesField').addClass('d-none');
            selectValues($('#editProductsSelect'), productIds);
            selectValues($('#editCategoriesSelect'), []); // clear other
        } else if (isCategories) {
            $('.categoriesField').removeClass('d-none');
            $('.productsField').addClass('d-none');
            selectValues($('#editCategoriesSelect'), categoryIds);
            selectValues($('#editProductsSelect'), []); // clear other
        } else {
            $('.productsField, .categoriesField').addClass('d-none');
            selectValues($('#editProductsSelect'), []);
            selectValues($('#editCategoriesSelect'), []);
        }

        $m.modal('show');
    });

    $('#editButton').on('click', function () {
        var nameEN = $('#tag-name-en').val();
        var nameAR = $('#tag-name-ar').val();
        var id = $('#tag-id').val();


        $('#edit-tag-name-en').val(nameEN);
        $('#edit-tag-name-ar').val(nameAR);
        $('#edit-tag-id').val(id);
        $('#editFlagModal').modal('show');

    });

    $('#editFlagForm').on('submit', function (e) {
        e.preventDefault();
        var tagId = $(this).find('#edit-tag-id').val();
        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');
        $.ajax({
            url: `flags/${tagId}`,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {

                Toastify({
                    text: "Flag updated successfully!",
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
                $('#editFlagModal').modal('hide');
                $('#showFlagModal').modal('hide');
                $('.offer-list-table').DataTable().ajax.reload(null, false);


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


    $(document).on("click", ".open-delete-offer-modal", function () {
        const OfferId = $(this).data("id");
        const OfferAction = $(this).data("action");
        $("#deleteOfferForm").data("id", OfferId).attr("action", OfferAction);

    });

    $(document).on('submit', '#deleteOfferForm', function (e) {
        e.preventDefault();
        const tagId = $(this).data("id");

        $.ajax({
            url: `/offers/${tagId}`,
            method: "DELETE",
            success: function (res) {
                $("#deleteOfferModal").modal("hide");

                Toastify({
                    text: "Offer deleted successfully!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                }).showToast();
                $(".offer-list-table").DataTable().ajax.reload(null, false);


            },
            error: function () {
                $("#deleteOfferModal    ").modal("hide");
                Toastify({
                    text: "Something Went Wrong!",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455", // red
                    close: true,
                }).showToast();
                $(".offer-list-table").DataTable().ajax.reload(null, false);

            },
        });
    });

// Open the bulk delete modal (only if at least one is selected)
    $(document).on('click', '#open-bulk-delete-modal', function () {
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Toastify({
                text: "Select at least one offer first.",
                duration: 2000, gravity: "top", position: "right",
                backgroundColor: "#EA5455", close: true
            }).showToast();
            return;
        }

        // show modal
        $('#deleteOffersModal').modal('show');
    });

// When user confirms in the modal, submit the hidden form
    $(document).on('click', '#confirm-bulk-delete', function () {
        $('#bulk-delete-form').trigger('submit');
    });

    $(document).on("submit", "#bulk-delete-form", function (e) {
        e.preventDefault();
        const selectedIds = $(".category-checkbox:checked").map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) return;

        $.ajax({
            url: "offers/bulk-delete",
            method: "POST",
            data: {
                ids: selectedIds,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $("#deleteOffersModal").modal("hide");
                Toastify({
                    text: "Selected offers deleted successfully!",
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
                $(".offer-list-table").DataTable().ajax.reload(null, false);

            },
            error: function () {
                $("#deleteOffersModal").modal("hide");
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
