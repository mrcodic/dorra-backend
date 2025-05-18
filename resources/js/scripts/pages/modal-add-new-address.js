$(function () {
  ('use strict');

  var addNewAddressForm = $('#addNewAddressForm'),
    modalAddressCountry = $('#modalAddressCountry');

  // --- add new address ----- //

  // Select2 initialization
  if (modalAddressCountry.length) {
    modalAddressCountry.wrap('<div class="position-relative"></div>').select2({
      dropdownParent: modalAddressCountry.parent()
    });
  }

  // add new address validation
  if (addNewAddressForm.length) {
    addNewAddressForm.validate({
      rules: {
        modalAddressFirstName: {
          required: true
        },
        modalAddressLastName: {
          required: true
        }
      }
    });
  }
  // --- / add new address ----- //
});


$(document).ready(function () {
    $(document).on('change', '.country-select', function () {
        const countryId = $(this).val();
        const baseUrl = $('#state-url').data('url');
        const stateSelect = $('.state-select');
        if (countryId) {
            $.ajax({
                url: `${baseUrl}?filter[country_id]=${countryId}`,
                method: 'GET',
                success: function (response) {
                    console.log(response)
                    stateSelect.empty().append('<option value="">Select a State</option>');
                    $.each(response.data, function (index, state) {
                        stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
                    });
                },
                error: function () {
                    stateSelect.empty().append('<option value="">Error loading states</option>');
                }
            });
        } else {
            stateSelect.empty().append('<option value="">Select a State</option>');
        }
    });

    $(document).on('submit','#addNewAddressForm',function (e){
        var actionUrl = form.attr('action');
        e.preventDefault();
        $.ajax({
            url: actionUrl,
            method: 'PUT',
            success: function (response) {
                console.log(response)

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

        })
    });

});
