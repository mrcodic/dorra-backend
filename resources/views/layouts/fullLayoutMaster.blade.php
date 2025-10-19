@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

<!DOCTYPE html>
@php $configData = Helper::applClasses(); @endphp

<html class="loading {{ $configData['theme'] === 'light' ? '' : $configData['layoutTheme'] }}"
  lang="@if (session( )->has('locale')){{ session()->get('locale') }}@else{{ $configData['defaultLanguage'] }}@endif" data-textdirection="{{ env('MIX_CONTENT_DIRECTION') === 'rtl' ? 'rtl' : 'ltr' }}"
  @if ($configData['theme'] === 'dark') data-layout="dark-layout"@endif>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta name="description"
    content="Vuexy admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
  <meta name="keywords"
    content="admin template, Vuexy admin template, dashboard template, flat admin template, responsive admin template, web app">
  <meta name="author" content="PIXINVENT">
  <title>@yield('title') - Dorra</title>
  <link rel="apple-touch-icon" href="{{ asset('images/favicon-32x32.png') }}">
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600"
    rel="stylesheet">

  {{-- Include core + vendor Styles --}}
  @include('panels/styles')

  {{-- Include core + vendor Styles --}}
  @include('panels/styles')
</head>



<body
  class="vertical-layout vertical-menu-modern {{ $configData['bodyClass'] }} {{ $configData['theme'] === 'dark' ? 'dark-layout' : '' }} {{ $configData['blankPageClass'] }} blank-page"
  data-menu="vertical-menu-modern" data-col="blank-page" data-framework="laravel"
  data-asset-path="{{ asset('/') }}">

  <!-- BEGIN: Content-->
  <div class="app-content content {{ $configData['pageClass'] }}">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    <div class="content-wrapper">
      <div class="content-body bg-white">

        {{-- Include Startkit Content --}}
        @yield('content')

      </div>
    </div>
  </div>
  <!-- End: Content-->

  {{-- include default scripts --}}
  @include('panels/scripts')


  <script type="text/javascript">
    $(window).on('load', function() {
      if (feather) {
        feather.replace({
          width: 14,
          height: 14
        });
      }
    })
  </script>
  <script !src="">
      function handleAjaxFormSubmit(formSelector, options = {}) {
          $(document).on('submit', formSelector, function (e) {
              e.preventDefault();
              const $form = $(this);

              // Clear previous errors (if you added placeholders)
              $form.find('.is-invalid').removeClass('is-invalid');
              $form.find('[data-error-for]').text('');

              const $submitBtn = $form.find('button[type="submit"]');
              const $loader = $form.find('.spinner-border');

              $submitBtn.prop('disabled', true);
              $loader.removeClass('d-none');

              const formData = new FormData(this);

              $.ajax({
                  url: $form.attr('action'),
                  method: $form.attr('method') || 'POST',
                  data: formData,
                  contentType: false,
                  processData: false,
                  headers: {
                      'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                      'Accept': 'application/json' // 👈 important for Fortify
                  },
                  success: function (response) {
                      // If your Fortify response is JSON with redirect, follow it
                      if (response && response.redirect) {
                          window.location.href = response.redirect;
                          return;
                      }

                      // Otherwise show toast & optionally reset
                      if (window.Toastify) {
                          Toastify({
                              text: options.successMessage || "✅ Operation successful!",
                              duration: 3000,
                              gravity: "top",
                              backgroundColor: "#28a745",
                          }).showToast();
                      }

                      if (options.onSuccess) options.onSuccess(response, $form);
                      if (options.resetForm !== false) $form.trigger('reset');
                      if (options.closeModal) $(options.closeModal).modal('hide');
                  },
                  error: function (xhr) {
                      // 419 = CSRF, 401 = unauthorized, 422 = validation
                      if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                          const errors = xhr.responseJSON.errors;

                          // Paint per-field errors into data-error-for="field"
                          Object.keys(errors).forEach(key => {
                              const msg = errors[key][0];

                              const $slot = $form.find(`[data-error-for="${key}"]`);
                              if ($slot.length) $slot.text(msg);

                              const $input = $form.find(`[name="${key}"]`);
                              if ($input.length) $input.addClass('is-invalid');

                              if (window.Toastify) {
                                  Toastify({
                                      text: msg,
                                      duration: 4000,
                                      gravity: "top",
                                      position: "right",
                                      backgroundColor: "#EA5455",
                                      close: true,
                                  }).showToast();
                              }
                          });

                          // If Fortify put the generic error under email, also show it globally
                          if (!errors.general && errors.email) {
                              $form.find('[data-error-for="general"]').text(errors.email[0]);
                          }
                      } else {
                          // non-validation errors
                          $form.find('[data-error-for="general"]').text('Something went wrong. Please try again.');
                      }

                      if (options.onError) options.onError(xhr, $form);
                  },
                  complete: function () {
                      $submitBtn.prop('disabled', false);
                      $loader.addClass('d-none');
                  }
              });
          });
      }
  
          handleAjaxFormSubmit(".auth-login-form")


  </script>

</body>

</html>
