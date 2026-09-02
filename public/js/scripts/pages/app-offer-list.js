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
            data: null,
            defaultContent: "",
            orderable: false,

            render: function (data, type, row) {
                return row?.action?.can_delete
                    ? `<input type="checkbox" name="ids[]" class="category-checkbox" value="${row.id}">`
                    : '';
            }
        },

        {
            data: 'name',
            orderable: false
        },

        {
            data: 'type',
            orderable: false,

            render: function (data, type, row) {
                return data?.label ?? '-';
            }
        },

        {
            data: 'value',
            orderable: false
        },

        {
            data: 'start_at',
            orderable: false
        },

        {
            data: 'end_at',
            orderable: false
        },

        {
            data: 'id',
            orderable: false,

            render: function (data, type, row, meta) {
                const canShow =
                    row?.action?.can_show ?? false;

                const canEdit =
                    row?.action?.can_edit ?? false;

                const canDelete =
                    row?.action?.can_delete ?? false;

                const btns = [];


                if (canShow) {
                    btns.push(`
                        <a
                            href="#"
                            class="view-details"
                            data-bs-toggle="modal"
                            data-bs-target="#showOfferModal"

                            data-id="${data}"
                            data-name="${row.name}"
                            data-value="${row.value}"
                            data-type="${row.type.value}"
                            data-start_at="${row.start_at}"
                            data-end_at="${row.end_at}"

                            data-products='${JSON.stringify(row.products || [])}'
                            data-categories='${JSON.stringify(row.categories || [])}'
                        >
                            <i data-feather="eye"></i>
                        </a>
                    `);
                }


                if (canEdit) {
                    btns.push(`
                        <a
                            href="#"
                            class="edit-details"
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
                    `);
                }


                if (canDelete) {
                    btns.push(`
                        <a
                            href="#"
                            class="text-danger open-delete-offer-modal"
                            data-id="${data}"
                            data-action="/offers/${data}"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteOfferModal"
                        >
                            <i data-feather="trash-2"></i>
                        </a>
                    `);
                }


                if (!btns.length) {
                    return '';
                }

                return `
                    <div class="d-flex gap-1 align-items-center">
                        ${btns.join('')}
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
        '<"d-flex mx-2 row mb-1"' +
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


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$('#clear-search').on('click', function () {
    $('#search-offer-form').val('');

    dt_user_table
        .search('')
        .draw();
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

    const saveButton =
        $('.saveChangesButton');

    const saveLoader =
        $('.saveLoader');

    const saveButtonText =
        $('.saveChangesButton .btn-text');


    /*
    |--------------------------------------------------------------------------
    | Add Flag
    |--------------------------------------------------------------------------
    */

    $('#addFlagForm').on('submit', function (e) {
        e.preventDefault();

        saveButton.prop('disabled', true);
        saveLoader.removeClass('d-none');
        saveButtonText.addClass('d-none');

        const formData =
            new FormData(this);


        $.ajax({
            url: $(this).attr('action'),
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

                saveLoader
                    .addClass('d-none');

                saveButtonText
                    .removeClass('d-none');


                $('#addFlagForm')[0].reset();

                $('#addFlagModal')
                    .modal('hide');


                location.reload();


                $('.offer-list-table')
                    .DataTable()
                    .ajax
                    .reload();
            },


            error: function (xhr) {

                const errors =
                    xhr.responseJSON?.errors || {};


                for (const key in errors) {

                    if (
                        Object.prototype
                            .hasOwnProperty
                            .call(errors, key)
                    ) {
                        Toastify({
                            text:
                                errors[key][0],

                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true
                        }).showToast();
                    }
                }


                saveButton.prop(
                    'disabled',
                    false
                );


                saveLoader
                    .addClass('d-none');


                saveButtonText
                    .removeClass('d-none');
            }
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Date Helper
    |--------------------------------------------------------------------------
    */

    function toDateForInput(s) {
        if (!s) {
            return '';
        }


        s =
            String(s)
                .trim();


        if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
            return s;
        }


        const m =
            s.match(
                /^(\d{2})\/(\d{2})\/(\d{4})$/
            );


        if (m) {
            return `${m[3]}-${m[2]}-${m[1]}`;
        }


        if (
            s.includes(' ') ||
            s.includes('T')
        ) {
            return s
                .split('T')[0]
                .split(' ')[0];
        }


        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | Items Helpers
    |--------------------------------------------------------------------------
    */

    function toItemsArray(raw) {

        if (raw == null) {
            return [];
        }


        if (typeof raw === 'string') {

            try {
                raw =
                    JSON.parse(raw);

            } catch (_) {
                // keep raw
            }
        }


        if (!Array.isArray(raw)) {
            return [];
        }


        return raw.map((it) => {

            const id =
                String(
                    it &&
                    (
                        it.id ??
                        it.value ??
                        it
                    )
                );


            let name =
                it &&
                (
                    it.name ??
                    it.title ??
                    it.label ??
                    null
                );


            if (
                name &&
                typeof name === 'object'
            ) {
                name =
                    name.en ||
                    name.ar ||
                    Object.values(name)[0] ||
                    `#${id}`;
            }


            if (!name) {
                name =
                    `#${id}`;
            }


            return {
                id,
                name: String(name)
            };
        });
    }


    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    function renderChips(
        $container,
        items
    ) {

        if (
            !items ||
            !items.length
        ) {
            $container.html(
                '<span class="text-muted">— none —</span>'
            );

            return;
        }


        const html =
            items.map(it => `
                <span class="badge rounded-pill bg-light border text-dark me-1 mb-1">
                    ${escapeHtml(it.name)}
                </span>
            `).join('');


        $container.html(html);
    }


    /*
    |--------------------------------------------------------------------------
    | Show Offer
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.view-details',
        function (e) {

            e.preventDefault();


            const $btn =
                $(this);

            const $m =
                $('#showOfferModal');


            const name =
                $btn.data('name') ?? '';

            const value =
                $btn.data('value') ?? '';

            const type =
                String(
                    $btn.data('type') ?? ''
                ).toLowerCase();

            const start =
                toDateForInput(
                    $btn.data('start_at')
                );

            const end =
                toDateForInput(
                    $btn.data('end_at')
                );


            const products =
                toItemsArray(
                    $btn.attr(
                        'data-products'
                    )
                );


            const categories =
                toItemsArray(
                    $btn.attr(
                        'data-categories'
                    )
                );


            $m
                .find('#showOfferName')
                .val(name);


            $m
                .find('#showOfferValue')
                .val(value);


            $m
                .find('#showStartDate')
                .val(start);


            $m
                .find('#showEndDate')
                .val(end);


            const isProducts =
                (
                    type === '1' ||
                    type === 'products' ||
                    type === 'product'
                );


            const isCategories =
                (
                    type === '2' ||
                    type === 'categories' ||
                    type === 'category'
                );


            $m
                .find('#showApplyToProducts')
                .prop(
                    'checked',
                    isProducts
                );


            $m
                .find('#showApplyToCategories')
                .prop(
                    'checked',
                    isCategories
                );


            $m
                .find('#showProductsWrap')
                .toggleClass(
                    'd-none',
                    !isProducts
                );


            $m
                .find('#showCategoriesWrap')
                .toggleClass(
                    'd-none',
                    !isCategories
                );


            renderChips(
                $m.find(
                    '#showProducts'
                ),
                categories
            );


            renderChips(
                $m.find(
                    '#showCategories'
                ),
                products
            );


            $m.modal('show');
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Percent Helper
    |--------------------------------------------------------------------------
    */

    function cleanPercent(val) {

        if (val == null) {
            return '';
        }


        let s =
            String(val)
                .trim();


        const arabicDigits = {
            '٠': '0',
            '١': '1',
            '٢': '2',
            '٣': '3',
            '٤': '4',
            '٥': '5',
            '٦': '6',
            '٧': '7',
            '٨': '8',
            '٩': '9'
        };


        s =
            s.replace(
                /[٠-٩]/g,
                d =>
                    arabicDigits[d] || d
            );


        s =
            s.replace(
                /[%٪]/g,
                ''
            ).trim();


        s =
            s.replace(
                /(?!^-)[^0-9.,]/g,
                ''
            );


        if (
            s.indexOf(',') !== -1 &&
            s.indexOf('.') === -1
        ) {
            s =
                s.replace(
                    ',',
                    '.'
                );
        }


        return s;
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    |
    | Edit Offer is handled inside:
    |
    | resources/views/modals/offers/edit-offer.blade.php
    |
    | DO NOT add another .edit-details handler here.
    |
    | Having two handlers causes the new category/product fields
    | to be overwritten by the old edit logic.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Delete Offer
    |--------------------------------------------------------------------------
    */

    $(document).on(
        "click",
        ".open-delete-offer-modal",
        function () {

            const OfferId =
                $(this).data("id");

            const OfferAction =
                $(this).data("action");


            $("#deleteOfferForm")
                .data(
                    "id",
                    OfferId
                )
                .attr(
                    "action",
                    OfferAction
                );
        }
    );


    $(document).on(
        'submit',
        '#deleteOfferForm',
        function (e) {

            e.preventDefault();


            const tagId =
                $(this).data("id");


            $.ajax({
                url:
                    `/offers/${tagId}`,

                method:
                    "DELETE",


                success: function (res) {

                    $("#deleteOfferModal")
                        .modal("hide");


                    Toastify({
                        text:
                            "Offer deleted successfully!",

                        duration:
                            4000,

                        gravity:
                            "top",

                        position:
                            "right",

                        backgroundColor:
                            "#28C76F",

                        close:
                            true
                    }).showToast();


                    $(".offer-list-table")
                        .DataTable()
                        .ajax
                        .reload(
                            null,
                            false
                        );
                },


                error: function () {

                    $("#deleteOfferModal")
                        .modal("hide");


                    Toastify({
                        text:
                            "Something Went Wrong!",

                        duration:
                            4000,

                        gravity:
                            "top",

                        position:
                            "right",

                        backgroundColor:
                            "#EA5455",

                        close:
                            true
                    }).showToast();


                    $(".offer-list-table")
                        .DataTable()
                        .ajax
                        .reload(
                            null,
                            false
                        );
                }
            });
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '#open-bulk-delete-modal',
        function () {

            const selectedIds =
                $(".category-checkbox:checked")
                    .map(function () {
                        return $(this).val();
                    })
                    .get();


            if (
                selectedIds.length === 0
            ) {
                Toastify({
                    text:
                        "Select at least one offer first.",

                    duration:
                        2000,

                    gravity:
                        "top",

                    position:
                        "right",

                    backgroundColor:
                        "#EA5455",

                    close:
                        true

                }).showToast();


                return;
            }


            $('#deleteOffersModal')
                .modal('show');
        }
    );


    $(document).on(
        'click',
        '#confirm-bulk-delete',
        function () {

            $('#bulk-delete-form')
                .trigger('submit');
        }
    );


    $(document).on(
        "submit",
        "#bulk-delete-form",
        function (e) {

            e.preventDefault();


            const selectedIds =
                $(".category-checkbox:checked")
                    .map(function () {
                        return $(this).val();
                    })
                    .get();


            if (
                selectedIds.length === 0
            ) {
                return;
            }


            $.ajax({
                url:
                    "offers/bulk-delete",

                method:
                    "POST",

                data: {
                    ids:
                    selectedIds,

                    _token:
                        $('meta[name="csrf-token"]')
                            .attr("content")
                },


                success: function (response) {

                    $("#deleteOffersModal")
                        .modal("hide");


                    Toastify({
                        text:
                            "Selected offers deleted successfully!",

                        duration:
                            1500,

                        gravity:
                            "top",

                        position:
                            "right",

                        backgroundColor:
                            "#28a745",

                        close:
                            true

                    }).showToast();


                    $('#bulk-delete-container')
                        .hide();


                    $('.category-checkbox')
                        .prop(
                            'checked',
                            false
                        );


                    $('#select-all-checkbox')
                        .prop(
                            'checked',
                            false
                        );


                    $(".offer-list-table")
                        .DataTable()
                        .ajax
                        .reload(
                            null,
                            false
                        );
                },


                error: function () {

                    $("#deleteOffersModal")
                        .modal("hide");


                    Toastify({
                        text:
                            "Something Went Wrong!",

                        duration:
                            1500,

                        gravity:
                            "top",

                        position:
                            "right",

                        backgroundColor:
                            "#EA5455",

                        close:
                            true

                    }).showToast();


                    $('#bulk-delete-container')
                        .hide();


                    $('.category-checkbox')
                        .prop(
                            'checked',
                            false
                        );


                    $('#select-all-checkbox')
                        .prop(
                            'checked',
                            false
                        );


                    $(".offer-list-table")
                        .DataTable()
                        .ajax
                        .reload(
                            null,
                            false
                        );
                }
            });
        }
    );

});
