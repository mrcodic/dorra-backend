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


              const $submitBtn = $form.find('button[type="submit"]');
              const $loader = $form.find('.spinner-border');

              $submitBtn.prop('disabled', true);
              $loader.removeClass('d-none');

              const formData = new FormData(this);
              // Debug log (optional)
              // for (let pair of formData.entries()) console.log(pair[0]+ ':', pair[1]);

              $.ajax({
                  url: $form.attr('action'),
                  method: $form.attr('method') || 'POST',
                  data: formData,
                  contentType: false,
                  processData: false,
                  headers: {
                      'X-CSRF-TOKEN': $('input[name="_token"]').val()
                  },
                  success: function (response) {
                      Toastify({
                          text: options.successMessage || "✅ Operation successful!",
                          duration: 3000,
                          gravity: "top",
                          backgroundColor: "#28a745",
                      }).showToast();

                      if (options.onSuccess) options.onSuccess(response, $form);
                      if (options.resetForm !== false) $form.trigger('reset');
                      if (options.closeModal) $(options.closeModal).modal('hide');
                  },
                  error: function (xhr) {
                      if (xhr.responseJSON && xhr.responseJSON.errors) {
                          const errors = xhr.responseJSON.errors;
                          for (const key in errors) {
                              Toastify({
                                  text: errors[key][0],
                                  duration: 4000,
                                  gravity: "top",
                                  position: "right",
                                  backgroundColor: "#EA5455",
                                  close: true,
                              }).showToast();
                          }
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
      
  </script>

</body>

</html>
