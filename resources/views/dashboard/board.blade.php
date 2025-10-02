@extends('layouts/contentLayoutMaster')

@section('title', 'Kanban')

@section('vendor-style')
<!-- Vendor css files -->
<link rel="stylesheet" href="{{ asset(mix('vendors/css/jkanban/jkanban.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/pickers/flatpickr/flatpickr.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/editors/quill/katex.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/editors/quill/monokai-sublime.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/editors/quill/quill.snow.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/editors/quill/quill.bubble.css')) }}">
@endsection
@section('page-style')
<!-- Page css files -->
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/pickers/form-flat-pickr.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-quill-editor.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('css/base/pages/app-kanban.css')) }}">
@endsection

@section('content')
<!-- Kanban starts -->
<section>
    <div class="d-flex flex-wrap justify-content-between">
        <div class="d-flex flex-column align-items-center">
            <h5 style="color: #121212; font-size: 20px;">Prepress</h5>
            <hr>
            <div class="d-flex flex-column">
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-center">
            <h5 style="color: #121212; font-size: 20px;">Print</h5>
            <hr>
            <div class="d-flex flex-column">
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-center">
            <h5 style="color: #121212; font-size: 20px;">Finish</h5>
            <hr>
            <div class="d-flex flex-column">
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-center">
            <h5 style="color: #121212; font-size: 20px;">QC</h5>
            <hr>
            <div class="d-flex flex-column">
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column align-items-center">
            <h5 style="color: #121212; font-size: 20px;">Pack</h5>
            <hr>
            <div class="d-flex flex-column">
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
                <div class="card p-1">
                    <img src="{{asset('/images/item-photo.png')}}" alt="Item Photo" width="172px">
                    <p style="color: #424746; margin: 0">JT-20251001-83-242-01</p>
                    <hr>
                    <h5 style="color: #121212; font-size: 18px">Item name</h5>
                    <div class="d-flex gap-1">
                        <span class="rounded-3"
                            style="color: #424746; background-color: #CED5D4; padding: 7px">Waiting</span>
                        <span class="rounded-3"
                            style="color: white; background-color: #F8AB1B; padding: 7px">Standard</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Kanban ends -->
@endsection

@section('vendor-script')
<!-- Vendor js files -->
<script src="{{ asset(mix('vendors/js/jkanban/jkanban.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/pickers/flatpickr/flatpickr.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/editors/quill/katex.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/editors/quill/highlight.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/editors/quill/quill.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
@endsection
@section('page-script')
<!-- Page js files -->
<script src="{{ asset('js/scripts/pages/app-kanban.js') }}?v={{ time() }}"></script>
@endsection