$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var dt_user_table = $('.user-list-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: usersDataUrl,
        type: 'GET'
    },
    columns: [
        { data: null, defaultContent: '', orderable: false },
        { data: 'name' },
        { data: 'email' },
        { data: 'status' },
        { data: 'joined_date' },
        { data: 'orders_count' },
        {
            data: 'id',
            orderable: false,
            render: function (data, type, row, meta) {
                console.log(data)
                return `
          <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
              <i data-feather="more-vertical"></i>
            </button>
            <div class="dropdown-menu">
             <a href="/users/${data}" class="dropdown-item">
                <i data-feather="file-text"></i> Details
              </a>


              <a href="#" class="dropdown-item text-danger delete-user" data-id="${data}">
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
            text: feather.icons['external-link'].toSvg({ class: 'font-small-4 me-50' }) + 'Export',
            buttons: [
                {
                    extend: 'print',
                    text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'csv',
                    text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'excel',
                    text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'pdf',
                    text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
                },
                {
                    extend: 'copy',
                    text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
                    className: 'dropdown-item',
                    exportOptions: { columns: [1, 2, 3, 4, 5] }
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
            text: 'Add New User',
            className: 'add-new btn btn-primary',
            attr: {
                'data-bs-toggle': 'modal',
                'data-bs-target': '#modals-slide-in'
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
        $('.add-new-user').submit(function (event) {
            event.preventDefault();

            var form = $(this);
            var isChecked = $('#status').is(':checked')
            var status = isChecked ?1 :0;
            form.find('input[name="status"]').remove();
            form.append('<input type="hidden" name="status" value="' + status + '">');

            var selectedOption = $('#phone-code option:selected');
            var phoneCode = selectedOption.data('phone-code');
            var phoneNumber = $('#phone_number').val();
            var fullPhoneNumber =  phoneCode + phoneNumber.replace(/\D/g, '');
            $('#full_phone_number').val(fullPhoneNumber);

            var actionUrl = form.attr('action');

            $('.alert-danger').remove();
            let formData = new FormData(form[0]);
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response)
                    if (response.success) {
                        form[0].reset();
                        $('#modals-slide-in').modal('hide');
                        Toastify({
                            text: "User added successfully!",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true
                        }).showToast();


                    }
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
                                backgroundColor: "#EA5455", // red for errors
                                close: true
                            }).showToast();
                        }
                    }
                }

            });
        });
    });


    $(document).on('change','.country-select',function (){
        const countryId = $(this).val();
        const stateSelect = $(this).closest('[data-repeater-item]').find('.state-select');
        const baseUrl = $('#state-url').data('url');
        if (countryId)
        {
            $.ajax({
                url:`${baseUrl}?filter[country_id]=${countryId}`,
                method: "GET",
                success: function (response) {
                    stateSelect.empty().append('<option value="">Select State</option>');
                    $.each(response.data, function (index, state) {
                        stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
                    });
                },
                error: function () {
                    stateSelect.empty().append('<option value="">Error loading states</option>');
                }
            })

        }else {
            stateSelect.empty().append('<option value="">Select State</option>');
        }
    })


    $(document).on('click', '.delete-user', function (e) {
        e.preventDefault();

        var $table = $('.user-list-table').DataTable();
        var $row = $(this).closest('tr');
        var rowData = $table.row($row).data();

        var userId = $(this).data('id');
        var userName = rowData.name;

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to delete user "${userName}". This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/users/${userId}`,
                    method: 'DELETE',
                    success: function (res) {
                        Swal.fire('Deleted!', 'User has been deleted.', 'success');
                        $table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire('Failed', 'Could not delete user.', 'error');
                    }
                });
            }
        });
    });





});
