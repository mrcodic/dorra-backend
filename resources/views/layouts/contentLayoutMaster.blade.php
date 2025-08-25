@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset

<!DOCTYPE html>
@php
$configData = Helper::applClasses();
@endphp

<html class="loading {{ $configData['theme'] === 'light' ? '' : $configData['layoutTheme'] }}"
    lang="@if (session()->has('locale')){{ session()->get('locale') }}@else{{ $configData['defaultLanguage'] }}@endif"
    data-textdirection="{{ env('MIX_CONTENT_DIRECTION') === 'rtl' ? 'rtl' : 'ltr' }}" @if ($configData['theme']==='dark'
    ) data-layout="dark-layout" @endif>

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
    <title>@yield('title') - Dorra Dashboard</title>
    <link rel="apple-touch-icon" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600"
        rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>


    <!-- jQuery Validation -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    {{-- Include core + vendor Styles --}}

    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" />
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    @include('panels/styles')
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/style.css') }} ?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/file-uploaders/dropzone.min.css') }} ?v={{ time() }}">

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->
@isset($configData['mainLayoutType'])
@extends((( $configData["mainLayoutType"] === 'horizontal') ? 'layouts.horizontalLayoutMaster' :
'layouts.verticalLayoutMaster' ))
@endisset
<script src="https://unpkg.com/feather-icons"></script>
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


    function setupClearInput(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);

        if (input && button) {
            button.addEventListener('click', function () {
                if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else {
                    input.value = '';
                }

                input.dispatchEvent(new Event('change'));

                // Submit the form that contains the input
                const form = input.closest('form');
                if (form) {
                    form.submit(); // <-- submit the form to reload data
                }
            });
        }
    }

</script>


@stack('scripts')
