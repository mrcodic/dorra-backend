$(function () {
  const select2 = $('.select2'),
    editUserForm = $('#editUserForm'),
    modalEditUserPhone = $('.phone-number-mask');

  // Select2 Country
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent()
      });
    });
  }

  // Phone Number Input Mask
  if (modalEditUserPhone.length) {
    modalEditUserPhone.each(function () {
      new Cleave($(this), {
        phone: true,
        phoneRegionCode: 'US'
      });
    });
  }

  // Edit user form validation
  if (editUserForm.length) {
    editUserForm.validate({
      rules: {
        modalEditUserFirstName: {
          required: true
        },
        modalEditUserLastName: {
          required: true
        },
        modalEditUserName: {
          required: true,
          minlength: 6,
          maxlength: 30
        }
      },
      messages: {
        modalEditUserName: {
          required: 'Please enter your username',
          minlength: 'The name must be more than 6 and less than 30 characters long',
          maxlength: 'The name must be more than 6 and less than 30 characters long'
        }
      }
    });
  }
});

$(document).ready(function (){
   $(document).on('submit','#editUserForm',function (e){
       e.preventDefault();
       var form = $(this);
       var actionUrl = form.attr('action');
       let formData = new FormData(form[0]);
       console.log(actionUrl)
      $.ajax({
          url:actionUrl,
          method: "POST",
          processData: false,
          contentType: false,
          data: formData,
              success: function (response) {
                  if (response.success) {
                      $('#editUser').modal('hide');
                      Toastify({
                          text: "User updated successfully!",
                          duration: 1500,
                          gravity: "top",
                          position: "right",
                          backgroundColor: "#28a745",
                          close: true
                      }).showToast();

                      setTimeout(function () {
                          location.reload();
                      }, 1600);
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
      })
   })

});
